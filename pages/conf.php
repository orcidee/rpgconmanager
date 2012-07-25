<?php

require_once(dirname(__FILE__).'/../conf/bd.php');
require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');


echo "<h1>Configuration</h1>";

$user = User::getFromSession();

if($user){
    
    if($user->getRole() == "administrator"){
        
        echo "<h2>Contrôles de l'application</h2>";
        echo "<p>L'application est actuellement " . ((Controls::isAppOpen()) ? "ouverte" : "fermée") . ".</p>";
        
        $ts = Controls::getAppOpenDate();
        $ds = Controls::getAppOpenDate("%d.%m.%Y à %H:%M");
        $hs = Controls::getAppOpenDate("%H");
        
        $tf = Controls::getAppCloseDate();
        $df = Controls::getAppCloseDate("%d.%m.%Y à %H:%M");
        $hf = Controls::getAppCloseDate("%H");
    
        ?>
        <table class='application-controls'>
            <tr>
                <td>Ouverture de l'application</td>
                <td class="open-date"><?php echo $ds;?></td>
            </tr><tr>
                <td>Fermeture de l'application</td>
                <td class="close-date"><?php echo $df;?></td>
            </tr><tr>
                <td>Redéfinir la date et l'heure d'ouverture</td>
                <td>
                    <div id='datepicker0' class='datepicker' data-selected='<?php echo $ts;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($hs == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                    <input type="button" value="Définir" id="open-app"/>
                </td>
            </tr><tr>
                <td>Redéfinir la date et l'heure de fermeture</td>
                <td>
                    <div id='datepicker1' class='datepicker' data-selected='<?php echo $tf;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($hf == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                    <input type="button" value="Définir" id="close-app"/>
                </td>
            </tr>
        </table>
        
        <?php
        
        echo "<h2>Contrôles des services MJ</h2>";
        if(!Controls::isAppOpen()){
            echo "<p>Attention l'application est fermée. Aucune fonctionnalités liées aux inscriptions de parties par les MJ n'est donc accessible.</p>";
        }
        
        $ts = Controls::getMjOpenDate();
        $ds = Controls::getMjOpenDate("%d.%m.%Y à %H:%M");
        $hs = Controls::getMjOpenDate("%H");
        
        $tf = Controls::getMjCloseDate();
        $df = Controls::getMjCloseDate("%d.%m.%Y à %H:%M");
        $hf = Controls::getMjCloseDate("%H");
    
        ?>
        <table class='mj-controls'>
            <tr>
                <td>Ouverture des service MJ</td>
                <td class="open-date"><?php echo $ds;?></td>
            </tr><tr>
                <td>Fermeture des service MJ</td>
                <td class="close-date"><?php echo $df;?></td>
            </tr><tr>
                <td>Redéfinir la date et l'heure d'ouverture</td>
                <td>
                    <div id='datepicker2' class='datepicker' data-selected='<?php echo $ts;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($hs == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                    <input type="button" value="Définir" id="open-mj"/>
                </td>
            </tr><tr>
                <td>Redéfinir la date et l'heure de fermeture</td>
                <td>
                    <div id='datepicker3' class='datepicker' data-selected='<?php echo $tf;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($hf == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                    <input type="button" value="Définir" id="close-mj"/>
                </td>
            </tr>
        </table>
        
        <?php
        
        echo "<h2>Contrôles des services Joueurs</h2>";
        if(!Controls::isAppOpen()){
            echo "<p>Attention l'application est fermée. Aucune fonctionnalités liées aux inscriptions des joueurs n'est donc accessible.</p>";
        }
        
        $ts = Controls::getPlayerOpenDate();
        $ds = Controls::getPlayerOpenDate("%d.%m.%Y à %H:%M");
        $hs = Controls::getPlayerOpenDate("%H");
        
        $tf = Controls::getPlayerCloseDate();
        $df = Controls::getPlayerCloseDate("%d.%m.%Y à %H:%M");
        $hf = Controls::getPlayerCloseDate("%H");
    
        ?>
        <table class='player-controls'>
            <tr>
                <td>Ouverture des service Joueurs</td>
                <td class="open-date"><?php echo $ds;?></td>
            </tr><tr>
                <td>Fermeture des service Joueurs</td>
                <td class="close-date"><?php echo $df;?></td>
            </tr><tr>
                <td>Redéfinir la date et l'heure d'ouverture</td>
                <td>
                    <div id='datepicker4' class='datepicker' data-selected='<?php echo $ts;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($hs == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                    <input type="button" value="Définir" id="open-player"/>
                </td>
            </tr><tr>
                <td>Redéfinir la date et l'heure de fermeture</td>
                <td>
                    <div id='datepicker5' class='datepicker' data-selected='<?php echo $tf;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($hf == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                    <input type="button" value="Définir" id="close-player"/>
                </td>
            </tr>
        </table>
        
        <?php
        
    }else{
        echo "<p>Acces restreint à l'administrateur</p>";
    }
    
}else{
    echo "<p>Vous n'êtes pas authentifié.</p>";
}
?>