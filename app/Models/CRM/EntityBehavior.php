<?php

namespace App\Models\CRM;

interface EntityBehavior
{
    public function sendToCrm(Crm $entity);

    public function getOneCParams(Crm $entity): array;
}
