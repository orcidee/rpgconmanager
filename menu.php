<?php
require_once(dirname(__FILE__).'/conf/bd.php');
require_once(dirname(__FILE__).'/conf/conf.php');
require_once(dirname(__FILE__).'/classes/controls.php');

// $user hérité de index.php
// $user = User::getFromSession();

if(!$user && isset($_SESSION["userEmail"])){
    $user = User::pseudoAuth($_SESSION["userEmail"]);
}

$root = Controls::home();

?>
<nav id='main-nav'>
    <ul id='manager-main-menu'><?php
    
        echo "<li class='list'><a href=" . $root . ">Accueil</a> -</li>";
        echo "<li class='list'><a href='".$root."?page=list'>Liste des parties</a> -</li>";
        
        if( $user ){
			if (isset($_SESSION) && isset($_SESSION['userId'])) {
				echo "<li class='create'><a href='".$root."?page=create'>Proposer une partie / animation</a> -</li>";
				echo "<li class='profile'><a href='".$root."?page=profile'>Profil</a> -</li>";
				
				if($user->isAdmin()){
					echo "<li class='print'><a href='".$root."?page=print'>Imprimer les plans</a> -</li>";
					echo "<li class='tables'><a href='".$root."?page=tables'>Numéros de table</a> -</li>";
					echo "<li class='tables'><a href='".$root."?page=users'>Utilisateurs</a> -</li>";
					echo "<li class='conf'><a href='".$root."?page=conf'>Contrôles de l'application</a> -</li>";
				}
				
			}

            echo "<li class='logout'><a href='".$root."?page=logout'>Déconnexion</a></li>";
        } else {
            echo "<li class='login'><a href='login.php".@$forward."'>S'authentifier / S'enregistrer</a></li>";
        }
    
    ?></ul>
</nav>