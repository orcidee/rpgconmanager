<?php
require_once(dirname(__FILE__).'/../conf/conf.php');

$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());;
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');

header("Content-type: application/json; charset=UTF-8");

if(!$db){
    echo '{"status":"error", "message":"Connextion impossible à la base de données."}';
}else{

    // Si activ session && get User From session
    $user = User::getFromSession();

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
                    if(Controls::setDate($action, $stamp)){
                        echo '"ok", "dateId":"'.$action.'", "newDate":"' . Controls::getDate($action, "%d.%m.%Y à %H:%M") . '"}';
                    }else{
                        echo '"error"}';
                    }

                }else{
                    echo '"error"}';
                }
            }
    
        }
    }
}
mysql_close($dbServer);