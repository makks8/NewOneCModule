<?php

namespace App\Models\CRM\Behavior;

use App\Models\Bitrix;
use App\Models\CRM\Entity\Company;
use App\Models\CRM\Entity\Product;
use App\Models\CRM\EntityBehavior;

class DealBehavior implements EntityBehavior
{
    public function add($entity)
    {

    }

    public function getParams($entity)
    {
        // TODO: Implement getFields() method.
    }


    public function getEntityData($entity)
    {
        $method = 'crm.' . $entity->name;
        $params = ['id' => $entity->bitrix_id];
        $deal = Bitrix::request("$method.get", $params);
        $entity->description = $deal['TITLE'];
        if (!empty($entity->guid)) {
            $deal['GUID'] = $entity->guid;
        }
        $productRows = Bitrix::request($method . '.productrows.get', $params);
        foreach ($productRows as $count => $productRow) {
            $productEntity = Product::getEntityByID($productRow['PRODUCT_ID']);
            $productRows[$count]['GUID'] = $productEntity->guid;
        }
        $deal['PRODUCTS'] = $productRows;

        $companyID = $deal['COMPANY_ID'];
        $company = Company::getEntityByID($companyID);

        $deal['COMPANY'] = $company->guid;

        return $deal;
    }
}
