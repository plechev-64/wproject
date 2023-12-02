<?php

namespace Common\Cron;

use Common\Entity\CronTask;
use Core\Container\Container;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CronCheckMessageHandler
{
    private CronService $cronService;
    private EntityManagerInterface $entityManager;
    private Container $container;
    private MessageBusInterface $messageBus;

    public function __construct(
        CronService $cronService,
        EntityManagerInterface $entityManager,
        Container $container,
        MessageBusInterface $messageBus
    ) {
        $this->cronService   = $cronService;
        $this->entityManager = $entityManager;
        $this->container     = $container;
        $this->messageBus    = $messageBus;
    }

    public function __invoke(CronCheckMessage $message): void
    {

        $taskHandlerName = $message->getTaskHandler();
        $isForce         = $message->isForce();

        if ($taskHandlerName !== null) {

            /** @var CronTaskHandler $handler */
            $handler = $this->container->get($taskHandlerName);

            if ($isForce || $this->cronService->isNeedHandle($handler)) {
                if(!$this->cronService->handleTask($handler)) {
                    $this->messageBus->dispatch($message->setForce(true));
                }
            }

        } else {

            if ($handlers = $this->cronService->getActualHandlers()) {
                /** @var CronTaskHandler $handler */
                foreach ($handlers as $handler) {
                    $this->messageBus->dispatch(
                        ( new CronCheckMessage() )
                            ->setTaskHandler($handler::class)
                    );
                }
            }
        }

        $this->entityManager->flush();
    }

}
