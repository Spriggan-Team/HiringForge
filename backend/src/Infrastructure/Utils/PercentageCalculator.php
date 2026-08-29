<?php


namespace App\Infrastructure\Utils;


class PercentageCalculator
{
    public function calculatePercentageIncrease(int $currentPeriodCount, int $previousPeriodCount): float
    {
        if ($previousPeriodCount === 0) {
            return $currentPeriodCount > 0 ? 100.0 : 0.0;
        }

        $increase = (($currentPeriodCount - $previousPeriodCount) / $previousPeriodCount) * 100;
        
        return round($increase, 2);
    }
}