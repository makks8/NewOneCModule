<?php

namespace App\Models\CRM;

interface EntityBehavior
{
    public function add(CRM $entity);

    //public function getCRMParams(CRM $entity);

    public function getOneCParams(CRM $entity): array;
}
