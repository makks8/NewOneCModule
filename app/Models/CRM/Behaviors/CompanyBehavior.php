<?php

namespace App\Models\CRM\Behaviors;

use App\Models\Bitrix;
use App\Models\CRM\EntityBehavior;

class CompanyBehavior implements EntityBehavior
{

    public function sendToCrm($company)
    {
        $params = $company->getParams();
        $method = $company->getMethod();

        return Bitrix::request($method, $params);
    }

    public function getOneCParams($company): array
    {
        $method = 'crm.' . $company->name . '.get';
        $params = ['id' => $company->crm_id];

        return Bitrix::request($method, $params);
    }
}
