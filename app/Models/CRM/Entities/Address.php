<?php


namespace App\Models\CRM\Entities;


use App\Models\CRM\Behaviors\AddressBehavior;
use App\Models\CRM\Crm;

class Address extends Crm
{
    public function __construct()
    {
        $this->setEntityBehavior(new AddressBehavior());
        parent::__construct();

    }
}
