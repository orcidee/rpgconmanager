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
		<link rel='stylesheet' type='text/css' href='css/2019.css' />
		
    </head>
    <body>
";

require_once(dirname(__FILE__).'/conf/conf.php');
require_once(dirname(__FILE__).'/classes/controls.php');
require_once(dirname(__FILE__).'/classes/user.php');
require_once(dirname(__FILE__).'/classes/orcimail.php');

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
                        $msg = "Le mot de passe a été réinitialisé, vous le trouverez dans l'email qui vient de vous être envoyé.";
                    }else{
                        $msg = "Erreur inconnue lors de la réinitialisation du mot de passe.";
                    }
                }else{
                    $msg = "Erreur inconnue lors de la réinitialisation du mot de passe.";
                }
            }else{
                $msg = "Cette adresse email nous est inconnue. Peut-être avez-vous utilisé une autre adresse par le passé. N'hésitez pas à vous enregistrer à nouveau si tel est votre désir.";
            }
        }else{
            $msg = "Saisissez une adresse email valide SVP ! Ainsi nous serons en mesure de vous réinitialiser le mot de passe.";
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
include("scripts.php");

?>

    </body>
</html>
