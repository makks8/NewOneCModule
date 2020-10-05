<?php

namespace App\Models\CRM\Entity;

use App\Models\CRM\CRM;

class Contact extends CRM
{
    public function __construct()
    {
        parent::__construct();

    }


    public function onDelete($entities)
    {
        if ($this->hasChanges($entities, __FUNCTION__)) {
            echo __FUNCTION__ . '<br>';
            foreach ($entities as $entity) {
                echo nl2br($this->name . " $entity was deleted." . PHP_EOL);
            }
        }
    }

    public function onAdd($entities)
    {
        if ($this->hasChanges($entities, __FUNCTION__)) {
            echo __FUNCTION__ . '<br>';
            foreach ($entities as $entity) {
                $this->addEntity();
                echo nl2br($this->name . " $entity was added." . PHP_EOL);
            }
        }
    }

    public function onUpdate($entities)
    {
        if ($this->hasChanges($entities, __FUNCTION__)) {
            echo __FUNCTION__ . '<br>';
            foreach ($entities as $entity) {
                echo nl2br($this->name . " $entity updated." . PHP_EOL);
            }
        }
    }
}
