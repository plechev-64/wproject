<?php

namespace Common\Cron;

use Common\Entity\CronTask;
use Core\LocalDateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class CronTaskStepHandler extends CronTaskHandlerAbstract
{
    abstract public function handleStep(ArrayCollection $data): void;
    abstract public function getMainQuery(): QueryBuilder;
    abstract public function getLimit(): int;

    public function handle(CronTask $task): bool
    {

        $result = $this->getMainQuery()
                  ->setMaxResults($this->getLimit())
                  ->setFirstResult($task->getHandled())
                  ->getQuery()
                  ->getResult();

        $data = new ArrayCollection($result);

        try {
            if ($data->count()) {

                $this->handleStep($data);
                $task->setHandled($task->getHandled() + $data->count());
                return false;

            } else {
                $task->setEndDate(new LocalDateTime())
                     ->setIsActive(false);
            }

        } catch (\Exception $e) {
            $task->setEndDate(new LocalDateTime())
                 ->setIsActive(false)
                 ->setErrorMessage($e->getMessage());
            return true;
        }

        return true;
    }
}
