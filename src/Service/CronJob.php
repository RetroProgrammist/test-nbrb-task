<?php
declare(strict_types = 1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use App\Service\Validator\Json;
use App\Contract\DbDriverInterface;
use App\Contract\RatesProviderInterface;

class CronJob
{

    /**
     * CronJob constructor.
     *
     * @param LoggerInterface $logger
     * @param DbDriverInterface $dbConnection
     * @param RatesProviderInterface $ratesProvider
     */
    public function __construct(
        private LoggerInterface $logger,
        private DbDriverInterface $dbConnection,
        private RatesProviderInterface $ratesProvider
    ) {
    }

    public function run(): void
    {
        $json = $this->ratesProvider->getTodayRates();

        if ($this->isValidData($json)) {
            $this->dbConnection->insert($json);
            $this->logger->info('CRON: Updated rates, date - ' . TimeHelper::today()->format('Y-m-d H:i:s'));
        } else {
            $this->logger->info('CRON: Invalid data, see debug.log, date - ' . TimeHelper::today()->format('Y-m-d H:i:s'));
        }
    }

    /**
     * Функция сервисная, для наполнения данными таблицы, в целях теста, наполняет за последние 5 дней
     *
     * @return void
     */
    public function collectDataForTest(): void
    {
        $days = 5;

        $outputData = [];
        while ($days--) {
            $json = $this->ratesProvider->getRatesByDate(TimeHelper::daysAgo($days));

            if ($this->isValidData($json)) {
                $outputData[] =
                    ['date' => TimeHelper::daysAgo($days)->format(TimeHelper::DATE_FORMAT), 'json' => $json];

                $this->dbConnection->insertWithDate($json, TimeHelper::daysAgo($days)->format(TimeHelper::DATE_FORMAT));
            }
        }
        print_r(json_encode($outputData));
    }

    private function isValidData(string $json): bool
    {
        return !empty($json) && Json::isValid($json);
    }
}