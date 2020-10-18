<?php


namespace App\Http\Controllers;


use App\Models\Client;

class ClientController
{

    public function renderClientList()
    {
        $clients = Client::getClientsList();
        $test = 1;
        return view('client.list', ['clients' => $clients]);

    }
}
