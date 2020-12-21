<?php


namespace App\Models\CRM\Entities;


use App\Models\Bitrix;

class User
{
    public static function getByEmail($email)
    {
        $method = 'user.get';
        $filterData = [
            'EMAIL' => $email
        ];
        $response = Bitrix::request($method, $filterData);
        return !empty($response) ? $response[0]['ID'] : '7';
    }
}
