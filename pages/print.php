<?php

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');

$user = User::getFromSession();

// Debug
echo "<div class='dbg'>User:";
echo ($user) ? ($user->getLastname()." (".$user->getRole().") ") : "0" ;
echo "<br/>Adresse en session : ".@$_SESSION["userEmail"]."</div>";

if($user){
    
    if($user->getRole() == "administrator"){
?>
		<h1>Extractions possibles</h1>
        <ul>
			<li><a href="extractions/timeline.php" target="_blank">timeline par partie</a></li>
			<li><a href="extractions/timelineTable.php" target="_blank">timeline par table</a></li>
			<li><a href="extractions/parties.php" target="_blank">parties</a></li>
		</ul>
<?php
	}else{
        echo "<p>Acces restreint à l'administrateur</p>";
    }
    
}else{
    echo "<p>Vous n'êtes pas authentifié.</p>";
}
?>