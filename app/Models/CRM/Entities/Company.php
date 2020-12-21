<?php

namespace App\Models\CRM\Entities;

use App\Models\Bitrix;
use App\Models\CRM\Behaviors\CompanyBehavior;

use App\Models\CRM\Crm;


class Company extends Crm
{

    public function __construct()
    {
        $this->setEntityBehavior(new CompanyBehavior());
        parent::__construct();
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

    public function addContacts(array $contactsID): void
    {
        $method = 'crm.company.contact.items.set';
        $params = ['id' => $this->crm_id];
        foreach ($contactsID as $count => $contactID) {
            $params['items'][$count]['CONTACT_ID'] = $contactID;
        }
        Bitrix::request($method, $params);
    }

    public static function setContactDataIntoParams(array $paramsFields): array
    {
        $paramsFields['PHONE'] = isset($paramsFields['PHONE']) ? self::getContactDataArr($paramsFields['PHONE']) : null;
        $paramsFields['EMAIL'] = isset($paramsFields['EMAIL']) ? self::getContactDataArr($paramsFields['EMAIL']) : null;
        $paramsFields['WEB'] = isset($paramsFields['WEB']) ? self::getContactDataArr($paramsFields['WEB']) : null;
        return $paramsFields;
    }

    private static function getContactDataArr(array $contactData): array
    {
        $dataArr = array();
        foreach ($contactData as $data) {
            $dataArr[] = array('VALUE' => $data['VALUE'], 'VALUE_TYPE' => $data['VALUE_TYPE']);
        }
        if (empty($dataArr)) {
            $dataArr[] = null;
        }
        return $dataArr;
    }
}
