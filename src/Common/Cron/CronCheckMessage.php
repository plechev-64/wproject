<?php

namespace Common\Cron;

class CronCheckMessage
{
    public ?string $taskHandler = null;
    public bool $force = false;

    /**
     * @return bool
     */
    public function isForce(): bool
    {
        return $this->force;
    }

    /**
     * @param bool $force
     *
     * @return CronCheckMessage
     */
    public function setForce(bool $force): CronCheckMessage
    {
        $this->force = $force;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getTaskHandler(): ?string
    {
        return $this->taskHandler;
    }

    /**
     * @param string|null $taskHandler
     *
     * @return CronCheckMessage
     */
    public function setTaskHandler(?string $taskHandler): CronCheckMessage
    {
        $this->taskHandler = $taskHandler;

        return $this;
    }

}
