<?php

namespace App\Models\CRM\Entity;

use App\Models\CRM\Behavior\CompanyBehavior;

use App\Models\CRM\CRM;


class Company extends CRM
{
    public function __construct()
    {
        parent::__construct(new CompanyBehavior());

        $data = $this->getData();
        $this->description = $data['TITLE'];
        $this->guid = $data['GUID'];

    }

}
