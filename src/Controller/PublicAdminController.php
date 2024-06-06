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
 * @Route("/public-admin")
 */
class PublicAdminController extends AbstractController
{
    /**
     * @Route("/", name="public-admin")
     */
    public function index(UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('public_admin\index.html.twig', [
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/Administration", name="public_administration")
     */
    public function publicAdmin(UserRepository $userRepository)
    {
        // nouveau split
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('public_admin\AdminSplit.html.twig', [
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/Developpement", name="public_developpement")
     */
    public function publicDev(MessageRepository $messageRepository, UserRepository $userRepository)
    {
        // on montre les msg direct
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('public_admin\voirAdmin.html.twig', [
            'messages' => $messageRepository->findBy(array('type' => 'Dev'), array('date' => 'DESC')),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/Administration/MesDemandes", name="Admin")
     */
    public function publicAdminMyList(MessageRepository $messageRepository, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        if ( !$this->getUser() ) {
            return $this->redirectToRoute("connexion");
        }
        $user = $this->getUser();
        
        return $this->render('public_admin\MesDemandes.html.twig', [
            'user' => $user,
            'messages' => $messageRepository->findBy(array('type' => 'Admin' , 'auteur' => $user->getId() ), array('date' => 'DESC')),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/Administration/ToutesLesDemandes", name="AllList")
     */
    public function publicAdminallList(MessageRepository $messageRepository, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        if ( !$this->getUser() && $this->isGranted('ROLE_ADMIN') ) {
            return $this->redirectToRoute("connexion");
        }
        $user = $this->getUser();
        
        return $this->render('public_admin\MesDemandes.html.twig', [
            'user' => $user,
            'messages' => $messageRepository->findBy(array('type' => 'Admin'), array('date' => 'DESC')),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/{titre}-{id}", name="voir_demande")
     */
    public function voir(Message $message, ReponseRepository $reponseRepository, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));

        if ( !$this->getUser() ) {
            return $this->redirectToRoute("connexion");
        }
        if ( !($message->getAuteur() === $this->getUser() || $this->isGranted('ROLE_ADMIN')) ) {
            return $this->redirectToRoute("connexion");
        }
        
        $reponses = $reponseRepository->findBy(array('message' => $message));
        
        return $this->render('public_admin/voir.html.twig', [
            'message' => $message,
            'reponses' => $reponses,
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/nouveau/{type}", name="Dev", methods={"GET","POST"})
     */
    public function newMessage(Request $request, $type, UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        $typelist = ["Admin","Dev"];
        $verif = in_array($type, $typelist) ? "vrai" : "faux";
        if ( $verif == "faux" || !($this->getUser()) ) {
            return $this->redirectToRoute("connexion");
        }
        if ( $type == 'Dev' && !$this->isGranted('ROLE_ADMIN') ){
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
        return $this->render('public_admin\new.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/modifier/{titre}-{id}", name="Dev_edit_msg", methods={"GET","POST"})
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

            if ($message->getType() == 'Dev') {
                return $this->redirectToRoute('public_developpement');
            }
            return $this->redirectToRoute('voir_demande', array('titre'=> $message->getTitre() ,'id'=> $message->getId()));
        }

        return $this->render('discussion/editMessage.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/repondre/{titre}-{id}", name="repondre_demande")
     */
    public function repondre(Request $request, Message $message, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        if ( !($this->getUser()) ) {
            return $this->redirectToRoute("connexion");
        }
        if ( !($message->getAuteur() === $this->getUser() || $this->isGranted('ROLE_ADMIN')) ) {
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

            return $this->redirectToRoute('voir_demande', array('titre'=> $message->getTitre() ,'id'=> $message->getId()));
        }

        return $this->render('discussion/repondre.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/modifier/reponse/{id}", name="edit_reponseDemande", methods={"GET","POST"})
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

            return $this->redirectToRoute('voir_demande', array('titre'=> $msg->getTitre() ,'id'=> $msg->getId()));
        }

        return $this->render('discussion/editReponse.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }
}
