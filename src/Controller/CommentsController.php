<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Form\CommentsType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Repository\CommentsRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[Route('/comments')]
class CommentsController extends AbstractController
{

    public function __construct(Security $security)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
        $this->tokenUser = $this->security->getUser();
    }

    #[Route('/', name: 'app_comments_reported_index', methods: ['GET'])]
    public function index(CommentsRepository $commentsRepository): Response
    {
        if (!$this->isGranted('VIEW_REPORTED_COMMENTS', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, devez être connecté pour acceder à cette section');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('comments/index.html.twig', [
            'comments' => $commentsRepository->findBy(['reported' => true, 'moderated' => null], ['id' => 'ASC']),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comments_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comments $comment, CommentsRepository $commentsRepository): Response
    {
        $slug = $comment->getArticle()->getSlug();

        if (!$this->isGranted('EDIT_COMMENT', $comment)) {
            $this->addFlash('unauthorised', 'Désolé, vous ne pouvez pas supprimé ce commentaire');
            return $this->redirectToRoute('app_articles_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentsRepository->add($comment);

            return $this->redirectToRoute('app_articles_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comments/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_comments_delete', methods: ['POST'])]
    public function delete(Request $request, Comments $comment, CommentsRepository $commentsRepository): Response
    {
        $slug = $comment->getArticle()->getSlug();

        if (!$this->isGranted('DELETE_COMMENT', $comment)) {
            $this->addFlash('unauthorised', 'Désolé, vous ne pouvez pas supprimé ce commentaire');
            return $this->redirectToRoute('app_articles_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('success', 'Le commentaire a étè supprimé avec succés');
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $commentsRepository->remove($comment);
        }

        return $this->redirectToRoute('app_articles_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete/admin/{id}', name: 'app_comments_admin_delete', methods: ['POST'])]
    public function deletedByAdmin(Request $request, Comments $comment, CommentsRepository $commentsRepository, MailerInterface $mailer, UserRepository $userRepository): Response
    {
        if (!$this->isGranted('DELETE_COMMENT', $comment)) {
            $this->addFlash('unauthorised', 'Désolé, vous ne pouvez pas supprimé ce commentaire');
            return $this->redirectToRoute('app_login');
        }

        $user = $comment->getUser();
        $warning = $user->getWarning();
        $warning = $warning + 1;

        $email = (new TemplatedEmail())
            ->from('wampkarl@gmail.com')
            ->to(new Address($user->getEmail()))
            ->subject('Moderation')

            // path of the Twig template to render
            ->htmlTemplate('emails/moderation_email.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'username' => $user->getDisplayname(),
                'comment' => $comment->getComment(),
                'date' => $comment->getCreatedAt(),
                'warning' => $warning,
            ]);

           
            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
               $this->addFlash('warning','Un problémé est survenue lors de l\'envoit de l\'email au client');
               return $this->redirectToRoute('app_comments_reported_index');
            }

            $user->setWarning($warning);
            $userRepository->add($user, true);

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $commentsRepository->remove($comment);
        }

        return $this->redirectToRoute('app_comments_reported_index');
    }


    #[Route('/{id}/report', name: 'app_comments_report', methods: ['GET', 'POST'])]
    public function reportComment(Comments $comment, CommentsRepository $commentsRepository): Response
    {
        $slug = $comment->getArticle()->getSlug();

        if (!$this->isGranted('REPORT_COMMENT', $comment)) {
            $this->addFlash('unauthorised', 'Désolé, le commentaire ne peut plus être signalé');
            return $this->redirectToRoute('app_articles_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
        }

        $comment->setReported(true);
        $commentsRepository->add($comment);

        $this->addFlash('success', 'Merci, d\'avoir prit le temps de nous signaler ce commentaire');
        return $this->redirectToRoute('app_articles_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/allow', name: 'app_comments_allow', methods: ['GET', 'POST'])]
    public function allowComment(Comments $comment, CommentsRepository $commentsRepository): Response
    {

        if (!$this->isGranted('ALLOW_COMMENT', $comment)) {
            $this->addFlash('unauthorised', 'Désolé, le commentaire ne peut plus être signalé');
            return $this->redirectToRoute('app_admin');
        }

        $comment->setReported(false);
        $comment->setModerated(true);
        $commentsRepository->add($comment);

        $this->addFlash('success', 'Le commentaire à été autorisé');
        return $this->redirectToRoute('app_comments_reported_index');
    }

    #[Route('/{id}/reply', name: 'app_comment_reply', methods: ['GET', 'POST'])]
    public function reply(Request $request,Comments $comment, CommentsRepository $commentsRepository)
    {


        if ($this->isCsrfTokenValid('reply' . $comment->getId(), $request->request->get('_token'))) {
        $reply = new Comments;
        $reply->setComment($request->get("replyF"));
        $reply->setUser($comment->getUser());
        $reply->setArticle($comment->getArticle());
        $reply->setCreatedAt(new \DateTime('now'));
        $reply->setParent($comment);
        $commentsRepository->add($reply);
        return $this->redirectToRoute('app_articles_show', ['slug' => $comment->getArticle()->getSlug()], Response::HTTP_SEE_OTHER);
        }
        return $this->redirectToRoute('app_articles_show', ['slug' => $comment->getArticle()->getSlug()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete/reply/{id}', name: 'app_reply_delete', methods: ['POST'])]
    public function deleteReply(Request $request, Comments $comment, CommentsRepository $commentsRepository): Response
    {
        $slug = $comment->getArticle()->getSlug();

        if (!$this->isGranted('DELETE_COMMENT', $comment)) {
            $this->addFlash('unauthorised', 'Désolé, vous ne pouvez pas supprimé ce commentaire');
            return $this->redirectToRoute('app_articles_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('reply' . $comment->getId(), $request->request->get('_token'))) {

            $reply = new Comments;
            $reply->removeReply($comment);
            $commentsRepository->remove($reply);
            $this->addFlash('success', 'Le commentaire a étè supprimé avec succés');
        }

        return $this->redirectToRoute('app_articles_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
    }
    
}
