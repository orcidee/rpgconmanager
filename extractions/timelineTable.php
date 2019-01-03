<?php

require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');
require_once(dirname(__FILE__).'/../classes/inscription.php');

$mysqli = new mysqli(HOST, USER, PASSWORD, DB);
/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$user = User::getFromSession();

if($user){

    if($user->getRole() == "administrator"){
        $controls = new Controls();

        ob_start();
        header("Content-type: application/vnd.ms-excel; charset=iso-8859-1");
        header("Content-Disposition: attachment; filename=TimeLine_Orcidee.xls");

        $startDate = new DateTime($controls->getDate(Controls::CONV_START, '%Y-%m-%d %H:%M:00'));
        $start = $controls->getDate(Controls::CONV_START);
        $end = $controls->getDate(Controls::CONV_END);
        $startHour = intval($startDate->format("H"));
        $duration = intval($end - $start) / 3600;

        $saturdayColumns = 48 - $startHour * 2;
        $sundayColumns = $duration * 2 - $saturdayColumns;
        $tableName = "TIMELINE DE LA CONVENTION PAR TABLE";

        // TABLE HEADER
?>
        <table border='1' width='100%' cellspacing='3' cellpadding='3'>
            <tr>
                <td align='center' valign='middle' colspan='<?= $duration * 2 + 3 ?>'>
                    <font size='+6'><b><?= $tableName ?></b><br><br></font>
                </td>
            </tr>
            <tr>
                <td align='center' valign='middle'>&nbsp;</td>
                <?php include '_days.php' ?>
            </tr>
            <tr>
                <td align='left' WIDTH='300'><font size='-2'>&nbsp; <b>Table</b> &nbsp;</font></td>
                <?php include('_hours.php'); ?>
    </tr><?php

            // FETCH & PREPARE DATA

            $thisYear = $controls->getDate(Controls::CONV_START, '%Y');
            $res = $mysqli->query("SELECT Parties.* FROM Parties WHERE Parties.state in ('validated', 'verified') and Parties.table is not null AND Parties.year = ".$thisYear." order by Parties.start ASC");

            $colorTypes = array();
            $colorTypes[1] = "yellow";
            $colorTypes[2] = "royalblue";
            $colorTypes[3] = "green";
            $colorTypes[4] = "orange";
            $colorTypes[6] = "cyan";

            $tableMatrix = array();
            while($row = $res->fetch_array()) {
                $tables = explode(',', $row['table']);
                foreach($tables as $t){
                    if(!array_key_exists($t, $tableMatrix) || !is_array($tableMatrix[$t])){
                        $tableMatrix[$t] = array();
                    }
                    $tableMatrix[$t][] = array(
                        'typeId' => intval($row['typeId']),
                        'offset' => (strtotime($row["start"]) - $start) / 3600 * 2,
                        'duration'=> intval($row['duration']) * 2,
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
                for($i = $currentIndex ; $i < $duration * 2; $i++){
                    echo "<td align='center'>&nbsp</td>";
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
