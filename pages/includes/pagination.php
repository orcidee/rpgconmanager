<?php
if ($pageSize < $total) {
    echo "<div class='pagination-info'>Parties ".((($currentP-1)* $pageSize) + 1)." à ".min($currentP * $pageSize, $total)." sur ".$total."</div>";
    echo '<ul class="pagination">';
    for($i = 1 ; $i <= $max ; $i++){
        echo "<li><a href=\"#\" onclick=\"document.forms['filteringForm'].pageNb.value='".$i."';document.forms['filteringForm'].submit();\" ".(($i == $currentP)? "class='activ'" : "").">$i</a></li>";
    }
    echo "</ul>";
}