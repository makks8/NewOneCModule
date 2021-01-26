<?php

namespace App\Models\CRM\Behaviors;

use App\Models\Bitrix;
use App\Models\CRM\EntityBehavior;

class ProductBehavior implements EntityBehavior
{

    public function sendToCrm($product)
    {
        $params = $product->getParams();
        $fields = $params['FIELDS'];
        $vatParams = $fields['VAT_ID'];
        $measureParams = $fields['MEASURE'];
        //$catalogSections = $fields['SECTION_ID'];

        $fields['VAT_ID'] = self::getVatID($vatParams);
        $fields['MEASURE'] = self::getMeasureID($measureParams);
        $fields['SECTION_ID'] = isset($fields['SECTION_ID'])? self::getSectionID($fields['SECTION_ID']) : null;
        $params['FIELDS'] = $fields;

        $method = $product->getMethod();

        return Bitrix::request($method, $params);
    }

    public function getOneCParams($product): array
    {
        $method = 'crm.' . $product->name . '.get';
        $params = ['id' => $product->crm_id];

        return Bitrix::request($method, $params);
    }

    private static function getSectionID($catalogSections)
    {
        $firstSectionCode = $catalogSections[0]['CODE'];
        $checkSectionResult = self::checkCatalogSection($firstSectionCode);
        if (empty($checkSectionResult)) {
            $sectionID = self::createParentFolders($catalogSections);
        } else {
//            $sectionID = $checkSectionResult[0]['id'];
            $sectionID = $checkSectionResult[0]['ID'];
        }
        return $sectionID;
    }

    /**
     * @param array $params = [ 'CODE' => string, 'MEASURE_TITLE' => string, 'SYMBOL_RUS' => string ]
     * @return int
     */
    private static function getMeasureID(array $params)
    {
        $params['CODE'] = intval($params['CODE']);
        if (empty($params['MEASURE_TITLE']) || $params['CODE'] == 0) {
            return null;
        }
        $method = 'crm.measure.list';
        $filter = [
            'select' => ['ID'],
            'filter' => ['CODE' => $params['CODE']]
        ];
        $response = Bitrix::request($method, $filter)/*[0]['ID']*/;

        if(empty($response)){
            $method = 'crm.measure.add';
            $filter = [
                'fields' => $params
            ];
            $response = Bitrix::request($method, $filter);
            return $response;
        } else {
            return $response[0]['ID'];
        }
    }

    /**
     * @param array $params = [ 'NAME' => string, 'RATE' => string ]
     * @return int
     */
    private static function getVatID(array $params): int
    {
        $method = 'crm.vat.list';
        $filter = [
            'select' => ['ID'],
            'filter' => ['RATE' => $params['RATE']]
        ];
        $response = Bitrix::request($method, $filter);

        if(empty($response)){
            $method = 'crm.vat.add';
            $filter = [
                'fields' => $params
            ];
            $response = Bitrix::request($method, $filter);
            return $response;
        } else {
            return $response[0]['ID'];
        }
    }

    private static function checkCatalogSection($code)
    {

//        $method = 'catalog.section.list';
//        $filter = ['iblockId' => 13, 'code' => $code];
//        $filterArray = self::createFilter('id', $filter);
        $method = 'crm.productsection.list';
        $filter = ['XML_ID' => $code];
        $filterArray = self::createFilter('CATALOG_ID', $filter);

        $response = Bitrix::request($method, $filterArray);
        if(empty($response)){ return null;}
//        return $response['sections'];
        return $response;
    }

    private static function createParentFolders($catalogSections)
    {
        $catalogSections = array_reverse($catalogSections);
        foreach ($catalogSections as $count => $section) {

            $checkSectionResult = self::checkCatalogSection($section['CODE']);
            $sectionID = 0;
            if (empty($checkSectionResult)) {

                if ($count > 0) {
                    $sectionID = $catalogSections[$count - 1];
                }
//                $sectionData = [
//                    'fields' => [
//                        'iblockId' => 13,
//                        'iblockSectionId' => $sectionID,
//                        'name' => $section['NAME'],
//                        'code' => $section['CODE']
//                    ]
//                ];
//                $method = 'catalog.section.add';

                $sectionData = [
                    'fields' => [
                        'SECTION_ID' => $sectionID,
                        'NAME' => $section['NAME'],
                        'XML_ID' => $section['CODE']
                    ]
                ];
                $method = 'crm.productsection.add';
                $response = Bitrix::request($method, $sectionData);
                $sectionID = $response;

            } else {
//                $sectionID = $checkSectionResult[0]['id'];
                $sectionID = $checkSectionResult[0]['ID'];
            }
            $catalogSections[$count] = $sectionID;
            //usleep(400000);
        }
        return end($catalogSections);
    }

    private static function createFilter($select, $filter)
    {
        return [
            'select' => $select,
            'filter' => $filter
        ];
    }
}
