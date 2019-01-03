<?php

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');
require_once(dirname(__FILE__).'/../classes/inscription.php');

$user = User::getFromSession();
If(!$user && isset($_SESSION["userEmail"])){
	$user = User::pseudoAuth($_SESSION["userEmail"]);
}

// Libellés des états de parties
$stateLabels = array();
$stateLabels['created'] = "Créée";
$stateLabels['verified'] = "Vérifiée";
$stateLabels['validated'] = "Validée";
$stateLabels['refused'] = "Refusée";
$stateLabels['canceled'] = "Annulée";

$controls = new Controls();

$mysqli = new mysqli(HOST, USER, PASSWORD, DB);
/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}
$mysqli->query("SET NAMES 'utf8'");

$isListShowable = $controls->isAppOpen() || ($user && $user->getRole() == 'administrator');
$isListShowable = $isListShowable && (@$_GET['action'] != 'unsubscribe') ;

// filters
$filtered = false;
$addTitle = "";
$sql = "SELECT p.* FROM Parties p";
$sqlCount = "SELECT COUNT(p.partyId) AS NumberOfParties FROM Parties as p";
$join = "";
$where = array();
$limit = "";
$order = "";

$freeSpaceOnly = false;
if(isset($_GET['free-space-only']) && $_GET['free-space-only'] == "on" ){
	$freeSpaceOnly = true;
	$sql = "SELECT distinct (p.partyId), p.* FROM Parties p
			where (
				p.partyId not in (
					select i.partyId from Inscriptions i where i.partyId = p.partyId
				) or p.playerMax > (
					SELECT count(i.partyId) from Inscriptions i WHERE i.partyId = p.partyId
				)
			) ";
	$sqlCount = "SELECT count(distinct (p.partyId)) AS NumberOfParties, p.* FROM Parties p
				where (
					p.partyId not in (
						select i.partyId from Inscriptions i where i.partyId = p.partyId
					) or p.playerMax > (
						SELECT count(i.partyId) from Inscriptions i WHERE i.partyId = p.partyId
					)
				) ";
}

if(isset($_GET['filter'])){
    switch ($_GET['filter']){

        // Filter by mail (or current user if logged in) => Parties I will play
        case 'mail':
            if(!$user && isset($_GET['email'])){
                $user = User::pseudoAuth($_GET['email']);
            }
            if($user){
                $_SESSION["userEmail"] = $user->getEmail();
                $join = " JOIN Inscriptions i ON p.partyId=i.partyId ";
				$where[] = "i.userId=".$user->getId();
				$filtered = true;
				$addTitle = " auxquelles je participe";
            }else{
                $isListShowable = false;
            }
            break;

        // Parties I animate
        case 'animate':
            if($user){
				$where[] = "userId=".$user->getId();
				$filtered = true;
				$addTitle = " que j'anime";
            }else{
                $isListShowable = false;
            }
    }
}

if($user && $user->getRole() == 'administrator'){
    // Admin: Take all parties
}elseif($user && $user->getRole() == 'animator'){
    // MJ: Take all state except canceled, then filter on animated by me or state is validated or verified.
    $where[] = "p.state NOT LIKE 'canceled' AND (p.state in ('validated', 'verified') OR p.userId = ".$user->getId().")";
}else{
    // Others: Take only verified or validated parties
    $where[] = "p.state in ('validated', 'verified')";
}

$filterYear = false;
$selectedTypes = [];
$selectedAnimators = [];
$selectedStates = [];
if(@$_GET['formFiltered']){
    if(@$_GET['year'] != "")
	{
		$filterYear = true;
		if (is_numeric($_GET['year'])) $where[] = "p.year = ".$_GET['year'];
	}
	if(@$_GET['typeId'] != "" && is_array($_GET['typeId'])){
        $selectedTypes = $_GET['typeId'];
		$where[] = "p.typeId IN (".implode($selectedTypes, ','). ")";
	}
	if(@$_GET['animId'] != "" && is_array($_GET['animId'])){
        $selectedAnimators = $_GET['animId'];
		$where[] = "p.userId IN (".implode($selectedAnimators, ','). ")";
	}

    if(is_array(@$_GET['partyState'])) {
        foreach ($_GET['partyState'] as $selectedState) {
            if (array_key_exists($selectedState, $stateLabels)) {
                $selectedStates[] = $selectedState;
            }
        }
    }
	if(count($selectedStates) > 0) {
		$where[] = "p.state IN ('".implode($selectedStates, "','")."')";
	}
}
$thisYear = $controls->getDate(Controls::CONV_START, '%Y');
if(!$filterYear) $where[] = "p.year = ".$thisYear;

