<?php
declare(strict_types = 1);

namespace App\Service\Validator;

use App\Service\TimeHelper;
use App\Contract\ValidatorInterface;

class Date implements ValidatorInterface
{
    public static function isValid(string $date): bool
    {
        $d = TimeHelper::createFromString($date);
        return $d && $d->format(TimeHelper::DATE_FORMAT) === $date;
    }
}