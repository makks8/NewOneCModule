<?php

namespace App\Models\CRM;

interface EntityBehavior
{
    public function add(CRM $entity);

    public function getParams(CRM $entity);

    public function getEntityData($id);
}