$sortType = "p.start";
if (isset($_GET['sortType'])){
	switch ($_GET['sortType']){
		case "duration":
			$sortType = "p.duration";
			break;
		case "partyName":
			$sortType = "p.name";
			break;
		case "partyId":
			$sortType = "p.partyId";
	}
}
$sortOrder = "ASC";
if(isset($_GET['sortOrder']) && $_GET['sortOrder'] == "desc"){
	$sortOrder = "DESC";
}
$order = " ORDER BY ".$sortType." ".$sortOrder;


if($isListShowable){

    echo "<h1>Liste des parties".$addTitle."</h1>";

	// Lien pour ajouter une partie
	if (!$user || $user->getRole() == 'player') {
		$forwardValues = "page=".$_GET['page']."&";
		if(isset($_GET['filter'])){
			$forwardValues .= "filter=".$_GET['filter']."&";
		}
		if(isset($_GET['email'])){
			$forwardValues .= "email=".$_GET['email']."&";
		}
		if(isset($_GET['p'])){
			$forwardValues .= "p=".$_GET['p']."&";
		}
		echo "<div><a href='login.php?forward=".urlencode($forwardValues)."'>Vous pouvez vous authentifier ici pour inscrire ou éditer une partie.</a></div>";
	}

    // Lightbox qui apparaît au clic sur "Je veux m'inscrire à cette partie"
    // Cf. javascript: orcidee.manager.list.dialogBox
    ?>
	<div id="dialog-form" style="display:none;">
        <p class="partyTitle"></p>
        <form>
        <fieldset>
            <?php
            if($user){
                $email = $user->getEmail();
                $lastname = $user->getLastname();
                $firstname = $user->getFirstname();
			}
            echo "<label for='email'>Email</label><input type='text' name='email' id='email' class='text ui-widget-content' value='".@$email."' />";
            echo "<label for='lastname'>Nom</label><input type='text' name='lastname' id='lastname' class='text ui-widget-content' value='".@$lastname."' />";
            echo "<label for='firstname'>Prénom</label><input type='text' name='firstname' id='firstname' class='text ui-widget-content' value='".@$firstname."' />";
            ?>
        </fieldset>
        </form>
        <p>Une confirmation va être envoyée par email, avec toutes les infos.</p>
    </div>

	<!-- Form to filter and sort results -->
	<form id="filteringForm" action="" method="GET">
		<input type='hidden' name="formFiltered" value="true">
		<input type='hidden' name="page" value="list">
		<div>
			<fieldset>
				<legend>Filtrer par :</legend>

                <?php if ($user && $user->isAdmin()) { ?>
                    <label for="year">Année</label>
                    <select name="year" id="year">
                        <?php
                        $years = Party::getYears();
                        foreach($years as $year){
                            echo "<option ".((@$_GET['year']==$year) ? "selected='selected'" : "")." value='".$year."' >".$year."</option>";
                        } ?>
                        <option value='all'>Toutes</option>
                    </select>
                <?php } ?>


                <label for="typeId">Type</label>
				<select name='typeId[]' id="typeId" multiple>
					<?php
						// Get the types from DB
						foreach(Party::getTypes() as $typeId => $type){
							echo "<option ".(in_array($typeId, $selectedTypes) ? "selected='selected'" : "")." value='".$typeId."' title='".$type['description']."'>".stripslashes($type['name'])."</option>";
						}
					?>
				</select>


				<label for="animId">Animateur</label>
				<?php
				// ANIMATOR FILTER
				$defaultYear = $controls->getDate(Controls::CONV_START, "%Y");
				$year = is_numeric(@$_GET['year'])?@$_GET['year']:$defaultYear;

				$sqlUsers = "SELECT Distinct Users.userId, Users.firstname, Users.lastname FROM Users".
				" JOIN Parties ON Users.userId = Parties.UserId".$join.
				" WHERE Parties.year = ".$year;

				if(count($selectedTypes) > 0){
					$sqlUsers .= " AND Parties.typeId IN (".implode($selectedTypes, ',').')';
				}
				$sqlUsers .= " order by Users.firstname, Users.lastname";
				$resUsers = $mysqli->query($sqlUsers); ?>
				<select name='animId[]' multiple id="animId">
					<?php while ($rowUser = $resUsers->fetch_assoc()) {
						echo "<option ".(in_array($rowUser['userId'], $selectedAnimators) ? "selected='selected'" : "")." value='".$rowUser['userId']."'>".stripslashes($rowUser['firstname'])." ".stripslashes($rowUser['lastname'])."</option>";
					} ?>
				</select>

				<?PHP
					if($user && $user->getRole() == 'administrator'){
				?>
						<label for="partyState">Status</label>
						<select name='partyState[]' id="partyState" multiple>
							<option value='created' <?= in_array("created", $selectedStates) ? "selected='selected'" : "" ?>><?= $stateLabels['created'] ?></option>
							<option value='verified' <?= in_array("verified", $selectedStates) ? "selected='selected'" : "" ?>><?= $stateLabels['verified'] ?></option>
							<option value='validated' <?= in_array("validated", $selectedStates) ? "selected='selected'" : "" ?>><?= $stateLabels['validated'] ?></option>
							<option value='refused' <?= in_array("refused", $selectedStates) ? "selected='selected'" : "" ?>><?= $stateLabels['refused'] ?></option>
							<option value='canceled' <?= in_array("canceled", $selectedStates) ? "selected='selected'" : "" ?>><?= $stateLabels['canceled'] ?></option>
						</select>
				<?PHP
					}
				?>

				<div class="elements-per-page clear">
					<label for="elements-per-page">Nombre de parties affichées par page</label>
					<select name='elements-per-page'>
						<option value='10' <?= (@$_GET['elements-per-page']=='10')?'selected="selected"':''?>>10</option>
						<option value='20' <?= (@$_GET['elements-per-page']=='20')?'selected="selected"':''?>>20</option>
						<option value='50' <?= (@$_GET['elements-per-page']=='50')?'selected="selected"':''?>>50</option>
						<option value='all' <?= ! in_array(@$_GET['elements-per-page'], ['10', '20', '50'])?'selected="selected"':''?>>tout</option>
					</select>
				</div>

				<div class="free-space-only clear">
					<label for="free-space-only">Seulement les parties avec de la place disponible</label>
					<input type="checkbox" name="free-space-only" id="free-space-only" <?php echo (@$_GET['free-space-only']=="on") ? "checked='checked'" : "" ?> />
				</div>

			</fieldset>
			<fieldset>
				<legend>Trier par :</legend>
				<select name='sortType'>
					<option value='startTime' <?php echo (@$_GET['sortType']=="startTime") ? "selected='selected'" : "" ?>>Heure de début</option>
					<option value='duration' <?php echo (@$_GET['sortType']=="duration") ? "selected='selected'" : "" ?>>Durée</option>
					<option value='partyName' <?php echo (@$_GET['sortType']=="partyName") ? "selected='selected'" : "" ?>>Nom du jeu</option>
					<option value='partyId' <?php echo (@$_GET['sortType']=="partyId") ? "selected='selected'" : "" ?>>Numéro de partie</option>
				</select>
				<select name='sortOrder'>
					<option value='asc' <?php echo (@$_GET['sortOrder']=="asc") ? "selected='selected'" : "" ?>>croissant</option>
					<option value='desc' <?php echo (@$_GET['sortOrder']=="desc") ? "selected='selected'" : "" ?>>décroissant</option>
				</select>
			</fieldset>
			<input type="submit" class="submit" value="Filtrer et Trier" />
		</div>

		<?php

		// PAGINATION
		$sqlCount .= $join;
		if(count($where) > 0) {
			if($freeSpaceOnly){
				$sqlCount .= ' AND ' . implode(" AND ", $where);
			}else {
				$sqlCount .= ' WHERE ' . implode(" AND ", $where);
			}
		}

		$res = $mysqli->query($sqlCount);
		$row = $res->fetch_assoc();
		$total = $row['NumberOfParties'];

		if ($total > 0){

			// Pagination's stuff
			$limit = '';
			$pageSize = $total;
			$pageSizeParam = @$_GET['elements-per-page'];
			$max = 1;
			$currentP = 1;
			if (in_array($pageSizeParam, ['10', '20', '50'])) {
				$pageSize = intval($pageSizeParam);
				$max = ceil($total / $pageSize);
				if (isset($_GET['pageNb']) && is_numeric($_GET['pageNb']) && $_GET['pageNb'] > 0 && $_GET['pageNb'] <= $max) {
					$currentP = $_GET['pageNb'];
				}
				$limit = " LIMIT " . (($currentP - 1) * $pageSize) . "," . $pageSize;
			}

			// construction de la requete
			$sql .= $join;
			if(count($where) > 0) {
				if($freeSpaceOnly){
					$sql .= ' AND ' . implode(" AND ",$where);
				}else{
					$sql .= ' WHERE ' . implode(" AND ",$where);
				}
			}

			$sql .= $order . $limit;

			$res = $mysqli->query($sql);

			?>
			<div class='game-list'>
				<input type='hidden' name="pageNb" value="1">

				<?php include('includes/pagination.php'); ?>

				<ul id='game-list' cellspacing='0' cellpadding='0'>

					<?php

					// Foreach parties in this page

					while ($row = $res->fetch_assoc()) {

						$party = new Party($row['partyId'], false);

						$isAdmin = $user && $user->isAdmin();
						$animates = $user && $user->animates($party->getId());
						$participates = $user && $user->participatesTo($party->getId());

						$showable = $party->getState() != 'canceled' || $isAdmin || $animates;

						$type = $party->getType();
						$date = strftime("%d.%m.%Y à %H:%M", strtotime($party->getStart()));

						if ($showable) { ?>
						<li>

							<div class="party-header">
								<span class="name">
									<?= $party->getName() ?>
									<?= strlen($party->getScenario()) > 0 ? ':':'' ?>
								</span>
								<span class="scenario"><?= $party->getScenario() ?></span>
							</div>

							<div class="type"><?= stripslashes($type['name']); ?></div>

							<div class="planning">
								<span class="start">Débute le: <?= $date ?>, durée: <?= $party->getDuration() ?>h.</span> &ndash;
								<?php
                $remaining = $party->getPlayerMax() - count($party->getPlayers());
                $isFull = $remaining < 0;
								?>
								<span class="free-space <?= $isFull ? 'red' : 'green' ?>">
									<?= $isFull ? 'Complet' : $remaining.' place(s) restante(s)'?>
								</span> &ndash; <a href="?page=party&partyId=<?= $party->getId() ?>">Détails & Inscription</a>
							</div>

							<div class="description">
								<?= View::MultilineFormat($party->getDescription(), true);?>
							</div>

							<?php
							include('includes/moderation.php');
							?>

							<div class="separator"></div>
						</li>
						<?php
						}
					} ?>
				</ul>

				<?php include('includes/pagination.php'); ?>

			</div>
			<?php
		}else{
			echo "<p>Aucune partie !</p>";
			if ($filtered){
				echo '<p><a href="'.Controls::home().'?page=list">Liste complète des parties</a></p>';
			}
		}
	echo "</form>";

// mail filter but invalid or unknown email
}elseif(@$_GET['filter']=='mail'){
	if (isset($_GET['email'])){
		$emailText = "<p>L'email '".$_GET['email']."' n'est pas reconnu, merci d'en saisir un valide pour filtrer les parties auxquelles vous participez !</p>";
	}else{
		$emailText = "<p>Merci de saisir un email pour filtrer les parties auxquelles vous participez :</p>";
	}
	?>
	<div>
		<form action='' method='GET'>
			<input type="hidden" name="page" value="list" />
			<input type="hidden" name="filter" value="mail" />
			<?php echo $emailText; ?><input type="text" name='email' value='<?php echo @$_GET['email']; ?>' />
            <input type='submit' value="Voir mes parties" class='submit' />
		</form>
	</div>
<?php

} else {

    echo "<p>Impossible d'afficher cette page pour le moment.</p>";
    if(!$user){
        $forwardValues = "page=".$_GET['page']."&";
        if(isset($_GET['filter'])){
            $forwardValues .= "filter=".$_GET['filter']."&";
        }
        if(isset($_GET['email'])){
            $forwardValues .= "email=".$_GET['email']."&";
        }
        if(isset($_GET['p'])){
            $forwardValues .= "p=".$_GET['p']."&";
        }
        echo "<a href='login.php?forward=".urlencode($forwardValues)."'>Vous pouvez vous enregistrer ou vous authentifier comme MJ ou administrateur en cliquant ici.</a>";
    }
    if(!$controls->isPlayerOpen()){
        echo "<p>L'inscription joueur n'est pas encore disponible.</p>";
    }
}

$mysqli->close();