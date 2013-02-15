<?php
require_once(dirname(__FILE__).'/../conf/bd.php');
$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());;
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');
require_once(dirname(__FILE__).'/../classes/inscription.php');
require_once(dirname(__FILE__).'/../classes/orcimail.php');

header("Content-type: application/json; charset=UTF-8");

if(!$db){
    echo '{"status":"error", "message":"Connexion impossible à la base de données."}';
}else{
    if(isset($_GET['partyId']) && isset($_GET['email']) && Controls::validateEmail($_GET['email']) && isset($_GET['lastname']) && isset($_GET['firstname'])){
        $email = $_GET['email'];
        $partyId = $_GET['partyId'];
        // Si un user est authentifié, verifier l'email et procéder.
        $user = User::getFromSession();
        if($user){
			// Si l'e-mail ne correspond pas, on va chercher l'utilisateur correspondant
            if($email != $user->getEmail()){
                $user = false;
            }
        }
		if(!$user){
            // Verifier si l'email existe déjà
			if(User::emailExists($email)){
				// Pseudo validation par nom/prenom
				$data = array("lastname" => $_GET['lastname'], "firstname" => $_GET['firstname']);
				$user = User::pseudoAuth($email, $data, true);
			}else{
				// Enregistrement d'un nouvel utilisateur (joueur)
				$user = User::register($email, $_GET['lastname'], $_GET['firstname']);
			}
        }

        // Procéder à l'inscription
        if($user){
            $_SESSION["userEmail"] = $user->getEmail();
			$p = new Party($partyId,false);
			
			if($p->freeSlot() > 0){
				$inscription = new Inscription($user->getUserId(), $partyId);
				if($inscription->isValid){
					if($inscription->status == "created"){
						$isMailOk = Orcimail::subscribeToParty($p, $user);
						Orcimail::notifySubscribtion($p, $user);
						if($isMailOk){
							echo '{"status":"ok", "message":"Inscription enregistrée !"}';
						}else{
							echo '{"status":"error", "message":"Envoi de mail impossible."}';
						}
					}elseif($inscription->status == "old"){
						echo '{"status":"ok", "message":"Déjà inscrit précédemment !"}';
					}else {
						echo '{"status":"error", "message":"Erreur à l\'inscription !"}';
					}
				}else{
					echo '{"status":"error", "message":"Inscription invalide."}';
				}
			}else{
				echo '{"status":"error", "message":"Partie complète, merci d\'essayer une autre partie !"}';
			}
        }else{
            echo '{"status":"error", "message":"L\'adresse email est déjà enregistrée, mais les nom/prénom ne correpondent pas."}';
        }
	}else{
        echo '{"status":"error", "message":"Données POST invalides."}';
    }
}
mysql_close($dbServer);
?>