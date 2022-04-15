<?php

namespace App\Controller;

use App\Entity\Tags;
use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Repository\TagsRepository;
use App\Repository\ArticlesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/articles')]
class ArticlesController extends AbstractController
{

    public function __construct(Security $security)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
    }

    #[Route('/', name: 'app_articles_index', methods: ['GET'])]
    public function index(ArticlesRepository $articlesRepository): Response
    {
        
        return $this->render('articles/index.html.twig', [
            'articles' => $articlesRepository->findBy(['draft' => 0],['publishedAt'=>'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArticlesRepository $articlesRepository,TagsRepository $tagsRepository): Response
    {
       

        if (!$this->isGranted('new_article', $this->security->getUser())) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
            return $this->redirectToRoute('app_login');
        }

        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newArticle = $form->getData();

            $error = false;

            if (!$form->getData()->getTags()) {
               
                // je récupére le nouveau tag
                $newTag = $form->get('newTags')->getData();

                // si le nouveau tag est null
                if (!$newTag) {
                    $this->addFlash(
                        'tags',
                        'Vous devez choisir ou créer un tag'
                    );

                    $error = true;
                }

                // erreur il existe déja
                if($tagsRepository->findOneBy(['name' => $newTag]))
                {
                    $this->addFlash(
                        'tags',
                        'Le tag existe déjà'
                    );
                    $error = true;
                }
                
                // il existe pas j'enregistre
                $tag = new Tags;         
                $tag->setName($form->get('newTags')->getData());
                $article->setTags($tag);
            }
            else{
                //enregistrement du tag venant de la séléction
              $article->setTags($newArticle->getTags());
            }

            if ($articlesRepository->findOneBy(['title' => $newArticle->getTitle()])) {
                $this->addFlash(
                    'title',
                    'Un article similaire existe déjà'
                );
                $error = true;
            }

            if ($error) {
                return $this->renderForm('articles/new.html.twig', [
                    'article' => $article,
                    'form' => $form,
                ]);
            }

            if (!$newArticle->getDraft()) {
               $article->setPublishedAt(new \DateTime('now'));
            }

            $article->setSlug($newArticle->getTitle());
            $article->setUser($this->getUser());

            if(isset($tag)){
                $tagsRepository->add($tag);
            }
           
            $articlesRepository->add($article);
           
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
