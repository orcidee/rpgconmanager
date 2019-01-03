<?php
if ($isAdmin || $animates) {

    // Libellés des états de parties
    $stateLabels = array();
    $stateLabels['created'] = "Créée";
    $stateLabels['verified'] = "Vérifiée";
    $stateLabels['validated'] = "Validée";
    $stateLabels['refused'] = "Refusée";
    $stateLabels['canceled'] = "Annulée";

    $controls = new Controls();

    $allow = array(
        'edit' => ($animates || $isAdmin) && ($party->getState() == 'created' || $party->getState() == 'verified' || $party->getState() == 'refused' || $party->getState() == 'validated'),
        'cancel' => ($animates || $isAdmin) && $party->getState() !== 'canceled',
        'refuse' => $isAdmin && ($party->getState() == 'created' || $party->getState() == 'verified' || $party->getState() == 'validated'),
        'verify' => $isAdmin && ($party->getState() == "created" || $party->getState() == "refused"),
        'validate' => $isAdmin && $party->getState() == "verified",
        'subscribe' => !$animates && !$participates && $party->getState() == 'validated' && $controls->isPlayerOpen()
    );

    ?>

    <div class="admin">
        <ul>
            <li>Status: <?php echo $stateLabels[$party->getState()]; ?></li>
            <li>Nb de tables: <?= $party->getTableAmount() ?></li>
            <li>Note aux orgas: <?= $party->getNote()?></li>
        </ul>

        <?php
        echo "<div class='actions' data-id='".$party->getId()."' data-state='".$party->getState()."'><span>Actions: </span>";
        echo ($allow['edit']) ? "<a href='?page=edit&partyId=".$party->getId()."' class='edit'><img src='http://www.orcidee.ch/orcidee/manager/img/edit.png' title='Éditer'/></a>" : "";
        echo ($allow['cancel']) ? "<a href='actions/party.php' class='cancel'><img src='http://www.orcidee.ch/orcidee/manager/img/cancel.png' title='Annuler'/></a>" : "";
        echo ($allow['refuse']) ? "<a href='actions/party.php' class='refuse' ><img src='http://www.orcidee.ch/orcidee/manager/img/refuse.png'title='Refuser'/></a>" : "";
        echo ($allow['verify']) ? "<a href='actions/party.php' class='verify'><img src='http://www.orcidee.ch/orcidee/manager/img/verify.png' title='Vérifier'/></a>" : "";
        echo ($allow['validate']) ? "<a href='actions/party.php' class='validate'><img src='http://www.orcidee.ch/orcidee/manager/img/validate.png' title='Valider'/></a>" : "";
        echo "</div>";
        ?>

    </div>

    <?php
}