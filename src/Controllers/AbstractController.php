<?php
declare(strict_types = 1);

namespace App\Controllers;

use Psr\Log\LoggerInterface;
use App\Contract\DbDriverInterface;

abstract class AbstractController implements ControllerInterface
{
    protected LoggerInterface $logger;
    protected DbDriverInterface $dbConnection;

    public function __construct(
        protected array $context
    ) {
        $this->logger = $this->context['logger'];
        $this->dbConnection = $this->context['db'];
    }

    public function __invoke(mixed ...$params): void
    {
        header('Content-Type: application/json');
        try {
            $this->dispatch($params);
        } catch (\InvalidArgumentException $exception) {
            http_response_code(400);
            printf('{"error":"%s"}', $exception->getMessage());
        } catch (\Exception $exception) {
            http_response_code(500);
            $this->logger->error($exception->getMessage());
        }
    }
}