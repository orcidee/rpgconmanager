<?php

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');


echo "<h1>Configuration</h1><div class='dates-controls'>";

$user = User::getFromSession();

if($user){
    
    if($user->getRole() == "administrator"){

        echo "<h2>Dates de la convention</h2>";
        $convStartTime = Controls::getDate(Controls::CONV_START);
        $convStartDate = Controls::getDate(Controls::CONV_START, "%d.%m.%Y à %H:%M");
        $convStartHour = Controls::getDate(Controls::CONV_START, "%H");
        $convEndTime = Controls::getDate(Controls::CONV_END);
        $convEndDate = Controls::getDate(Controls::CONV_END, "%d.%m.%Y à %H:%M");
        $convEndHour = Controls::getDate(Controls::CONV_END, "%H");
        ?>
        <div class='convention-date clear'>
            <p class="info">La prochaine convention aura lieu du
                <span class='info-<?=Controls::CONV_START;?>'><?=$convStartDate;?></span> au
                <span class='info-<?=Controls::CONV_END;?>'><?=$convEndDate;?></span>
            </p>
            <div class='row clear'>
                <div class='left w300' data-dateId="<?=Controls::CONV_START;?>">
                    <div id='datepicker6' class='datepicker' data-selected='<?php echo $convStartTime;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($convStartHour == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                </div>

                <div class='left w300' data-dateId="<?=Controls::CONV_END;?>">
                    <div id='datepicker7' class='datepicker' data-selected='<?php echo $convEndTime;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($convEndHour == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                </div>
            </div>

            <input type="button" value="Définir" id="conv-date" style="margin:15px 0 20px;text-align:center;"/>
        </div>
        <?php
        
        echo "<h2>Contrôles de l'application</h2>";

        $appOpenTime = Controls::getDate(Controls::APP_OPEN);
        $appOpenDate = Controls::getDate(Controls::APP_OPEN, "%d.%m.%Y à %H:%M");
        $appOpenHour = Controls::getDate(Controls::APP_OPEN, "%H");
        $appCloseTime = Controls::getDate(Controls::APP_CLOSE);
        $appCloseDate = Controls::getDate(Controls::APP_CLOSE, "%d.%m.%Y à %H:%M");
        $appCloseHour = Controls::getDate(Controls::APP_CLOSE, "%H");

        ?>
        <div class='application-controls clear'>

            <p class="info">L'application est active du
                <span class='info-<?=Controls::APP_OPEN;?>'><?=$appOpenDate;?></span> au
                <span class='info-<?=Controls::APP_CLOSE;?>'><?=$appCloseDate;?></span>
            </p>

            <p class="info">
                L'application est actuellement <span><?=(Controls::isAppOpen()) ? "ouverte" : "fermée"?></span>
            </p>

            <div class='row clear'>
                <div class='left w300' data-dateId="<?=Controls::APP_OPEN;?>">
                    <p>Redéfinir la date et l'heure d'ouverture</p>
                    <div id='datepicker0' class='datepicker' data-selected='<?php echo $appOpenTime;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($appOpenHour == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                </div>
                <div class='left w300' data-dateId="<?=Controls::APP_CLOSE;?>">
                    <p>Redéfinir la date et l'heure de fermeture</p>
                    <div id='datepicker1' class='datepicker' data-selected='<?php echo $appCloseTime;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($appCloseHour == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                </div>
            </div>

            <input type="button" value="Définir" id="app-dates" style="margin:15px 0 20px;text-align:center;" class="clear"/>

        </div>
        
        <?php
        
        echo "<h2>Contrôles des services MJ</h2>";
        if(!Controls::isAppOpen()){
            echo "<p>Attention l'application est fermée. Aucune fonctionnalités liées aux inscriptions de parties par les MJ n'est donc accessible.</p>";
        }


        $mjOpenTime = Controls::getDate(Controls::MJ_OPEN);
        $mjOpenDate = Controls::getDate(Controls::MJ_OPEN, "%d.%m.%Y à %H:%M");
        $mjOpenHour = Controls::getDate(Controls::MJ_OPEN, "%H");
        $mjCloseTime = Controls::getDate(Controls::MJ_CLOSE);
        $mjCloseDate = Controls::getDate(Controls::MJ_CLOSE, "%d.%m.%Y à %H:%M");
        $mjCloseHour = Controls::getDate(Controls::MJ_CLOSE, "%H");
        ?>
        <div class='mj-controls clear'>

            <p class="info">L'application est ouverte aux MJ du
                <span class='info-<?=Controls::MJ_OPEN;?>'><?=$mjOpenDate;?></span> au
                <span class='info-<?=Controls::MJ_CLOSE;?>'><?=$mjCloseDate;?></span>
            </p>

            <div class='row clear'>
                <div class='left w300' data-dateId="<?=Controls::MJ_OPEN;?>">
                    <div id='datepicker2' class='datepicker' data-selected='<?php echo $mjOpenTime;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($mjOpenHour == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                </div>
                <div class='left w300' data-dateId="<?=Controls::MJ_CLOSE;?>">
                    <div id='datepicker3' class='datepicker' data-selected='<?php echo $tf;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($mjCloseHour == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                </div>
            </div>
            <input type="button" value="Définir" id="mj-dates" style="margin:15px 0 20px;text-align:center;" class="clear"/>
        </div>
        
        <?php
        
        echo "<h2>Contrôles des services Joueurs</h2>";
        if(!Controls::isAppOpen()){
            echo "<p>Attention l'application est fermée. Aucune fonctionnalités liées aux inscriptions des joueurs n'est donc accessible.</p>";
        }


        $playerOpenTime = Controls::getDate(Controls::PLAYER_OPEN);
        $playerOpenDate = Controls::getDate(Controls::PLAYER_OPEN, "%d.%m.%Y à %H:%M");
        $playerOpenHour = Controls::getDate(Controls::PLAYER_OPEN, "%H");
        $playerCloseTime = Controls::getDate(Controls::PLAYER_CLOSE);
        $playerCloseDate = Controls::getDate(Controls::PLAYER_CLOSE, "%d.%m.%Y à %H:%M");
        $playerCloseHour = Controls::getDate(Controls::PLAYER_CLOSE, "%H");
        ?>
        <div class='player-controls clear'>

            <p class="info">L'application est ouverte aux Joueurs du
                <span class='info-<?=Controls::PLAYER_OPEN;?>'><?=$playerOpenDate;?></span> au
                <span class='info-<?=Controls::PLAYER_CLOSE;?>'><?=$playerCloseDate;?></span>
            </p>

            <div class='row clear'>
                <div class='left w300' data-dateId="<?=Controls::PLAYER_OPEN;?>">
                    <div id='datepicker4' class='datepicker' data-selected='<?php echo $playerOpenTime;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($playerOpenHour == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                </div>
                <div class='left w300' data-dateId="<?=Controls::PLAYER_CLOSE;?>">
                    <div id='datepicker5' class='datepicker' data-selected='<?php echo $playerCloseTime;?>'></div>
                    <select>
                        <?php for($i = 0 ; $i<24 ; $i++){
                            $v = ((strlen($i)==1)?'0':'')."$i";
                            $selected = ($playerCloseHour == $v)? "selected='selected'" : "";
                            echo "<option value='$v:00' $selected>$v:00</option>";
                        } ?>
                    </select>
                </div>
            </div>
            <input type="button" value="Définir" id="player-dates" style="margin:15px 0 20px;text-align:center;" class="clear"/>
        </div>
        
        <?php
        
    }else{
        echo "<p>Acces restreint à l'administrateur</p>";
    }
    
}else{
    echo "<p>Vous n'êtes pas authentifié.</p>";
}
echo "</div>";