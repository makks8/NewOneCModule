<?php


namespace App\Models\CRM\Entities;


use App\Models\CRM\Behaviors\BankRequisiteBehavior;
use App\Models\CRM\Crm;

class BankRequisite extends Crm
{
    public function __construct()
    {
        $this->setEntityBehavior(new BankRequisiteBehavior());
        parent::__construct();

    }

    public function getMethod()
    {
        $method = 'crm.requisite.bankdetail';
        if ($this->exists) return $method . '.update';
        return $method . '.add';
    }
}
