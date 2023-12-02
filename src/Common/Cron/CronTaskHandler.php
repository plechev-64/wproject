<?php

namespace Common\Cron;

use Common\Entity\CronTask;

interface CronTaskHandler
{
    public function handle(CronTask $task): bool;
    public static function getCode(): string;
    public function getPeriodSec(): int;
    public function isSupportCondition(): bool;
}
