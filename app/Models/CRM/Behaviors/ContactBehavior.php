<?php

namespace App\Models\CRM\Behaviors;

use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\Entities\Contact;
use App\Models\CRM\Entities\Company;
use App\Models\CRM\Entities\User;
use App\Models\CRM\EntityBehavior;

use phpDocumentor\Reflection\Types\Mixed_;

class ContactBehavior implements EntityBehavior
{

    public function sendToCrm(Crm $contact)
    {
        $params = $contact->getParams();
        $method = $contact->getMethod();

        $paramsFields = $params['FIELDS'];
        $paramsFields['ASSIGNED_BY_ID'] = isset($paramsFields['ASSIGNED_EMAIL'])
            ? User::getByEmail($paramsFields['ASSIGNED_EMAIL']) : '1';

        $paramsFields = Company::setContactDataIntoParams($paramsFields);

        if (isset($params['FIELDS']['COMPANIES'])) {
            $arrOfCompanies = self::checkContactCompanies($paramsFields['COMPANIES']);
            $paramsFields['COMPANY_IDS'] = $arrOfCompanies;
        }

        $params['FIELDS'] = $paramsFields;

        return Bitrix::request($method, $params);
    }

    public function getOneCParams($contact): array
    {
        $method = 'crm.' . $contact->name . '.get';
        $params = ['id' => $contact->crm_id];

        return Bitrix::request($method, $params);
    }

    public function checkContactCompanies($arrCompanyParams)
    {
        $arrOfCompanies = array();
        foreach ($arrCompanyParams as $companyKey => $companyValue) {
            $company = Company::sendToCrm($companyValue);
            array_push($arrOfCompanies, $company->crm_id);
        }
        return $arrOfCompanies;
    }
}
