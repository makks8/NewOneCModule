<?php

namespace App\Models\CRM\Entities;

use App\Models\CRM\Behaviors\CompanyBehavior;

use App\Models\CRM\Crm;


class Company extends Crm
{

    public function __construct()
    {
        $this->setEntityBehavior(new CompanyBehavior());
        parent::__construct();
//        $data = $this->getData();
//        $this->description = $data['NAME'];
//        $this->guid = $data['GUID'];
    }

    public static function create($data)
    {
        $company = self::getByGuid($data['GUID']);
        parent::create($company);
        $company->description = $data['TITLE'];
        if (!$company->exists) $company->guid = $data['GUID'];
        $company->setEntityBehavior(new CompanyBehavior());
        return $company;
    }

}
