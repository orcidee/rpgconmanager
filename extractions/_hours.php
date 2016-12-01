<?php
// One column per half hour
for ($timeline=$startHour; $timeline<=($startHour + $duration - 0.5); $timeline += 0.5){

    // Write only plain hours
    $hour = "";
    if (floor($timeline) == $timeline) {
        // Restart hour label when passing from saturday to sunday
        $hour = (($timeline<24) ? $timeline : $timeline-24) . 'h';
    } ?>

    <td align='center' valign='middle' WIDTH='50'>
        <font size='-2'><b><?= $hour ?></b></font>
    </td>
<?php }