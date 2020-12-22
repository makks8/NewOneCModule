<?php


namespace App\Models\CRM\Behaviors;



use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\EntityBehavior;

class BankRequisiteBehavior implements EntityBehavior
{

    public function sendToCrm(Crm $bankRequisite)
    {
        $bankRequisiteParams = $bankRequisite->getParams();
        $bankRequisiteSendMethod = $bankRequisite->getMethod();
        $bankRequisiteID = Bitrix::request($bankRequisiteSendMethod, $bankRequisiteParams);
        if (!$bankRequisite->exists) {
            $bankRequisite->crm_id = $bankRequisiteID;
        }
        $bankRequisite->save();
    }

    public function getOneCParams(Crm $bankRequisite): array
    {
        // TODO: Implement getOneCParams() method.
    }
}
