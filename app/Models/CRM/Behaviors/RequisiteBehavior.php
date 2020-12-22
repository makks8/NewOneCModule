<?php

namespace App\Models\CRM\Behaviors;

use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\Entities\Company;
use App\Models\CRM\Entities\Contact;
use App\Models\CRM\Entities\User;
use App\Models\CRM\EntityBehavior;

class RequisiteBehavior implements EntityBehavior
{

    public function sendToCrm(Crm $requisite)
    {
        $requisiteParams = $requisite->getParams();
        $requisiteSendMethod = $requisite->getMethod();
        $requisiteID = Bitrix::request($requisiteSendMethod, $requisiteParams);
        if(isset($requisiteID)){
            if (!$requisite->exists) {
                $requisite->crm_id = $requisiteID;
            }
            $requisite->save();
        }
    }

    public function getOneCParams(Crm $requisite): array
    {
        // TODO: Implement getOneCParams() method.
    }
}
