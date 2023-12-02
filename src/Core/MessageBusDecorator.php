<?php

namespace Core;

use Core\MainConfig;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class MessageBusDecorator implements MessageBusInterface
{
    private MessageBus $messageBus;

    /**
     * @param MessageBus $messageBus
     */
    public function __construct(MessageBus $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function dispatch(object $message, array $stamps = []): Envelope
    {

        foreach(MainConfig::AMPQ_MESSAGES as $transport => $messages) {
            if(isset($messages[$message::class])) {
                if($transport !== MainConfig::TRANSPORT_DEFAULT) {

                    $allStamps = [
                        new TransportNamesStamp($transport),
                        new BusNameStamp(MessageBusInterface::class)
                    ];

                    if($stamps) {
                        $allStamps = array_merge($allStamps, $stamps);
                    }

                    return $this->messageBus->dispatch($message, $allStamps);
                }
            }
        }

        return $this->messageBus->dispatch($message);
    }
}
