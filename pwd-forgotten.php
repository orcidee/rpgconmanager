<?php

/*  This page could forward with header function.
    In this case, we are not allowed to print anything before.
*/

$head = "
<!DOCTYPE html> 
<html lang='fr' >
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
        <meta name='description' lang='fr' content='Module de gestion des parties Orc'Idee'>
        <meta name='keywords' lang='fr' content='orcidee'>
		<link rel='stylesheet' type='text/css' href='css/jquery-ui.css' />
        <link rel='stylesheet' type='text/css' href='css/styles.css' />
		<link rel='stylesheet' type='text/css' href='css/2012.css' />
		
    </head>
    <body>
";

require_once(dirname(__FILE__).'/conf/bd.php');
$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());;
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/conf/conf.php');
require_once(dirname(__FILE__).'/classes/controls.php');
require_once(dirname(__FILE__).'/classes/user.php');
require_once(dirname(__FILE__).'/classes/orcimail.php');

if(!$db){
    echo "<p class='dbg'>Impossible de selectionner la base de donnees</p>";
}else{

    echo $head;
    
    $user = User::getFromSession();
    if($user) {
        
        // Vous êtes déjà authentifié
        echo $head;
        ?>
        <h1>Authentification</h1>
        <div class='login'>
            <p>Tu es déjà authentifié</p>
            <ul>
                <li><a href="<?php echo Controls::home();?>?page=profile">Voir le profil</a></li>
                <li><a href="<?php echo Controls::home();?>?page=logout">Se déconnecter</a></li>
                <li><a href="<?php echo Controls::home();?>?page=list">Liste des parties</a></li>
                <li><a href="<?php echo Controls::home();?>?page=create">Inscrire une nouvelle partie</a></li>
            </ul>
        </div>
        <?php
        
    } else {
        $msg = null;
        if(!$user && isset($_POST['email'])){
            $email = $_POST["email"];
            if(Controls::validateEmail($email)){
                if(User::emailExists($email)){
                    $user = User::pseudoAuth($email);
                    $pwd = $user->resetPassword();
                    if($pwd){
                        if(Orcimail::sendPassword($user, $pwd)){
                            $msg = "Ton mot de passe a été réinitialisé, tu le trouveras dans l'email qui vient de t'être envoyé.";
                        }else{
                            $msg = "Erreur inconnue lors de la réinitialisation du mot de passe.";
                        }
                    }else{
                        $msg = "Erreur inconnue lors de la réinitialisation du mot de passe.";
                    }
                }else{
                    $msg = "Cette adresse email nous est inconnue. Peut-être as-tu utilisé une autre adresse par le passé. N'hésite pas à t'enregistrer à nouveau si tel est ton désir.";
                }
            }else{
                $msg = "Saisis une adresse email valide STP ! Ainsi nous serons en mesure de te retransmettre ton mot de passe.";
            }
        }
        ?><form action='' method='POST'>
            <label for='email'>Email *</label>
            <input type='text' name='email' value='' />
            <input type='submit' value="Récupérer le mot de passe" class='submit' />
        </form><?php
        
        if(!is_null($msg)){
            echo "<p class='form-result'>$msg</p>";
            echo "<p><a href='login.php'>Retour à la page d'authentification et enregistrement...</a></p>";
        }
    }
}
mysql_close($dbServer);
include("scripts.php");

?>

    </body>
</html>