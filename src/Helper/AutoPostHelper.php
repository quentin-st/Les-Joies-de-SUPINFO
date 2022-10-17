<?php

namespace App\Helper;

abstract class AutoPostHelper
{
    /**
     * Get publication times for a week part
     */
    public static function getPublicationTimes(int $wp): array
    {
        return match ($wp) {
            WeekPart::WEEK_DAYS => ['15:45'],
            WeekPart::WEEK_END => ['14:00'],
            default => [],
        };
    }
}
