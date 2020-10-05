<?php

namespace App\Models\CRM\Behavior;

use App\Models\Bitrix;
use App\Models\CRM\EntityBehavior;

class DefaultBehavior implements EntityBehavior
{

    public function add($entity)
    {
        $entityData = $entity->getData();
        $method = $entity->getMethod();

        return Bitrix::request($method, $entityData);
    }


    public function getParams($entity)
    {
        $data = $entity->getData();
        $params = ['FIELDS' => $data];

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
}
