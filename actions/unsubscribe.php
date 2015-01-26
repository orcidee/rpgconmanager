<?php

require_once(dirname(__FILE__).'/../conf/conf.php');

$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());;
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');
require_once(dirname(__FILE__).'/../classes/inscription.php');
require_once(dirname(__FILE__).'/../classes/orcimail.php');

header("Content-type: application/json; charset=UTF-8");


if(!$db){
    echo '{"status":"error", "message":"Connexion impossible à la base de données."}';
}else{
    if(isset($_GET['partyId'])){
		// on a le login encodé : on désinscrit
		if(isset($_GET['u'])){
			if ((($user = User::getFromSession()) != FALSE) && $user->getRole() == "administrator"){
				$p = new Party($_GET['partyId'], false);
				if($p && $p->isValid){
				
					$players = $p->getPlayers();
					foreach($players as $player){
						if(sha1($player->getId()) == $_GET['u']){
							// This player want to unsubscribe
							$res = Inscription::unsubscribe($p->getId(), $player->getId());
							Orcimail::notifyUnsubscribtion($p, $player, true);
							break;
						}
					}
				
					if(!isset($res)){
						echo '{"status":"error", "message":"Joueur non inscrit sur cette partie numéro '.$p->getId().'"}';
					}elseif($res){
						echo '{"status":"ok", "message":"Le joueur '.$player->getFirstname().' '.$player->getLastname().' a bien été désinscrit de la partie numéro '.$p->getId().'"}';

						// If admin is unsubscribing a player, send a mail to this player !
						if (@$_GET['admin']){
							Orcimail::unsubscribedToParty($p, $player);
						}
					}else{
						echo '{"status":"error", "message":"Erreur lors de la désinscriptio de la partie numéro '.$p->getId().'"}';
					}
				}else{
					echo '{"status":"error", "message":"Numéro de partie '.$p->getId().' inconnu"}';
				}
			}else{
				echo '{"status":"error", "message":"Utilisateur non autorisé"}';
			}				

		}elseif(isset($_GET['email']) && Controls::validateEmail($_GET['email'])){
//echo '{"status":"error", "message":"pas bon"}';

			// On n'a que l'email : envoi un message pour demander confirmation
			$email = $_GET['email'];
            // Verifier si l'email existe
			if(User::emailExists($email)){
				$user = User::pseudoAuth($email, null, true);
				$p = new Party($_GET['partyId'], false);
				if($p && $p->isValid){
					if(Orcimail::unsubscribeToParty($p, $user)){
						echo '{"status":"ok", "message":"Message pour se désinscrire envoyé à l\'adresse '.$email.'"}';
					}else{
						echo '{"status":"error", "message":"Erreur lors de l\'envoi du message à l\'adresse '.$email.'"}';
					}
				}else{
					echo '{"status":"error", "message":"Numéro de partie '.$p->getId().' inconnu"}';
				}
			}else{
				echo '{"status":"error", "message":"Adresse mail '.$email.' inconnue"}';
			}

		}else{
			echo '{"status":"error", "message":"Utilisateur non autorisé"}';
        }
	}else{
        echo '{"status":"error", "message":"Numéro de partie nécessaire"}';
    }
}
mysql_close($dbServer);
?>