<?php

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
		<form id="tablesForm" action="" method="POST" class="admin-table-form">

      <?php

      $thisYear = Controls::getDate(Controls::CONV_START, '%Y');
      $sql = "SELECT Parties.*, Types.typeId as typeId, Users.* FROM Parties".
          " JOIN Types on Parties.typeId = Types.typeId".
          " JOIN Users on Parties.userId = Users.userId".
          " WHERE Parties.state in ('validated', 'verified')".
          " AND Parties.year = ".$thisYear.
          " ORDER BY Parties.start ASC";
      $res = mysql_query ( $sql );

      $updatedParties = array();
      $errorParties = array();

      $types = Party::getTypes();

      $parties = array();
      foreach ($types as $type) {
        $parties[$type['typeId']] = array();
      }

      while($row = mysql_fetch_array($res)) {
        $parties[$row['typeId']][] = $row;
      }

      foreach (Party::getTypes() as $type) {

        ?><h2><?= stripslashes($type['name']) ?></h2>
        <table border='1' width='100%'>
          <thead>
            <tr>
              <td>N°</td>
              <td>Nom de la partie</td>
              <td>Animateur</td>
              <td>Début</td>
              <td>Fin</td>
              <td>Nb joueurs min/max</td>
              <td>Nb de table(s)</td>
              <td>Table</td>
            </tr>
          </thead>
          <tbody>
            <?php

            foreach ($parties[$type['typeId']] as $row) {

                    $tables = $row['table'];
                    // Save new table(s)
                    if (isset($_POST['table-0-' . $row["partyId"]])) {
                        $tables = $_POST['table-0-' . $row["partyId"]];
                        for ($i = 1; $i <= 3; $i++) {
                            if (isset($_POST["table-$i-" . $row["partyId"]])) {
                                $tables .= ',' . $_POST["table-$i-" . $row["partyId"]];
                            }
                        }
                        if (Party::setTableForParty($row["partyId"], $tables)) {
                            $updatedParties[] = $row["partyId"];
                        } else {
                            $errorParties[] = $row["partyId"];
                        }
                    }
                    ?>
                  <tr>
                    <td><?= $row["partyId"] ?></td>
                    <td title=""><?= stripslashes($row["name"]) ?></td>
                    <td><?= stripslashes($row["firstname"]) . " " . stripslashes($row["lastname"]) ?></td>
                    <td><?= strftime("%d.%m.%Y à %H:%M", strtotime($row['start'])) ?></td>
                    <td><?= strftime("%d.%m.%Y à %H:%M", strtotime($row['start']) + ($row['duration'] * 3600)) ?></td>
                    <td><?= $row['playerMin'] . "/" . $row['playerMax'] ?></td>
                    <td><?= $row['tableAmount'] ?></td>
                    <td>
                        <?php $tablesArray = explode(',', $tables); ?>
                      <input type='text' name='table-0-<?= $row["partyId"] ?>' value='<?= $tablesArray[0] ?>'
                             maxlength="20"/>
                        <?php
                        $tableAmount = intval($row['tableAmount']);
                        for ($i = 1; $i < $tableAmount; $i++) {
                            $value = array_key_exists($i, $tablesArray) ? $tablesArray[$i] : '';
                            ?><input type='text' name='table-<?= $i . '-' . $row["partyId"] ?>' value='<?= $value ?>'
                                     maxlength="20" /><?php
                        } ?>
                    </td>
                  </tr>
                    <?php
            }
            ?>
          </tbody>
        </table><?php
      }
      ?>
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