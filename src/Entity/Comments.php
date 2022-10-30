<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentsRepository;


#[ORM\Entity(repositoryClass: CommentsRepository::class)]
class Comments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $comment;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: Articles::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private $article;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $reported;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $moderated;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'reply')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent',cascade:['persist'], orphanRemoval: true, targetEntity: self::class)]
    private Collection $reply;

    
    public function __construct()
    {
        $this->reportings = new ArrayCollection();
        $this->reply = new ArrayCollection();
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getArticle(): ?Articles
    {
        return $this->article;
    }

    public function setArticle(?Articles $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function isReported(): ?bool
    {
        return $this->reported;
    }

    public function setReported(?bool $reported): self
    {
        $this->reported = $reported;

        return $this;
    }

    public function isModerated(): ?bool
    {
        return $this->moderated;
    }

    public function setModerated(?bool $moderated): self
    {
        $this->moderated = $moderated;

        return $this;
    }

    public function getComments(): ?self
    {
        return $this->comments;
    }

    public function setComments(?self $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getReply(): Collection
    {
        return $this->reply;
    }

    public function addReply(self $reply): self
    {
        if (!$this->reply->contains($reply)) {
            $this->reply->add($reply);
            $reply->setParent($this);
        }

        return $this;
    }

    public function removeReply(self $reply): self
    {
        if ($this->reply->removeElement($reply)) {
            // set the owning side to null (unless already changed)
            if ($reply->getParent() === $this) {
                $reply->setParent(null);
            }
        }

        return $this;
    }

 
}
