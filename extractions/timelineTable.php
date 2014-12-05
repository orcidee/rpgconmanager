<?php

require_once(dirname(__FILE__).'/../conf/bd.php');
require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');
require_once(dirname(__FILE__).'/../classes/inscription.php');

$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());
$db = (mysql_select_db(DB));

if(!$db){
    echo "<p class='dbg'>Impossible de selectionner la base de donnees</p>";
}else{

	$user = User::getFromSession();

	if($user){
		
		if($user->getRole() == "administrator"){
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=TimeLine_Orcidee.xls");

            $startDate = new DateTime(Controls::getDate(Controls::CONV_START, "%Y-%m-%d %H:%i:%s"));
            $start = strtotime(Controls::getDate(Controls::CONV_START, "%Y-%m-%d %H:%i:%s"));
            $end = strtotime(END_AT);
			$startHour = $startDate->format("H");
			$duration = ($end - $start) / 3600;

?>
			<table border='1' width='100%' cellspacing='3' cellpadding='3'>
				<tr>
					<td align='center' valign='middle' colspan='<?= $duration + 1 ?>'>
						<font size='+6'><b>TIMELINE DE LA CONVENTION PAR TABLE</b><br><br></font>
					</td>
				</tr>
				<tr>
					<td align='center' valign='middle'>
						&nbsp;
					</td>
					<td align='center' valign='middle' colspan='<?= 24 - $startHour ?>'>
						<font color=red size='+4'><br><b>S A M E D I</b></font><br><br>
					</td>
					<td align='center' valign='middle' colspan='<?= $duration + $startHour - 24 ?>'>
						<font color=red size='+4'><br><b>D I M A N C H E</b></font><br><br>
					</td>
				</tr>

				<tr>
					<td align='left' WIDTH='300'><font size='-2'>&nbsp; <b>Table</b> &nbsp;</font></td>
<?php
					for ($timeline=$startHour; $timeline<=($startHour + $duration - 1); $timeline++){
						if ($timeline<24){
							$heure_a=$timeline;
						}else{
							$heure_a=$timeline-24;
						}
						$heure_b=$heure_a+1;
?>
						<td align='center' valign='middle' WIDTH='50'>
							<font size='-2'><b>'<?= $heure_a."-".$heure_b ?></b></font>
						</td>
<?php
					}

                $thisYear = Controls::getDate(Controls::CONV_START, '%Y');
				$sql = "SELECT Parties.* FROM Parties WHERE Parties.state in ('validated', 'verified') and Parties.table is not null AND Parties.year = ".$thisYear." order by Parties.table, Parties.start ASC";
				$res = mysql_query ( $sql );

				$colorTypes = array();
				$colorTypes[1] = "yellow";
				$colorTypes[2] = "royalblue";
				$colorTypes[3] = "green";
				$colorTypes[4] = "orange";
				$colorTypes[6] = "cyan";

				$currentTable = "";
				$currentIndex = $duration;
				while($row = mysql_fetch_array($res))
				{
					if ($currentTable != $row["table"]) {
						// fini la ligne
						for ($index=$currentIndex; $index<$duration; $index++) {
								echo "<td align='center'>&nbsp</td>";
						}
						echo "</tr>";
						
						// démarre une nouvelle ligne
						$currentTable = $row["table"];
						$currentIndex = 0;
						echo "<tr><td>".$currentTable."</td>";
					}

					$partyDate = strtotime($row["start"]);
					$partyOffset = ($partyDate - $start) / 3600;
					$partyDuration = $row["duration"];

					// avance jusqu'à la prochaine partie
					for ($index=$currentIndex; $index<$partyOffset; $index++) {
							echo "<td align='center'>&nbsp</td>";
					}

					// affiche la partie
					echo "<td align='center' bgcolor='".$colorTypes[$row["typeId"]]."' colspan='".$partyDuration."'>";
					echo "<b>P".($row["partyId"]<10 ? "0" : "").$row["partyId"]." - ".$row["name"]."</b>";
					if ($partyOffset < $currentIndex) echo " <font color='red'>ATTENTION : devrait commencer plus tôt !!!</font>";
					echo "</td>";

					// met à jour l'index courant
					$currentIndex = $partyOffset + $partyDuration;
				}
				// fini la dernière ligne
				for ($index=$currentIndex; $index<$duration; $index++) {
						echo "<td align='center'>&nbsp</td>";
				}
				echo "</tr>";
			echo "</table>";
		}else{
			echo "<p>Acces restreint à l'administrateur</p>";
		}
		
	}else{
		echo "<p>Vous n'êtes pas authentifié.</p>";

		// Debug
		echo "<div class='dbg'>User:";
		echo ($user) ? ($user->getLastname()." (".$user->getRole().") ") : "0" ;
		echo "</div>";
	}
}