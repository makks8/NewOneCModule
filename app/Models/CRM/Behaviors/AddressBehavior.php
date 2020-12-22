<?php


namespace App\Models\CRM\Behaviors;


use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\EntityBehavior;

class AddressBehavior implements EntityBehavior
{

    public function sendToCrm(Crm $address)
    {
        $addressParams = $address->getParams();
        $addressSendMethod = $address->getMethod();
        $addressParams['ENTITY_TYPE_ID'] = 8;
        $addressID = Bitrix::request($addressSendMethod, $addressParams);
        if (!$address->exists) {
            $address->crm_id = $addressID;
        }
        $address->save();
    }

    public function getOneCParams(Crm $address): array
    {
        // TODO: Implement getOneCParams() method.
    }
}
