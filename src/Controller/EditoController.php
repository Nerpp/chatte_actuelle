<?php

namespace App\Controller;

use App\Entity\Edito;
use App\Form\EditoType;
use App\Repository\EditoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/edito')]
class EditoController extends AbstractController
{
    #[Route('/', name: 'app_edito_index', methods: ['GET'])]
    public function index(EditoRepository $editoRepository): Response
    {
        return $this->render('edito/index.html.twig', [
            'editos' => $editoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_edito_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EditoRepository $editoRepository): Response
    {
        $edito = new Edito();
        $form = $this->createForm(EditoType::class, $edito);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $editoRepository->add($edito);
            return $this->redirectToRoute('app_edito_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('edito/new.html.twig', [
            'edito' => $edito,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_edito_show', methods: ['GET'])]
    public function show(Edito $edito): Response
    {
        return $this->render('edito/show.html.twig', [
            'edito' => $edito,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_edito_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Edito $edito, EditoRepository $editoRepository): Response
    {
        $form = $this->createForm(EditoType::class, $edito);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $editoRepository->add($edito);
            return $this->redirectToRoute('app_edito_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('edito/edit.html.twig', [
            'edito' => $edito,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_edito_delete', methods: ['POST'])]
    public function delete(Request $request, Edito $edito, EditoRepository $editoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$edito->getId(), $request->request->get('_token'))) {
            $editoRepository->remove($edito);
        }

        return $this->redirectToRoute('app_edito_index', [], Response::HTTP_SEE_OTHER);
    }
}
