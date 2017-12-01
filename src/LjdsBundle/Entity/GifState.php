<?php

namespace LjdsBundle\Entity;

abstract class GifState
{
    const SUBMITTED = 0;
    const ACCEPTED = 1;
    const REFUSED = 2;
    const PUBLISHED = 3;
    const REPORTED = 4; // That's not really a type, but hey

    public static function fromName($name)
    {
        switch ($name) {
            case 'submitted':    return self::SUBMITTED;
            case 'accepted':    return self::ACCEPTED;
            case 'refused':        return self::REFUSED;
            case 'published':    return self::PUBLISHED;
            case 'reported':    return self::REPORTED;
            default:            return -1;
        }
    }

    public static function getAll()
    {
        return ['submitted', 'accepted', 'refused', 'published', 'reported'];
    }

    public static function getLabels()
    {
        return [
            self::SUBMITTED => 'En attente',
            self::ACCEPTED => 'Acceptés',
            self::REFUSED => 'Refusés',
            self::PUBLISHED => 'Publiés',
            self::REPORTED => 'Signalés'
        ];
    }

    public static function getLabel($name)
    {
        return self::getLabels()[self::fromName($name)];
    }
}
