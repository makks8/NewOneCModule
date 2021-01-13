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

    public static function fillObject(int $crmID,array $fields)
    {
        $requisite = new self();
        $requisite->fill(array('crm_id'=>$crmID,'guid'=>$fields['GUID']))->save();
    }
}
