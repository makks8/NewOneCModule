<?php


namespace App\Models;


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

    public static function drawMessage($message)
    {
        echo $message."<br>";
        flush();
        ob_flush();
    }
}
