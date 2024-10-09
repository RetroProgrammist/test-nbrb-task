<?php
declare(strict_types = 1);

namespace App\Controllers;

use App\Service\Validator\Json;

class CurrencyAll extends AbstractController
{

    public function dispatch(array $params = []): void
    {
        $tableName = $this->context['config']['db']['tableName'];
        $rows = $this->dbConnection->queryFetchAll('SELECT * FROM ' . $tableName . ' ORDER BY `created_at` DESC');

        $output = [];
        foreach ($rows as $row) {
            if(Json::isValid($row['data'])) {
                $output = array_merge($output, json_decode($row['data']));
            } else {
                $this->logger->debug('Error during json validation of record with ID ' . $row['id']);
            }
        }
        print_r(json_encode($output));
    }
}