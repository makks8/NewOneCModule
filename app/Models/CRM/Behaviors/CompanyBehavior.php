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

        $companyParamsFields = $companyParams['FIELDS'];

        if(isset($companyParamsFields['REQUISITE']['RQ_INN']) && $companyParamsFields['REQUISITE']['RQ_INN'] != ""){
            $companyParamsFields['REQUISITE']['RQ_KPP'] = (isset($companyParamsFields['REQUISITE']['RQ_KPP']) && $companyParamsFields['REQUISITE']['RQ_KPP'] != "") ? $companyParamsFields['REQUISITE']['RQ_KPP'] : "";
            $companyFromRequisite = self::checkCompanyByInn($companyParamsFields['REQUISITE']['RQ_INN'], $companyParamsFields['REQUISITE']['RQ_KPP']);
            if($companyFromRequisite !== null){
                $companyParams['id'] = $companyFromRequisite['ENTITY_ID'];
                $company->crm_id = $companyFromRequisite['ENTITY_ID'];
                $company->save();

                //Requisite::fillObject($companyFromRequisite['ID'],$companyParamsFields['REQUISITE']);

                $companyParamsFields['REQUISITE']['ID'] = $companyFromRequisite['ID'];
                $companyParamsFields['REQUISITE']['ENTITY_ID'] = $companyFromRequisite['ENTITY_ID'];
            }
        }

        $companySendMethod = $company->getMethod();

        $companyParamsFields['ASSIGNED_BY_ID'] = isset($companyParamsFields['ASSIGNED_EMAIL']) ?
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

        /* Создание списка с привязкой созданной/обновленной компании */

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

    public function checkCompanyByInn($companyInn, $companyKpp)
    {
        $method = 'crm.requisite.list';
        $data = [
            'order' => [ 'DATE_CREATE' => 'ASC'],
            'filter' => [ '=RQ_INN' => $companyInn, '=RQ_KPP' => $companyKpp ],
            'select' => [ 'ID', 'ENTITY_ID', 'ENTITY_TYPE_ID' ] // 4
        ];
        $requisites = Bitrix::request($method, $data);
        if(!empty($requisites)){
            foreach ($requisites as $requisite){
                if($requisite['ENTITY_TYPE_ID'] == 4){
                   return [ 'ID' => $requisite['ID'], 'ENTITY_ID' => $requisite['ENTITY_ID'] ];

                }
            }
        }
        return null;
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
                'fields' => $addressValue
            ];
            $data['fields']['ENTITY_TYPE_ID'] = 8;
            $data['fields']['ENTITY_ID'] = $requisiteID;
            Bitrix::request($method, $data);
        }
    }

}
