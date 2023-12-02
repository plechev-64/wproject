<?php

namespace Core\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

class StringType extends TextType
{
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }
}
