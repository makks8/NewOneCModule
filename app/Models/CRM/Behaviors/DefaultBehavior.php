<?php

namespace App\Models\CRM\Behaviors;

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

    public function getOneCParams($entity):array
    {
        $method = 'crm.' . $entity->name . '.get';
        $params = ['id' => $entity->bitrix_id];

        return Bitrix::request($method, $params);
    }
}
