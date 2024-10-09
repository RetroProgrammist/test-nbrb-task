<?php
declare(strict_types = 1);

namespace App\Service;

use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{

    /**
     * @inheritDoc
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        file_put_contents(getFilePath('/logs/' . $level . '.log'), $message . PHP_EOL . implode($context), FILE_APPEND);
    }
}