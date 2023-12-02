<?php

namespace Core\Controller;

use Common\Cron\CronCheckMessage;
use Common\Cron\CronService;
use Core\Attributes\Param;
use Core\Attributes\Route;
use Core\Rest\ControllerAbstract;
use Core\Rest\Response;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use YoastSEO_Vendor\Psr\Container\ContainerInterface;

#[Route(path: '/cron')]
class CronController extends ControllerAbstract
{
    /**
     * @description запустить актульные задачи очереди
     */
    #[Route(path: '/handle-queue', method: 'POST')]
    public function sendMessage(
        MessageBusInterface $messageBus,
    ): Response {

        $messageBus->dispatch(
            new CronCheckMessage()
        );

        return $this->response([]);
    }

    /**
     * @description запуск определенной задачи
     */
    #[Route(path: '/handle-task', method: 'POST')]
    #[Param(name: 'force', type: 'bool')]
    #[Param(name: 'async', type: 'bool')]
    #[Param(name: 'taskName', type: 'string')]
    public function handleTask(
        string $taskName,
        bool $force,
        bool $async,
        MessageBusInterface $messageBus,
        CronService $cronService,
        EntityManagerInterface $entityManager
    ): Response {

        $handler = $cronService->getHandlerByCode($taskName);

        if (!$handler) {
            return $this->error('Не найден обработчик для задачи', 404);
        }

        if ($async) {
            $messageBus->dispatch(
                ( new CronCheckMessage() )
                    ->setTaskHandler($handler::class)
                    ->setForce($force)
            );
        } else {
            $cronService->handleTask($handler);
            $entityManager->flush();
        }

        return $this->response([]);
    }

}
