<?php
require_once(dirname(__FILE__) . '/conf/conf.php');

/*
 * If you want to clean the partyId, just close this comment,
 * deploy the file, and visit /clean-ids.php
 *


$link = mysqli_connect(HOST, USER, PASSWORD, DB);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}else{
    echo "Connection ok<br/>";

    mysqli_autocommit($link, FALSE);

    $sql = "SELECT partyId FROM Parties ORDER BY partyId ASC";
    $res = mysqli_query ($link,$sql);
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $oldId = $row['partyId'];
        $newId = $i++;

        $sql = "UPDATE Inscriptions SET partyId = $newId WHERE partyId = $oldId";
        $success = mysqli_query($link, $sql);

        if($success){
            $sql = "UPDATE Parties SET partyId = $newId WHERE partyId = $oldId";
            $success = mysqli_query($link, $sql);
            if($success){
                //echo "Row update OK<br/>";
            }else{
                echo "Something went wrong. No SQL statement has been commited";
                mysqli_rollback($link);
                die();
            }
        }else{
            echo "Something went wrong. No SQL statement has been commited";
            mysqli_rollback($link);
            die();
        }
    }
    echo "Update OK<br/>";
}