<?php

namespace Core\Entity\Taxonomy;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'wp_terms')]
class Term
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "term_id", type: 'integer')]
    private int $id;

    #[ORM\Column(name: "name", type: 'string')]
    private string $name;

    #[ORM\Column(name: "slug", type: 'string')]
    private string $slug;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Term
     */
    public function setId(int $id): Term
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Term
     */
    public function setName(string $name): Term
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return Term
     */
    public function setSlug(string $slug): Term
    {
        $this->slug = $slug;

        return $this;
    }

}
