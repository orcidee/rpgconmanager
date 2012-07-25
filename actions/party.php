<?php
require_once(dirname(__FILE__).'/../conf/bd.php');
$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());;
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');

header("Content-type: application/json; charset=UTF-8");

if(!$db){
    echo '{"status":"error", "message":"Connextion impossible à la base de données."}';
}else{

    if(isset($_GET['partyId']) && isset($_GET['action'])){
    
        // Si activ session && get User From session
        $user = User::getFromSession();
        $p = new Party($_GET['partyId'], false);
        
        $isAdmin = $user && $user->getRole() == "administrator";
        $isMJ = $user && $user->getRole() == "animator" && $user->animates($p->getId());
        
        switch ($_GET['action']){
            case "cancel":
				if($isAdmin || $isMJ){
                    echo '{"auth":"ok",';
                    $res = $p->cancel();
                }
                break;
            case "refuse":
                if($isAdmin && ($p->getState() == 'created' || $p->getState() == 'verified' || $p->getState() == 'validated')){
                    echo '{"auth":"ok",';
                    $res = $p->refuse();
                }
                break;
            case "verify":
                if($isAdmin && ($p->getState() == "created" || $p->getState() == "refused")){
                    echo '{"auth":"ok",';
                    $res = $p->verify();
                }
                break;
            case "validate":
                if($isAdmin && $p->getState() == "verified"){
                    echo '{"auth":"ok",';
                    $res = $p->validate();
                }
                break;
        }
        
        echo '"status":"' . (($res) ? "ok" : "ko") . '"}';
        
    }
}
mysql_close($dbServer);
?>