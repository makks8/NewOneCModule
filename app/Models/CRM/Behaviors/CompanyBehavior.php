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
            $requisiteRequestData = self::getRequisiteBy($requisiteParams['RQ_INN'], 'inn');
            if (!empty($companyID)) {
                $company->crm_id = $companyID['ENTITY_ID'];
                $company->save();
                $companyParams['id'] = $companyID['ENTITY_ID'];
                $companySendMethod = $company->getMethod();
            }
        } else {
            $requisiteRequestData = self::getRequisiteBy($companyParams['id'], 'id');
        }

        $companySendResult = Bitrix::request($companySendMethod, $companyParams);

        $requisiteParams['ENTITY_ID'] = $company->exists ? $companyParams['id'] : $companySendResult;
        $requisiteParams['ENTITY_TYPE_ID'] = 4;
        $requisiteParams['PRESET_ID'] = 1;
        $requisiteParams['NAME'] = 'Реквизиты из 1с';
        if(empty($requisiteRequestData['ID'])){
            self::addCompanyReq($requisiteParams);
        } else {
            $requisiteID = $requisiteRequestData['ID'];
            self::updateCompanyReq($requisiteID, $requisiteParams);
        }

        return $companySendResult;
    }

    public function getOneCParams($company): array
    {
        $method = 'crm.' . $company->name . '.get';
        $params = [ 'id' => $company->crm_id ];

        return Bitrix::request($method, $params);
    }

    private static function getRequisiteBy($value, $type)
    {
        $method = 'crm.requisite.list';
        $data = [
            'filter' => [
                'ENTITY_TYPE_ID' => 4
            ]
        ];
        switch ($type){
            case 'inn':
                $data['filter']['RQ_INN'] = $value;
                break;

            case 'id':
                $data['filter']['ENTITY_ID'] = $value;
                break;

            default:
                return null;
                break;
        }
        if (empty($value)) {
            return null;
        }
        $requisiteID = Bitrix::request($method, $data);

        if (empty($requisiteID)) {
            return null;
        }

        return $requisiteID[0];
    }

    private static function addCompanyReq($requisiteParams)
    {
        $method = 'crm.requisite.add';
        $data = [
            'fields' => $requisiteParams
        ];
        $test = Bitrix::request($method, $data);
    }

    private static function updateCompanyReq($requisiteID, $requisiteParams)
    {
        $method = 'crm.requisite.update';
        $data = [
            'id' => $requisiteID,
            'fields' => $requisiteParams
        ];
        $test = Bitrix::request($method, $data);
    }
}
