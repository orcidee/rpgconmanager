<?php

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

			ob_start();

			header("Content-type: application/vnd.ms-excel; charset=iso-8859-1");
			header("Content-Disposition: attachment; filename=TimeLine_Orcidee.xls");

			$startDate = new DateTime(Controls::getDate(Controls::CONV_START, '%Y-%m-%d %H:%M:00'));
			$start = Controls::getDate(Controls::CONV_START);
			$end = Controls::getDate(Controls::CONV_END);
			$startHour = $startDate->format("H");
			$duration = ($end - $start) / 3600;

?>
			<table border='1' width='100%' cellspacing='3' cellpadding='3'>
				<tr>
					<td align='center' valign='middle' colspan='<?= $duration + 2 ?>'>
						<font size='+6'><b>TIMELINE DE LA CONVENTION PAR PARTIE</b><br><br></font>
					</td>
				</tr>
				<tr>
					<td align='center' valign='middle' colspan='2'>
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
					<td align='left' WIDTH='300'><font size='-2'>&nbsp; <b>Nom du Jeu:</b> &nbsp;</font></td>
					<td align='left' WIDTH='100'><font size='-2'>&nbsp; <b>Type de Jeu:</b> &nbsp;</font></td>
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
				echo "</tr>";

                $thisYear = Controls::getDate(Controls::CONV_START, '%Y');
				$sql = "SELECT Parties.*, Types.name as typeName FROM Parties join Types on Parties.typeId = Types.typeId WHERE Parties.state in ('validated', 'verified') AND Parties.year = ".$thisYear." order by Parties.start ASC";
				$res = mysql_query ( $sql );

				while($ligne = mysql_fetch_array($res))
				{
					$num = $ligne["partyId"];

					echo "<tr><td align='left'><font size='2'>";
					echo "&nbsp;<b>P".($num<10 ? "0" : "").$num." - ".$ligne["name"]."</b> &nbsp";
					echo "</font></td><td>".stripslashes($ligne["typeName"])."</td>";

					$partyDate = strtotime($ligne["start"]);
					$partyOffset = ($partyDate - $start) / 3600;
					$partyDuration = $ligne["duration"];

					for ($index=0; $index<$duration; $index++) {
						if ($index >= $partyOffset && $index < $partyOffset + $partyDuration) {
							echo "<td align='center' bgcolor='gold'>&nbsp</td>";
						} else {
							echo "<td align='center'>&nbsp</td>";
						}
					}
					echo "</tr>";
				}
			echo "</table>";

			$output = mb_convert_encoding(ob_get_contents(),'iso-8859-1','utf-8');
			ob_end_clean();
			echo $output;

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