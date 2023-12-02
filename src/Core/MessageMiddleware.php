<?php

namespace Core;

use Core\MainConfig;
use Core\Container\Container;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpTransport;

class MessageMiddleware
{
    private Container $container;
    private array $handlersLocatorArray = [];
    private array $sendersLocatorArray = [];

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function initMiddleware(): void
    {

        foreach(MainConfig::AMPQ_MESSAGES as $messages) {
            foreach($messages as $message => $handlers) {
                foreach($handlers as $handlerName) {
                    $this->handlersLocatorArray[$message][] = $this->container->get($handlerName);
                }
                $this->sendersLocatorArray[$message] = [AmqpTransport::class];
            }
        }

    }

    /**
     * @return array
     */
    public function getHandlersLocatorArray(): array
    {
        return $this->handlersLocatorArray;
    }

    /**
     * @return array
     */
    public function getSendersLocatorArray(): array
    {
        return $this->sendersLocatorArray;
    }

}
