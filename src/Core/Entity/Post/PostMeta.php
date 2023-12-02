<?php

namespace Core\Entity\Post;

use Core\Entity\MetaEntityInterface;
use Core\Entity\MetaInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

#[ORM\Entity]
#[ORM\Table(name: 'wp_postmeta')]
class PostMeta implements MetaInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "meta_id")]
    private int $metaId;

    #[ORM\Column(name: "post_id")]
    private int $postId;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'metaData')]
    #[JoinColumn(name: 'post_id', referencedColumnName: 'ID')]
    private MetaEntityInterface $entity;

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
     * @return PostMeta
     */
    public function setMetaId(int $metaId): PostMeta
    {
        $this->metaId = $metaId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;
    }

    /**
     * @param int $postId
     *
     * @return PostMeta
     */
    public function setPostId(int $postId): PostMeta
    {
        $this->postId = $postId;

        return $this;
    }

    /**
     * @return MetaEntityInterface
     */
    public function getEntity(): MetaEntityInterface
    {
        return $this->entity;
    }

    /**
     * @param MetaEntityInterface $entity
     *
     * @return PostMeta
     */
    public function setEntity(MetaEntityInterface $entity): PostMeta
    {
        $this->entity = $entity;

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
     * @return PostMeta
     */
    public function setKey(string $key): PostMeta
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
     * @param string|int|array|null $value
     *
     * @return PostMeta
     */
    public function setValue(string|int|array|null $value): PostMeta
    {
        $this->value = $value;

        return $this;
    }

}
