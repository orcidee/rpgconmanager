<script src="js/jquery-1.7.1.min.js"></script>
<script src="js/jquery-ui-1.8.17.custom.min.js"></script>
<script src="js/manager.js?_<?php echo CACHE_KILL;?>"></script>
<script src="js/script.js"></script>

<?php
$controls = new Controls();
?>
<script><!--
    orcidee.manager.createParty.options = {
        max: <?php  echo $controls->getNbTables(); ?>,
        start: <?php echo $controls->getDate(Controls::CONV_START, "%H"); ?>
    };
    orcidee.manager.isDebug = <?php echo (IS_DEBUG)?'true':'false'; ?>;
//--></script>