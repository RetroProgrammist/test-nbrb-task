<?php
declare(strict_types = 1);

namespace App\Service\Validator;

use App\Contract\ValidatorInterface;

class Json implements ValidatorInterface
{
    public static function isValid(string $jsonSting): bool
    {
        if (!empty($jsonSting)) {
            @json_decode($jsonSting);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }
}