<?php

namespace App\Controller;

use App\Entity\Tags;
use App\Entity\Images;
use App\Entity\Articles;
use App\Entity\Comments;
use App\Services\Cleaner;
use App\Form\ArticlesType;
use App\Form\CommentsType;
use App\Services\FileSysteme;
use App\Form\ArticlesEditType;
use App\Services\ImageOptimizer;
use App\Repository\TagsRepository;
use App\Repository\ImagesRepository;
use App\Repository\ArticlesRepository;
use App\Repository\CommentsRepository;
use Doctrine\Persistence\ManagerRegistry;
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
            'articles' => $articlesRepository->findBy(['draft' => 0, 'censure' => 0], ['publishedAt' => 'ASC']),
        ]);
    }

    // articles by tag
    #[Route('/bytag/{id}', name: 'app_articles_by_tag_index', methods: ['GET'])]
    public function indexByTag(Tags $tags): Response
    {
        return $this->render('articles/index_by_tag.html.twig', [
            'articles' => $tags->getArticles(),
        ]);
    }



    // Index de tout les Brouillons uniquement pour super admin
    #[Route('/index/draft', name: 'app_draft_index', methods: ['GET'])]
    public function indexDraft(ArticlesRepository $articlesRepository): Response
    {

        if (!$this->isGranted('VIEW_ALL_DRAFT', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que super administrateur');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('articles/index_draft.html.twig', [
            'articles' => $articlesRepository->findBy(['draft' => 1], ['id' => 'DESC']),
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

    #[Route('/censure', name: 'app_index_censure', methods: ['GET'])]
    public function indexArticleCensure(ArticlesRepository $articlesRepository): Response
    {
        
        if (!$this->isGranted('ACCES_CENSURE', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('articles/index_censured_articles.html.twig', [
            'articles' => $articlesRepository->findBy(['censure' => 1], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {

        if (!$this->isGranted('NEW_ARTICLE', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
            return $this->redirectToRoute('app_login');
        }

        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->remove('publishedAt');
        $form->remove('modifiedAt');
        $form->remove('censure');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $doctrine->getManager();
            $newArticle = $form->getData();

            //enregistrement du tag venant de la séléction
            $article->setTags($newArticle->getTags());


            $cleaner = new Cleaner;
            $slug = strToLower($cleaner->delAccent($newArticle->getTitle()));
            $article->setSlug($slug);

            // je vérifie qu'il existe des images
            $files = $form->get('image')->getData();

            // si il existe des images je crée un dossier au nom de l'article dans public img avec le slug
            if ($files) {
                $where = $this->getParameter('images_directory');

                $filesystem = new FileSysteme;
                $filesystem->createFolder($where.$slug);
            }

            foreach ($files as $image) {
                $filename = "_" . md5(uniqid()) . "." . $image->guessExtension();

                if ($image) {
                    try {
                        $image->move(
                            $where.$slug.'/',
                            $filename
                        );

                        $resizeImg = new ImageOptimizer;
                        $resizeImg->resize($where.$slug.'/'. $filename);
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

            if ($newArticle->getDraft()) {
                return $this->redirectToRoute('app_draft_index_personnal', [], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('articles/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_articles_show', methods: ['GET','POST'])]
    public function show(Request $request,Articles $article, CommentsRepository $commentsRepository): Response
    {
        if ($article->getDraft()) {
            if (!$this->isGranted('VIEW_DRAFT', $article)) {
                $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
                return $this->redirectToRoute('app_login');
            }
        }

        if ($article->getCensure()) {
            if (!$this->isGranted('VIEW_CENSURE', $article)) {
                $this->addFlash('unauthorised', 'Désolé, cette article à étè retiré');
                return $this->redirectToRoute('app_login');
            }
        }

        $comment = new Comments();
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTime('now'));
            $comment->setUser($this->tokenUser);
            $comment->setArticle($article);
            $commentsRepository->add($comment);

            return $this->redirectToRoute('app_articles_show',['slug' => $article->getSlug()]);
        }

        return $this->render('articles/show.html.twig', [
            'article' => $article,
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_articles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Articles $article, ManagerRegistry $doctrine, ArticlesRepository $articlesRepository): Response
    {
        if (!$this->isGranted('EDIT_ARTICLE', $article)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ArticlesEditType::class, $article);
        $form->remove('publishedAt');
        $form->remove('modifiedAt');
        if (!$this->isGranted('CENSURE_ARTICLE', $article)) {
            $form->remove('censure');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();

            if ($article->getPublishedAt()) {
                $article->setModifiedAt(new \DateTime('now'));
            }

            $oldSlug = $article->getSlug();

            $cleaner = new Cleaner;
            $slug = strToLower($cleaner->delAccent($article->getTitle()));
            $article->setSlug($slug);

            // je vérifie qu'il existe des images
            $files = $form->get('image')->getData();

            $where = $this->getParameter('images_directory');

            $filesystem = new FileSysteme;
            $filesystem->renameFolder($where . $oldSlug . '/', $where.$slug .'/');

            foreach ($files as $image) {
                $filename = "_" . md5(uniqid()) . "." . $image->guessExtension();

                if ($image) {
                    try {
                        $image->move(
                            $where.$slug.'/',
                            $filename
                        );

                        $resizeImg = new ImageOptimizer;
                        $resizeImg->resize($where.$slug.'/'.$filename);
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
            $entityManager->flush();
            $articlesRepository->add($article);

            if ($article->getCensure()) {
                return $this->redirectToRoute('app_index_censure', [], Response::HTTP_SEE_OTHER);
            }

            if ($article->getDraft()) {
                return $this->redirectToRoute('app_draft_index_personnal', [], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('articles/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{slug}', name: 'app_articles_delete', methods: ['POST'])]
    public function delete(Request $request, Articles $article, ArticlesRepository $articlesRepository): Response
    {
        // https://gmanier.com/memo/6/php-supprimer-dossier-a-l-aide-de-la-recursivite a voir pour supprimer dossier php

        if (!$this->isGranted('DELETE_ARTICLE', $article)) {
            $this->addFlash('unauthorised', 'Désolé, vous devez être connecté en tant que administrateur');
            return $this->redirectToRoute('app_login');
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {

        $filesystem = new FileSysteme;
            $filesystem->remove($this->getParameter('images_directory'). $article->getSlug().'/');

            $articlesRepository->remove($article);
        }

        return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/image/{id}', name: 'app_images_delete', methods: ['POST', 'GET'])]
    public function deleteImage(Request $request, Images $image, ImagesRepository $imagesRepository): Response
    {

        if (!$this->isGranted('DELETE_IMAGE', $image)) {
            $this->addFlash('unauthorised', 'Désolé, vous ne disposez pas des droits nécessaires');
            return $this->redirectToRoute('app_login');
        }

        // if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {

        $article = $image->getArticles()->getSlug();

        $filesystem = new FileSysteme;
        $filesystem->remove($this->getParameter('images_directory').$article. '/' . $image->getSource());
        $imagesRepository->remove($image);

        return  $this->redirectToRoute('app_articles_edit', ['slug' => $article], Response::HTTP_SEE_OTHER);
        // }

        // return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}

// $filesystem->remove(['symlink', '/path/to/directory', 'activity.log']);