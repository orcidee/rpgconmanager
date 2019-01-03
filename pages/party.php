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

$isAdmin = $user && $user->isAdmin();
$animates = $user && $user->animates($partyId);
$participates = $user && $user->participatesTo($partyId);

$controls = new Controls();

$contactable = $controls->isPlayerOpen() || $isAdmin;
$subscribable = !$animates && !$participates && $party->getState() == 'validated' && $controls->isPlayerOpen();

$date = strftime("%d.%m.%Y à %H:%M", strtotime($party->getStart()));
$nbPlayers = $party->getPlayerMin() == $party->getPlayerMax() ?
    $party->getPlayerMin() : $party->getPlayerMin()." à ".$party->getPlayerMax();



// A player want to unsubscribe from a party
if(@$_GET['action'] == 'unsubscribe') {
    if (isset($_GET['partyId']) && isset($_GET['u'])) {

        echo "<h1>Désincription</h1>";

        $p = new Party($_GET['partyId'], false);
        if ($p && $p->isValid) {

            $players = $p->getPlayers();
            foreach ($players as $player) {
                if (sha1($player->getId()) == $_GET['u']) {
                    // This player want to unsubscribe
                    $res = Inscription::unsubscribe($p->getId(), $player->getId());
                    Orcimail::notifyUnsubscribtion($p, $player);
                    break;
                }
            }

            if (!isset($res)) {
                echo "<p>Ce joueur n'est pas (plus ?) inscrit sur la partie numéro " . $p->getId() . " \"" . $p->getName() . "\".</p>";
            } elseif ($res) {
                echo "<p>Le joueur " . $player->getFirstname() . " " . $player->getLastname() . " a correctement 
						été désincrit de la partie numéro " . $p->getId() . " \"" . $p->getName() . "\".</p>";

                // If admin is unsubscribing a player, send a mail to this player !
                if ($user && $user->getRole() == 'administrator') {
                    $isMailOk = Orcimail::unsubscribedToParty($p, $player);
                    if ($isMailOk) {
                        echo "<p>Et un mail lui a été envoyé pour l'en informer.</p>";
                    } else {
                        echo "<p>Mais le mail pour l'en informer n'a pas pu être envoyé.</p>";
                    }
                }
            } else {
                echo "<p>Erreur lors de la désinscription.</p>";
            }
        } else {
            echo "<p>Partie '" . $_GET['partyId'] . "' introuvable !</p>";
        }
    } else {
        echo "<p>Impossible de désinscrire un joueur sans connaitre son ID et la partie !</p>";
    }
} ?>





<div class="party-detail" data-id="<?= $party->getId()?>">

    <h1>
        <span class="name"><?= $party->getName() ?></span>
        <span class="scenario"><?= strlen($party->getScenario()) > 0 ? ':':'' ?> <?= $party->getScenario() ?></span>
    </h1>

    <h2>
        <span class="type"><?= $party->getTypeName() ?></span>
        <span class="kind"><?= strlen($party->getKind()) > 0 ? ':':'' ?> <?= $party->getKind() ?></span>
    </h2>

    <div class="planning">
        Débute le: <?= $date ?>, durée: <?= $party->getDuration() ?>h. &ndash;
        Nombre de joueurs: <?= $nbPlayers ?> &ndash; Niveau de jeu: <?= $party->getLevel() ?> &ndash;
        Langue: <?= $party->getLanguage() ?>
    </div>

    <div class="animator">
        Animé par:
        <?php if ($contactable) { ?>
            <a href="#" class="contact-mj" onClick="showElem('ctct_mj_<?= $partyId ?>')">
                <?= $animator->getFirstname().' '.$animator->getLastname() ?>
            </a>

            <ul id="ctct_mj_<?= $partyId ?>" style="display:none">

                <div id="ret_ctct_mj_<?= $partyId ?>" style="width:100%"></div>
                <?php if($user){?>
                    <input type="text" value="<?php echo $user->getEmail();?>" id="mail_ctct_mj_<?= $partyId ?>">
                <?php }else{?>
                    <span>Email:</span>
                    <input email="true" error_id="error_mail_<?= $partyId ?>"
                           required="true" onBlur="testInput(this)" type="text" value="" id="mail_ctct_mj_<?= $partyId ?>">
                    <br /><div id="error_mail_<?= $partyId ?>"></div>
                    <br />
                <?php }?>

                <p><span>Message:</span></p>
                <textarea id="txt_ctc_mj_<?= $partyId ?>" rows="10" cols="50"></textarea>
                <input class="submit" type="button" onClick="sendMailAdmin('<?= $partyId ?>')" value="Envoyer">
            </ul>

        <?php } else { ?>
            <span>
                <?= $animator->getFirstname().' '.$animator->getLastname() ?>
            </span>
        <?php } ?>
    </div>

    <div class="description">
        <?= View::MultilineFormat($party->getDescription(), true);?>
    </div>

    <div class="players">
        Inscriptions:<br/> <?php
            /** @var User $player */
            $isFirst = true;
            foreach ($party->getPlayers() as $player) {
                echo (!$isFirst ? ', ':'').$player->getFirstname().' '.$player->getLastname();
                $isFirst = false;

                if ($isAdmin) {
                    echo "<a href='actions/party.php' class='unsubscribeNow' player-code='".sha1($player->getId())."' player-name='".$player->getFirstname()." ".$player->getLastname()."'><img src='http://www.orcidee.ch/orcidee/manager/img/cancel.png' title='Désinscrire'/></a>";
                }elseif($user && $user->getId() == $player->getId()){
                    echo "<a href='actions/party.php' class='unsubscribe' player-mail='".$player->getEmail()."' player-id='" . $user->getId() . "' player-name='".$player->getFirstname()." ".$player->getLastname()."'><img src='http://www.orcidee.ch/orcidee/manager/img/cancel.png' title='Désinscrire'/></a>";
                }
            }
        ?>
    </div>

    <div class="subscribe">
        <?php if ($subscribable) {
            if (count($party->getPlayers()) < $party->getPlayerMax()) {?>
                <input type="button" class="subscribe" value="Je veux m'inscrire à cette partie !" data-partyId="<?= $partyId ?>" />
            <?php 	} else {?>
                <span class="red">C'est complet !</span>
            <?php 	}
        }?>
    </div>

    <?php
    include('includes/moderation.php');

    // Lightbox qui apparaît au clic sur "Je veux m'inscrire à cette partie"
    // Cf. javascript: orcidee.manager.list.dialogBox
    ?>
    <div id="dialog-form" style="display:none;">
        <p class="partyTitle"><?= $party->getName() ?></p>
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

</div>
