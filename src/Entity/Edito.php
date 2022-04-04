<?php

namespace App\Entity;

use App\Repository\EditoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EditoRepository::class)]
class Edito
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $edito;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $draft;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $publishedAt;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEdito(): ?string
    {
        return $this->edito;
    }

    public function setEdito(string $edito): self
    {
        $this->edito = $edito;

        return $this;
    }

    public function getDraft(): ?bool
    {
        return $this->draft;
    }

    public function setDraft(?bool $draft): self
    {
        $this->draft = $draft;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

}
