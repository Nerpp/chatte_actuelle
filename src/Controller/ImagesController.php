<?php

namespace App\Controller;

use App\Entity\Images;
use App\Form\ImagesType;
use App\Form\ArticlesEditType;
use App\Repository\ArticlesRepository;
use App\Repository\ImagesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/images')]
class ImagesController extends AbstractController
{
    // #[Route('/', name: 'app_images_index', methods: ['GET'])]
    // public function index(ImagesRepository $imagesRepository): Response
    // {
    //     return $this->render('images/index.html.twig', [
    //         'images' => $imagesRepository->findAll(),
    //     ]);
    // }

    // #[Route('/new', name: 'app_images_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, ImagesRepository $imagesRepository): Response
    // {
    //     $image = new Images();
    //     $form = $this->createForm(ImagesType::class, $image);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $imagesRepository->add($image);
    //         return $this->redirectToRoute('app_images_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('images/new.html.twig', [
    //         'image' => $image,
    //         'form' => $form,
    //     ]);
    // }

    // #[Route('/{id}', name: 'app_images_show', methods: ['GET'])]
    // public function show(Images $image): Response
    // {
    //     return $this->render('images/show.html.twig', [
    //         'image' => $image,
    //     ]);
    // }

    #[Route('/{id}/edit', name: 'app_images_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Images $image, ImagesRepository $imagesRepository,ArticlesRepository $articlesRepository): Response
    {
        $form = $this->createForm(ImagesType::class, $image);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
          
            $imagesRepository->add($image);
            $article = $image->getArticles();
            return $this->redirectToRoute('app_articles_show', ['slug' => $article->getSlug() ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('images/edit.html.twig', [
            'image' => $image,
            'form' => $form,
        ]);
    }

}
