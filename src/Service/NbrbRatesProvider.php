<?php
declare(strict_types = 1);

namespace App\Service;

use App\Contract\RatesProviderInterface;
use GuzzleHttp\Client;

class NbrbRatesProvider implements RatesProviderInterface
{

    const URL = 'https://api.nbrb.by/exrates/rates';

    protected Client $client;
    protected Logger $logger;

    public function __construct()
    {
        $this->client = new Client();
        $this->logger = new Logger();
    }

    public function getTodayRates(): string
    {
        return $this->getRatesByDate(TimeHelper::today());
    }

    public function getRatesByDate(\DateTimeImmutable $date): string
    {
        try {
            $response = $this->client->get(
                self::URL,
                [
                    'query' => [
                        'periodicity' => 0,
                        'ondate'      => $date->format(TimeHelper::DATE_FORMAT)
                    ],
                ]
            );
        } catch (\GuzzleHttp\Exception\GuzzleException $guzzleException) {
            $this->logger->debug($guzzleException->getMessage());
            return '';
        }

        if ($response->getStatusCode() !== 200) {
            return json_encode($response->getBody()->getContents());
        }

        return $response->getBody()->getContents();
    }
}