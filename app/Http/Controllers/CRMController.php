<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Bitrix;
use App\Models\CRM\Entity\Company;
use Illuminate\Http\Request;

class CRMController extends Controller
{

    public function addCompany(Request $request)
    {
        if ($request->exists('GUID')) {
            Company::getByGUID($request['GUID'])->addEntity();
        }
    }

    public function addProduct()
    {
        $data = json_decode($_POST['data'], true);
        $guid = $data['GUID'];
        $company = Product::getByGUID($guid, $data);
        $company->addEntity();
    }

    public function getID()
    {
        $data = json_decode($_POST['data'], true);
        $guid = $data['GUID'];
        echo CRM::getBitrixID($guid);
    }

    public function sync()
    {
        $client = Client::getClient();
        $client->getAccessToken();
    }

    public function test()
    {
        Bitrix::request('crm.deal.get', ['id' => 49]);
    }


}
