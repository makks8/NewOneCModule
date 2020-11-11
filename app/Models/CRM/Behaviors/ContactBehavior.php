<?php

namespace App\Models\CRM\Behaviors;

use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\Entities\Contact;
use App\Models\CRM\Entities\Company;
use App\Models\CRM\EntityBehavior;
use phpDocumentor\Reflection\Types\Mixed_;

class ContactBehavior implements EntityBehavior
{

    public function sendToCrm(Crm $contact)
    {
        $params = $contact->getParams();
        $method = $contact->getMethod();
        $arrOfCompanies = self::checkContactCompanies($params['FIELDS']['OWNERS']);

        $params['FIELDS']['COMPANY_IDS'] = $arrOfCompanies;
        $params['FIELDS']['PHONE'] = self::getContactDataArr($params['FIELDS']['PHONE']);
        $params['FIELDS']['EMAIL'] = self::getContactDataArr($params['FIELDS']['EMAIL']);

        return Bitrix::request($method, $params);
    }

    public function getOneCParams($contact): array
    {
        $method = 'crm.' . $contact->name . '.get';
        $params = [ 'id' => $contact->crm_id ];

        return Bitrix::request($method, $params);
    }

    public function checkContactCompanies($arrCompanyParams)
    {
        $arrOfCompanies = array();
        foreach ($arrCompanyParams as $companyKey => $companyValue){
            $company = Company::sendToCrm($companyValue);
            array_push($arrOfCompanies, $company->crm_id);
        }
        return $arrOfCompanies;
    }

    private static function getContactDataArr(array $contactData): array
    {
        $dataArr = array();
        foreach ($contactData as $data) {
            $dataArr[] = array('VALUE' => $data, 'VALUE_TYPE'=>'WORK');
        }
        if (empty($dataArr)) {
            $dataArr[] = null;
        }
        return $dataArr;
    }


}
