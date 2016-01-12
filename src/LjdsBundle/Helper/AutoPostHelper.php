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
                return ['10:45', '15:45'];
            case WeekPart::WEEK_END:
                return ['14:00'];
            default:
                return [];
        }
    }
}
