<?php
declare(strict_types = 1);

namespace App\Controllers;

use App\Service\TimeHelper;
use App\Service\Validator\Date;
use App\Service\Validator\Json;

class CurrencyByDate extends AbstractController
{

    public function dispatch(array $params = []): void
    {
        if (!Date::isValid($params['date'])) {
            throw new \InvalidArgumentException('incorrect date format, use ' . TimeHelper::DATE_FORMAT . ' format');
        }

        $date = $this->dbConnection->getConnection()->quote($params['date']);
        $tableName = $this->context['config']['db']['tableName'];
        $rows = $this->dbConnection->queryFetchAll(
            'SELECT * FROM ' . $tableName . ' WHERE `created_at` = ' . $date . ' ORDER BY `created_at` DESC'
        );

        $output = [];
        foreach ($rows as $row) {
            if (Json::isValid($row['data'])) {
                $output = array_merge($output, json_decode($row['data']));
            } else {
                $this->logger->debug('Error during json validation of record with ID ' . $row['id']);
            }
        }
        print_r(json_encode($output));
    }
}