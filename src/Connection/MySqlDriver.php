<?php
declare(strict_types = 1);

namespace App\Connection;

use PDO;
use PDOException;
use PDOStatement;
use Psr\Log\LoggerInterface;
use App\Contract\DbDriverInterface;

class MySqlDriver implements DbDriverInterface
{
    private ?PDO $dbh;
    private string $tableName;

    public function __construct(
        array $config,
        private readonly LoggerInterface $logger
    ) {

        $this->tableName = $config['tableName'] ?? 'rates';

        $user = $config['user'] ?? 'root';
        $pass = $config['password'] ?? '';
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $dbName = $config['dbname'] ?? 'currency'; //Должна быть создана БД для подключения
        $dsn = "mysql:host={$host};port={$port};dbname={$dbName}";

        $this->dbh = new PDO($dsn, $user, $pass);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getConnection(): PDO
    {
        return $this->dbh;
    }

    /**
     * Execute sql string with passed parameters
     *
     * @param string $query
     * @param array $options
     * @return PDOStatement
     */
    public function query(string $query, array $options = []): PDOStatement
    {
        $this->dbh->beginTransaction();
        try {
            $result = $this->prepare($query);
            $result->execute($options);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
            $this->dbh->rollBack();
            throw $e;
        }

        if ($this->dbh->inTransaction()) {
            $this->dbh->commit();
        }
        return $result;
    }

    public function queryFetchAll(string $query, array $options = []): array
    {
        return $this->query($query, $options)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function prepare(string $query): PDOStatement
    {
        return $this->dbh->prepare($query, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    }

    /**
     * Execute the sql string
     *
     * @param string $query
     * @return int|false
     */
    public function exec(string $query): int|false
    {
        return $this->dbh->exec($query);
    }

    /**
     * Inserts data into DB
     *
     * @param string $json
     * @return bool
     */
    public function insert(string $json): bool
    {
        $sql = 'INSERT INTO `' . $this->tableName . '` (`data`) VALUES (:json)';

        try {
            $this->query($sql, [':json' => $json]);
        } catch (\PDOException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        return true;
    }

    public function insertWithDate(string $json, string $date): bool
    {
        $sql = 'INSERT INTO `' . $this->tableName . '` (`data`, `created_at`) VALUES (:json, :date)';

        try {
            $this->query($sql, [':json' => $json, ':date' => $date]);
        } catch (\PDOException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        return true;
    }

}