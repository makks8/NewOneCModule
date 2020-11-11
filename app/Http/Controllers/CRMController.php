<?php

namespace App\Http\Controllers;

use App\Models\Bitrix;
use App\Models\CRM\Crm;
use App\Models\CRM\Entities\Company;
use App\Models\CRM\Entities\Contact;
use App\Models\CRM\Entities\Deal;
use App\Models\CRM\Entities\Product;
use App\Models\OneC;


class CRMController extends Controller
{

    public function addCompany()
    {
        Company::sendToCrm(OneC::getData());
    }

    public function addContact()
    {
        Contact::sendToCrm(OneC::getData());
    }

    public function addProduct()
    {
        Product::sendToCrm(OneC::getData());
    }

    public function addDeal()
    {
        Deal::sendToCrm(OneC::getData());
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
