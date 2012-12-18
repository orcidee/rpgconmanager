<?php
require_once(dirname(__FILE__).'/../conf/bd.php');
$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());;
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/../conf/conf.php');
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
                    switch ($action) {
                        case "conv-date" :
                            if(Controls::setConvDate($stamp)){
                                echo '"ok", "newDate":"' . Controls::getConvDate("%d.%m.%Y") . '"}';
                            }else{
                                echo '"error"}';
                            }
                            break;
                        case "open-app" :
                            if(Controls::setAppOpenDate($stamp)){
                                echo '"ok", "newDate":"' . Controls::getAppOpenDate("%d.%m.%Y à %H:%M") . '"}';
                            }else{
                                echo '"error"}';
                            }
                            break;
                        case "close-app":
                            if(Controls::setAppCloseDate($stamp)){
                                echo '"ok", "newDate":"' . Controls::getAppCloseDate("%d.%m.%Y à %H:%M") . '"}';
                            }else{
                                echo '"error"}';
                            }
                            break;
                        case "open-mj":
                            if(Controls::setMjOpenDate($stamp)){
                                echo '"ok", "newDate":"' . Controls::getMjOpenDate("%d.%m.%Y à %H:%M") . '"}';
                            }else{
                                echo '"error"}';
                            }
                            break;
                        case "close-mj":
                            if(Controls::setMjCloseDate($stamp)){
                                echo '"ok", "newDate":"' . Controls::getMjCloseDate("%d.%m.%Y à %H:%M") . '"}';
                            }else{
                                echo '"error"}';
                            }
                            break;
                        case "open-player":
                            if(Controls::setPlayerOpenDate($stamp)){
                                echo '"ok", "newDate":"' . Controls::getPlayerOpenDate("%d.%m.%Y à %H:%M") . '"}';
                            }else{
                                echo '"error"}';
                            }
                            break;
                        case "close-player":
                            if(Controls::setPlayerCloseDate($stamp)){
                                echo '"ok", "newDate":"' . Controls::getPlayerCloseDate("%d.%m.%Y à %H:%M") . '"}';
                            }else{
                                echo '"error"}';
                            }
                            break;
                    }
                }else{
                    echo '"error"}';
                }
            }
    
        }
    }
}
mysql_close($dbServer);
?>