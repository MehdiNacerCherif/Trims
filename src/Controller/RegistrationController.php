<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/connexion/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator, UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ( $userRepository->findOneBy( array('email' => $form->get('email')->getData()) ) ) {
            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
                'erreur' => "L'adresse email \"" . $form->get('email')->getData() . "\" est déja inscrite",
                'top3' => $top3,
                'classement' => $classement,
                'user' => $user,
            ]);
        }
        if ( $userRepository->findOneBy( array('nom' => $form->get('nom')->getData()) ) ) {
            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
                'erreur' => "Le nom \"" . $form->get('nom')->getData() . "\" est déja utilisé",
                'top3' => $top3,
                'classement' => $classement,
                'user' => $user,
            ]);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $confirmpassword = $_POST["ConfirmPassword"];
            if ( !($form->get('plainPassword')->getData() == $confirmpassword ) ) {
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'erreur' => "Le mot de passe doit Correspondre",
                    'top3' => $top3,
                    'classement' => $classement,
                    'user' => $user,
                ]);
            }

            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setScore(0);
            $user->setVisible(1);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
            'user' => $user,
        ]);
    }
}
