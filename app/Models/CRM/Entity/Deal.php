<?php

namespace App\Models\CRM\Entity;

use App\Models\CRM\Behavior\DealBehavior;
use App\Models\CRM\CRM;

class Deal extends CRM
{
    public function __construct()
    {
        $this->setEntityBehavior(new DealBehavior());
        parent::__construct();
    }


    /*public function onDelete($entities)
    {
        if ($this->hasChanges($entities, __FUNCTION__)) {
            echo __FUNCTION__ . '<br>';
            foreach ($entities as $entity) {
                echo nl2br($this->name . " $entity was deleted." . PHP_EOL);
            }
        }
    }*/

    /* public function onAdd($entities)
     {
         if (Event::hasChanges($entities, __FUNCTION__)) {
             echo __FUNCTION__ . '<br>';
             foreach ($entities as $entityID) {
                 self::sendEntityToOneC($entityID);
                 echo nl2br($this->name . " $entityID updated." . PHP_EOL);
             }
         }
     }*/

    public function onUpdate($entities)
    {
        if (Event::hasChanges($entities, __FUNCTION__)) {
            echo __FUNCTION__ . '<br>';
            foreach ($entities as $entityID) {
                $entity = Deal::getEntityByID($entityID);
                $entity->sendEntityToOneC();
                echo nl2br($this->name . " $entityID updated." . PHP_EOL);
            }
        }
    }
}
