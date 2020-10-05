<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;


class Client extends Model
{

    private static Client $client;

    public static function getClient(): Client
    {
        $clientID = self::getClientId();
        if (empty(self::$client)) {
            /** @var Client $client */
            $client = self::query()->findOrFail($clientID);
            self::$client = $client;
        }
        return self::$client;
    }

    public static function getWebHook(): string
    {
        return self::$client->portal . self::$client->webhook;
    }

    public function getAccessToken(): string
    {
        if (time() > $this->expires_in) {

            $url = 'https://oauth.bitrix.info/oauth/token/?grant_type=refresh_token' .
                '&client_id=' . $this->client_id .
                '&client_secret=' . $this->client_secret .
                '&refresh_token=' . $this->refresh_token;

            $response = Http::get($url);

            if ($response->failed()) {
                abort(404);
            }

            $this->refresh_token = $response['refresh_token'];
            $this->access_token = $response['access_token'];
            $this->expires_in = time() + $response['expires_in'];
            $this->save();

            $accessToken = $response['access_token'];

        } else {
            $accessToken = $this->access_token;
        }

        return '?auth=' . $accessToken;
    }

    public static function getClientId(): int
    {
        return request()['client_id'];
    }
}
