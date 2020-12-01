<?php


namespace App\Models\CRM\Additional;


use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\OneC;

class Timeline
{
    public static function addTimeline()
    {
        $data = OneC::getData();
        $entityData = Crm::getByGuid($data['GUID']);
        if(!empty($entityData->crm_id)){
            $method = 'crm.timeline.comment.add';
            $requestParams = [
                'fields' => [
                    'ENTITY_ID' => $entityData->crm_id,
                    'ENTITY_TYPE' => $entityData->name,
                    'COMMENT' => 'Документ из 1с'
                ]
            ];
            if (!empty($data['FILES'])){
                $requestParams['fields']['FILES'] = $data['FILES'];
                $requestParams['fields']['FILES'][0][1] = str_replace("\r\n", "", $requestParams['fields']['FILES'][0][1]);
                $requestParams['fields']['FILES'][0][1] = str_replace(" ", "+", $requestParams['fields']['FILES'][0][1]);
            }


            return Bitrix::request($method, $requestParams);
        }
        return null;
    }
}
