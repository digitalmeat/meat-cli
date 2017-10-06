<?php

namespace Meat\Cli\Helpers;

use GuzzleHttp\Client;

class MeatAPI
{
    /**
     * MeatAPI constructor.
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('api_url', 'https://api.meat.cl/'),
            // You can set any number of default request options.
            'timeout'  => 30.0,
        ]);

    }


    public function login($user, $pass)
    {
        $response = $this->client->post('meat-cli/authorize', [
            'email' => $user,
            'password' => $pass
        ]);

        return $response->getBody()->getContents();
    }
}