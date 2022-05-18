<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\NewArticleFormType;
use App\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function publicationList(ArticleRepository $articleRepository): Response
    {

        $articles = $articleRepository->findAll();



        return $this->render('blog/publication_list.html.twig', [
            'articles' => $articles
        ]);
    }



}
