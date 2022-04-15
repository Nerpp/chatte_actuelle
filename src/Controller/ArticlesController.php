<?php

namespace App\Controller;

use App\Entity\Tags;
use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Repository\TagsRepository;
use App\Repository\ArticlesRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use ContainerC6fd1RO\getUserRepositoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/articles')]
class ArticlesController extends AbstractController
{
    #[Route('/', name: 'app_articles_index', methods: ['GET'])]
    public function index(ArticlesRepository $articlesRepository): Response
    {
        return $this->render('articles/index.html.twig', [
            'articles' => $articlesRepository->findBy(['draft' => 0],['publishedAt'=>'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ManagerRegistry $doctrine, ArticlesRepository $articlesRepository,TagsRepository $tagsRepository): Response
    {
        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

       
        if ($form->isSubmitted() && $form->isValid()) {

           
            $entityManager = $doctrine->getManager();
            $newArticle = $form->getData();

            

            // tag venant de la séléction null
            if (!$form->getData()->getTags()) {
               
                // je récupére le nouveau tag
                $newTag = $form->get('newTags')->getData();

                // si le nouveau tag est null
                if (!$newTag) {
                    $this->addFlash(
                        'tags',
                        'Vous devez choisir ou créer un tag'
                    );

                    return $this->renderForm('articles/new.html.twig', [
                        'article' => $article,
                        'form' => $form,
                    ]);
                }

                // je vais chercher le new tag dans la bdd
                $checkedNewTag = $tagsRepository->findOneBy(['name' => $newTag]);

                // erreur il existe déja
                if($checkedNewTag)
                {
                    $this->addFlash(
                        'tags',
                        'Le tag existe déjà'
                    );
                    return $this->renderForm('articles/new.html.twig', [
                        'article' => $article,
                        'form' => $form,
                    ]);
                }

                // il existe pas j'enregistre
                $tag = new Tags;
                $tag->setName($newArticle->getTags()->getName());
                $tag->addArticle($article);
                $entityManager->persist($tag);

            }
            else{
                //enregistrement du tag venant de la séléction
                $article->setTags($newArticle->getTags());
            }

           
            
            $article->setSlug($newArticle->getTitle());
            $article->setTitle($newArticle->getTitle());
            $article->setArticle($newArticle->getArticle());
            
            $article->setUser($this->getUser());
           
            // $articlesRepository->add($article);
           
            $entityManager->persist($article);
            
            $entityManager->flush();
            

            return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('articles/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_articles_show', methods: ['GET'])]
    public function show(Articles $article): Response
    {
        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_articles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Articles $article, ArticlesRepository $articlesRepository): Response
    {
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articlesRepository->add($article);
            return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('articles/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_articles_delete', methods: ['POST'])]
    public function delete(Request $request, Articles $article, ArticlesRepository $articlesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $articlesRepository->remove($article);
        }

        return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
    }
}
