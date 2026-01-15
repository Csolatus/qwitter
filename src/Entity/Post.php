<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_filename = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $media_type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * @var Collection<int, Hashtag>
     */
    #[ORM\ManyToMany(targetEntity: Hashtag::class, inversedBy: 'posts')]
    private Collection $hashtags;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post')]
    private Collection $comments;

    public function __construct()
    {
        $this->hashtags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->reposts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->image_filename;
    }

    public function setImageFilename(?string $image_filename): static
    {
        $this->image_filename = $image_filename;

        return $this;
    }

    public function getMediaType(): ?string
    {
        return $this->media_type;
    }

    public function setMediaType(?string $media_type): static
    {
        $this->media_type = $media_type;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Hashtag>
     */
    public function getHashtags(): Collection
    {
        return $this->hashtags;
    }

    public function addHashtag(Hashtag $hashtag): static
    {
        if (!$this->hashtags->contains($hashtag)) {
            $this->hashtags->add($hashtag);
        }

        return $this;
    }

    public function removeHashtag(Hashtag $hashtag): static
    {
        $this->hashtags->removeElement($hashtag);

        return $this;
    }

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'reposts')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?self $originalPost = null;

    #[ORM\OneToMany(mappedBy: 'originalPost', targetEntity: self::class)]
    private Collection $reposts;

    public function getOriginalPost(): ?self
    {
        return $this->originalPost;
    }

    public function setOriginalPost(?self $originalPost): self
    {
        $this->originalPost = $originalPost;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getReposts(): Collection
    {
        return $this->reposts;
    }

    public function addRepost(self $repost): self
    {
        if (!$this->reposts->contains($repost)) {
            $this->reposts->add($repost);
            $repost->setOriginalPost($this);
        }

        return $this;
    }

    public function removeRepost(self $repost): self
    {
        if ($this->reposts->removeElement($repost)) {
            // set the owning side to null (unless already changed)
            if ($repost->getOriginalPost() === $this) {
                $repost->setOriginalPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $likes;

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setPost($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getPost() === $this) {
                $like->setPost(null);
            }
        }

        return $this;
    }
    #[ORM\OneToOne(mappedBy: 'post', cascade: ['persist', 'remove'])]
    private ?Poll $poll = null;

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(Poll $poll): static
    {
        // set the owning side of the relation if necessary
        if ($poll->getPost() !== $this) {
            $poll->setPost($this);
        }

        $this->poll = $poll;

        return $this;
    }
}

