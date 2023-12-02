<?php

namespace Common\Entity;

use Core\Entity\Post\Post;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: 'wp_cron_task')]
class CronTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "handler_name", type: "string")]
    private string $handlerName;

    #[ORM\Column(name: "handled", type: "integer")]
    private ?int $handled = null;

    #[ORM\Column(name: "is_active", type: "boolean")]
    private bool $isActive = false;

    #[ORM\Column(name: "start_date", type: "datetime")]
    private ?DateTime $startDate = null;

    #[ORM\Column(name: "end_date", type: "datetime")]
    private ?DateTime $endDate = null;

    #[ORM\Column(name: "error_message", type: "string")]
    private ?string $errorMessage = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     *
     * @return CronTask
     */
    public function setId(?int $id): CronTask
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getHandlerName(): string
    {
        return $this->handlerName;
    }

    /**
     * @param string $handlerName
     *
     * @return CronTask
     */
    public function setHandlerName(string $handlerName): CronTask
    {
        $this->handlerName = $handlerName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return CronTask
     */
    public function setIsActive(bool $isActive): CronTask
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime|null $startDate
     *
     * @return CronTask
     */
    public function setStartDate(?DateTime $startDate): CronTask
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime|null $endDate
     *
     * @return CronTask
     */
    public function setEndDate(?DateTime $endDate): CronTask
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @param string|null $errorMessage
     *
     * @return CronTask
     */
    public function setErrorMessage(?string $errorMessage): CronTask
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHandled(): ?int
    {
        return $this->handled;
    }

    /**
     * @param int|null $handled
     *
     * @return CronTask
     */
    public function setHandled(?int $handled): CronTask
    {
        $this->handled = $handled;

        return $this;
    }

}
