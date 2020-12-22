<?php

namespace App\Http\Controllers;

use App\Models\Bitrix;
use App\Models\CRM\Additional\Timeline;
use App\Models\CRM\Crm;
use App\Models\CRM\Entities\Address;
use App\Models\CRM\Entities\BankRequisite;
use App\Models\CRM\Entities\Company;
use App\Models\CRM\Entities\Contact;
use App\Models\CRM\Entities\Deal;
use App\Models\CRM\Entities\Product;
use App\Models\CRM\Entities\Requisite;
use App\Models\OneC;


class CRMController extends Controller
{

    public function addDeal()
    {
        Deal::sendToCrm(OneC::getData());
    }

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

    public function addTimeline()
    {
        Timeline::addTimeline();
    }

    public function addRequisite()
    {
        Requisite::sendToCrm(OneC::getData());
    }

    public function addBankRequisite()
    {
        BankRequisite::sendToCrm(OneC::getData());
    }

    public function addAddress()
    {
        Address::sendToCrm(OneC::getData());
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
