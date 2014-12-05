<?php

require_once(dirname(__FILE__).'/../conf/bd.php');
require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');

$user = User::getFromSession();

// Debug
echo "<div class='dbg'>User:";
echo ($user) ? ($user->getLastname()." (".$user->getRole().") ") : "0" ;
echo "<br/>Adresse en session : ".@$_SESSION["userEmail"]."</div>";

if($user){
    
    if($user->getRole() == "administrator"){
?>
		<h1>Définition des numéros de table</h1>
		<form id="tablesForm" action="" method="POST">
			<table border='1' width='100%' cellspacing='3' cellpadding='3'>
				<tr>
					<td>N°</td>
					<td>Nom de la partie</td>
					<td>Animateur</td>
					<td>Type de partie</td>
					<td>Début</td>
					<td>Fin</td>
					<td>Nb joueurs min/max</td>
					<td>Table</td>
				</tr>
<?php
                $thisYear = Controls::getDate(Controls::CONV_START, "Y");
				$sql = "SELECT Parties.*, Types.name as typeName, Users.* FROM Parties".
						" JOIN Types on Parties.typeId = Types.typeId".
						" JOIN Users on Parties.userId = Users.userId".
						" WHERE Parties.state in ('validated', 'verified')".
						" AND Parties.year = ".$thisYear.
						" ORDER BY Parties.start ASC";
				$res = mysql_query ( $sql );

				$updatedParties = array();
				$errorParties = array();

				while($row = mysql_fetch_array($res))
				{
					$partyTable = $row['table'];
					if(isset($_POST['table-'.$row["partyId"]])){
						$partyTable = trim($_POST['table-'.$row["partyId"]]);
						if ($partyTable != $row['table']){
							if (Party::setTableForParty($row["partyId"], $partyTable))
								$updatedParties[] = $row["partyId"];
							else
								$errorParties[] = $row["partyId"];
						}
					}
?>
				<tr>
					<td><?= $row["partyId"] ?></td>
					<td title="<?= stripslashes($row["description"]) ?>"><?= stripslashes($row["name"]) ?></td>
					<td><?= stripslashes($row["firstname"])." ".stripslashes($row["lastname"]) ?></td>
					<td><?= stripslashes($row["typeName"]) ?></td>
					<td><?= strftime("%d.%m.%Y à %H:%M", strtotime($row['start'])) ?></td>
					<td><?= strftime("%d.%m.%Y à %H:%M", strtotime($row['start'])+($row['duration']*3600))?></td>
					<td><?= $row['playerMin']."/".$row['playerMax'] ?></td>
					<td><input type='text' name='table-<?= $row["partyId"] ?>' value='<?= $partyTable ?>' maxlength="20" /></td>
				</tr>
<?php
				}
?>
			</table>
			<p>
				<input type="submit" class="submit" value="Soumettre les données saisies" />
				<input type="reset" class="submit" value="Réinitialiser le formulaire" />
			</p>
		</form>
<?php
		if(count($updatedParties) > 0) echo "<p>Mis à jour les ".count($updatedParties)." parties suivantes : ".join(", ", $updatedParties)."</p>";
		if(count($errorParties) > 0) echo "<p><font color='red'><b>Pas pu mettre à jour les ".count($errorParties)." parties suivantes : ".join(", ", $errorParties)." !</b></font></p>";
	}else{
        echo "<p>Acces restreint à l'administrateur</p>";
    }
    
}else{
    echo "<p>Vous n'êtes pas authentifié.</p>";
}
?>