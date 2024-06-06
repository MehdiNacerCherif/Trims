<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/admin/nouveau", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user, Request $request, UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ( $form->get('password')->getData() && $_POST["confirm-mdp"] ) {
                if ( $form->get('password')->getData() == $_POST["confirm-mdp"]) {
                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user,
                            $form->get('password')->getData()
                        )
                    );
                }
            } else {
                $user->setPassword( $user->getPassword() );
            }
            if ( isset($_POST["pts"]) ) {
                $user->setScore( $_POST["pts"] );
            } else {
                $user->setScore( $user->getScore() );
            }
            if ( !($form->get('roles')->getData()) ) {
                $user->setRoles( $user->getRoles() );
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_show', array('id' => $user->getId() ));
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->redirectToRoute('accueil');
        
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
