<?php

namespace App\Controller;

use App\Entity\ImgProfile;
use App\Entity\User;
use App\Security\EmailVerifier;
use App\Services\ImageOptimizer;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (strval($session->get('captcha')) !== $form->get('captcha')->getData()) {
                $this->addFlash('', 'Le captcha est incorrect');

                $firstElement =  random_int(0, 10);
                $secondElement = random_int(0, 10);
                $session->set('captcha', $firstElement + $secondElement);

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'captcha' => $firstElement . '+' . $secondElement,
                ]);
            }

            // je vérifie qu'il existe des images
            $files = $form->get('imgProfile')->getData();
            $recImg = new ImgProfile();

            if ($files) {
                $where = $this->getParameter('images_directory') . 'profile/';
                $filename = "_" . md5(uniqid()) . "." . $files->guessExtension();

                try {
                    $files->move(
                        $where,
                        $filename
                    );

                    $resizeImg = new ImageOptimizer();
                    $resizeImg->resizeImgProfile($where . '/' . $filename);
                } catch (FileException $e) {
                    $this->addFlash('verify_email_error', 'Une érreur est survenue lors du chargement de l\'image !');
                    $firstElement =  random_int(0, 10);
                    $secondElement = random_int(0, 10);
                    $session->set('captcha', $firstElement + $secondElement);
                    return $this->render('registration/register.html.twig', [
                        'registrationForm' => $form->createView(),
                        'captcha' => $firstElement . '+' . $secondElement,
                    ]);
                }

                $recImg->setSource($filename);
            } else {
                $recImg->setSource(false);
            }

            $entityManager->persist($recImg);
                $user->setImgProfile($recImg);
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('wampkarl@gmail.com'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('bien', 'Derniére étape, veuillez verifier votre email pour confirmer, ou vos spams..');

            return $this->redirectToRoute('app_login');
        }

        $firstElement =  random_int(0, 10);
        $secondElement = random_int(0, 10);

        $session->set('captcha', $firstElement + $secondElement);

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'captcha' => $firstElement . '+' . $secondElement,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {

        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('bien', 'Bienvenue, vous pouvez vous connecter.');

        return $this->redirectToRoute('app_login');
    }
}
