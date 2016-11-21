<?php

require_once(dirname(__FILE__).'/../conf/conf.php');

$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());;
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');

header("Content-type: application/json; charset=UTF-8");

if(!$db){
    echo '{"status":"error", "message":"Connextion impossible à la base de données."}';
}else{
    $user = User::getFromSession();
    if($user){
        if($user->getRole() == "administrator" or $user->getRole() == "animator"){
        
            if(isset($_GET['duration']) && isset($_GET['start'])){
                
                $res = Party::getCurrentSlots($_GET['start'], $_GET['duration'], $_GET['partyId'], $_GET['tableAmount']);
                
            }else{
            
                $res = Party::getCurrentSlots();
            
            }
            echo json_encode($res);
        }
    }
}