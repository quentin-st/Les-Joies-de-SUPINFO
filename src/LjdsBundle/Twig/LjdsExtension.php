<?php

namespace LjdsBundle\Twig;

class LjdsExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'relativeTime',
                [$this, 'relativeTime']
            )
        ];
    }

    public function relativeTime(\DateTime $datetime)
    {
        $time = time() - strtotime($datetime->format('Y-m-d h:m:s'));

        if ($time > 0)
            $when = "il y a";
        else if ($time < 0)
            $when = "dans environ";
        else
            return "il y a moins d'une seconde";

        $time = abs($time);

        $times = array( 31104000 =>  'an{s}',       // 12 * 30 * 24 * 60 * 60 secondes
            2592000  =>  'mois',        // 30 * 24 * 60 * 60 secondes
            86400    =>  'jour{s}',     // 24 * 60 * 60 secondes
            3600     =>  'heure{s}',    // 60 * 60 secondes
            60       =>  'minute{s}',   // 60 secondes
            1        =>  'seconde{s}'); // 1 seconde

        foreach ($times as $seconds => $unit) {
            $delta = round($time / $seconds);

            if ($delta >= 1) {
                if ($delta == 1)
                    $unit = str_replace('{s}', '', $unit);
                else
                    $unit = str_replace('{s}', 's', $unit);

                return $when." ".$delta." ".$unit;
            }
        }

        return '';
    }

    public function getName() { return 'ljds_extension'; }
}
