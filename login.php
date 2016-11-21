<?php

/*  This page could forward with header function.
    In this case, we are not allowed to print anything before.
*/

$head = "
<!DOCTYPE html> 
<html lang='fr' >
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
        <meta name='description' lang='fr' content='Module de gestion des parties OrcIdee'>
        <meta name='keywords' lang='fr' content='orcidee'>
		<link rel='stylesheet' type='text/css' href='css/jquery-ui.css' />
        <link rel='stylesheet' type='text/css' href='css/styles.css' />
		<link rel='stylesheet' type='text/css' href='css/2017.css' />
		
    </head>
    <body>
";
require_once(dirname(__FILE__).'/conf/conf.php');

$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());;
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/classes/controls.php');
require_once(dirname(__FILE__).'/classes/user.php');
require_once(dirname(__FILE__).'/classes/view.php');

if(!$db){
    echo "<p class='dbg'>Impossible de selectionner la base de donnees</p>";
}else{

    $user = User::getFromSession();

    include "menu.php";
    
    if($user && $user->getRole() != "player") {
        
        // Vous êtes déjà authentifié
        echo $head;
        ?>
        <h1>Authentification</h1>
        <div class='login'>
            <p>Bravo, vous êtes authentifié</p>
        </div>
        <?php
        
    } else {

        $res = null;
        
        // Processus d'authentification
        if(@$_POST['action'] == 'auth'){
            $res = User::auth($_POST["email"], $_POST["password"]);
            if($res['status'] === 0){
                // Authentification échouée, credentials non valides
                $msg = "Désolé, mot de passe et/ou adresse email incorrect(e)(s).";
                unset($_SESSION["userId"]);
            }elseif($res['status'] === 1){
                // Authentification échouée car email pas valide
                $msg = "Votre adresse email n'est pas une adresse email valide !";
                unset($_SESSION["userId"]);
            }elseif($res['status'] === 2){
                // Authentification réussie
                $_SESSION["userId"] = $res['userId'];
                $user = User::getFromSession();
            }
        }
        
        // Processus d'enregistrement
        if(@$_POST['action'] == 'register'){
            if(strlen(@$_POST['lastname']) <= 0){
                $msg["lastname"] = "Merci de nous indiquer votre nom.";
            }
            if(strlen(@$_POST['firstname']) <= 0){
                $msg["firstname"] = "Merci de nous indiquer votre prénom.";
            }
            if(strlen(@$_POST['phone']) <= 0){
                $msg["phone"] = "Désolé de vous demander ça, mais nous devons pouvoir contacter les MJ par téléphone. Merci de bien vouloir saisir ce champ.";
            }
            if(strlen(@$_POST['password']) <= 0){
                $msg["password"] = "Vous devez saisir un mot de passe.";
            }elseif( ! User::validatePassword(@$_POST['password'])){
                $msg["password"] = "Votre mot de passe est trop faible, il lui faut au moins 5 caractères.";
            }
            if(@$_POST['password'] != @$_POST['confirm']){
                $msg["confirm"] = "La confirmation de mot de passe que saisie ne correspond pas.";
            }
            if(strlen(@$_POST['email']) <= 0){
                $msg["email"] = "Merci de nous indiquer votre adresse email.";
            }elseif( ! Controls::validateEmail(@$_POST['email'])){
                $msg["email"] = "Bein ça alors?! Votre adresse email n'est pas valide.";
            }elseif(User::emailExists(@$_POST['email'])){
                
                // Upgrade Player => MJ
                $testUser = User::pseudoAuth($_POST['email']);
                if($testUser->getRole() == 'player'){
                    if(!isset($msg) || count($msg) == 0){
                        $res = User::upgradeToMJ($testUser, $_POST);
                        $user = $res['user'];
                        if($user){
                            $_SESSION["userId"] = $user->getId();
                        }else{
                            $msg['unkown'] = "Une erreur s'est produite lors de la sauvegarde des données. Vous êtes toujours inscrit comme 'joueur'.";
                        }
                        $msg["email"] = 'Vous êtiez inscrit comme "joueur", vous êtes désormais "animateur". Félicitations!';
                    }
                }else{
                    $msg["email"] = "Cette adresse email est déjà enregistrée. <a href='pwd-forgotten.php'>Cliquez ici si vous avez oublié votre mot de passe</a>.";
                }
            }
            
            if(!isset($msg) || count($msg) == 0){
				$user = User::registerMJ($_POST);
				if($user){
					$_SESSION["userId"] = $user->getId();
				}else{
					$msg['unkown'] = "Une erreur s'est produite lors de la sauvegarde des données. Vous n'avez pas été enregistré.";
				}
            }
        }
        
        // Si l'authentification ou l'enregistrement ont échoués, quel qu'en soit la raison.
        if(!$user || $user->getRole() == "player"){
			$email = $user ? $user->getEmail() : @$_POST['email'];
            echo $head;
            ?>
            <h1>Authentification</h1>
            <div class='login'>
                <p>Seuls votre nom et votre prénom seront affichés sur le site. Les autres informations seront gardées confidentielles et utilisées uniquement par des membres de l'association Orc'idee.</p>
                <div class="left">
                
                    <?php
                    if (@$_POST['action'] == 'auth'){
                        echo "<p class='auth-result'>$msg</p>";
                    }else{
                        echo "<p>Vous avez déjà un compte et souhaitez vous authentifier...</p>";
                    }
                    ?>
                    
                    <form action='' method='POST'>
                        
                        <label for='email'>Email *</label>
                        <input type='text' name='email' value='<?php echo $email; ?>' />
                        
                        <label for='password'>Mot de passe *</label>
                        <input type='password' name='password' value='' />
                        
                        <input type='submit' value="S'authentifier" class='submit' />
                        <input type='hidden' name='action' value='auth' />
                        <input type='hidden' name="forward" value="<?php echo @$_REQUEST['forward']; ?>" />
                        
                    </form>
                    
                    <div class="forgottenPwd"><a href="pwd-forgotten.php">Mot de passe oublié</a></div>
                    
                    <div class="msg">
                        <?php if((@$_POST['action'] == 'auth') && ($res === 0 || $res === 1)){
                            echo "<p>$msg</p>";
                        }?>
                    </div>
                    
                </div>
                
                
                <?php if (Controls::isMjOpen()){ ?>
                    
                    <div class="right">
                    
                        <?php
                        if (@$_POST['action'] == 'register'){
                            echo "<div class='msg register-result'><ul>";
                            foreach($msg as $key => $v){
                                echo "<li>$v</li>";
                            }
                            echo "</ul></div>";
                        }else{
                            echo "<p>Vous souhaitez vous enregistrer comme Animateur / MJ, afin de pouvoir proposer des parties ou des activités...</p>";
                        }
                        ?>
                        <form action='' method='POST'>
                            
                            
                            <fieldset>
                    
                                <legend>Champs obligatoires</legend>
                                
                                <label for='lastname'>Nom *</label>
                                <input type='text' name='lastname' value='<?php echo @$_POST['lastname']; ?>' />
                                
                                <label for='firstname'>Prénom *</label>
                                <input type='text' name='firstname' value='<?php echo @$_POST['firstname']; ?>' />
                                
                                <label for='email'>Email *</label>
                                <input type='text' name='email' value='<?php echo $email; ?>' />
                                
                                <label for='password'>Mot de passe *</label>
                                <input type='password' name='password' value='' />
                                
                                <label for='confirm'>Confirmation MdP *</label>
                                <input type='password' name='confirm' value='' />
                                
                                <label for='phone'>Téléphone *</label>
                                <input type='text' name='phone' value='<?php echo @$_POST['phone']; ?>' />
                                
                            </fieldset>
                            
                            <fieldset>
                                <legend>Champs facultatifs</legend>
                                
                                <label for='address'>Adresse</label>
                                <input type='text' name='address' value='<?php echo @$_POST['address']; ?>' />
                                
                                <label for='npa'>NPA</label>
                                <input type='text' name='npa' value='<?php echo @$_POST['npa']; ?>' />
                                
                                <label for='city'>Ville</label>
                                <input type='text' name='city' value='<?php echo @$_POST['city']; ?>' />
                                
                                <label for='country'>Pays</label>
                                <input type='text' name='country' value='<?php echo @$_POST['country']; ?>' />
                            </fieldset>
                            
                            <input type='submit' value="S'enregistrer" class='submit' />
                            <input type='hidden' name="forward" value="<?php echo @$_REQUEST['forward']; ?>" />
                            <input type='hidden' name='action' value='register' />
                            
                        </form>
                    </div>
                
                <?php } ?>
            </div><?php
        }else{
            // Authentification ou enregistrement réussi.
            if(isset($_REQUEST['forward']) && strlen($_REQUEST['forward']) > 0){
                header("Location:".Controls::home()."?".urldecode($_REQUEST['forward']));
            } else {
                header("Location:".Controls::currentURI());
            }
        }
    }
}
mysql_close($dbServer);

include("scripts.php");

?>

    </body>
</html>
