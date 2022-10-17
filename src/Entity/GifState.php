<?php

namespace App\Entity;

abstract class GifState
{
    public const SUBMITTED = 0;
    public const ACCEPTED = 1;
    public const REFUSED = 2;
    public const PUBLISHED = 3;
    public const REPORTED = 4; // That's not really a type, but hey

    public static function fromName(string $name): int
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

    public static function getAll(): array
    {
        return ['submitted', 'accepted', 'refused', 'published', 'reported'];
    }

    public static function getLabels(): array
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
