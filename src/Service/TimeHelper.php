<?php
declare(strict_types = 1);

namespace App\Service;

use DateTimeImmutable;

class TimeHelper
{
    public const DATE_FORMAT = 'Y-m-d';

    public static function today(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public static function tomorrow(): DateTimeImmutable
    {
        return self::today()->modify('+1 day');
    }

    public static function yesterday(): DateTimeImmutable
    {
        return self::today()->modify('-1 day');
    }

    public static function daysAgo(int $days): DateTimeImmutable
    {
        return self::today()->modify("- {$days} days");
    }

    public static function createFromString(string $dateString): DateTimeImmutable|false
    {
        return DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $dateString);
    }
}