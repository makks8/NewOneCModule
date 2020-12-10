<?php

namespace App\Models\CRM\Behaviors;

use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\Entities\Company;
use App\Models\CRM\Entities\Product;
use App\Models\CRM\EntityBehavior;

class DealBehavior implements EntityBehavior
{
    public function sendToCrm($deal)
    {
        $params = $deal->getParams();
        $method = $deal->getMethod();

//        if(!empty($params['FIELDS']['ASSIGNED_EMAIL'])){
//            $assignedByID =  self::getUserByEmail($params['FIELDS']['ASSIGNED_EMAIL']);
//        } else {
//            $assignedByID = '7';
//        }
//        $params['FIELDS']['ASSIGNED_BY_ID'] = $assignedByID;
        $params['FIELDS']['ASSIGNED_BY_ID'] = (!empty($params['FIELDS']['ASSIGNED_EMAIL'])) ? self::getUserByEmail($params['FIELDS']['ASSIGNED_EMAIL']) : '7';

        $productRows = $params['FIELDS']['PRODUCTS'];
        unset($params['FIELDS']['PRODUCTS']);
        $params['FIELDS']['COMPANY_ID'] = Company::getByGuid($params['FIELDS']['COMPANY_ID'])->crm_id;

        $result = Bitrix::request($method, $params);

        $dealID = $deal->exists ? $deal->crm_id : $result;

        foreach ($productRows as $count => $productRow) {
            $product = Product::getByGuid($productRow['GUID']);
            $productRows[$count]['PRODUCT_ID'] = $product->crm_id;
            unset($productRows[$count]['GUID']);
        }

        $params = [
            'id' => $dealID,
            'rows' => $productRows
        ];

        $method = 'crm.deal.productrows.set';

        Bitrix::request($method, $params);

        return $result;
    }

    public function getOneCParams($deal): array
    {
        $method = 'crm.' . $deal->name;
        $params = ['id' => $deal->crm_id];
        $dealParams = Bitrix::request("$method.get", $params);
        $deal->description = $dealParams['TITLE'];
        if (!empty($deal->guid)) {
            $dealParams['GUID'] = $deal->guid;
        }
        $productRows = Bitrix::request($method . '.productrows.get', $params);
        foreach ($productRows as $count => $productRow) {
            $productEntity = Product::getByID($productRow['PRODUCT_ID']);
            $productRows[$count]['GUID'] = $productEntity->guid;
        }
        $dealParams['PRODUCTS'] = $productRows;

        $companyID = $dealParams['COMPANY_ID'];
        $company = Company::getByID($companyID);

        $dealParams['COMPANY'] = $company->guid;

        return $dealParams;
    }

    private static function getUserByEmail($email)
    {
        $method = 'user.get';
        $filterData = [
            'FILTER' => [ 'EMAIL' => $email ]
        ];
        $response = Bitrix::request($method, $filterData);
        $result = (!empty($response)) ? $response[0]['ID'] : '7' ;
        return $result;
    }
}
