<?php
require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');

header("Content-type: application/json; charset=UTF-8");


// Si activ session && get User From session
$user = User::getFromSession();
$controls = new Controls();

// Admin ok
if($user !== FALSE && $user->getRole() === "administrator"){

    echo '{"auth":"ok",';

    // Nouvelle date reçue
    if(isset($_GET["stamp"])){

        $stamp = $_GET["stamp"];

        // Action demandée reçue
        if(isset($_GET["action"])){

            $action = $_GET["action"];
            echo '"action":"'.$action.'", "status":';

            // Block sql injection
            if(strpos($stamp,"'") === FALSE && strpos($stamp,'"') === FALSE){

                // Exécution
                if($controls->setDate($action, $stamp)){
                    echo '"ok", "dateId":"'.$action.'", "newDate":"' . $controls->getDate($action, "%d.%m.%Y à %H:%M") . '"}';
                }else{
                    echo '"error"}';
                }

            }else{
                echo '"error"}';
            }
        }

    }elseif(isset($_GET["value"]) && @$_GET["action"] == Controls::NB_TABLES){
        echo '"action":"'.Controls::NB_TABLES.'", "status":';
        $val = $_GET["value"];
        echo (is_int($val) || ctype_digit($val)) ? '"ok"' : '"error"';
        echo ', "oldValue": '.$controls->getNbTables().'}';
        $controls->setNbTables($val);
    }
}