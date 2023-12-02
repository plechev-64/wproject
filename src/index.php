<?php

use Core\MainConfig;
use Core\Doctrine\LocalDateTimeType;
use Core\ORM;
use Core\Rest\RestApi;
use Doctrine\DBAL\Types\Type;

require_once 'Core/Module/MetaBox/index.php';

define('APF_URL', trailingslashit(get_template_directory_uri()) . 'src/Core/Module/CustomFields');

Type::overrideType('datetime', LocalDateTimeType::class);

(ORM::get())->init([
    'dbname'   => DB_NAME,
    'user'     => DB_USER,
    'password' => DB_PASSWORD,
    'host'     => DB_HOST,
    'driver'   => 'mysqli',
    'charset'  => DB_CHARSET,
]);

$container = Core\Container\Container::getInstance();

/** @var RestApi $restApi */
$restApi = $container->get(RestApi::class);
$restApi->setControllers(MainConfig::CONTROLLERS);
$restApi->init();