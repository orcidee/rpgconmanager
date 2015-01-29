<?php

require_once(dirname(__FILE__).'/../conf/conf.php');

$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());;
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/../classes/user.php');

header("Content-type: application/json; charset=UTF-8");

if(!$db){
    echo '{"status":"error", "message":"Connextion impossible à la base de données."}';
}else{

    if(isset($_GET['year'])){

        $fetchAssoc = User::getUsersByYear("animator", addslashes($_GET['year']), "firstname");

        echo '{"auth":"ok", "status":"ok", "list":[';

        $isFirst = true;
        while ($row = mysql_fetch_assoc($fetchAssoc)) {
            echo ($isFirst)?'':',';
            echo '{"id":'.$row['userId'].',';
            echo '"firstName":"'.$row['firstname'].'",';
            echo '"lastName":"'.$row['lastname'].'"}';
            $isFirst = false;
        }
        echo ']}';
    }
}