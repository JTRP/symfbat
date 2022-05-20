<?php

namespace App\Controller;

use App\Form\EditPhotoFormType;
use App\Repository\ArticleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
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


    /**
     * Contrôleur de la page de modification de la photo de profile
     *
     * Accès réservé au connecté
     */
    #[Route('/editer-photo/', name: 'edit_photo')]
    #[IsGranted('ROLE_USER')]
    public function editPhoto(Request $request, ManagerRegistry $doctrine): Response
    {

        $form = $this->createForm( EditPhotoFormType::class );

        $form->handleRequest( $request );

        // Si le formulaire à été envoyé et n'a pas d'erreur
        if ( $form->isSubmitted() && $form->isValid() ) {

            // Si l'utilisateur a déjà une photo de profil, on la supprime

            if (
                $this->getUser()->getPhoto() != null &&
                file_exists(
                    $this->getParameter('app.user.photo.directory') . $this->getUser()->getPhoto()
                )
            ) {

                unlink( $this->getParameter('app.user.photo.directory') . $this->getUser()->getPhoto() );
            }

            // Récupération des infos de la photo envoyé
            $photo = $form->get('photo')->getData();

            // TODO: Si l'utilisateur à déjà une photo de profil la supprimé

            // Création d'un nouveau nom pour la photo ( tans aue le nom est déjà pris on en régnère un )
            do {
                $newFileName = md5( random_bytes( 100 ) ) . '.' . $photo->guessExtension() ;

                dump($newFileName);

            } while( file_exists( $this->getParameter( 'app.user.photo.directory' ) . $newFileName ) );

            // Sauvegarde du nom de la photo dans l'utilisateur connécter
            $this->getUser()->setPhoto( $newFileName );

            $em = $doctrine->getManager();

            $em->flush();

            // Déplacemelent physic de l'image dans le dossier paramétrer dans service.yaml
            $photo->move(
                $this->getParameter( 'app.user.photo.directory'),
                $newFileName
            );


            // Message flash de succès
            $this->addFlash('success', 'Photo de profil modifier avec succès !');

            // Redirection vers la page de profil
            return $this->redirectToRoute('main_profil');
        }

        return $this->render('main/edit_photo.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
