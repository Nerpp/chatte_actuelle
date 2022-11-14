<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\ImgProfile;
use App\Services\FileSysteme;
use App\Services\ImageOptimizer;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    public function __construct(Security $security)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
        $this->tokenUser = $this->security->getUser();
    }
    
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        if (!$this->isGranted('USER_INDEX', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, vous n\'avez pas les droits suffisants pour accéder à cet espace');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        if (!$this->isGranted('USER_INDEX', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, vous n\'avez pas les droits suffisants pour accéder à cet espace');
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository,ManagerRegistry $doctrine): Response
    {
        $userProfile = $user;
        if (!$this->isGranted('EDIT_PROFILE', $userProfile)) {
            $this->addFlash('unauthorised', 'Désolé, vous n\'avez pas les droits suffisants pour accéder à cet espace');
            return $this->redirectToRoute('app_login');
        }
        
        $form = $this->createForm(UserType::class, $user);

        if (!$this->isGranted('CHANGE_ROLE', $this->tokenUser)) {
            $form->remove('roles');
        }

        if (!$this->isGranted('CHANGE_AVERTISSEMENT_USER', $this->tokenUser)) {
            $form->remove('warning');
        }

        $form->remove('email');
       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $doctrine->getManager();
            $files = $form->get('imgProfile')->getData();

            if($files){

                $where = $this->getParameter('images_directory').'profile/';
                $filename = "_" . md5(uniqid()) . "." . $files->guessExtension();
                
                $userImg = $user->getImgProfile();
                if ($userImg) {
                    $filesystem = new FileSysteme;
                    $filesystem->remove($this->getParameter('images_directory').'profile/'.$userImg->getSource());
                }
                   
                try {
                    $files->move(
                        $where,
                        $filename
                    );


                    $resizeImg = new ImageOptimizer;
                    $resizeImg->resizeImgProfile($where.'/'. $filename);

                } catch (FileException $e) {
                    $this->addFlash('verify_email_error', 'Une érreur est survenue lors du chargement de l\'image !');
                    return $this->redirectToRoute('app_user_edit');
                }
                
                $userImg->setSource($filename);
                  
            }

             $userRepository->add($user, true);

           
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if (!$this->isGranted('DELETE_USER', $this->tokenUser)) {
            $this->addFlash('unauthorised', 'Désolé, vous n\'avez pas les droits suffisants pour accéder à cet espace');
            return $this->redirectToRoute('app_login');
        }

        $img = $user->getImgProfile()->getSource();
        if ($img) {
            $filesystem = new FileSysteme;
            $filesystem->remove($this->getParameter('images_directory').'profile/'.$img);
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
