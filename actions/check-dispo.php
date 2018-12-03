<?php

require_once(dirname(__FILE__).'/../conf/conf.php');

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');

header("Content-type: application/json; charset=UTF-8");

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