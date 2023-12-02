<?php

namespace Core;

use DateTimeZone;

class LocalDateTime extends \DateTime
{
    public const YMDHIS_FORMAT = 'Y-m-d H:i:s';

    public function __construct($datetime = 'now')
    {
        parent::__construct($datetime, new DateTimeZone('Europe/Moscow'));
    }

    public static function createFromFormat($format, $datetime, DateTimeZone $timezone = null): \DateTime|false
    {
        return parent::createFromFormat($format, $datetime, new DateTimeZone('Europe/Moscow'));
    }
}
