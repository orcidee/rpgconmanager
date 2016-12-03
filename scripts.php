<script src="js/jquery-1.7.1.min.js"></script>
<script src="js/jquery-ui-1.8.17.custom.min.js"></script>
<script src="js/manager.js?_<?php echo CACHE_KILL;?>"></script>
<script src="js/script.js"></script>

<?php
$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");
?>
<script><!--
    orcidee.manager.createParty.options = {
        max: <?php  echo Controls::getNbTables(); ?>,
        start: <?php echo Controls::getDate(Controls::CONV_START, "%H"); ?>
    };
    orcidee.manager.isDebug = <?php echo (IS_DEBUG)?'true':'false'; ?>;
//--></script>