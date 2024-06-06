<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/suggestions")
 */
class SuggestionController extends AbstractController
{
    /**
     * @Route("/", name="suggestion")
     */
    public function index(MessageRepository $messageRepository, UserRepository $userRepository)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        return $this->render('suggestion/index.html.twig', [
            'messages' => $messageRepository->findBy(array('type' => 'suggestion')),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/nouveau", name="sugg_new", methods={"GET","POST"})
     */
    public function newMessage(Request $request, UserRepository $userRepository): Response
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));

        if ( !($this->getUser()) ) {
            return $this->redirectToRoute("connexion");
        }
        
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setType("suggestion");
            $message->setDate( new \DateTime() );
            $message->setAuteur( $this->getUser() );
            $categories = explode(";", $_POST["categories"]);
            $message->setCategories( $categories );
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('suggestion');
        }
        // message/new.html.twig
        return $this->render('suggestion/new.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }

    /**
     * @Route("/modifier/{titre}-{id}", name="edit_sugg", methods={"GET","POST"})
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

            return $this->redirectToRoute('suggestion');
        }

        return $this->render('suggestion/editMessage.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }
}
