<?php

namespace Vlad\JobTest;

use GuzzleHttp\Client;

class ExchangeRateService
{
    private array $eurRates;
    public function __construct()
    {
        //TODO: apikey have to be in .env.local and outside the git
        $client = new Client([
            'base_uri' => 'https://api.apilayer.com',
            'timeout'  => 10.0,
            'headers' => ['apikey' => 'Y9K2crA0FPyNkGKNQQpUigCmPCKcpoQl']
        ]);

        $response = $client->get('exchangerates_data/latest');
        if($response->getStatusCode() !== 200){
            throw new \Exception("Can't get rates. Status code: " . $response->getStatusCode());
        }
        $bodyResponse = json_decode($response->getBody()->getContents(), true);
        if($bodyResponse === null){
            throw new \UnexpectedValueException("Can't parse json. Response: " . $bodyResponse);
        }

        $this->eurRates = $bodyResponse['rates'];
    }

    public function getRate(string $alpha2CountryCode): float
    {
        return $this->eurRates[$alpha2CountryCode];
    }
}
