<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->tokenUser = $this->security->getUser();
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        if (!$this->isGranted('SECTION_ADMIN', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
            return $this->redirectToRoute('app_login');
        }

        
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
