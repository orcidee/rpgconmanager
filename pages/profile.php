<?php
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');


echo '<h1>Profil utilisateur</h1>';

$user = User::getFromSession();
$controls = new Controls();

// Define what is available to show
if(isset($_GET['id'])){
	// TODO probablement possible de simplifier ces différentes conditions...
    $animator  = $user && $user->getRole() == "animator" && $user->getId() == @$_GET['id'] ;
    $light     = $user && (($controls->isAppOpen() && $user->getId() == @$_GET['id']) || $user->getRole() == "administrator") ;
    $full      = $user && (($light && $animator) || $user->getRole() == "administrator") ;
    $profileId = @$_GET['id'];
    $userDisplayed = new User($profileId);
}else{
    $light     = $user && (($controls->isAppOpen() && $user->getRole() == "animator") || $user->getRole() == "administrator") ;
    $full      = $light ;
    $profileId = ($user) ? $user->getId() : false;
    $userDisplayed = $user;
}


if($light){

    echo "<div class='profile'>";

    if($full){
        if(@$_POST['action'] === 'validPassword'){
            $msg = array();
            $success = false;
            if($userDisplayed->verifyPassword(@$_POST['old'])){
                if(User::validatePassword(@$_POST['new'])){
                    if(@$_POST['new'] == @$_POST['confirm']){
                        if($userDisplayed->updatePassword($_POST['new'])){
                            $msg[] = "Ton mot de passe a été mis à jour.";
                            $success = true;
                        }else{
                            $msg[] = "Une erreur inconnue et improbable s'est produite. Votre mot de passe n'a pas pu être mis à jour.";
                        }
                    }else{
                        $msg[] = "Votre confirmation de nouveau mot de passe ne correspond pas.";
                    }
                }else{
                    $msg[] = "Votre nouveau mot de passe est trop faible, équipez le de plus de caractères.";
                }
            }else{
                $msg[] = "Ancien mot de passe incorrect. Déconnectez-vous et réinitialisez votre mot de passe.";
            }

            echo "<ul class='result'>";
            foreach ($msg as $v){
                echo "<li>".$v."</li>";
            }
            echo "</ul>";
        }
    }

    if($full && (@$_POST['action'] === 'password' || (@$_POST['action'] === 'validPassword' && @$success === false))){

        // Demande de modification de mot de passe.
        ?>
        <form action="" method="POST">
            <label for='old'>Ancien mot de passe</label>
            <input type='password' name='old' />
            <label for='new'>Nouveau mot de passe</label>
            <input type='password' name='new' />
            <label for='confirm'>Confirmation du nouveau mot de passe</label>
            <input type='password' name='confirm' />
            <input type='hidden' value='validPassword' name="action" />
            <input type='submit' value='Valider' class='submit' />

        </form>
		<form action="" method="POST">
			<input type="submit" value="Annuler" class="submit"/>
		</form>
        <?php

    }else{

		$validate = false;
        if (@$_POST['action'] === 'validate'){
			$validate = true;
            $valid = true;
            $msg = array();
            if(Controls::validateEmail(@$_POST['email'])){
                if(User::emailExists(@$_POST['email'], $userDisplayed->getUserId())){
                    // email deja enregistré
                    $msg[] = "Cette adresse email est déjà enregistrée chez Orc'idée. Si vous avez oublié votre mot passe, <a href='pwd-forgotten.php'>cliquez ici</a>.";
                    $valid = false;
                }
            }else{
                // email pas valide
                $msg[] = "Votre adresse email n'est pas valide";
                $valid = false;
            }
            if(strlen(@$_POST['lastname']) == 0){
                $msg[] = "Vous n'avez pas de nom ? Merci de contacter l'administrateur par courrier recommandé du lundi au jeudi de 10h30 à 11h45, en joignant le formulaire 1A révision cY du contrôle des habitants de la ville de Lerne.";
                $valid = false;
            }
            if(strlen(@$_POST['firstname']) == 0){
                $msg[] = "Il nous faudrait encore votre prénom pour accéder à votre requête.";
                $valid = false;
            }
            if($valid){
                $res = $userDisplayed->updateData($_POST);
                $userDisplayed = $res['user'];
                $msg[] = $res['msg'];
            }
            echo "<ul class='result'>";
            foreach ($msg as $v){
                echo "<li>".$v."</li>";
            }
            echo "</ul>";
        }

		$isedit = @$_POST['action'] === 'edit' || (@$_POST['action'] === 'validate' && $valid == false);
        $readonly = $isedit ? "" : "readonly='readonly' class='info'" ;

        if ($isedit){
            echo '<form action="" method="POST">';
        }

        echo '<ul class="profile"><li><label for="lastname">Nom *</label>';
        echo '<input name="lastname" type="text" value="'.($validate ? @$_POST['lastname'] : $userDisplayed->getLastname()).'" '.$readonly.' /></li>';

        echo '<li><label for="firstname">Prénom *</label>';
        echo '<input name="firstname" type="text" value="'.($validate ? @$_POST['firstname'] : $userDisplayed->getFirstname()).'" '.$readonly.' /></li>';

        echo '<li><label for="email">Email *</label>';
        echo '<input name="email" type="text" value="'.($validate ? @$_POST['email'] : $userDisplayed->getEmail()).'" '.$readonly.' /></li>';

        echo '<li><label for="role">Rôle</label>';
        echo '<input name="role" type="text" value="'.($validate ? @$_POST['role'] : $userDisplayed->getRole()).'" readonly="readonly" class="info" /></li>';

        if($full){
            echo '<li><label for="phone">Téléphone</label>';
            echo '<input name="phone" type="text" value="'.($validate ? @$_POST['phone'] : $userDisplayed->getPhone()).'" '.$readonly.' /></li>';

            echo '<li><label for="address">Adresse</label>';
            echo '<input name="address" type="text" value="'.($validate ? @$_POST['address'] : $userDisplayed->getAddress()).'" '.$readonly.' /></li>';

            echo '<li><label for="npa">NPA</label>';
            echo '<input name="npa" type="text" value="'.($validate ? @$_POST['npa'] : $userDisplayed->getNpa()).'" '.$readonly.' /></li>';

            echo '<li><label for="city">Ville</label>';
            echo '<input name="city" type="text" value="'.($validate ? @$_POST['city'] : $userDisplayed->getCity()).'" '.$readonly.' /></li>';

            echo '<li><label for="country">Pays</label>';
            echo '<input name="country" type="text" value="'.($validate ? @$_POST['country'] : $userDisplayed->getCountry()).'" '.$readonly.' /></li>';
        }
        echo '</ul>';

        if($full){
            if ($isedit){
            echo "<div class='Pactions'>";
                echo '<input type="hidden" name="action" value="validate"/>';
                echo '<input type="submit" value="Valider les modifications" class="submit"/></form>';

                echo '<form action="" method="POST">';
                echo '<input type="submit" value="Annuler" class="submit"/></form>';
            echo "</div>";

            }else{

               echo "<div class='Pactions'>";
            	echo '<form action="" method="POST">';
                echo '<input type="hidden" name="action" value="edit"/>';
                echo '<input type="submit" value="Modifier les données" class="submit"/></form>';

                echo '<form action="" method="POST">';
                echo '<input type="hidden" name="action" value="password"/>';
                echo '<input type="submit" value="Modifier le mot de passe" class="submit"/></form>';
                echo "</div>";
            }
        }
    }

    echo "</div>";

} else {
    // Conditions non remplies pour afficher cette page.
	echo "Vous n'êtes pas autorisé à voir ce profil.";
}
