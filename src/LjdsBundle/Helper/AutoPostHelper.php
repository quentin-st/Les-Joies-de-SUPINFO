<?php

namespace LjdsBundle\Helper;

class AutoPostHelper
{
    /**
     * Get publication times for a week part
     * @param $wp integer Week part
     * @return array
     */
    public static function getPublicationTimes($wp)
    {
        switch ($wp) {
            case WeekPart::WEEK_DAYS:
                return ['11:00', '18:00'];
            case WeekPart::WEEK_END:
                return ['16:00'];
            default:
                return [];
        }
    }
}

abstract class WeekPart
{
    const WEEK_DAYS = 0;
    const WEEK_END = 1;
}
