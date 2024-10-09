<?php
declare(strict_types = 1);

namespace App\Contract;

interface ValidatorInterface
{
    public static function isValid(string $data): bool;
}