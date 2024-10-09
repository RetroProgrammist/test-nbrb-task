<?php
declare(strict_types = 1);

namespace App;

use Exception;
use Dotenv\Dotenv;
use App\Service\Logger;
use App\Service\CronJob;
use Psr\Log\LoggerInterface;
use App\Service\NbrbRatesProvider;
use App\Contract\DbDriverInterface;

/**
 * APP Core
 */
class Application
{
    private static ?Application $instance = null;

    private ?DbDriverInterface $dbConnection;
    private Router $router;
    private LoggerInterface $logger;

    private array $context = [];

    private function __construct()
    {
        $env = Dotenv::createArrayBacked(ROOT_FOLDER)->load();

        $tableName = $env['TABLE_NAME'] ?? 'rates';

        $config = [
            'db' => [
                'connection' => $env['DB_CONNECTION'] ?? 'mysql',
                'host'       => $env['DB_HOST'],
                'port'       => $env['DB_PORT'],
                'dbname'     => $env['MYSQL_DATABASE'],
                'user'       => $env['MYSQL_USER'],
                'password'   => $env['MYSQL_PASSWORD'],
                'tableName'  => $tableName
            ]
        ];

        $this->logger = new Logger();
        $this->router = new Router();

        /**
         * A good example for using the factory method
         */
        if ($config['db']['connection'] === 'mysql') { //у нас всегда true
            $this->dbConnection = new \App\Connection\MySqlDriver($config['db'], $this->getLogger());
        }

        $this->context = [
            'config' => $config,
            'router' => $this->getRouter(),
            'db'     => $this->getDbConnection(),
            'logger' => $this->getLogger()
        ];

        $this->createDefaultTables($tableName);
    }

    /**
     * Init application
     *
     * @return self
     */
    public static function instance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Run application, entry point
     *
     * @param $argv
     * @return void
     */
    public function run($argv): void
    {
        if (isset($argv[1]) && $argv[1] === 'cron') { ///usr/bin/php index.php cron
            $this->startCron();
        } else {
            $this->startRouting();
        }
    }

    protected function startRouting(): void
    {

        $router = $this->getRouter();

        $router->addRoute(
            'GET',
            '/currencies/all',
            new \App\Controllers\CurrencyAll($this->context)
        );

        $router->addRoute(
            'GET',
            '/currencies/:date',
            new \App\Controllers\CurrencyByDate($this->context)
        );

        $router->addRoute(
            'GET',
            '/load/',
            new \App\Controllers\Loader($this->context)
        );

        $router->matchRoute();
    }

    protected function startCron(): void
    {
        $cronJob = new CronJob($this->getLogger(), $this->getDBConnection(), new NbrbRatesProvider());
        $cronJob->run();
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getDBConnection(): DbDriverInterface
    {
        return $this->dbConnection;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    private function createDefaultTables(string $tableName): void
    {
        $sql = '
        CREATE TABLE IF NOT EXISTS ' . $tableName . ' (
            `id` INT NOT NULL AUTO_INCREMENT,
            `data` JSON NOT NULL,
            `created_at` DATE DEFAULT CURRENT_TIMESTAMP ,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

        $this->getDBConnection()->exec($sql);
    }

    /**
     * @throws Exception
     */
    public function __clone(): void
    {
        throw new Exception('Cloning is not allowed.');
    }

    /**
     * @throws Exception
     */
    public function __wakeup(): void
    {
        throw new Exception('Wakeup is not allowed.');
    }

    /**
     * @throws Exception
     */
    public function __sleep(): array
    {
        throw new Exception('Sleep is not allowed.');
    }

    public function __destruct()
    {
        if ($this->getDBConnection()->getConnection()->inTransaction()) {
            $this->getDBConnection()->getConnection()->rollBack();
        }
    }
}