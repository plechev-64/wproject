<?php

namespace Core\Doctrine;

use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

class LocalDateTimeType extends DateTimeType
{
    private static DateTimeZone $timeZone;

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        $converted = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::getTimeZone()
        );

        if (!$converted) {

            $converted = \DateTime::createFromFormat(
                $platform->getDateFormatString(),
                $value,
                self::getTimeZone()
            );

            if (!$converted) {
                throw ConversionException::conversionFailedFormat(
                    $value,
                    $this->getName(),
                    $platform->getDateTimeFormatString()
                );
            }
        }

        return $converted;
    }

    private static function getTimeZone(): DateTimeZone
    {
        return self::$timeZone ??= new DateTimeZone('Europe/Moscow');
    }
}
