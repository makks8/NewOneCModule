<?php

namespace App\Models\CRM\Behaviors;

use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\Entities\Address;
use App\Models\CRM\Entities\BankRequisite;
use App\Models\CRM\Entities\Company;
use App\Models\CRM\Entities\Contact;
use App\Models\CRM\Entities\Requisite;
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
        if(isset($companyParamsFields['REQUISITE'])){ unset($companyParamsFields['REQUISITE']); }
        $bankRequisiteParams = isset($companyParamsFields['BANK_REQUISITE']) ? $companyParamsFields['BANK_REQUISITE'] : null;
        if(isset($companyParamsFields['BANK_REQUISITE'])){ unset($companyParamsFields['BANK_REQUISITE']); }
        $addressParams = isset($companyParamsFields['ADR']) ? $companyParamsFields['ADR'] : null;
        if(isset($companyParamsFields['ADR'])){ unset($companyParamsFields['ADR']); }

        $companyParams['FIELDS'] = $companyParamsFields;
        $companySendResult = Bitrix::request($companySendMethod, $companyParams);

        if (!empty($companySendResult) && isset($companyParamsFields['CONTACTS'])) {
            $arrOfContacts = self::checkCompanyContacts($companyParamsFields['CONTACTS']);
            if (!$company->exists) {
                $company->crm_id = $companySendResult;
            }
            $company->addContacts($arrOfContacts);
        }

        $requisiteParams['ENTITY_ID'] = $company->exists ? $company->crm_id : $companySendResult;

        /* Добавление реквизитов и привзяка к ним адресов и банковских реквизитов (NEW) */
        if(isset($requisiteParams)){
            $requisiteID = self::checkCompanyRequisites($requisiteParams);
            if(isset($bankRequisiteParams) && isset($requisiteID)){
                self::checkCompanyBankRequisites($requisiteID, $bankRequisiteParams);
            }
            if(isset($addressParams) && isset($requisiteID)){
                self::checkCompanyAddress($requisiteID, $addressParams);
            }
        }
        /*  */
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

    public function checkCompanyRequisites($arrRequisiteParams)
    {
        $requisite = Requisite::sendToCrm($arrRequisiteParams);
        return $requisite->crm_id;
    }

    public function checkCompanyBankRequisites($requisiteID, $arrBankRequisiteParams)
    {
        $arrOfBankRequisites = array();
        foreach ($arrBankRequisiteParams as $bankRequisiteValue) {
            $bankRequisiteValue['ENTITY_ID'] = $requisiteID;
            $bankRequisite = BankRequisite::sendToCrm($bankRequisiteValue);
            array_push($arrOfBankRequisites, $bankRequisite->crm_id);
        }
        return $arrOfBankRequisites;
    }

    public function checkCompanyAddress($requisiteID, $arrAddressParams)
    {
        foreach ($arrAddressParams as $addressValue) {
            $method = 'crm.address.add';
            $data = [
                'fields' => [
                    $addressValue
                ]
            ];
            $data['ENTITY_TYPE_ID'] = 8;
            $data['ENTITY_ID'] = $requisiteID;
            Bitrix::request($method, $data);
        }
    }

}
