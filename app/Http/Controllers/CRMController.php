<?php

namespace App\Http\Controllers;

use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\Entities\Company;
use App\Models\CRM\Entities\Deal;
use App\Models\CRM\Entities\Product;
use App\Models\OneC;


class CRMController extends Controller
{

    public function addCompany()
    {
        Company::sendToCrm();
    }

    public function addProduct()
    {
        Product::sendToCrm();
    }

    public function addDeal()
    {
        Deal::sendToCrm();
    }

    public function getID()
    {
        $data = OneC::getData();
        $id = Crm::getID($data['GUID']);
        return response($id, 200)
            ->header('Content-Type', 'application/json');
    }

    public function sync()
    {
        Deal::startSync();
    }

    public function test()
    {
        Bitrix::request('crm.deal.get', ['id' => 515]);
    }


}
