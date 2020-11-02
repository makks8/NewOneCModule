<?php

namespace App\Models\CRM\Behaviors;

use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\Entities\Company;
use App\Models\CRM\EntityBehavior;
use phpDocumentor\Reflection\Types\Mixed_;

class CompanyBehavior implements EntityBehavior
{

    public function sendToCrm(Crm $company)
    {
        $companyParams = $company->getParams();
        $companySendMethod = $company->getMethod();

        $requisiteParams = $companyParams['FIELDS']['REQUISITE'];

        if (!$company->exists) {
            $companyID = self::getByInn($requisiteParams['RQ_INN']);

            if (!empty($companyID)) {
                $company->crm_id = $companyID;
                $company->save();
                $companyParams['id'] = $companyID;
                $companySendMethod = $company->getMethod();
            }
        }

        $companySendResult = Bitrix::request($companySendMethod, $companyParams);

        $requisiteParams['ENTITY_ID'] = $company->exists ? $companyParams['id'] : $companySendResult;
        $requisiteParams['ENTITY_TYPE_ID'] = 4;
        $requisiteParams['PRESET_ID'] = 1;
        self::addCompanyReq($requisiteParams);

        return $companySendResult;
    }

    public function getOneCParams($company): array
    {
        $method = 'crm.' . $company->name . '.get';
        $params = ['id' => $company->crm_id];

        return Bitrix::request($method, $params);
    }

    private static function getByInn($inn): ?Mixed_
    {
        $method = 'crm.requisite.list';
        if (empty($inn)) {
            return null;
        }
        $data = [
            'filter' => [
                'RQ_INN' => $inn,
                'ENTITY_TYPE_ID' => 4
            ]
        ];
        $requisiteID = Bitrix::request($method, $data);
        if (empty($requisiteID)) {
            return null;
        }
        return $requisiteID[0]['ENTITY_ID'];
    }

    private static function addCompanyReq($requisiteParams)
    {
        $method = 'crm.requisite.add';
        $data = [
            'fields' => $requisiteParams
        ];
        $test = Bitrix::request($method, $data);
    }
}
