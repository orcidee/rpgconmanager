<?php

require_once(dirname(__FILE__).'/../conf/bd.php');
require_once(dirname(__FILE__).'/../conf/conf.php');
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

// Debug
echo "<div class='dbg'>User:";
echo ($user) ? ($user->getLastname()." (".$user->getRole().") ") : "0" ;
echo "<br/>Adresse en session : ".@$_SESSION["userEmail"]."</div>";

$isListShowable = Controls::isAppOpen() || ($user && $user->getRole() == 'administrator');
$isListShowable = $isListShowable && (@$_GET['action'] != 'unsubscribe') ;

// filters
$filtered = false;
$addTitle = "";
$sql = "SELECT Parties.* FROM Parties";
$sqlCount = "SELECT COUNT(Parties.partyId) AS NumberOfParties FROM Parties";
$join = "";
$where = array();
$limit = "";
$order = "";
if(isset($_GET['filter'])){
    switch ($_GET['filter']){
        
        // Filter by mail (or current user if logged in) => Parties I will play
        case 'mail':
            if(!$user && isset($_GET['email'])){
                $user = User::pseudoAuth($_GET['email']);
            }
            if($user){
                $_SESSION["userEmail"] = $user->getEmail();
                $join = " JOIN Inscriptions ON Parties.partyId=Inscriptions.partyId ";
				$where[] = "Inscriptions.userId=".$user->getId();
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
    $where[] = "Parties.state NOT LIKE 'canceled' AND (Parties.state in ('validated', 'verified') OR Parties.userId = ".$user->getId().")";
}else{
    // Others: Take only verified or validated parties
    $where[] = "Parties.state in ('validated', 'verified')";
}

if(@$_POST['formFiltered']){
    if(@$_POST['year'] != "" && is_numeric($_POST['year']) && $_POST['year'] != 'all'){
		$where[] = "Parties.year = ".$_POST['year'];
	}
	if(@$_POST['typeId'] != "" && is_numeric($_POST['typeId'])){
		$where[] = "Parties.typeId = ".$_POST['typeId'];
	}
	if(@$_POST['animId'] != "" && is_numeric($_POST['animId'])){
		$where[] = "Parties.userId = ".$_POST['animId'];
	}
	if(isset($_POST['partyState']) && $_POST['partyState'] != "" && strlen($stateLabels[$_POST['partyState']]) > 0){
		$where[] = "Parties.state = '".$_POST['partyState']."'";
	}
}
$sortType = "Parties.start";
if (isset($_POST['sortType'])){
	switch ($_POST['sortType']){
		case "duration":
			$sortType = "Parties.duration";
			break;
		case "partyName":
			$sortType = "Parties.name";
			break;
		case "partyId":
			$sortType = "Parties.partyId";
	}
}
$sortOrder = "ASC";
if(isset($_POST['sortOrder']) && $_POST['sortOrder'] == "desc"){
	$sortOrder = "DESC";
}
$order = " ORDER BY ".$sortType." ".$sortOrder;

// Debug
echo "<div class='dbg'>User:";
echo ($user) ? ($user->getLastname()." (".$user->getRole().") ") : "0" ;
echo "<br/>Is list showable: $isListShowable";
echo "<br/>sql: $sql</div>";


if($isListShowable){
    
    echo "<h1>Liste des parties".$addTitle."</h1>";

    
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
        <p>Une confirmation va t'être envoyée par email, avec toutes les infos.</p>
    </div>

	<!-- Form to filter and sort results -->
	<form id="filteringForm" action="" method="POST">
		<input type='hidden' name="formFiltered" value="true">
		<div>
			<fieldset>
				<legend>Filtrer par :</legend>
                <label for="year">Année</label>
                <select name="year">
                    <option value='all'>Toutes</option>
                    <?php
                    $years = Party::getYears();
                    foreach($years as $year){
                        echo "<option ".((@$_POST['year']==$year) ? "selected='selected'" : "")." value='".$year."' >".$year."</option>";
                    } ?>
                </select>
				<label for="typeId">Type</label>
				<select name='typeId'>
					<option value=''>---</option>
					<?php
						// Get the types from DB
						foreach(Party::getTypes() as $typeId => $type){
							echo "<option ".((@$_POST['typeId']==$typeId) ? "selected='selected'" : "")." value='".$typeId."' title='".$type['description']."'>".stripslashes($type['name'])."</option>";
						}
					?>
				</select>
				<label for="animId">Animateur</label>
				<select name='animId'>
					<option value=''>---</option>
					<?php
						// Get the users from DB
						$sqlUsers = "SELECT Distinct Users.userId, Users.firstname, Users.lastname FROM Users".
									" JOIN Parties ON Users.userId = Parties.UserId".
									$join;
						if(count($where) > 0) {
							$sqlUsers .= ' WHERE ' . implode(" AND ",$where);
						}
						$sqlUsers .= " order by Users.firstname, Users.lastname";
						$resUsers = mysql_query ( $sqlUsers );
						while ($rowUser = mysql_fetch_assoc($resUsers)) {
							echo "<option ".((@$_POST['animId']==$rowUser['userId']) ? "selected='selected'" : "")." value='".$rowUser['userId']."'>".stripslashes($rowUser['firstname'])." ".stripslashes($rowUser['lastname'])."</option>";
						}
					?>
				</select>
				<?PHP
					if($user && $user->getRole() == 'administrator'){
				?>
						<label for="partyState">Status</label>
						<select name='partyState'>
							<option value=''>---</option>
							<option value='created' <?php echo (@$_POST['partyState']=="created") ? "selected='selected'" : "" ?>><?= $stateLabels['created'] ?></option>
							<option value='verified' <?php echo (@$_POST['partyState']=="verified") ? "selected='selected'" : "" ?>><?= $stateLabels['verified'] ?></option>
							<option value='validated' <?php echo (@$_POST['partyState']=="validated") ? "selected='selected'" : "" ?>><?= $stateLabels['validated'] ?></option>
							<option value='refused' <?php echo (@$_POST['partyState']=="refused") ? "selected='selected'" : "" ?>><?= $stateLabels['refused'] ?></option>
							<option value='canceled' <?php echo (@$_POST['partyState']=="canceled") ? "selected='selected'" : "" ?>><?= $stateLabels['canceled'] ?></option>
						</select>
				<?PHP
					}
				?>
			</fieldset>
			<fieldset>
				<legend>Trier par :</legend>
				<select name='sortType'>
					<option value='startTime' <?php echo (@$_POST['sortType']=="startTime") ? "selected='selected'" : "" ?>>Heure de début</option>
					<option value='duration' <?php echo (@$_POST['sortType']=="duration") ? "selected='selected'" : "" ?>>Durée</option>
					<option value='partyName' <?php echo (@$_POST['sortType']=="partyName") ? "selected='selected'" : "" ?>>Nom du jeu</option>
					<option value='partyId' <?php echo (@$_POST['sortType']=="partyId") ? "selected='selected'" : "" ?>>Numéro de partie</option>
				</select>
				<select name='sortOrder'>
					<option value='asc' <?php echo (@$_POST['sortOrder']=="asc") ? "selected='selected'" : "" ?>>croissant</option>
					<option value='desc' <?php echo (@$_POST['sortOrder']=="desc") ? "selected='selected'" : "" ?>>décroissant</option>
				</select>
			</fieldset>
			<input type="submit" class="submit" value="Filter et Trier" />
		</div>
		<div class='dbg'>SQL users : <?= $sqlUsers ?></div>
	
		<?php
		
		// PAGINATION
		$sqlCount .= $join;
		if(count($where) > 0) {
			$sqlCount .= ' WHERE ' . implode(" AND ",$where);
		}   

		echo "<div class='dbg'>SQL count: $sqlCount</div>";

		$res = mysql_query ( $sqlCount );
		$row = mysql_fetch_assoc($res);
		$total = $row['NumberOfParties'];
		
		if ($total > 0){
			// Pagination's stuff
			$pageSize = 10;
			$max = ceil($total / $pageSize);
			$currentP = 1;
			if(isset($_POST['pageNb']) && is_numeric($_POST['pageNb']) && $_POST['pageNb'] > 0 && $_POST['pageNb'] <= $max){
				$currentP = $_POST['pageNb'];
			}
			$limit = " LIMIT ". (($currentP-1)* $pageSize) ."," . $pageSize;

			// construction de la requete
			$sql .= $join;
			if(count($where) > 0) {
				$sql .= ' WHERE ' . implode(" AND ",$where);
			}
			
			$sql .= $order . $limit;
			
			echo "<div class='dbg'>total: $total. <br/>SQL paginé: $sql</div>";

			$res = mysql_query ( $sql );
			
			echo "<div>Parties ".((($currentP-1)* $pageSize) + 1)." à ".min($currentP * $pageSize, $total)." sur ".$total."</div>";

			?>
			<br/>
			<div class='list'>
				<ul id='game-list' cellspacing='0' cellpadding='0'>

					<?php
					
					// Foreach parties in this page
					
					while ($row = mysql_fetch_assoc($res)) {
						
						$p = new Party($row['partyId'], false);
						
						$isAdmin = $user && $user->getRole() == "administrator";
						$animates = $user && $user->getRole() == "animator" && $user->animates($p->getId());
						$participates = $user && $user->participatesTo($p->getId());
						
						$showable = $p->getState() != 'canceled' || $isAdmin || $animates;
						
						$type = $p->getType();
						
						if($showable){
						
						?>
						<li>
						
							<div class='main'>
								<div class="top clear">
									<div class='left type'><?php echo stripslashes($type['name']); ?></div>
									<div class='right'>
										
										<?php
										
										$allow = array(
											'edit' => ($animates || $isAdmin) && ($p->getState() == 'created' || $p->getState() == 'verified' || $p->getState() == 'refused' || $p->getState() == 'validated'),
											'cancel' => ($animates || $isAdmin) && $p->getState() !== 'canceled',
											'refuse' => $isAdmin && ($p->getState() == 'created' || $p->getState() == 'verified' || $p->getState() == 'validated'),
											'verify' => $isAdmin && ($p->getState() == "created" || $p->getState() == "refused"),
											'validate' => $isAdmin && $p->getState() == "verified",
											'subscribe' => !$animates && !$participates && $p->getState() == 'validated' && Controls::isPlayerOpen()
										);
										
										if($user && ($user->getRole() == "administrator" || $user->getRole() == "animator")){ ?>
										
											<div class='state'>
												<span>Status : </span><?php echo $stateLabels[$p->getState()]; ?>
											</div>
											
										<?php }
										
										echo "<div class='actions clear' data-id='".$p->getId()."' data-state='".$p->getState()."'>";
											echo ($allow['edit']) ? "<a href='?page=edit&partyId=".$p->getId()."' class='edit'><img src='http://www.orcidee.ch/orcidee/manager/img/edit.png' title='Éditer'/></a>" : "";
											echo ($allow['cancel']) ? "<a href='actions/party.php' class='cancel'><img src='http://www.orcidee.ch/orcidee/manager/img/cancel.png' title='Annuler'/></a>" : "";
											echo ($allow['refuse']) ? "<a href='actions/party.php' class='refuse' ><img src='http://www.orcidee.ch/orcidee/manager/img/refuse.png'title='Refuser'/></a>" : "";
											echo ($allow['verify']) ? "<a href='actions/party.php' class='verify'><img src='http://www.orcidee.ch/orcidee/manager/img/verify.png' title='Vérifier'/></a>" : "";
											echo ($allow['validate']) ? "<a href='actions/party.php' class='validate'><img src='http://www.orcidee.ch/orcidee/manager/img/validate.png' title='Valider'/></a>" : "";
										echo "</div>";
										?>
										
										
									</div>
								</div>
								<div class='clear name'>
									<span class='partyId'><?php echo $p->getId();?> - </span>
									<span class="partyName"><?php echo $p->getName();?></span>
								</div>
								
								<div class="clear left">
									<div class='scenario'>
										<span>Scénario:</span>
										<?php echo $row['scenario'];?>
									</div>
								</div>
								<div class="right">
									<div class='start'>
										<span>Début:</span>
										<?php
										$date = strftime("%d.%m.%Y à %H:%M", strtotime($row['start']));
										echo $date;
										?>
									</div>
									<div class="clear duration">
										<span>Durée:</span>
										<?php echo $row['duration'];?>h
									</div>
								</div>
							</div>
							<div class="more clear">
							<span>Description:</span>
										<?php echo View::MultilineFormat($row['description']);?>
							</div>
							<div class="more clear">
								<div class='left'>
									<div class='kind'>
										<span>Genre:</span>
										<?php echo $row['kind'];?>
									</div>
									<div class='clear playerMin'>
										<span>Joueurs min:</span>
										<?php echo $row['playerMin'];?>
									</div>
									<div class='clear playerMax'>
										<span>Joueurs max:</span>
										<?php echo $row['playerMax'];?>
									</div>
								</div>
								<div class="right border-left">
									<div class='level'>
										<span>Niveau de jeu: </span><?php 
										if($row['level']=="low") {
											$lvl = "Débutant";
										} elseif ($row['level']=="middle") {
											$lvl = "Initié";
										} else {
											$lvl = ($row['level']=="high") ? "Expert" : "Peu importe";
										}
										echo $lvl; ?>
									</div>
									<div class="clear language">
										<span>Langue:</span>
										<?php echo $row['language'];?>
									</div>
									<div class="clear animator">
										<span>Animateur:</span>
										<?php 
										$animator = $p->getAnimator();
										echo $animator->getFirstname() . " " . $animator->getLastname();
										?>
									</div>
								</div>
							</div>
							<div class='more clear'>
								<?php if($animates || $isAdmin){ ?>
								<div class='clear note'>
									<span>Note aux orgas:</span>
									<?php echo View::MultilineFormat($row['note']);?>
								</div>
								<!--div class='clear year'>
									<span>Année:</span>
									<?php echo $row['year'];?>
								</div-->
								<?php } ?>
							</div>
							<div class="more clear">
								<span>Inscrits:</span>
								<ul class='players' data-partyId="<?php echo $row['partyId'];?>">
									<?php
									foreach($p->getPlayers() as $player){
										echo "<li>".$player->getFirstname()." ".$player->getLastname();
										if ($isAdmin) {
											echo "<a href='actions/party.php' class='unsubscribeNow' player-code='".sha1($player->getId())."' player-name='".$player->getFirstname()." ".$player->getLastname()."'><img src='http://www.orcidee.ch/orcidee/manager/img/cancel.png' title='Désinscrire'/></a>";
										}elseif($user && $user->getId() == $player->getId()){
											echo "<a href='actions/party.php' class='unsubscribe' player-mail='".$player->getEmail()."' player-name='".$player->getFirstname()." ".$player->getLastname()."'><img src='http://www.orcidee.ch/orcidee/manager/img/cancel.png' title='Désinscrire'/></a>";
										}									
										echo "</li>";
									}
									?>
								</ul>
							</div>
							<div class="center">
								<?php if ($allow['subscribe']) { 
										if (count($p->getPlayers()) < $row['playerMax']) {?>
								<input type="button" class="subscribe" value="Je veux m'inscrire à cette partie !" data-partyId="<?php echo $row['partyId'];?>" />
								<?php 	} else {?>
								<span>C'est complet !</span>
								<?php 	}
									  }?>
							</div>
							<div class="separator"></div>
						</li>
						<?php
						}
					}
						?>
				</ul>
				
				<input type='hidden' name="pageNb" value="1">
				<ul class='pagination'>
					<?php
					for($i = 1 ; $i <= $max ; $i++){
						echo "<li><a href=\"#\" onclick=\"document.forms['filteringForm'].pageNb.value='".$i."';document.forms['filteringForm'].submit();\" ".(($i == $currentP)? "class='activ'" : "").">$i</a></li>";
					}
					?>
				</ul>
			
			</div>
			<?php
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
				echo "<div><a href='login.php?forward=".urlencode($forwardValues)."'>Tu peux t'authentifier ici pour inscrire ou éditer une partie.</a></div>";
			}
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
			<?php echo $emailText; ?><input type="text" name='email' value='<?php echo @$_POST['email']; ?>' />
            <input type='submit' value="Voir mes parties" class='submit' />
		</form>
	</div>
<?php

// A player want to unsubscribe from a party
} elseif(@$_GET['action'] == 'unsubscribe'){
	if(isset($_GET['partyId']) && isset($_GET['u']) ) {
		
		echo "<h1>Désincription</h1>";

		$p = new Party($_GET['partyId'], false);
		if($p && $p->isValid){
		
			$players = $p->getPlayers();
			foreach($players as $player){
				if(sha1($player->getId()) == $_GET['u']){
					// This player want to unsubscribe
					$res = Inscription::unsubscribe($p->getId(), $player->getId());
					break;
				}
			}
		
			if(!isset($res)){
				echo "<p>Ce joueur n'est pas (plus ?) inscrit sur la partie numéro ".$p->getId()." \"".$p->getName()."\".</p>";
			}elseif($res){
				echo    "<p>Le joueur ".$player->getFirstname()." ".$player->getLastname()." a correctement 
						été désincrit de la partie numéro ".$p->getId()." \"".$p->getName()."\".</p>";

				// If admin is unsubscribing a player, send a mail to this player !
				if ($user && $user->getRole() == 'administrator'){
					$isMailOk = Orcimail::unsubscribedToParty($p, $player);
					if($isMailOk){
						echo "<p>Et un mail lui a été envoyé pour l'en informer.</p>";
					}else{
						echo "<p>Mais le mail pour l'en informer n'a pas pu être envoyé.</p>";
					}
				}
			}else{
				echo "<p>Erreur lors de la désinscription.</p>";
			}
		}else{
			echo "<p>Partie '".$_GET['partyId']."' introuvable !</p>";
		}
	}else{
		echo "<p>Impossible de désinscrire un joueur sans connaitre son ID et la partie !</p>";
	}

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
        echo "<a href='login.php?forward=".urlencode($forwardValues)."'>Tu peux t'enregistrer ou t'authentifier comme MJ ou administrateur en cliquant ici.</a>";
    }
    if(!Controls::isPlayerOpen()){
        echo "<p>L'inscription joueur n'est pas encore disponible.</p>";
    }

} ?>