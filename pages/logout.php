<?php
require_once(dirname(__FILE__).'/../classes/controls.php');

if(isset($_SESSION["userEmail"])){
	unset($_SESSION["userEmail"]);
    header("Location:".Controls::currentURI());
}
if(isset($_SESSION["userId"])){
    unset($_SESSION["userId"]);
    header("Location:".Controls::currentURI());
}

echo "<p class='message'>Vous avez été déconnecté</p>";