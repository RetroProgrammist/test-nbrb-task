<?php
declare(strict_types = 1);

namespace App\Contract;

interface RatesProviderInterface
{
    public function getTodayRates(): string;
    public function getRatesByDate(\DateTimeImmutable $date): string;
}