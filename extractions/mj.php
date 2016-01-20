<?php

require_once(dirname(__FILE__).'/../conf/conf.php');

$dbServer = mysql_connect(HOST,USER,PASSWORD) or die("Impossible de se connecter : " . mysql_error());
$db = (mysql_select_db(DB));
mysql_query("SET NAMES 'utf8'");

require_once(dirname(__FILE__).'/../classes/controls.php');
require_once(dirname(__FILE__).'/../classes/user.php');
require_once(dirname(__FILE__).'/../classes/party.php');
require_once(dirname(__FILE__).'/../classes/inscription.php');

$user = User::getFromSession();

if($user){

    if($user->getRole() == "administrator"){



        $thisYear = Controls::getDate(Controls::CONV_START, '%Y');
        $animators = User::getUsersByYear(User::ROLE_MJ, $thisYear);

        ob_start();

        header("Content-type: application/vnd.ms-excel; charset=iso-8859-1");
        header("Content-Disposition: attachment; filename=TimeLine_Orcidee.xls");

        ?>

        <table border='1' width='100%' cellspacing='3' cellpadding='3'>
        <tr>
            <td align='center' valign='middle' colspan='4'>
                <font size='+4'><b>Liste des animateurs <?= $thisYear ?></b><br><br></font>
            </td>
        </tr>
        <tr>
            <td valign='middle'><b>Nom</b></td>
            <td valign='middle'><b>Prénom</b></td>
            <td valign='middle'><b>Email</b></td>
            <td valign='middle'><b>Telephone</b></td>
        </tr>
        <?php

        while($row = mysql_fetch_assoc($animators)){  ?>
            <tr>
                <td valign='middle'><?= $row['lastname'] ?> </td>
                <td valign='middle'><?= $row['firstname'] ?></td>
                <td valign='middle'><?= $row['email'] ?>    </td>
                <td valign='middle'><?= $row['phone'] ?>    </td>
            </tr>

            <?php
        }
        ?>
        </table>
    <?php

        $output = mb_convert_encoding(ob_get_contents(),'iso-8859-1','utf-8');
        ob_end_clean();
        echo $output;

    }else{
        echo "<p>Acces restreint à l'administrateur</p>";
    }

}else{
    echo "<p>Vous n'êtes pas authentifié.</p>";

    // Debug
    echo "<div class='dbg'>User:";
    echo ($user) ? ($user->getLastname()." (".$user->getRole().") ") : "0" ;
    echo "</div>";
}
