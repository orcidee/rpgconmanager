<?php
require_once(dirname(__FILE__).'/../conf/bd.php');
require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');


echo '<h1>Profil utilisateur</h1>';

$user = User::getFromSession();

if(Controls::isAppOpen() && $user && ($user->getRole() == "animator" || $user->getRole() == "administrator") ){

    echo "<div class='profile'>";
    
    if(@$_POST['action'] === 'validPassword'){
        $msg = array();
        $success = false;
        if($user->verifyPassword(@$_POST['old'])){
            if(User::validatePassword(@$_POST['new'])){
                if(@$_POST['new'] == @$_POST['confirm']){
                    if($user->updatePassword($_POST['new'])){
                        $msg[] = "Ton mot de passe a été mis à jour.";
                        $success = true;
                    }else{
                        $msg[] = "Une erreur inconnue et improbable s'est produite. Ton mot de passe n'a pas pu être mis à jour.";
                    }
                }else{
                    $msg[] = "Ta confirmation de nouveau mot de passe ne correspond pas.";
                }
            }else{
                $msg[] = "Ton nouveau mot de passe est trop faible, équipe le de plus de caractère.";
            }
        }else{
            $msg[] = "Ancien mot de passe incorrect. <a href=''>Clique ici, si tu as oublié ton mot de passe</a>.";
        }
        
        echo "<ul class='result'>";
        foreach ($msg as $v){
            echo "<li>".$v."</li>";
        }
        echo "</ul>";
        if($success){
            ?><ul>
                <li><a href="<?php echo Controls::home();?>?page=profile">Voir le profil</a></li>
                <li><a href="<?php echo Controls::home();?>?page=logout">Se déconnecter</a></li>
                <li><a href="<?php echo Controls::home();?>?page=list">Afficher la liste des émissions</a></li>
            </ul><?php
        }
    }
    
    if(@$_POST['action'] === 'password' || (@$_POST['action'] === 'validPassword' && @$success === false)){
    
        // Demande de modification de mot de passe.
        ?>
        <form action="" method="POST">
            <label for='old'>Ancien mot de passe</label>
            <input type='password' name='old' />
            <label for='new'>Nouveau mot de passe</label>
            <input type='password' name='new' />
            <label for='confirm'>Confirmer le nouveau mot de passe</label>
            <input type='password' name='confirm' />
            <input type='hidden' value='validPassword' name="action" />
            <input type='submit' value='Valider' class='submit' />
        </form>
        <?php
        
    }else{
    
        $readonly = (@$_POST['action'] === 'edit') ? "" : "readonly='readonly' class='info'" ;
        
        if (@$_POST['action'] === 'validate'){
            $valid = true;
            $msg = array();
            if(Controls::validateEmail(@$_POST['email'])){
                if(User::emailExists(@$_POST['email'], $user->getUserId())){
                    // email deja enregistré
                    $msg[] = "Cette adresse email est déjà enregistrée chez Orc'idée. Si tu as oublié ton mot passe, <a href=''>clique ici</a>.";
                    $valid = false;
                }
            }else{
                // email pas valide
                $msg[] = "Ton adresse email n'est pas valide";
                $valid = false;
            }
            if(strlen(@$_POST['lastname']) == 0){
                $msg[] = "Tu n'as pas de nom ? Merci de contacter l'administrateur par courrier recommandé du lundi au jeudi de 10h30 à 11h45, en joignant le formulaire 1A révision cY du contrôle des habitants de la ville de Lerne.";
                $valid = false;
            }
            if(strlen(@$_POST['firstname']) == 0){
                $msg[] = "Il nous faudrait encore ton prénom pour accéder à ta requête.";
                $valid = false;
            }
            if($valid){
                $res = $user->updateData($_POST);
                $user = $res['user'];
                $msg[] = $res['msg'];
            }
            echo "<ul class='result'>";
            foreach ($msg as $v){
                echo "<li>".$v."</li>";
            }
            echo "</ul>";
        }
        
        if (@$_POST['action'] === 'edit'){
            echo '<form action="" method="POST">';
        }
        
        echo '<ul class="profile"><li><label for="lastname">Nom *</label>';
        echo '<input name="lastname" type="text" value="'.$user->getLastname().'" '.$readonly.' /></li>';
        
        echo '<li><label for="firstname">Prénom *</label>';
        echo '<input name="firstname" type="text" value="'.$user->getFirstname().'" '.$readonly.' /></li>';
        
        echo '<li><label for="email">Email *</label>';
        echo '<input name="email" type="text" value="'.$user->getEmail().'" '.$readonly.' /></li>';
        
        /* TODO : créer un flag "Le MJ est d'accord d'afficher ses infos en public" ... en attendant, on affiche rien.
        echo '<li><label for="phone">Téléphone</label>';
        echo '<input name="phone" type="text" value="'.$user->getPhone().'" '.$readonly.' /></li>';
        
        echo '<li><label for="address">Adresse</label>';
        echo '<input name="address" type="text" value="'.$user->getAddress().'" '.$readonly.' /></li>';
        
        echo '<li><label for="npa">NPA</label>';
        echo '<input name="npa" type="text" value="'.$user->getNpa().'" '.$readonly.' /></li>';
        
        echo '<li><label for="city">Ville</label>';
        echo '<input name="city" type="text" value="'.$user->getCity().'" '.$readonly.' /></li>';
        
        echo '<li><label for="country">Pays</label>';
        echo '<input name="country" type="text" value="'.$user->getCountry().'" '.$readonly.' /></li></ul>';
        */
        if (@$_POST['action'] === 'edit'){
        
            echo '<input type="hidden" name="action" value="validate"/>';
            echo '<input type="submit" value="Valider les modifications" class="submit"/></form>';
        
        }else{

            echo '<form action="" method="POST">';
            echo '<input type="hidden" name="action" value="edit"/>';
            echo '<input type="submit" value="Modifier les données" class="submit"/></form>';
            
            echo '<form action="" method="POST">';
            echo '<input type="hidden" name="action" value="password"/>';
            echo '<input type="submit" value="Modifier le mot de passe" class="submit"/></form>';
        }
    }

    echo "</div>";
    
} else {

    // Conditions non remplies pour afficher cette page.

}