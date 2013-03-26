<?php

require_once(dirname(__FILE__).'/../conf/bd.php');
require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');

// FIXME
// Cette page devrait s'apeler "party" et non "create", car elle sert ce création, édition, confirmation.

echo "<h1>Partie</h1>".
"<div class='party-create'>";

$user = User::getFromSession();

if($user){
    
    if($user->getRole() == "administrator" or $user->getRole() == "animator"){
    	
		// Indicate if the intial creation/edition form should be used
		$createOrEdit = false;

        // AFFICHAGE DES DONNEES SAISIES - DEMANDE DE CONFIRMATION
        if (isset($_POST) && (@$_POST['action'] == 'create' || @$_POST['action'] == 'edit')) {
            

			echo "<div class='dbg'>demande de confirmation (".@$_POST['action'].")</div>";
		
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
                
                $type = $party->getType();
                
                $txt .= "<table class='party-preview' cellspacing='0' cellpadding='0'>".
                        "<tr><td>Type de jeu</td><td><strong>".stripslashes($type['name'])."</strong><br/>".$type['description']."</td></tr>".
                        "<tr><td>Nom de la partie</td><td>".$party->getName()."</td></tr>".
                        "<tr><td>Genre</td><td>".$party->getKind()."</td></tr>".
                        "<tr><td>Scénario</td><td>".$party->getScenario()."</td></tr>".
                        "<tr><td>Nombre de joueur minimum</td><td>".$party->getPlayerMin()."</td></tr>".
                        "<tr><td>Nombre de joueur maximum</td><td>".$party->getPlayerMax()."</td></tr>".
                        "<tr><td>Niveau de jeu</td><td>";

                $lvl = 'Peu importe';
                switch ($party->getLevel()) {
                    case 'low': $kind = 'Débutant';break;
                    case 'middle': $kind = 'Initié';break;
                    case 'high': $kind = 'Expert';break;
                }
                            
                $txt .= "$lvl</td></tr>".
                        "<tr><td>Durée prévue</td><td>".$party->getDuration()." heure(s)</td></tr>".
                        "<tr><td>Heure de début souhaitée</td><td>".strftime("%d.%m.%Y à %H:%M", strtotime($party->getStart()))."</td></tr>".
                        "<tr><td>Description</td><td>".View::MultilineFormat($party->getDescription())."</td></tr>".
                        "<tr><td>Note aux orgas</td><td>".View::MultilineFormat($party->getNote())."</td></tr>".
                        "<tr><td>Langage</td><td>".$party->getLanguage()."</td></tr>".
                        "<tr><td>Statut actuel</td><td>En cours de création</td></tr>".
                        "<tr><td>Informations</td><td><ul>";
                        
                foreach ($party->infos as $v){
                    $txt .= "<li>$v</li>";
                }

                $txt .= "</ul></tr></table>";
				
				$animator = $user;
				if($user->getUserId() != $party->getUserId()){
					$animator = new User($party->getUserId());
				}
                $txt .= "<p class='mj'>Jeu animé par: ".$animator->getFirstname()." ".$animator->getLastname()."</p></div>";
        
                print $txt;
                
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
                /*print_r($party->errors);
                print_r($party->infos);*/
				$createOrEdit = true;
            }
        }
        
        // SAUVEGARDE DE LA PARTIE CONFIRMEE
        elseif(isset($_POST) and @$_POST['action'] == 'confirm' && isset($_SESSION['party'])) {
        
            $p = unserialize($_SESSION['party']);
            $edit = (!is_null($p->getId()) && strlen($p->getId()) > 0);

			echo "<div class='dbg'>sauvegarde";
			echo is_null($p->getId()) ? " (création)" : " (édition partie n°".$p->getId().")";
			echo "</div>";
            
            if($p->isValid){
                unset($_SESSION['party']);
                $saveOK = $p->save();
                if($saveOK) {
                    echo "<p>Partie <strong>".(($edit)?'éditée':'créée')."</strong> avec succès.</p>".
                    "<p>Vous allez recevoir un mail de confirmation sous peu!</p>";
                
                    // Send congratulation's mail
                    $isMailOk = Orcimail::notifyCreate($p, $edit);
                    
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
				$p = unserialize($_SESSION['party']);
                $pv = $p->toArray();
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
				$p = new Party($_GET['partyId'], false);
                if ($user->animates($p->getId()) || $user->getRole() == 'administrator'){
                    if ($p->getState() == 'created' || $p->getState() == 'verified' || $p->getState() == 'refused' || $p->getState() == 'validated') {
                        unset ($_SESSION['postData']);
						$pv = $p->toArray();
						$editExisting = true;
                    }
                }else{
					echo "<p><strong>Vous ne pouvez pas éditer la partie no ".$p->getId()." nommée '".stripslashes($p->getName())."' !</strong></p>";
				}
            }

			echo "<div class='dbg'>";
			echo $editExisting ? "édition partie n°".$pv['partyId'] : "création (?)";
			echo "</div>";
            
            // Avoid reediting critical fields when the party has been validated already
            $enable = "";
            if($editExisting && isset($p) && $p->getState() == 'validated'){
                echo "<p><strong>Votre partie a le status \"validée\". Cela implique que certains champs ne
                sont plus éditables. Merci pour votre compréhension.</strong></p>";
				$enable = "disabled='disabled'";
            }
			
			// Display a message if party has been canceled
			if(isset($p) && $p->getState() == 'canceled'){
				echo "<p><strong>La partie no ".$p->getId()." nommée '".stripslashes($p->getName())."' a été annulée et ne peut donc pas être éditée !</strong></p>";
			}
            
            ?>
            
            <form action="" method="POST">
            
                <input type="hidden" name="context" value="party" />
                
                <?php
                if($editExisting){
                    echo '<input type="hidden" id="partyId" name="partyId" value="'.$pv['partyId'].'" />';
                    echo '<input type="hidden" name="userId" value="'.$pv['userId'].'" />';
                    echo '<input type="hidden" name="action" value="edit" />';
					if(isset($p) && $p->getState() == 'validated'){
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
                        
                        <label for='description'>Description détaillée &#40;max. 1500 caractères&#41;</label>
                        <textarea name='description' class='clear' data-limit='1500'>".@$pv['description']."</textarea>
                    
                    </fieldset>
                
                    <fieldset>
                    
                        <legend>Organisation</legend>
                        
                        <div id='animation'>Nous informons nos très chers MJs qu'une table ronde sur le JDR sera organisée entre <strong>17h00 et 21h00</strong> le samedi soir. 
                       Merci de prendre cela en compte pour agender votre partie!</div>
                        
                        <label for='duration'>Durée prévue</label>
                        <select name='duration' id='duration' $enable>";
                            
                            for($i = 1 ; $i<=15 ; $i++){
                                echo "<option value='$i' ".(($pv['duration']==$i)?"selected='selected'":"").">$i heure".(($i==1)?'':'s')."</option>";
                            }

                        echo "</select>
                        
                        <label for='start'>Heure de début souhaitée</label>
                        <select name='start' class='' id='start' $enable>";
                            
                            $start = strtotime(Controls::getConvStart());
                            $end = strtotime(END_AT);
                            
                            for($i = $start ; $i<$end ; $i+=3600){
                                $l = (strftime("%d-%m-%Y %H", $i)) . ":00";
                                $v = (strftime("%Y-%m-%d %H", $i)) . ":00:00";
                                $selected = ($v == $pv['start']) ? "selected='selected'" : "";
                                echo "<option value='$v' $selected>$l</option>";
                            }

                        echo "</select>";
                        
                        /* Utile avec l'algorythme de répartition des parties (inaboutit)
                        <label for="flex">Créneau horaire souple</label>
                        <select name="flex">
                            <?php
                            echo "<option value='yes' ".(($pv['flex']=="yes")?"selected='selected'" : "").">Oui</option>";
                            echo "<option value='no' ".(($pv['flex']=="no")?"selected='selected'" : "").">Non</option>";
                            ?>
                        </select> */
                        
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
            
            <?php
                
            
        }
    }else{
        echo "<p>Acces restreint aux animateurs et maîtres de jeu</p>";
    }
    
}else{
    echo "<p>Vous n'êtes pas authentifié.</p>";
}
echo "</div>";