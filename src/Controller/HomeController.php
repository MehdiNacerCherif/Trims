<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UserType;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function index(UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('home/index.html.twig', [
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/duels", name="duels")
     */
    public function duels(UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('home/construction.html.twig', [
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/annexe", name="annexe")
     */
    public function annexe(UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('home/annexe.html.twig', [
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/connexion", name="connexion")
     */
    public function connexion(AuthenticationUtils $authenticationUtils, Request $request, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        
        $lastUsername = $authenticationUtils->getLastUsername();
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('home/connexion.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'registrationForm' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/profil", name="profil")
     */
    public function profil(Request $request, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }
}
