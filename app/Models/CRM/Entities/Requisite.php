<?php


namespace App\Models\CRM\Entities;

use App\Models\Bitrix;
use App\Models\CRM\Behaviors\RequisiteBehavior;
use App\Models\CRM\Crm;

class Requisite extends Crm
{
    public function __construct()
    {
        $this->setEntityBehavior(new RequisiteBehavior());
        parent::__construct();

    }
}
