<?php

namespace Common\Cron\TaskHandler;

use Common\Cron\CronTaskHandlerAbstract;
use Common\Entity\BlogPost;
use Common\Entity\CronTask;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @description параметры для блога
 */
class TestTaskHandler extends CronTaskHandlerAbstract
{


    public static function getCode(): string
    {
        return 'test';
    }

    public function getPeriodSec(): int
    {
        return 600;
    }

    public function handle(CronTask $task): bool
    {
        //to do something
        return true;
    }

}
