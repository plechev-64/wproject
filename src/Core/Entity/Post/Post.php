<?php

namespace Core\Entity\Post;

use Core\Repository\PostsRepository;
use Core\Entity\MetaEntityInterface;
use Core\Entity\MetaTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

#[ORM\Entity(repositoryClass: PostsRepository::class)]
#[ORM\Table(name: 'wp_posts')]
class Post implements MetaEntityInterface
{
    use MetaTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'post_author', type: 'integer')]
    private int $postAuthor;

    #[ORM\Column(name: 'post_status', type: 'string')]
    private string $postStatus;

    #[ORM\Column(name: 'post_type', type: 'string')]
    private string $postType;

    #[ORM\Column(name: 'post_date', type: 'datetime')]
    private ?DateTime $postDate = null;

    #[ORM\Column(name: 'post_modified', type: 'datetime')]
    private ?DateTime $postModified = null;

    #[ORM\Column(name: 'post_title', type: 'string')]
    private string $postTitle;

    #[ORM\Column(name: 'post_content', type: 'string')]
    private string $postContent;

    #[ORM\Column(name: 'post_excerpt', type: 'string')]
    private ?string $postExcerpt = null;

    #[ORM\Column(name: 'post_parent', type: 'integer')]
    private ?int $postParent = null;

    #[ORM\Column(name: 'post_name', type: 'string')]
    private ?string $postName = null;

    #[ORM\Column(name: 'post_mime_type', type: 'string')]
    private ?string $postMimeType = null;

    #[ORM\Column(type: 'string')]
    private ?string $guid = null;

    #[ORM\Column(name: 'comment_count', type: 'integer')]
    private ?int $commentCount = null;

    #[ORM\Column(name: 'comment_status', type: 'string')]
    private ?string $commentStatus = null;

    #[ORM\OneToMany(mappedBy: "entity", targetEntity: PostMeta::class)]
    private Collection $metaData;

    #[Pure] public function __construct()
    {
        $this->metaData = new ArrayCollection();
    }

    public function getClassNameMeta(): string
    {
        return PostMeta::class;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param   int|null  $id
     *
     * @return Post
     */
    public function setId(?int $id): Post
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getPostAuthor(): int
    {
        return $this->postAuthor;
    }

    /**
     * @param   int  $postAuthor
     *
     * @return Post
     */
    public function setPostAuthor(int $postAuthor): Post
    {
        $this->postAuthor = $postAuthor;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostStatus(): string
    {
        return $this->postStatus;
    }

    /**
     * @param   string  $postStatus
     *
     * @return Post
     */
    public function setPostStatus(string $postStatus): Post
    {
        $this->postStatus = $postStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostType(): string
    {
        return $this->postType;
    }

    /**
     * @param   string  $postType
     *
     * @return Post
     */
    public function setPostType(string $postType): Post
    {
        $this->postType = $postType;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getPostDate(): ?DateTime
    {
        return $this->postDate;
    }

    /**
     * @param DateTime|null $postDate
     *
     * @return Post
     */
    public function setPostDate(?DateTime $postDate): Post
    {
        $this->postDate = $postDate;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getPostModified(): ?DateTime
    {
        return $this->postModified;
    }

    /**
     * @param DateTime|null $postModified
     *
     * @return Post
     */
    public function setPostModified(?DateTime $postModified): Post
    {
        $this->postModified = $postModified;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostTitle(): string
    {
        return $this->postTitle;
    }

    /**
     * @param   string  $postTitle
     *
     * @return Post
     */
    public function setPostTitle(string $postTitle): Post
    {
        $this->postTitle = $postTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostContent(): string
    {
        return $this->postContent;
    }

    /**
     * @param   string  $postContent
     *
     * @return Post
     */
    public function setPostContent(string $postContent): Post
    {
        $this->postContent = $postContent;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostExcerpt(): ?string
    {
        return $this->postExcerpt;
    }

    /**
     * @param   string|null  $postExcerpt
     *
     * @return Post
     */
    public function setPostExcerpt(?string $postExcerpt): Post
    {
        $this->postExcerpt = $postExcerpt;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPostParent(): ?int
    {
        return $this->postParent;
    }

    /**
     * @param   int|null  $postParent
     *
     * @return Post
     */
    public function setPostParent(?int $postParent): Post
    {
        $this->postParent = $postParent;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostName(): ?string
    {
        return $this->postName;
    }

    /**
     * @param   string|null  $postName
     *
     * @return Post
     */
    public function setPostName(?string $postName): Post
    {
        $this->postName = $postName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostMimeType(): ?string
    {
        return $this->postMimeType;
    }

    /**
     * @param   string|null  $postMimeType
     *
     * @return Post
     */
    public function setPostMimeType(?string $postMimeType): Post
    {
        $this->postMimeType = $postMimeType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGuid(): ?string
    {
        return $this->guid;
    }

    /**
     * @param   string|null  $guid
     *
     * @return Post
     */
    public function setGuid(?string $guid): Post
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCommentCount(): ?int
    {
        return $this->commentCount;
    }

    /**
     * @param   int|null  $commentCount
     *
     * @return Post
     */
    public function setCommentCount(?int $commentCount): Post
    {
        $this->commentCount = $commentCount;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommentStatus(): ?string
    {
        return $this->commentStatus;
    }

    /**
     * @param   string|null  $commentStatus
     *
     * @return Post
     */
    public function setCommentStatus(?string $commentStatus): Post
    {
        $this->commentStatus = $commentStatus;

        return $this;
    }

}
