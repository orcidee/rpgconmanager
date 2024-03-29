<?php
error_reporting(E_ALL);
//error_reporting(E_ERROR);
session_start();
header('Content-type: text/html; charset=UTF-8');
ini_set('mbstring.language', 'UTF-8');
ini_set('default_charset','UTF-8');
ini_set('mbstring.detect_order','auto');

//putenv("TZ=UTC");

if($_SERVER['HTTP_HOST'] == "parties.orcidee.ch"){
    define("ENV", "prod");
} elseif($_SERVER['HTTP_HOST'] == "parties-dev.orcidee.ch"){
    define("ENV", "test");
}else{
    define("ENV", "local");
}

define("MAIL_FROM", "inscription@orcidee.ch");
define("MAIL_CC", "moduleparties@orcidee.ch");

define("CACHE_KILL", "20161118T17:30");


if(ENV == "test"){
    require_once(dirname(__FILE__).'/bd_test.php');
    define("MODULE_PATH", "");
}elseif(ENV == "prod"){
    require_once(dirname(__FILE__).'/bd_prod.php');
    define("MODULE_PATH", "");
}else{
    require_once(dirname(__FILE__).'/bd_local.php');
    define("MODULE_PATH", "");
}

define("IS_DEBUG", ENV != "PROD" && isset($_GET['debug']));
//define("IS_DEBUG", true);