<?php

namespace App\Models\CRM\Entities;

use App\Models\CRM\Crm;

class Contact extends Crm
{
    public function __construct()
    {
        parent::__construct();

    }


    public function onCrmDelete($entities)
    {
        if ($this->hasChanges($entities, __FUNCTION__)) {
            echo __FUNCTION__ . '<br>';
            foreach ($entities as $entity) {
                echo nl2br($this->name . " $entity was deleted." . PHP_EOL);
            }
        }
    }

    public function onCrmAdd($entities)
    {
        if ($this->hasChanges($entities, __FUNCTION__)) {
            echo __FUNCTION__ . '<br>';
            foreach ($entities as $entity) {
                echo nl2br($this->name . " $entity was added." . PHP_EOL);
            }
        }
    }

    public function onCrmUpdate($entities)
    {
        if ($this->hasChanges($entities, __FUNCTION__)) {
            echo __FUNCTION__ . '<br>';
            foreach ($entities as $entity) {
                echo nl2br($this->name . " $entity updated." . PHP_EOL);
            }
        }
    }
}
