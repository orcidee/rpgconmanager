<?php
require_once(dirname(__FILE__).'/conf/conf.php');
require_once(dirname(__FILE__).'/classes/user.php');

$user = User::getFromSession();
if($user && $user->getRole() == "administrator"){
    phpinfo();
}
?>