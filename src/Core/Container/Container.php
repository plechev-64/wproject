<?php

namespace Core\Container;

use Core\MainConfig;
use Core\MessageBusDecorator;
use Core\MessageMiddleware;
use Core\ORM;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpReceiver;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\Connection;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Container implements ContainerInterface
{
    private array $servicesFactories;
    private array $services;

    private function __construct()
    {

        $this->servicesFactories = [
            ValidatorInterface::class     => function (ContainerInterface $container) {
                return Validation::createValidatorBuilder()
                                 ->enableAttributeMapping()
                                 ->getValidator();
            },
            ContainerInterface::class     => function (ContainerInterface $container) {
                return $container;
            },
            EntityManagerInterface::class => function (ContainerInterface $container) {
                return ORM::get()->getManager();
            },
            Connection::class             => function (ContainerInterface $container) {
                return Connection::fromDsn(MainConfig::AMPQ_DNS, []);
            },
            MainConfig::WORKER_AMPQ       => function (ContainerInterface $container) {
                return new AmqpReceiver(
                    $container->get(Connection::class)
                );
            },
            MainConfig::TRANSPORT_AMPQ    => function (ContainerInterface $container) {
                return $container->get(AmqpTransport::class);
            },
            MessageBusInterface::class    => function (ContainerInterface $container) {

                /** @var MessageMiddleware $messagesMiddleware */
                $messagesMiddleware = $container->get(MessageMiddleware::class);

                return new MessageBusDecorator(
                    new MessageBus([
                        new SendMessageMiddleware(
                            new SendersLocator([
                                $messagesMiddleware->getSendersLocatorArray()
                            ], $container)
                        ),
                        new HandleMessageMiddleware(
                            new HandlersLocator($messagesMiddleware->getHandlersLocatorArray())
                        )
                    ])
                );

            },
        ];
        $this->services          = [];

    }

    public static function getInstance()
    {
        static $instance;

        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }

    public function get(string $id): mixed
    {

        if (!$this->has($id)) {
            return null;
        }

        if (isset($this->servicesFactories[ $id ])) {
            $serviceFactory = $this->servicesFactories[ $id ];

            return $serviceFactory($this);
        }

        if (isset($this->services[ $id ])) {
            return $this->services[ $id ];
        }

        $this->services[ $id ] = $this->createInstance($id);

        return $this->services[ $id ];
    }

    public function has(string $id): bool
    {
        return isset($this->servicesFactories[ $id ]) || isset($this->services[ $id ]) || class_exists($id);
    }

    public function set(string $id, \Closure $instanceBuilder): void
    {
        $this->servicesFactories[ $id ] = $instanceBuilder;
        unset($this->services[ $id ]);
    }

    /**
     * @throws ReflectionException
     */
    public function createInstance(string $id)
    {

        $reflection = new ReflectionClass($id);

        $constructor = $reflection->getConstructor();

        if (null === $constructor) {
            return new $id();
        }

        /**
         * Если конструктор приватный, значит Singletone
         * и инстанс получаем через статический метод getInstance
         * @todo возможно это лишнее и надо удалить
         */
        if (!$constructor->isPublic()) {
            $id          = [ $id, 'getInstance' ];
            $constructor = $reflection->getMethod('getInstance');
        }

        $parameters = $constructor->getParameters();

        if (!$parameters) {
            return is_array($id) ? call_user_func($id) : new $id();
        }

        $dependencies = [];

        foreach ($parameters as $parameter) {

            if ($parameter->isOptional()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            //todo если тип данных параметра не указан - будет Null и фатал
            $parameterType = $parameter->getType();

            if ($parameterType->getName() === 'array') {

                if (!empty(MainConfig::ITERABLE_CONSTRUCT_PARAMS[ $id ])) {

                    foreach (MainConfig::ITERABLE_CONSTRUCT_PARAMS[ $id ] as $paramName => $classNames) {

                        if ($paramName !== $parameter->getName()) {
                            continue;
                        }

                        $instances = [];
                        foreach ($classNames as $className) {
                            $instances[] = $this->get($className);
                        }

                        $dependencies[] = $instances;

                    }
                }

            } else {
                $dependencies[] = $this->get($parameterType);
            }

        }

        return is_array($id) ? call_user_func_array($id, $dependencies) : new $id(...$dependencies);

    }

}
