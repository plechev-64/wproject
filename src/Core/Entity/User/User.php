<?php

namespace Common\Entity\User;

use Core\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'wp_users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "ID", type: 'integer')]
    private int $id;

    #[ORM\Column(name: "user_login", type: 'string')]
    private string $userLogin;

    #[ORM\Column(name: "user_email", type: 'string')]
    private string $userEmail;

    #[ORM\Column(name: "display_name", type: 'string')]
    private string $displayName;

    #[ORM\Column(name: "user_nicename", type: 'string')]
    private string $userNicename;

    #[ORM\Column(name: "user_registered", type: 'string')]
    private string $userRegistered;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param   int  $id
     *
     * @return User
     */
    public function setId(int $id): User
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserLogin(): string
    {
        return $this->userLogin;
    }

    /**
     * @param   string  $userLogin
     *
     * @return User
     */
    public function setUserLogin(string $userLogin): User
    {
        $this->userLogin = $userLogin;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    /**
     * @param   string  $userEmail
     *
     * @return User
     */
    public function setUserEmail(string $userEmail): User
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param   string  $displayName
     *
     * @return User
     */
    public function setDisplayName(string $displayName): User
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserNicename(): string
    {
        return $this->userNicename;
    }

    /**
     * @param   string  $userNicename
     *
     * @return User
     */
    public function setUserNicename(string $userNicename): User
    {
        $this->userNicename = $userNicename;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserRegistered(): string
    {
        return $this->userRegistered;
    }

    /**
     * @param   string  $userRegistered
     *
     * @return User
     */
    public function setUserRegistered(string $userRegistered): User
    {
        $this->userRegistered = $userRegistered;

        return $this;
    }

}
