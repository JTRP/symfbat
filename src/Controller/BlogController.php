<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\NewArticleFormType;
use App\Repository\ArticleRepository;
use Couchbase\RegexpSearchQuery;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController
{

    /**
     * Contrôleur de la page permettant de céer un nouvel article
     *
     * Accès reservé au administrateur
     */
    #[Route('/nouvelle-publication/', name: 'new_publication')]
    #[IsGranted('ROLE_ADMIN')]
    public function newPublication(Request $request , ArticleRepository $articleRepository, SluggerInterface $sluger) : Response
    {

        $article = new Article();

        $form = $this->createForm(NewArticleFormType::class, $article);

        $form->handleRequest($request);

        // Si le formulaire est envoyé et sans erreur
        if ( $form->isSubmitted() && $form->isValid() ) {

            // On termine d'hydrater l'article
            $article
                ->setPublicationDate( new \DateTime() )
                ->setAuthor( $this->getUser() )
                ->setSlug( $sluger->slug( $article->getTitle() )->lower() )
            ;

            // Sauvegarde de l'article en BDD via le manager général des entités de ArticleRepository
            $articleRepository->add( $article, true );

            // Message flash succès
            $this->addFlash('success', 'Article publié avec succès');

            // Redirection vers la page qui affiche l'article (en envoyant son id et sont slug dans l'url)
            return $this->redirectToRoute('blog_publication_view', [
                'id' => $article->getId(),
                'slug' => $article->getSlug()
            ]);

        }

        return $this->render('blog/new_publication.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * Contrôleur de la paage permettantr de voir l'article ene détail (via id et slug dans l'url)
     */
    #[Route('/publication/{id}/{slug}', name: 'publication_view')]
    #[ParamConverter('article', options: [ 'mapping' => [ 'id' => 'id', 'slug' => 'slug' ] ] )]
    public function publicationView( Article $article ) : Response
    {

        return $this->render('blog/publication_view.html.twig', [
            'article' => $article,
        ]);
    }


    /**
     * Contrôleur de la page qui liste les articles
     */
    #[Route('/publications/liste/', name: 'publication_list')]
    public function publicationList(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {

        // Récupération $_GET['page'], 1 si elle n'existe pas
        $requestedPage = $request->query->getInt('page', 1);

        // Vérification que le nombre est positif
        if ( $requestedPage < 1 ) {
            throw new NotFoundHttpException();
        }

        $em = $doctrine->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');

        $articles = $paginator->paginate(
            $query, // Requête crééé juste avant
            $requestedPage, // Page au'on souhaite voir
            10 // Nombre d'article à afficher par page
        );

        return $this->render('blog/publication_list.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * Contôleur de la page qdmin permettant de supprimer un article via son id dans l'url
     *
     * Accès résèrvé au administrateur (ROLE_ADMIN)
     */
    #[Route('/publication/suppression/{id}/', name: 'publicaton_delete', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function publicationDelete(Article $article, ArticleRepository $articleRepository, Request $request) : Response
    {

        $token = $request->query->get('token', '');

        if ( !$this->isCsrfTokenValid( 'blog_publication_delete_' . $article->getId(), $token ) ) {

            $this->addFlash('error', 'Token invalide');

        } else {

            // Suppréssion de l'article
            $articleRepository->remove($article, true);

            // Message flash de succès
            $this->addFlash('success', 'La publication a été supprimée avec succès !');

        }


        // Redirection vers la page qui liste les articles
        return $this->redirectToRoute('blog_publication_list');
    }


    /**
     * Contôleur de la page qdmin permettant de modifier un article via son id dans l'url
     *
     * Accès résèrvé au administrateur (ROLE_ADMIN)
     */
    #[Route('/publication/modifier/{id}/', name: 'publicaton_modify', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function publicationModify(Article $article, ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger) : Response
    {

        // Instanciation d'un nouveau formulaire basé sur $article qui contient déjà les donnée actuelles de l"article à modifier
        $form = $this->createForm(NewArticleFormType::class, $article);

        $form->handleRequest($request);

        // Si le formulaire est envoyé et sans erreur
        if ( $form->isSubmitted() && $form->isValid() ) {

            // Sauvegarde dans la BDD
            $article->setSlug( $slugger->slug( $article->getTitle()  )->lower() );
            $em = $doctrine->getManager();
            $em->flush();

            $this->addFlash('success', 'Publication modifier avec succès !');

            return $this->redirectToRoute('blog_publication_view', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ]);

        }



        // Redirection vers la page qui liste les articles
        return $this->render('blog/modify_article.html.twig', [
            'form' => $form->createView()
        ]);
    }




}
