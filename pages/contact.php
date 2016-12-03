<?php

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');

// FIXME
// Cette page devrait s'apeler "party" et non "create", car elle sert ce création, édition, confirmation.

echo "<h1>Contacter les orgas</h1>".
"<div class='contact'>";

$user = User::getFromSession();

if($user){
    
    if($user->getRole() == "administrator" or $user->getRole() == "animator"){
    	
		
		if(isset($_POST) && strlen(@$_POST['subject']) > 0 && strlen(@$_POST['body']) > 0){
			
			$requestSignature = serialize($_POST);
			
			if(@$_SESSION['requestSignature'] == $requestSignature){
				
				// Spamming or bombing
				?><p>Ton message a déjà été transmis. <a href="">Revenir au formulaire</a></p><?php
			
			}else{
			
				$_SESSION['requestSignature'] = $requestSignature;
			
				// Send function
				$safeSubject = $_POST['subject'];
				$safeBody = $_POST['body'];
				$isMailOk = Orcimail::askSomething($user, $safeSubject, $safeBody);
				
				if($isMailOk){
					?><p>Ton message a bien été transmis, tu vas recevoir une confirmation par email et nous répondrons dès que possible.</p><?php
				}else{
					?><p>Ton message n'a malheureusement pas pu être transmis. Tu peux éventuellement réessayer plus tard, ou  <a href="http://www.orcidee.ch/index.php?option=com_alfcontact&view=alfcontact&Itemid=657">via cette page</a>.</p><?php
				}
			}
		
		}else{
		
			// Display FORM
		
		
			?>
			<form action="" method="POST">
				
				
				<fieldset>
				
					<p>N'hésitez pas à nous contacter si vous avez des difficultés pour inscrire votre partie, 
					ou pour toute autre question. Nous vous répondrons dans les plus brefs délais.</p>
					
					<label for="subject">Sujet*</label>
					<input type="text" value="" name="subject" id="subject"/>
					<label for="body">Message / Question*</label>
					<textarea name="body" id="body" class='clear' data-limit='200'></textarea>
					
					<input type="submit" class="submit" value="Envoyer" />
					
				</fieldset>
				
			</form>
			<?php
		}
		
    }else{
        echo "<p>Acces restreint aux animateurs et maîtres de jeu</p>";
    }
    
}else{
    echo "<p>Vous n'êtes pas authentifié.</p>";
}
echo "</div>";