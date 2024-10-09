<?php
declare(strict_types = 1);

namespace App\Controllers;

use App\Service\CronJob;
use App\Service\NbrbRatesProvider;

class Loader extends AbstractController
{
    public function dispatch(array $params = []): void
    {
        $cronJob = new CronJob($this->logger, $this->dbConnection, new NbrbRatesProvider());
        $cronJob->collectDataForTest();
    }
}