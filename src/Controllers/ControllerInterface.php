<?php
declare(strict_types = 1);

namespace App\Controllers;

interface ControllerInterface
{
    public function dispatch(array $params = []): void;
}