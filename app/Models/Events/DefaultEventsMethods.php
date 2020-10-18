<?php


namespace App\Models\Events;


interface DefaultEventsMethods
{
    public static function onCrmAdd($entities);

    public static function onCrmUpdate($entities);

    public static function onCrmDelete($entities);
}
