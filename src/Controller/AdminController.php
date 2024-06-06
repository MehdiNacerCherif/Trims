<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
*/
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_index")
     */
    public function index(UserRepository $userRepository, Request $request)
    {
        $top3 = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'), $limit = 3);
        $classement = $userRepository->findBy(array('visible' => 1), array('score' => 'DESC'));
        
        $resultats = $userRepository->findAll();
        $recherche = 'Tous';
        if ( $request->query->get("recherche") ) {
            $resultats = $userRepository->findByNom( $request->query->get("recherche") );
            $recherche = $request->query->get("recherche");
        }
        
        return $this->render('admin/index.html.twig', [
            'resultats' => $resultats,
            'recherche' => $recherche,
            'top3' => $top3,
            'classement' => $classement,
        ]);
    }
}
