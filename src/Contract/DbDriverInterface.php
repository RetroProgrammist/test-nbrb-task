<?php
declare(strict_types = 1);

namespace App\Contract;

use PDO;
use PDOStatement;

interface DbDriverInterface
{
    public function getConnection(): PDO;
    public function exec(string $query): int|false;
    public function insert(string $json): bool;
    public function query(string $query, array $options = []): PDOStatement;
    public function queryFetchAll(string $query, array $options = []): array;
}