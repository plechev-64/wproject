<?php

namespace Core;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;

class ORM
{
    private static array $instances = array();
    private EntityManager $entityManager;

    public static function get(): ORM
    {
        $class = get_called_class();
        if (array_key_exists($class, self::$instances) === false) {
            self::$instances[ $class ] = new $class();
        }

        return self::$instances[ $class ];
    }

    public function getManager(): EntityManager
    {
        return $this->entityManager;
    }

    public function getRepository(string $className): ObjectRepository|EntityRepository
    {
        return $this->entityManager->getRepository($className);
    }

    public function init(array $params)
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: array( __DIR__ . "/src" ),
            isDevMode: true,
        );

        // configuring the database connection
        $connection = DriverManager::getConnection($params, $config);

        // obtaining the entity manager
        $this->entityManager = new EntityManager($connection, $config);

    }
}
