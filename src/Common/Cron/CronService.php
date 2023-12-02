<?php

namespace Common\Cron;

use Common\Entity\CronTask;
use Common\Repository\CronTaskRepository;
use Core\LocalDateTime;
use Core\Service\ServiceAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @method static CronService init()
 */
class CronService extends ServiceAbstract
{
    private CronTaskRepository $cronTaskRepository;
    private array $handlers = [];
    private ?ArrayCollection $tasks = null;
    private ConsoleOutput $consoleOutput;
    private EntityManagerInterface $entityManager;

    public function __construct(
        CronTaskRepository $cronTaskRepository,
        ConsoleOutput $consoleOutput,
        EntityManagerInterface $entityManager,
        array $handlers
    ) {
        $this->consoleOutput      = $consoleOutput;
        $this->cronTaskRepository = $cronTaskRepository;
        $this->entityManager = $entityManager;
        $this->handlers = $handlers;
    }

    private function getTasks(): ArrayCollection
    {
        if ($this->tasks === null) {
            $this->tasks = new ArrayCollection($this->cronTaskRepository->findAll());
        }

        return $this->tasks;
    }

    public function getHandlerByCode(string $code): ?CronTaskHandler
    {
        /** @var CronTaskHandler $handler */
        foreach($this->handlers as $handler) {
            if($handler::getCode() === $code) {
                return $handler;
            }
        }
        return null;
    }

    public function getActualHandlers(): array
    {

        $handlers = [];
        /** @var CronTaskHandler $handler */
        foreach ($this->handlers as $handler) {
            if ($this->isNeedHandle($handler)) {
                $handlers[] = $handler;
            }
        }

        return $handlers;
    }

    public function isNeedHandle(CronTaskHandler $handler): bool
    {

        /** @var  CronTask|false $task */
        $task = $this->getTasks()->filter(function (CronTask $task) use ($handler) {
            return $task->getHandlerName() === $handler::class;
        })->first();

        if (
            !$task ||
            ($handler->getPeriodSec() && $handler->getPeriodSec() < ( new LocalDateTime() )->getTimestamp() - $task->getStartDate()?->getTimestamp())
        ) {
            return $handler->isSupportCondition();
        }

        return false;

    }

    public function handleTask(CronTaskHandler $taskHandler): bool
    {

        /** @var  CronTask|false $task */
        $task = $this->getTasks()->filter(function (CronTask $task) use ($taskHandler) {
            return $task->getHandlerName() === $taskHandler::class;
        })->first();

        if (!$task) {
            $task = $this->createCronTask($taskHandler::class);
        }

        $now = new LocalDateTime();

        if (!$task->isActive()) {
            $task
                ->setStartDate($now)
                ->setEndDate(null)
                ->setIsActive(true)
                ->setHandled(0);
            $this->entityManager->flush();
        }

        try {
            $this->consoleOutput->writeln(sprintf('[%s] Выполняется задание обработчиком %s', $now->format('Y-m-d H:i:s'), $taskHandler::class));
            if ($taskHandler->handle($task)) {
                $this->consoleOutput->writeln(sprintf('[%s] Обрабочик %s завершил задание', $now->format('Y-m-d H:i:s'), $taskHandler::class));
                $task->setEndDate(new LocalDateTime())
                     ->setErrorMessage(null)
                     ->setIsActive(false);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->consoleOutput->writeln(sprintf('[%s] Завершено с ошибкой %s', $now->format('Y-m-d H:i:s'), $e->getMessage()));
            $task->setEndDate(new LocalDateTime())
                 ->setIsActive(false)
                 ->setErrorMessage($e->getMessage());
            return true;
        }

        return true;

    }

    private function createCronTask(string $handlerName): CronTask
    {
        $task = ( new CronTask() )
            ->setHandlerName($handlerName);
        $this->getTasks()->add($task);
        $this->entityManager->persist($task);
        return $task;
    }

}
