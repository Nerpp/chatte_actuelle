<?php

namespace App\Twig;

use App\Entity\Tags;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Doctrine\ORM\EntityManagerInterface;

class CatTagsExtension extends AbstractExtension
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('catTag', [$this,'getTag'])
        ];
    }

    public function getTags()
    {
        return $this->em->getRepository(Tags::class)->findAll();
    }
}
