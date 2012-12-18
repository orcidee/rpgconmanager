<?php
error_reporting(E_ALL);
//error_reporting(E_ERROR);
session_start();
header('Content-type: text/html; charset=UTF-8');
ini_set('mbstring.language', 'UTF-8');
ini_set('mbstring.internal_encoding','UTF-8');
ini_set('mbstring.http_input','UTF-8');
ini_set('mbstring.http_output','UTF-8');
ini_set('mbstring.detect_order','auto');

//putenv("TZ=UTC");

define("MODULE_PATH", "rpgconmanager");

define("MAIL_FROM", "inscription@orcidee.ch");
define("MAIL_CC", "moduleparties@orcidee.ch");

// FIXME:
// Should be controls properties (instead of constants)
// Used in pages/create.php, ...

define("END_AT", "2013-04-01 17:00:00");

define("THIS_YEAR", "2013");
define("TABLES", 30);

define("CACHE_KILL", "20120325T22:04");

?>