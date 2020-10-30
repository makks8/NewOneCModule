<?php

namespace App\Models\CRM\Entities;

use App\Models\CRM\Behaviors\DealBehavior;
use App\Models\CRM\Crm;

class Deal extends Crm
{
    public function __construct()
    {
        $this->setEntityBehavior(new DealBehavior());
        parent::__construct();
    }


    /*public function onCrmDelete($entities)
    {
        if ($this->hasChanges($entities, __FUNCTION__)) {
            echo __FUNCTION__ . '<br>';
            foreach ($entities as $entity) {
                echo nl2br($this->name . " $entity was deleted." . PHP_EOL);
            }
        }
    }*/

    public static function onCrmAdd($entities)
    {
        foreach ($entities as $entityID) {
            $entity = Deal::getByID($entityID);
            $entity->sendToOneC();
        }
    }

    public static function onCrmUpdate($entities)
    {
        foreach ($entities as $entityID) {
            $entity = Deal::getByID($entityID);
            $entity->sendToOneC();
        }
    }

}
