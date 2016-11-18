<?php

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');
require_once(dirname(__FILE__).'/../classes/inscription.php');

$user = User::getFromSession();
If(!$user && isset($_SESSION["userEmail"])){
    $user = User::pseudoAuth($_SESSION["userEmail"]);
}

$partyId = $_GET['partyId'];

$party = new Party($partyId, false);
$animator = $party->getAnimator();


?>

<h1><?= $party->getName() ?></h1>
<h2><?= $party->getTypeName() ?></h2>

<div class="page-detail">

    <div class="organisation">
        <h3>Organisation</h3>
        <ul>
            <li>Status: <?= $party->getState() ?></li>
            <li>Début: <?= $party->getStart() ?></li>
            <li>Durée: <?= $party->getDuration() ?></li>
            <li>Animateur: <?= $animator->getFirstname().' '.$animator->getLastname() ?></li>
            <li>Nb de tables: <?= $party->getTableAmount() ?></li>
        </ul>
    </div>

    <div class="meta">
        <h3>Description</h3>
        <ul>
            <li>Scénario: <?= $party->getScenario() ?></li>
            <li>Genre: <?= $party->getKind() ?></li>
            <li>Joueurs min: <?= $party->getPlayerMin() ?></li>
            <li>Joueurs max: <?= $party->getPlayerMax() ?></li>
            <li>Niveau de jeu: <?= $party->getLevel() ?></li>
            <li>Langue: <?= $party->getLanguage() ?></li>
        </ul>
    </div>

    <div class="intro">
        <h3>Introduction</h3>
        <?= $party->getDescription() ?>
    </div>

    <div class="players">
        <h3>Inscriptions</h3>
        <ul>
            <?php
            /** @var User $player */
            foreach ($party->getPlayers() as $player) { ?>
                <li><?= $player->getFirstname().' '.$player->getLastname() ?></li>
            <?php } ?>
        </ul>
    </div>
</div>