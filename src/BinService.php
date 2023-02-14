<?php

namespace Vlad\JobTest;

use GuzzleHttp\Client;

class BinService
{
    private Client $client;
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://lookup.binlist.net',
            'timeout'  => 10.0,
        ]);
    }

    /**
     * @param string[] $bins
     * @return string[]
     */
    public function getCountriesByBins(array $bins): array
    {
        $countryCodes = [];

        //TODO;IMPROVEMENT if api allows batch request better to use batch request
        foreach ($bins as $bin){
            $response = $this->client->get($bin);
            if($response->getStatusCode() !== 200){
                throw new \Exception("Can't get country code. Status code: " . $response->getStatusCode());
            }
            $bodyResponse = json_decode($response->getBody()->getContents(), true);
            if($bodyResponse === null){
                throw new \UnexpectedValueException("Can't parse json. Response: " . $bodyResponse);
            }
            $countryCodes[$bin] = $bodyResponse["country"]["alpha2"];
        }

        return $countryCodes;
    }
}
