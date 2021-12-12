<?php

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');

// FIXME
// Cette page devrait s'apeler "party" et non "create", car elle sert ce création, édition, confirmation.

echo "<h1>Partie</h1>".
"<div class='party-create'>";

$user = User::getFromSession();

if($user){

    $controls = new Controls();

    if($user->getRole() == "administrator" or $user->getRole() == "animator"){

        // Indicate if the intial creation/edition form should be used
        $createOrEdit = false;

        // AFFICHAGE DES DONNEES SAISIES - DEMANDE DE CONFIRMATION
        if (isset($_POST) && (@$_POST['action'] == 'create' || @$_POST['action'] == 'edit')) {

            if(IS_DEBUG) {
                echo "<div class='dbg'>demande de confirmation (" . @$_POST['action'] . ")</div>";
            }

            // create a fake party
            $data = $_POST;
            if(!isset($data["userId"]) || strlen($data['userId']) == 0){
                $data["userId"] = $user->getUserId();
            }

            if(@$data["validatedParty"] == 'true'){
                $party = new Party($data['partyId'], false);
                $party->setDescription($data['description']);
                $party->setNote($data['note']);
            }else{
                $party = new Party($data);
            }

            if($party->isValid){

                // Display the party & talk about validity & ask confirmation
                $txt = "<div class='your-party'>" .
                "<p>Le gratte-papier chargé de valider les parties considère que ces informations ".
                "sont suffisantes pour prendre en considération votre demande. </p>".
                "<p>A vous maintenant d'y jeter un dernier coup d'oeil avant de lui ".
                "confirmer ces données en cliquant sur le bouton \"Confirmer\". Si vous souhaitez ".
                "corriger quelque chose, cliquez alors sur \"Corriger\".</p>".
                "<p>Un mail de confirmation vous sera envoyé.</p>";

                $txt .= "<table class='party-preview' cellspacing='0' cellpadding='0'>".
                        "<tr><td>Type de jeu</td><td><strong>".$party->getTypeName()."</strong><br/>".$party->getType()['description']."</td></tr>".
                        "<tr><td>Nom de la partie</td><td>".$party->getName()."</td></tr>".
                        "<tr><td>Genre</td><td>".$party->getKind()."</td></tr>".
                        "<tr><td>Scénario</td><td>".$party->getScenario()."</td></tr>".
                        "<tr><td>Nombre de joueur minimum</td><td>".$party->getPlayerMin()."</td></tr>".
                        "<tr><td>Nombre de joueur maximum</td><td>".$party->getPlayerMax()."</td></tr>".
                        "<tr><td>Niveau de jeu</td><td>";

                $txt .= $party->getLevel()."</td></tr>".
                        "<tr><td>Nombre de tables</td><td>".$party->getTableAmount()."</td></tr>".
                        "<tr><td>Durée prévue</td><td>".$party->getDuration()." heure(s)</td></tr>".
                        "<tr><td>Heure de début souhaitée</td><td>".strftime("%d.%m.%Y à %H:%M", strtotime($party->getStart()))."</td></tr>".
                        "<tr><td>Description</td><td>".View::MultilineFormat($party->getDescription())."</td></tr>".
                        "<tr><td>Note aux orgas</td><td>".View::MultilineFormat($party->getNote())."</td></tr>".
                        "<tr><td>Langage</td><td>".$party->getLanguage()."</td></tr>".
                        "<tr><td>Statut actuel</td><td>En cours de création</td></tr></table>";

                $animator = $user;
                if($user->getUserId() != $party->getUserId()){
                    $animator = new User($party->getUserId());
                }
                $txt .= "<p class='mj'>Jeu animé par: ".$animator->getFirstname()." ".$animator->getLastname()."</p></div>";

                echo $txt;

                $_SESSION['party'] = serialize($party); ?>

                <form action="" method="POST">
                    <input type="hidden" name="action" value="confirm" />
                    <input type="submit" class="submit" value="Confirmer" />
                </form>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="undo" />
                    <input type="submit" class="submit" value="Corriger" />
                </form> <?php

            }else{
                print "<p>Le gratte-papier chargé de valider les parties pense que ces informations ".
                "contiennent des erreurs.</p><p>Pouvez-vous vérifier :</p><ul>".
                "<li>que tous les champs obligatoires soient remplis</li>".
                "<li>que le nombre minimum de joueurs soit bien un nombre strictement positif</li>".
                "<li>que le nombre maximum de joueurs soit bien un nombre ".
                "égal ou supérieur au nombre minumum de joueurs</li></ul> ";

                $createOrEdit = true;
            }
        }

        // SAUVEGARDE DE LA PARTIE CONFIRMEE
        elseif(isset($_POST) and @$_POST['action'] == 'confirm' && isset($_SESSION['party'])) {

            $party = unserialize($_SESSION['party']);
            $edit = (!is_null($party->getId()) && strlen($party->getId()) > 0);

            if(IS_DEBUG) {
                echo "<div class='dbg'>sauvegarde";
                echo is_null($party->getId()) ? " (création)" : " (édition partie n°" . $party->getId() . ")";
                echo "</div>";
            }

            if($party->isValid){
                unset($_SESSION['party']);
                $saveOK = $party->save();
                if($saveOK) {
                    echo "<p>Partie <strong>".(($edit)?'éditée':'créée')."</strong> avec succès.</p>".
                    "<p>Vous allez recevoir un mail de confirmation sous peu!</p>";

                    // Send congratulation's mail
                    $isMailOk = Orcimail::notifyCreate($party, $edit);

                }else{
                    // Display errors
                    echo "<p class='dbg'>Sauvegarde impossible.</p>";
                }
            }
        }

        // SAISIE INITIALE DES DONNEES  / (RE)EDITION
        elseif( ! isset($_POST) or @$_POST['action'] == 'undo' or count($_POST) == 0) {
        	$createOrEdit = true;
        }

        if($createOrEdit) {

            // Default: Reedit form, before saving new party.
            // pv = previous values. No previous values by default.
            $editExisting = false;
            $pv = null;

            if(@$_POST['action'] == 'undo' && isset($_SESSION['party'])) {
                // Case of undo previous edition
                $party = unserialize($_SESSION['party']);
                $pv = $party->toArray();
                $editExisting = true;
                unset ($_SESSION['party']);

            }elseif (isset($_POST) && (@$_POST['action'] == 'create' || @$_POST['action'] == 'edit')) {
                // Correcting invalid data
                $pv = $_POST;
                if(isset($_GET['partyId']) && $user) {
                    $editExisting = true;
                }

            }elseif(isset($_GET['partyId']) && $user){
                // Edit an existing party by id
                $party = new Party($_GET['partyId'], false);
                if ($user->animates($party->getId()) || $user->getRole() == 'administrator'){
                    if ($party->getState() == 'created' || $party->getState() == 'verified' || $party->getState() == 'refused' || $party->getState() == 'validated') {
                        unset ($_SESSION['postData']);
                        $pv = $party->toArray();
                        $editExisting = true;
                    }
                }else{
                    echo "<p><strong>Vous ne pouvez pas éditer la partie no ".$party->getId()." nommée '".$party->getName()."' !</strong></p>";
                    exit;
                }
            }

            if(IS_DEBUG) {
                echo "<div class='dbg'>";
                echo $editExisting ? "édition partie n°" . $pv['partyId'] : "création (?)";
                echo "</div>";
            }

            // Avoid reediting critical fields when the party has been validated already
            $enable = "";
            if($editExisting && isset($party) && $party->getState() == 'validated'){
                echo "<p><strong>Votre partie a le status \"validée\". Cela implique que certains champs ne
                sont plus éditables. Merci pour votre compréhension.</strong></p>";
                $enable = "disabled='disabled'";
            }

            // Display a message if party has been canceled
            if(isset($party) && $party->getState() == 'canceled'){
                echo "<p><strong>La partie no ".$party->getId()." nommée '".$party->getName()."' a été annulée et ne peut donc pas être éditée !</strong></p>";
            }

            ?>

            <form action="" method="POST">

                <input type="hidden" name="context" value="party" />

                <?php
                if($editExisting){
                    echo '<input type="hidden" id="partyId" name="partyId" value="'.$pv['partyId'].'" />';
                    echo '<input type="hidden" name="userId" value="'.$pv['userId'].'" />';
                    echo '<input type="hidden" name="action" value="edit" />';
                    if(isset($party) && $party->getState() == 'validated'){
                        echo '<input type="hidden" name="validatedParty" value="true" />';
                    }
                } else {
                    echo '<input type="hidden" name="action" value="create" />';
                }

                ?>

                <fieldset>

                    <legend>Champs obligatoires</legend>

                    <fieldset>

                        <legend>Descriptions de la partie</legend>

                        <label for="typeId">Type</label>
                        <?php
                        echo "<select name='typeId' $enable>";
                            // Get the types from DB
                            foreach(Party::getTypes() as $typeId => $type){
                                echo "<option ".(($pv['typeId']==$typeId) ? "selected='selected'" : "")." value='".$typeId."' title='".$type['description']."'>".stripslashes($type['name'])."</option>";
                            }
                        echo "</select>
                        
                        <label for='name'>Nom du jeu</label>
                        <input type='text' name='name' value=\"".stripslashes(@$pv['name'])."\" $enable />
                        
                        <label for='playerMin'>Nombre de joueur minimum</label>
                        <input type='text' name='playerMin' value='".@$pv['playerMin']."' $enable />
                        
                        <label for='playerMax'>Nombre de joueur maximum</label>
                        <input type='text' name='playerMax' value='".@$pv['playerMax']."' $enable />
                        
                        <label for='level'>Niveau de jeu</label>
                        <select name='level' $enable>";

                            echo "<option value='anyway' ".(($pv['level']=="anyway")?"selected='selected'" : "").">Peu importe</option>";
                            echo "<option value='low' ".(($pv['level']=="low")?"selected='selected'" : "").">Débutant</option>";
                            echo "<option value='middle' ".(($pv['level']=="middle")?"selected='selected'" : "").">Initié</option>";
                            echo "<option value='high' ".(($pv['level']=="high")?"selected='selected'" : "").">Expert</option>";

                        echo "</select>
                        <label for='tableAmount' class='tableAmount'>Nombre de table souhaitée <br/>
                            <span class='small'>Si 0, merci de préciser dans la \"note aux orgas\" ci-dessous</span>
                        </label>
                        <select id='tableAmount' name='tableAmount' $enable>
                            <option name='0' value='0' ".(($pv['tableAmount']=='0')?"selected='selected'" : '').">0</option>
                            <option name='1' value='1' ".((is_null($pv['tableAmount']) || $pv['tableAmount']=='1')?"selected='selected'" : '').">1</option>
                            <option name='2' value='2' ".(($pv['tableAmount']=='2')?"selected='selected'" : '').">2</option>
                            <option name='3' value='3' ".(($pv['tableAmount']=='3')?"selected='selected'" : '').">3</option>
                        </select>
                        
                        <label for='description'>Description détaillée <br/>
                         <span class='small'>&#40;max. 2000 caractères&#41;</span>
                         </label>
                        <textarea name='description' class='clear tiny-mce' data-limit='3000'>".@$pv['description']."</textarea>
                    
                    </fieldset>
                
                    <fieldset>
                    
                        <legend>Organisation</legend>
                        
                        <div id='animation'>Les traditionnelles conférences de JDR'idée auront lieu entre <strong>18h00 et 21h30</strong> le samedi soir.
                 Avis aux intéressés!</div>
                        
                        <label for='duration'>Durée prévue</label>
                        <select name='duration' id='duration' $enable>";

                            for($i = 1 ; $i<=15 ; $i++){
                                echo "<option value='$i' ".(($pv['duration']==$i)?"selected='selected'":"").">$i heure".(($i==1)?'':'s')."</option>";
                            }
                        echo '</select>';
                        ?>

                        <label for='day-start'>Jour de début de la partie</label>
                        <select     id='day-start' name='startDay'
                                    data-start='<?= $controls->getDate(Controls::CONV_START)?>'
                                    data-end='<?= $controls->getDate(Controls::CONV_END)?>'
                                    <?=$enable?>>
                            <option value='1' <?= (!is_null($pv) && $pv['startDay'] == 1) ? "selected='selected'" : '' ?>>Samedi</option>
                            <option value='2' <?= (!is_null($pv) && $pv['startDay'] == 2) ? "selected='selected'" : '' ?>>Dimanche</option>
                        </select>

                        <label for="time-start-day1" class="time-start-day">Heure de début</label>
                        <select id='time-start-day1' name="time-start-day1" <?=$enable?> class="time-start-day">
                            <?php
                            $start  = $controls->getDate(Controls::CONV_START);
                            $midnight = strtotime($controls->getDate(Controls::CONV_END, '%Y-%m-%d 00:00'));
                            $end    = $controls->getDate(Controls::CONV_END);

                            for($i = $start ; $i<$midnight ; $i+=1800){
                                $l = strftime("%H:%M", $i);
                                $v = strftime("%Y-%m-%d %H:%M", $i);
                                $selected = (strpos($pv['start'], $v) === 0) ? "selected='selected'" : "";
                                echo "<option value='$v' $selected>$l</option>";
                            } ?>

                        </select>

                        <label for="time-start-day2" class="time-start-day">Heure de début</label>
                        <select id='time-start-day2' name="time-start-day2" <?=$enable?> class="time-start-day">
                            <?php
                            for($i = $midnight ; $i<$end ; $i+=1800){
                                $l = strftime("%H:%M", $i);
                                $v = strftime("%Y-%m-%d %H:%M", $i);
                                $selected = (strpos($pv['start'], $v) === 0) ? "selected='selected'" : "";
                                echo "<option value='$v' $selected>$l</option>";
                            }

                            echo "</select>";
                            echo " <br><br><br><br><br><br><br><br><br><br>";
                             echo "<div style='width:30%;margin:auto;padding: 5px; border:1px solid black;'><p style='font-size:1.1em; font-weight:bold;margin-top:0px;'>Légende:</p>";
                             echo "<p style='background-color:green;color:white;line-height: 1.4em;vertical-align:middle;'>moins de 50% de tables occupées</p>";
                             echo "<p style='background-color:orange;color:white;line-height: 1.4em;vertical-align:middle;'>moins de 75% de tables occupées</p>";
                             echo "<p style='background-color:red;color:white;line-height: 1.4em;vertical-align:middle;'>moins de 95% de tables occupées</p>";
                             echo "<p style='background-color:black;color:white;line-height: 1.4em;vertical-align:middle;'>plus de 95% de tables occupées</p></div><br><br>";

                        echo "<input type='button' id='check-dispo' value='Tester la disponibilité' $enable />
                        
                        <div id='check-dispo-result'></div>
                        
                    </fieldset>
                    
                </fieldset>
                
                <fieldset>
                
                    <legend>Champs facultatifs</legend>
                
                    <label for='kind'>Genre</label>
                    <input type='text' name='kind' value='".@$pv['kind']."' $enable />
                    
                    <label for='scenario'>Nom du scénario</label>
                    <input type='text' name='scenario' value='".@$pv['scenario']."' $enable />
                    
                    <label for='note'>Note aux organisateurs</label>
                    <textarea name='note' class='clear' data-limit='200'>".@$pv['note']."</textarea>
                    
                    <label for='language'>Langue</label>
                    <select name='language' $enable>";

                        echo "<option value='fr' ".(($pv['language']=="fr")?"selected='selected'" : "").">Français</option>";
                        echo "<option value='en' ".(($pv['language']=="en")?"selected='selected'" : "").">Anglais</option>";
                        echo "<option value='other' ".(($pv['language']=="other")?"selected='selected'" : "").">Autre</option>";
                        ?>
                    </select>

                </fieldset>

                <input type="submit" class="submit" value="Soumettre les données" />
                <input type="reset" class="submit" value="Réinitialiser le formulaire" />

            </form>

            <p class="center"><a href="?page=contact" id="create-help">Help!</a></p>

            <?php


        }
    }else{
        echo "<p>Acces restreint aux animateurs et maîtres de jeu</p>";
    }

}else{
    echo "<p>Vous n'êtes pas authentifié.</p>";
}
echo "</div>";
