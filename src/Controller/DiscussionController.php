<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Message;
use App\Entity\Reponse;
use App\Form\MessageType;
use App\Form\ReponseType;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use App\Repository\ReponseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/discussions")
 */
class DiscussionController extends AbstractController
{
    /**
     * @Route("/", name="discussion")
     */
    public function index(UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('discussion/index.html.twig', [
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/probleme", name="probleme")
     */
    public function probleme(MessageRepository $messageRepository, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('discussion/probleme.html.twig', [
            'messages' => $messageRepository->findBy(array('type' => 'probleme'), array('date' => 'DESC')),
            'typeVue' => 'Problèmes',
            'type' => 'probleme',
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/sujet", name="sujet")
     */
    public function sujet(MessageRepository $messageRepository, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('discussion/probleme.html.twig', [
            'messages' => $messageRepository->findBy(array('type' => 'sujet'), array('date' => 'DESC')),
            'typeVue' => 'Sujets',
            'type' => 'sujet',
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/nouveau/{type}", name="msg_new", methods={"GET","POST"})
     */
    public function newMessage(Request $request, $type, UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        $typelist = ["probleme","sujet"];
        $verif = in_array($type, $typelist) ? "vrai" : "faux";
        if ( $verif == "faux" || !($this->getUser()) ) {
            return $this->redirectToRoute("connexion");
        }
        
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setType($type);
            $message->setDate( new \DateTime() );
            $message->setAuteur( $this->getUser() );
            $categories = explode(";", $_POST["categories"]);
            $message->setCategories( $categories );
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute($type);
        }
        // message/new.html.twig
        return $this->render('discussion/new.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/{titre}-{id}", name="voir_msg")
     */
    public function voir(Message $message, ReponseRepository $reponseRepository, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        $reponses = $reponseRepository->findBy(array('message' => $message));
        
        return $this->render('discussion/voir.html.twig', [
            'message' => $message,
            'reponses' => $reponses,
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/modifier/{titre}-{id}", name="edit_msg", methods={"GET","POST"})
     */
    public function editMessage(Request $request, Message $message, UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        if ( !$this->getUser() ) {
            return $this->redirectToRoute("connexion");
        }
        if ( !($message->getAuteur() === $this->getUser() || $this->isGranted('ROLE_ADMIN')) ) {
            return $this->redirectToRoute("connexion");
        }
        
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ( $message->getAuteur() ) {
            $_SESSION['auteur'] = $message->getAuteur()->getId();
            $_SESSION['type'] = $message->getType();
            $_SESSION['date'] = $message->getDate();
        }
        $auteur = $userRepository->find( $_SESSION['auteur'] );
        $type = $_SESSION['type'];
        $date = $_SESSION['date'];

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setAuteur( $auteur );
            $message->setType( $type );
            $message->setDate( $date );
            $categories = explode(";", $_POST["categories"]);
            $message->setCategories( $categories );
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('voir_msg', array('titre' => $message->getTitre(),'id' => $message->getId() ));
        }

        return $this->render('discussion/editMessage.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/repondre/{titre}-{id}", name="repondre_msg")
     */
    public function repondre(Request $request, Message $message, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        if ( !($this->getUser()) ) {
            return $this->redirectToRoute("connexion");
        }
        
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setDate( new \DateTime() );
            $reponse->setAuteur( $this->getUser() );
            $reponse->setMessage( $message );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('voir_msg', array('titre'=> $message->getTitre() ,'id'=> $message->getId()));
        }

        return $this->render('discussion/repondre.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/modifier/reponse/{id}", name="edit_reponse", methods={"GET","POST"})
     */
    public function editReponse(Request $request, Reponse $reponse , MessageRepository $messageRepository, UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        if ( !$this->getUser() ) {
            return $this->redirectToRoute("connexion");
        }
        if ( !($reponse->getAuteur() === $this->getUser() || $this->isGranted('ROLE_ADMIN')) ) {
            return $this->redirectToRoute("connexion");
        }
        
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ( $reponse->getAuteur() ) {
            $_SESSION['auteur'] = $reponse->getAuteur()->getId();
            $_SESSION['msg'] = $reponse->getMessage()->getId();
        }
        $auteur = $userRepository->find( $_SESSION['auteur'] );
        $msg = $messageRepository->find( $_SESSION['msg'] );

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setDate( $reponse->getDate() );
            $reponse->setAuteur( $auteur );
            $reponse->setMessage( $msg );

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('voir_msg', array('titre'=> $msg->getTitre() ,'id'=> $msg->getId()));
        }

        return $this->render('discussion/editReponse.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }
}
