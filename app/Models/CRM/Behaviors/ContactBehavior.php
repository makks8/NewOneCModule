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

//        if(!empty($params['FIELDS']['ASSIGNED_EMAIL'])){
//            $assignedByID =  self::getUserByEmail($params['FIELDS']['ASSIGNED_EMAIL']);
//        } else {
//            $assignedByID = '7';
//        }
//        $params['FIELDS']['ASSIGNED_BY_ID'] = $assignedByID;
        $params['FIELDS']['ASSIGNED_BY_ID'] = (!empty($params['FIELDS']['ASSIGNED_EMAIL'])) ? self::getUserByEmail($params['FIELDS']['ASSIGNED_EMAIL']) : '1';

        if (isset($params['FIELDS']['COMPANIES'])) {
            $arrOfCompanies = self::checkContactCompanies($params['FIELDS']['COMPANIES']);
            $params['FIELDS']['COMPANY_IDS'] = $arrOfCompanies;
        }

        $params['FIELDS']['PHONE'] = isset($params['FIELDS']['PHONE']) ? self::getContactDataArr($params['FIELDS']['PHONE']) : null;
        $params['FIELDS']['EMAIL'] = isset($params['FIELDS']['EMAIL']) ? self::getContactDataArr($params['FIELDS']['EMAIL']) : null;
        $params['FIELDS']['WEB'] = isset($params['FIELDS']['WEB']) ? self::getContactDataArr($params['FIELDS']['WEB']) : null;

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
            array_push($arrOfCompanies, $company);
        }
        return $arrOfCompanies;
    }

    private static function getContactDataArr(array $contactData): array
    {
        $dataArr = array();
        foreach ($contactData as $data) {
            $dataArr[] = array('VALUE' => $data['VALUE'], 'VALUE_TYPE'=>$data['VALUE_TYPE']);
        }
        if (empty($dataArr)) {
            $dataArr[] = null;
        }
        return $dataArr;
    }

    private static function getUserByEmail($email)
    {
        $method = 'user.get';
        $filterData = [
            'EMAIL' => $email
        ];
        $response = Bitrix::request($method, $filterData);
        $result = (!empty($response)) ? $response[0]['ID'] : '7' ;
        return $result;
    }

}
