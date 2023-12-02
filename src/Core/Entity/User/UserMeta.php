<?php

namespace Core\Entity\User;

use Core\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'wp_usermeta')]
class UserMeta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "umeta_id")]
    private int $metaId;

    #[ORM\Column(name: "user_id")]
    private int $userId;

    #[ORM\Column(name: "meta_key")]
    private string $key;

    #[ORM\Column(name: "meta_value")]
    private ?string $value = null;

    /**
     * @return int
     */
    public function getMetaId(): int
    {
        return $this->metaId;
    }

    /**
     * @param int $metaId
     *
     * @return UserMeta
     */
    public function setMetaId(int $metaId): UserMeta
    {
        $this->metaId = $metaId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return UserMeta
     */
    public function setUserId(int $userId): UserMeta
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return UserMeta
     */
    public function setKey(string $key): UserMeta
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     *
     * @return UserMeta
     */
    public function setValue(?string $value): UserMeta
    {
        $this->value = $value;

        return $this;
    }

}
