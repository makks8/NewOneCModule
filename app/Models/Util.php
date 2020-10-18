<?php


namespace App\Models\Util;


class Util
{
    public static function drawPercentage($current, $total)
    {
        $percent = $current / $total * 100;
        $percent = (int)$percent . " %<br>";
        echo $percent;
        flush();
        ob_flush();
    }
}
