<?php
require_once(dirname(__FILE__).'/../conf/bd.php');
require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');

if(isset($_SESSION["userEmail"])){
	unset($_SESSION["userEmail"]);
}

if(isset($_SESSION["userId"])){
    
    unset($_SESSION["userId"]);
    
    echo "<h1>Vous avez été déconnecté</h1>";
    echo "<p><a href=" . Controls::home() . ">Cliquez ici pour revenir à la page d'accueil.<a></p>";
    
} else {

    header("Location:".Controls::home());

}
    