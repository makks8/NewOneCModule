<?php

namespace App\Models\CRM\Behavior;

use App\Models\Bitrix;
use App\Models\CRM\EntityBehavior;

class CompanyBehavior implements EntityBehavior
{

    public function add($company)
    {
        $companyData = $company->getParams();
        $method = $company->getMethod();

        return Bitrix::request($method, $companyData);
    }

    public function getParams($company)
    {
        $data = $company->getData();
        $params = ['FIELDS' => $data];

        if ($company->entityExists()) {
            $params['id'] = $company->bitrix_id;
        }
        return $params;
    }

    public function getEntityData($entity)
    {
        $method = 'crm.' . $entity->name . '.get';
        $params = ['id' => $entity->bitrix_id];

        return Bitrix::request($method, $params);
    }
}
