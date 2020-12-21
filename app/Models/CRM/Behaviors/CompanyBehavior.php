<?php

namespace App\Models\CRM\Behaviors;

use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\Entities\Company;
use App\Models\CRM\Entities\Contact;
use App\Models\CRM\Entities\User;
use App\Models\CRM\EntityBehavior;
use phpDocumentor\Reflection\Types\Mixed_;

class CompanyBehavior implements EntityBehavior
{
    /**
     * @param Crm $company
     * @return mixed|null
     */
    public function sendToCrm(Crm $company)
    {
        $companyParams = $company->getParams();
        $companySendMethod = $company->getMethod();

        $companyParamsFields = $companyParams['FIELDS'];
        $companyParamsFields['ASSIGNED_BY_ID'] = !empty($companyParamsFields['ASSIGNED_EMAIL']) ?
            User::getByEmail($companyParamsFields['ASSIGNED_EMAIL']) : '1';
        $companyParamsFields = Company::setContactDataIntoParams($companyParamsFields);

        $requisiteParams = isset($companyParamsFields['REQUISITE']) ? $companyParamsFields['REQUISITE'] : null;
        $addressParams = isset($companyParamsFields['ADR']) ? $companyParamsFields['ADR'] : null;

        if (!$company->exists) {
            $requisiteRequestData = self::getRequisiteBy($requisiteParams['RQ_INN'], 'inn');
            if (!empty($requisiteRequestData)) {
                $company->crm_id = $requisiteRequestData['ENTITY_ID'];
                $company->save();
                $companyParams['id'] = $requisiteRequestData['ENTITY_ID'];
                $companySendMethod = $company->getMethod();
            }
        } else {
            $requisiteRequestData = self::getRequisiteBy($companyParams['id'], 'id');
        }
        $companyParams['FIELDS'] = $companyParamsFields;
        $companySendResult = Bitrix::request($companySendMethod, $companyParams);

        if (!empty($companySendResult) && isset($companyParamsFields['CONTACTS'])) {
            $arrOfContacts = self::checkCompanyContacts($companyParamsFields['CONTACTS']);
            if (!$company->exists) {
                $company->crm_id = $companySendResult;
            }
            $company->addContacts($arrOfContacts);
        }

        $requisiteParams['ENTITY_ID'] = $company->exists ? $companyParams['id'] : $companySendResult;
        $requisiteParams['ENTITY_TYPE_ID'] = 4;
        $requisiteParams['PRESET_ID'] = 1;
        $requisiteParams['NAME'] = 'Реквизиты из 1с';
        if (empty($requisiteRequestData['ID'])) {
            $requisiteID = self::addCompanyReq($requisiteParams);
            self::addCompanyAddress($addressParams, $requisiteID);
        } else {
            $requisiteID = $requisiteRequestData['ID'];
            self::updateCompanyReq($requisiteID, $requisiteParams);

            $addressID = self::getAddressBy($requisiteID, 'entity');
            if (empty($addressID)) {
                self::addCompanyAddress($addressParams, $requisiteID);
            } else {
                self::updateCompanyAddress($addressID, $addressParams);
            }
        }

        return $companySendResult;
    }

    public function getOneCParams($company): array
    {
        $method = 'crm.' . $company->name . '.get';
        $params = ['id' => $company->crm_id];

        return Bitrix::request($method, $params);
    }

    public function checkCompanyContacts($arrContactParams)
    {
        $arrOfContacts = array();
        foreach ($arrContactParams as $contactKey => $contactValue) {
            $contact = Contact::sendToCrm($contactValue);
            array_push($arrOfContacts, $contact->crm_id);
        }
        return $arrOfContacts;
    }

    private static function addCompanyReq($requisiteParams)
    {
        $method = 'crm.requisite.add';
        $data = [
            'fields' => $requisiteParams
        ];
        Bitrix::request($method, $data);
    }

    private static function getRequisiteBy($value, $type)
    {
        $method = 'crm.requisite.list';
        $data = [
            'filter' => [
                'ENTITY_TYPE_ID' => 4
            ]
        ];
        switch ($type) {
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

    private static function updateCompanyReq($requisiteID, $requisiteParams)
    {
        $method = 'crm.requisite.update';
        $data = [
            'id' => $requisiteID,
            'fields' => $requisiteParams
        ];
        Bitrix::request($method, $data);
    }

    private static function addCompanyAddress($addressParams = null, $reqID)
    {
        $method = 'crm.address.add';
        $data = [
            'fields' => [
                'TYPE_ID' => $addressParams['TYPE_ID'],
                'ENTITY_TYPE_ID' => 8,
                'ENTITY_ID' => $reqID,
                'ADDRESS_1' => $addressParams['VALUE']
            ]
        ];
        $test = Bitrix::request($method, $data);
    }

    private static function getAddressBy($value, $type = null)
    {
        $method = 'crm.address.list';
        $data = [
            'filter' => [
                'ENTITY_ID' => $value,
                'ENTITY_TYPE_ID' => 8
            ]
        ];

        if (empty($value)) {
            return null;
        }
        $addressID = Bitrix::request($method, $data);

        if (empty($addressID)) {
            return null;
        }

        return $addressID[0];
    }

    private static function updateCompanyAddress($addressID, $addressParams)
    {
        $method = 'crm.address.update';
        $data = [
            'id' => $addressID,
            'fields' => $addressParams
        ];
        $test = Bitrix::request($method, $data);
    }
}
