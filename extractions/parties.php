<?php
require_once(dirname(__FILE__).'/../conf/conf.php');

$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');
require_once(dirname(__FILE__).'/../classes/inscription.php');

if(!$db){
    echo "<p class='dbg'>Impossible de selectionner la base de donnees</p>";
}else{

	$user = User::getFromSession();

	if($user){
		
		if($user->getRole() == "administrator"){
            $thisYear = Controls::getDate(Controls::CONV_START, '%Y');

			$sql = "SELECT Parties.*, Users.lastname, Users.firstname FROM Parties join Users on Parties.userId = Users.userId WHERE Parties.state in ('validated', 'verified') AND Parties.year = ".$thisYear." order by Parties.partyId ASC";
			$res = mysql_query ( $sql );

			include("../FPDF/fpdf.php");
			$PDF = new FPDF();
			$PDF->SetFont('Arial');
			$PDF->SetLineWidth(.5);

			$Col1width = 30;
			$Col2width = 90;
			$Col3width = 25;
			$Col4width = 45;
			
			while($row = mysql_fetch_array($res))
			{
				$sql2 = "SELECT Users.* FROM Users join Inscriptions on Users.userId = Inscriptions.userId WHERE partyId = '".$row["partyId"]."'";
				$res2 = mysql_query ( $sql2 );

				$PDF->AddPage();
				$PDF->SetFont('','B', 20);
				$PDF->Cell($Col1width + $Col2width,15," Table n°".$row['table'],"LT");
				$PDF->SetTextColor(255,0,0);
				$fullText = " ";
				if (mysql_num_rows($res2) >= $row["playerMax"]) $fullText = "COMPLET";
				$PDF->Cell($Col3width + $Col4width,15,$fullText,"RT",1);
				$PDF->SetTextColor(0);

				$PDF->SetFont('','B', 25);
				$PDF->MultiCell($Col1width + $Col2width + $Col3width + $Col4width,12,"P".($row["partyId"]<10 ? "0" : "").$row["partyId"]." - ".$row["name"],"LR");

				$PDF->SetFont('','B', 11);
				$PDF->Cell($Col1width,7,"par","L",0,"R");
				$PDF->SetFont('','I');
				$PDF->Cell($Col2width + $Col3width + $Col4width,7,$row["firstname"]." ".$row["lastname"],"R",1);

				$PDF->SetLineWidth(.1);
				$PDF->Cell($Col1width + $Col2width + $Col3width + $Col4width,0,"","T",1);
				$PDF->SetLineWidth(.5);

				$PDF->SetFont('','B');
				$PDF->Cell($Col1width,7,"Scénario :","L",0,"R");
				$PDF->SetFont('');
				$PDF->Cell($Col2width,7,strip_tags($row["scenario"]));
				$PDF->SetFont('','B');
				$PDF->Cell($Col3width,7,"Début :",0,0,"R");
				$PDF->SetFont('');
				$PDF->Cell($Col4width,7,strftime("%d.%m.%Y à %H:%M", strtotime($row['start'])),"R",1);

				$PDF->SetFont('','B');
				$PDF->Cell($Col1width,7,"Genre :","L",0,"R");
				$PDF->SetFont('');
				$PDF->Cell($Col2width,7,$row["kind"]);
				$PDF->SetFont('','B');
				$PDF->Cell($Col3width,7,"Durée :",0,0,"R");
				$PDF->SetFont('');
				$PDF->Cell($Col4width,7,$row['duration']."h","R",1);

				$PDF->SetFont('','B');
				$PDF->Cell($Col1width,7,"Niveau de jeu :","L",0,"R");
				$PDF->SetFont('');
				if($row['level']=="low") {
					$lvl = "Débutant";
				} elseif ($row['level']=="middle") {
					$lvl = "Initié";
				} else {
					$lvl = ($row['level']=="high") ? "Expert" : "Peu importe";
				}
				$PDF->Cell($Col2width,7,$lvl);
				$PDF->SetFont('','B');
				$PDF->Cell($Col3width,7,"Nombre de joueurs :",0,0,"R");
				$PDF->SetFont('');
				$PDF->Cell($Col4width,7,"min ".$row["playerMin"]." / max ".$row["playerMax"],"R",1);

				$PDF->SetLineWidth(.1);
				$PDF->Cell($Col1width + $Col2width + $Col3width + $Col4width,0,"","T",1);
				$PDF->SetLineWidth(.5);
				
				$PDF->SetFont('','B');
				$PDF->Cell($Col1width,7,"Description :","L",0,"R");
				$PDF->SetFont('');
				$PDF->Cell($Col2width + $Col3width + $Col4width,7,"","R",1);

				$PDF->MultiCell($Col1width + $Col2width + $Col3width + $Col4width,7, html_entity_decode(strip_tags($row['description'])),"LR");

				$PDF->SetLineWidth(.1);
				$PDF->Cell($Col1width + $Col2width + $Col3width + $Col4width,0,"","T",1);
				$PDF->SetLineWidth(.5);
				
				$PDF->SetFont('','B');
				$PDF->Cell($Col1width + $Col2width + $Col3width + $Col4width,7,"Joueurs Inscrits :","LR",1);

				$PDF->SetFont('');
				while ($row2 = mysql_fetch_assoc($res2)) {
					$PDF->Cell($Col1width,7,"","L");
					$PDF->Cell($Col2width + $Col3width + $Col4width,7,$row2["firstname"]." ".$row2["lastname"],"R",1);
				}

				$PDF->Cell($Col1width + $Col2width + $Col3width + $Col4width,7,"","LRB",1);
			}

			$PDF->Output();
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