<?php

namespace App\Controller;

use App\Entity\Edito;
use App\Form\EditoType;
use App\Repository\EditoRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/edito')]
class EditoController extends AbstractController
{

    public function __construct(Security $security)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
        $this->tokenUser = $this->security->getUser();
    }
   
    #[Route('/{id}/edit', name: 'app_edito_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Edito $edito, EditoRepository $editoRepository): Response
    {
        if (!$this->isGranted('EDITO_EDIT', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que super administrateur');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EditoType::class, $edito);
        $form->remove('publishedAt');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $edito->setPublishedAt(new \DateTime('now'));
            $editoRepository->add($edito);
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('edito/edit.html.twig', [
            'edito' => $edito,
            'form' => $form,
        ]);
    }

}
