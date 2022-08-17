<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cet email n\'est pas disponible')]
#[UniqueEntity(fields: ['displayname'], message: 'Le nom d\'affichage est déjà utilisé')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message:"Vous devez saisir une adresse email.")]
    #[Assert\Email(message:"Le format de l'adresse email {{ value }} n'est pas correcte.")]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'string', length: 45, unique: true)]
    private $displayname;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Articles::class, orphanRemoval: true)]
    private $articles;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comments::class, orphanRemoval: true)]
    private $comments;


    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: BlackList::class, cascade: ['persist', 'remove'])]
    private $blackList;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $warning;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: ImgProfile::class, cascade: ['persist', 'remove'])]
    private $imgProfile;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->editos = new ArrayCollection();
        $this->reportings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getDisplayname(): ?string
    {
        return $this->displayname;
    }

    public function setDisplayname(string $displayname): self
    {
        $this->displayname = $displayname;

        return $this;
    }

    /**
     * @return Collection<int, Articles>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Articles $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setUser($this);
        }

        return $this;
    }

    public function removeArticle(Articles $article): self
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getUser() === $this) {
                $article->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }


    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getBlackList(): ?BlackList
    {
        return $this->blackList;
    }

    public function setBlackList(BlackList $blackList): self
    {
        // set the owning side of the relation if necessary
        if ($blackList->getUser() !== $this) {
            $blackList->setUser($this);
        }

        $this->blackList = $blackList;

        return $this;
    }

    public function getWarning(): ?int
    {
        return $this->warning;
    }

    public function setWarning(?int $warning): self
    {
        $this->warning = $warning;

        return $this;
    }
    
    public function __toString()
    {
        $this->roles;
    }

    public function getImgProfile(): ?ImgProfile
    {
        return $this->imgProfile;
    }

    public function setImgProfile(?ImgProfile $imgProfile): self
    {
        // unset the owning side of the relation if necessary
        if ($imgProfile === null && $this->imgProfile !== null) {
            $this->imgProfile->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($imgProfile !== null && $imgProfile->getUser() !== $this) {
            $imgProfile->setUser($this);
        }

        $this->imgProfile = $imgProfile;

        return $this;
    }
}
