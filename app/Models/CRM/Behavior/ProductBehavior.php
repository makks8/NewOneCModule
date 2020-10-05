<?php

namespace App\Models\CRM\Behavior;

use App\Models\Bitrix;
use App\Models\CRM\EntityBehavior;

class ProductBehavior implements EntityBehavior
{

    public function add($entity)
    {
        $entityData = $entity->getParams();
        $vatRate = $entityData['fields']['VAT_ID'];
        $catalogSections = $entityData['fields']['SECTION_ID'];
        $measureCode = $entityData['fields']['MEASURE'];
        $entityData['fields']['VAT_ID'] = self::getVatID($vatRate);
        $entityData['fields']['SECTION_ID'] = self::getSectionID($catalogSections);
        $entityData['fields']['MEASURE'] = self::getMeasureID($measureCode);

        $method = $entity->getMethod();

        return Bitrix::request($method, $entityData);
    }

    public function getParams($entity)
    {
        $data = $entity->getData();
        $params = ['fields' => $data];

        if ($entity->entityExists()) {
            $params['id'] = $entity->bitrix_id;
        }
        return $params;
    }

    public function getEntityData($entity)
    {
        $method = 'crm.' . $entity->name . '.get';
        $params = ['id' => $entity->bitrix_id];

        return Bitrix::request($method, $params);
    }

    private static function getSectionID($catalogSections)
    {
        $firstSectionCode = $catalogSections[0]['CODE'];
        $checkSectionResult = self::checkCatalogSection($firstSectionCode);
        if (empty($checkSectionResult)) {
            $sectionID = self::createParentFolders($catalogSections);
        } else {
            $sectionID = $checkSectionResult[0]['id'];
        }
        return $sectionID;
    }

    private static function getMeasureID($code)
    {
        $method = 'catalog.measure.list';
        $filter = [
            'select' => ['id'],
            'filter' => ['code' => $code]
        ];
        $response = Bitrix::request($method, $filter);
        return $response['measures'][0]['id'];
    }

    private static function getVatID($vatRate)
    {
        $method = 'catalog.vat.list';
        $filter = [
            'select' => ['id'],
            'filter' => ['rate' => $vatRate]
        ];
        $response = Bitrix::request($method, $filter);
        return $response['vats'][0]['id'];
    }

    private static function checkCatalogSection($code)
    {
        $filter = ['iblockId' => 13, 'code' => $code];
        $filterArray = self::createFilter('id', $filter);
        $method = 'catalog.section.list';

        $response = Bitrix::request($method, $filterArray);
        return $response['sections'];
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
                $sectionData = [
                    'fields' => [
                        'iblockId' => 13,
                        'iblockSectionId' => $sectionID,
                        'name' => $section['NAME'],
                        'code' => $section['CODE']
                    ]
                ];
                $method = 'catalog.section.add';
                $response = Bitrix::request($method, $sectionData);
                $sectionID = $response['section']['id'];
            } else {
                $sectionID = $checkSectionResult[0]['id'];
            }
            $catalogSections[$count] = $sectionID;
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
