<?php

namespace App\Controller;

use App\Entity\Edito;
use App\Entity\Articles;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ManagerRegistry $doctrine,Request $request): Response
    {
       
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'editos' => $doctrine->getRepository(Edito::class)->findOneBy(['id' => 1]),
            'last_articles' => $doctrine->getRepository(Articles::class)->findBy(['draft' => 0,'censure' => 0],['id'=>'DESC'],3),
        ]);
    }
}
