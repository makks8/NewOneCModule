<?php

namespace App\Http\Controllers;

use App\Models\Bitrix;
use App\Models\Client;
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
use App\Models\Util;


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

    public function refreshToken()
    {
        $client = Client::getClient();
        $client->getAccessToken();
    }

    public function saveProductObject()
    {
        $file = file_put_contents("ProductObject.txt", json_encode(OneC::getData()));
        return true;
    }
    public function loadProductObject()
    {
        $number = 0;//38098;
        $productObj = json_decode(file_get_contents("ProductObject.txt"), true);
        $count = count($productObj);
        foreach ($productObj as $key => $value){
            if ($key < $number){
                continue;
            }
            Product::sendToCrm($value);
            Util::drawMessage($key);
        }
        return true;
    }

    public function saveCompanyObject()
    {
        $file = file_put_contents("CompanyObject.txt", json_encode(OneC::getData()));
        return true;
    }
    public function loadCompanyObject()
    {
        $number = 3618;
        $companyObj = json_decode(file_get_contents("CompanyObject.txt"), true);
        $count = count($companyObj);
        foreach ($companyObj as $key => $value){
            if ($key < $number){
                continue;
            }
            Company::sendToCrm($value);
            Util::drawMessage($key);
        }
        return true;
    }
}
