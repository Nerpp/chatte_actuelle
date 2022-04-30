<?php

namespace App\Controller;

use App\Entity\Tags;
use App\Entity\Images;
use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Form\ArticlesEditType;
use App\Repository\TagsRepository;
use App\Repository\ArticlesRepository;
use App\Repository\ImagesRepository;
use App\Services\Cleaner;
use App\Services\CreateFolder;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\AssignOp\Mod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/articles')]
class ArticlesController extends AbstractController
{

    public function __construct(Security $security)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
        $this->tokenUser = $this->security->getUser();
    }

    // Index Publication
    #[Route('/', name: 'app_articles_index', methods: ['GET'])]
    public function index(ArticlesRepository $articlesRepository): Response
    { 
        return $this->render('articles/index.html.twig', [
            'articles' => $articlesRepository->findBy(['draft' => 0],['publishedAt'=>'ASC']),
        ]);
    }

    // Index Brouillon
    #[Route('/index/draft', name: 'app_draft_index', methods: ['GET'])]
    public function indexDraft(ArticlesRepository $articlesRepository): Response
    { 

        if (!$this->isGranted('VIEW_ALL_DRAFT', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que super administrateur');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('articles/index_draft.html.twig', [
            'articles' => $articlesRepository->findBy(['draft' => 1],['id'=>'ASC']),
        ]);
    }

     // Index article publié
     #[Route('/personnal/', name: 'app_article_index_personnal', methods: ['GET'])]
     public function indexPersonnalArticle(): Response
     { 
 
         if (!$this->isGranted('VIEW_PERSONNAL_ARTICLE', $this->tokenUser)) {
             $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
             return $this->redirectToRoute('app_login');
         }

         return $this->render('articles/index_personnal_article.html.twig', [
             'user' => $this->tokenUser,
         ]);
     }

       // Index Brouillon
       #[Route('/personnal/draft', name: 'app_draft_index_personnal', methods: ['GET'])]
       public function indexPersonnalDraft(): Response
       { 
   
           if (!$this->isGranted('VIEW_PERSONNAL_ARTICLE', $this->tokenUser)) {
               $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
               return $this->redirectToRoute('app_login');
           }
  
           return $this->render('articles/index_personnal_draft.html.twig', [
               'user' => $this->tokenUser,
           ]);
       }

    #[Route('/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
    public function new(Request $request,ManagerRegistry $doctrine ,ArticlesRepository $articlesRepository): Response
    {
       
        if (!$this->isGranted('NEW_ARTICLE', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
            return $this->redirectToRoute('app_login');
        }

        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->remove('publishedAt');
        $form->remove('modifiedAt');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $doctrine->getManager();
            $newArticle = $form->getData();
            $error = false;

            $title = $article->getTitle();
            
              // je vérifie que l'article n'existe pas déjà
              if ($articlesRepository->findOneBy(['title' =>  $title])) {
                $this->addFlash(
                    'title',
                    'Un article similaire existe déjà'
                );
                $error = true;
            }

            //enregistrement du tag venant de la séléction
              $article->setTags($newArticle->getTags());
            
            // administration tag

            if (!$article->getArticle()) {
                $this->addFlash(
                    'article',
                    'Vous devez écrire un article'
                );
                $error = true;
            }
          

            // j'envoit toutes les erreurs avant de traiter les images
            if ($error) {
                return $this->renderForm('articles/new.html.twig', [
                    'article' => $article,
                    'form' => $form,
                ]);
            }

            // je vérifie qu'il existe des images
            $files = $form->get('image')->getData();

            $cleaner = new Cleaner;
            $slug = strToLower($cleaner->delAccent($newArticle->getTitle()));
            $article->setSlug($slug);

            // si il existe des images je crée un dossier au nom de l'article dans public img avec le slug
            if ($files) {
                $where = $this->getParameter('images_directory');
                $folder = new CreateFolder;
                $folder->createFolder($where);
            }

            foreach ($files as $image) {
                $filename = "_" . md5(uniqid()) . "." . $image->guessExtension();

                if ($image) {
                    try {
                        $image->move(
                           $where,
                            $filename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('failed', 'Une érreur est survenue lors du chargement de l\'image !');
                        return $this->redirectToRoute('app_articles_new');
                    }
                }

                $recImage = new Images;
                $recImage->setSource($filename);
                $article->addImage($recImage);
                $entityManager->persist($recImage);
            }

            // Si draft est null cela signifie que l'article est publié donc je met une date de publication
            if (!$newArticle->getDraft()) {
               $article->setPublishedAt(new \DateTime('now'));
            }

            $article->setUser($this->getUser());
            $article->setArticle($newArticle->getArticle());

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
        if ($article->getDraft()) {
            if (!$this->isGranted('VIEW_DRAFT', $article)) {
                $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_articles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Articles $article,ManagerRegistry $doctrine,ArticlesRepository $articlesRepository): Response
    {
        if (!$this->isGranted('EDIT_ARTICLE', $article)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ArticlesEditType::class, $article);
        $form->remove('publishedAt');
        $form->remove('modifiedAt');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();

            if ($article->getPublishedAt()) {
                $article->setModifiedAt(new \DateTime('now'));
            }

            // je vérifie qu'il existe des images
            $files = $form->get('image')->getData();

            $cleaner = new Cleaner;
            $slug = strToLower($cleaner->delAccent($article->getTitle()));
            $article->setSlug($slug);

            // si il existe des images je crée un dossier au nom de l'article dans public img avec le slug
            if ($files) {
                $where = $this->getParameter('images_directory');
                $folder = new CreateFolder;
                $folder->createFolder($where);
            }

            foreach ($files as $image) {
                $filename = "_" . md5(uniqid()) . "." . $image->guessExtension();

                if ($image) {
                    try {
                        $image->move(
                           $where,
                            $filename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('failed', 'Une érreur est survenue lors du chargement de l\'image !');
                        return $this->redirectToRoute('app_articles_new');
                    }
                }

                $recImage = new Images;
                $recImage->setSource($filename);
                $article->addImage($recImage);
                $entityManager->persist($recImage);
                $entityManager->flush();
            }
            
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
        // https://gmanier.com/memo/6/php-supprimer-dossier-a-l-aide-de-la-recursivite a voir pour supprimer dossier php

        if (!$this->isGranted('DELETE_ARTICLE', $article)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
            return $this->redirectToRoute('app_login');
        }

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $articlesRepository->remove($article);
        }

        return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
    }
}
