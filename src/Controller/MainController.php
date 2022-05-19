<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
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
    public function index( ArticleRepository $articleRepository ): Response
    {

        // Récupération des dernier articles a affiché sur l'acceuil
        $articles = $articleRepository->findBy(
            [],  // WHERE du SELECT
            ['publicationDate' => 'DESC'], // ORDER BY du SELECT
            $this->getParameter('app.article.last_article_number_on_home') // LIMIT du SELECT (qu'on récupère dans service.yaml)
        );

       return $this->render('main/home.html.twig', [
           'articles' => $articles
       ]);
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
