<?php

namespace Core\Service;

use Core\Container\Container;

abstract class ServiceAbstract
{
    public static function init(): ServiceAbstract
    {
        $container  = Container::getInstance();
        return $container->get(get_called_class());
    }

}
