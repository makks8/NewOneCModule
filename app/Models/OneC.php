<?php


namespace App\Models;


use Illuminate\Support\Facades\Http;

class OneC
{
    public static function request($subUrl, $data = null, $method = 'POST')
    {
        $client = Client::getClient();
        $url = $client->onec_url . $subUrl;

        return Http::withBasicAuth(
            $client->onec_username,
            $client->onec_password
        )->$method($url, $data);
    }

    public static function getData(): array
    {
        $data = json_decode(request()->post('data'), true);
        return empty($data) ? [] : $data;
    }
}
