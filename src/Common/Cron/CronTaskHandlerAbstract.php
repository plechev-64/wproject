<?php

namespace Common\Cron;

use Common\Entity\CronTask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class CronTaskHandlerAbstract implements CronTaskHandler
{
    public function getPeriodSec(): int
    {
        return 0;
    }
    public function isSupportCondition(): bool
    {
        return true;
    }
}
