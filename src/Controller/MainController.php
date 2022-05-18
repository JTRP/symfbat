<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('', name: 'main_')]
class MainController extends AbstractController
{
    /**
     * Contrôleur de la page d'acceuil
     */
    #[Route('/', name: 'home')]
    public function index(): Response
    {


       return $this->render('main/home.html.twig');
    }

    /**
     * Contrôleur de la page de profil
     *
     * Accès reservé aux connectés (ROLES_USER)
     */

    #[Route('/mon-profil/', name: 'profil')]
    #[IsGranted('ROLE_USER')]
    public function profil() : Response
    {


        return $this->render('main/profil.html.twig');
    }
}
