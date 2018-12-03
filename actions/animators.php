<?php

require_once(dirname(__FILE__).'/../conf/conf.php');
require_once(dirname(__FILE__).'/../classes/user.php');

header("Content-type: application/json; charset=UTF-8");

if(isset($_GET['year'])){

    $result = User::getUsersByYear("animator", addslashes($_GET['year']), "firstname");

    echo '{"auth":"ok", "status":"ok", "list":[';

    $isFirst = true;
    while ($row = $result->fetch_assoc) {
        echo ($isFirst)?'':',';
        echo '{"id":'.$row['userId'].',';
        echo '"firstName":"'.$row['firstname'].'",';
        echo '"lastName":"'.$row['lastname'].'"}';
        $isFirst = false;
    }
    echo ']}';
}