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
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=TimeLine_Orcidee.xls");

            $startDate = new DateTime(Controls::getDate(Controls::CONV_START, '%Y-%m-%d %H:%M:00'));
            $start = Controls::getDate(Controls::CONV_START);
            $end = Controls::getDate(Controls::CONV_END);
            $startHour = $startDate->format("H");
            $duration = ($end - $start) / 3600;


            // TABLE HEADER
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

            // FETCH & PREPARE DATA

            $thisYear = Controls::getDate(Controls::CONV_START, '%Y');
            $sql = "SELECT Parties.* FROM Parties WHERE Parties.state in ('validated', 'verified') and Parties.table is not null AND Parties.year = ".$thisYear." order by Parties.start ASC";
            $res = mysql_query ( $sql );

            $colorTypes = array();
            $colorTypes[1] = "yellow";
            $colorTypes[2] = "royalblue";
            $colorTypes[3] = "green";
            $colorTypes[4] = "orange";
            $colorTypes[6] = "cyan";

            $tableMatrix = array();
            while($row = mysql_fetch_array($res)) {
                $tables = explode(',', $row['table']);
                foreach($tables as $t){
                    if(!array_key_exists($t, $tableMatrix) || !is_array($tableMatrix[$t])){
                        $tableMatrix[$t] = array();
                    }
                    $tableMatrix[$t][] = array(
                        'typeId' => intval($row['typeId']),
                        'offset' => (strtotime($row["start"]) - $start) / 3600 ,
                        'duration'=> intval($row['duration']),
                        'partyId'=>$row['partyId'],
                        'name'=>$row['name'],
                        'rowspan' => count($tables),
                        'start' => strftime('%H:%M', strtotime($row['start'])),
                    );
                }
            }

            // Deals with bad planning (table overflow)
            $backupOffset = 0;
            // To deal with rawspan
            $writtenParties = array();


            // OUTPUT DATA

            foreach($tableMatrix as $table => $parties){

                echo "<tr><td>".$table."</td>";
                $currentIndex = 0;

                foreach($parties as $p){

                    // previous empty slots
                    for($i = $currentIndex ; $i < $p['offset'] ; $i++){
                        echo "<td align='center'>&nbsp</td>";
                    }

                    // party slots
                    if( ! in_array($p['partyId'], $writtenParties)) { ?>
                        <td align='center'
                            colspan='<?=$p['duration']?>'
                            rowspan='<?=$p['rowspan']?>'
                            bgcolor='<?=$colorTypes[$p["typeId"]]?>'>
                            <b>P<?= $p["partyId"]." - ".$p["name"]?></b>

                            <?php if($p['offset'] < $currentIndex){?>
                                <b><font color="red"> - PROBLEME: Commence a <?= $p['start']?></font></b>
                                <?php $warning = true;
                            } ?>

                        </td>
                    <?php }
                    $currentIndex = $p['offset'] + $p['duration'];

                    // Deals with bad planning (table overflow)
                    if(@$warning){
                        $currentIndex += ($p['duration'] + $backupOffset - $p['offset']);
                        $warning = false;
                    }
                    $backupOffset = $p['offset'];

                    // Deals with rowspans
                    $writtenParties[] = $p['partyId'];

                }

                // Fill landing slots
                for($i = $currentIndex ; $i < $duration ; $i++){
                    echo "<td align='center'>&nbsp</td>";
                }
                echo "</tr>";
            }
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