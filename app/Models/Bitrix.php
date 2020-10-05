<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;

class Bitrix
{
    public static function request(string $method, array $data = null)
    {
        $url = self::createUrl($method);
        $response = Http::post($url, $data);
        if ($response->successful()) return $response['result'];
        return null;
    }

    private static function createUrl(string $method)
    {
        $client = Client::getClient();
        return $client->portal . $method . $client->getAccessToken();
    }
}
