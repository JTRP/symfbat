<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Article;
use Faker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    /**
     * Stockage du service d'encodage des mot de passe Symfony
     */
    private $encoder;
    private $sluger;

    /**
     * On utilise le constructeur pour demander à Symfony de récupérer le service d'encodage des mot de passes, pour ensuite le stocker dans $this->encoder
     */
    public function __construct( UserPasswordHasherInterface $encoder, SluggerInterface $sluger )
    {
        $this->encoder = $encoder;
        $this->sluger = $sluger;
    }


    public function load(ObjectManager $manager): void
    {

        // Instanciation du Faker en langue fr
        $faker = Faker\Factory::create('fr_FR');

        // Création du compte admin Bat
        $admin = new User();

        $admin
            ->setEmail('a@a.a')
            ->setRegistrationDate( $faker->dateTimeBetween('-1 year', 'now') )
            ->setPseudonym('Bat')
            ->setRoles( ['ROLE_ADMIN'] )
            ->setPassword( $this->encoder->hashPassword($admin, 'test') )
            ;

        $manager->persist( $admin );

        // Création de 10 compte utilisateur (avec une boucle)
        for ( $i = 0; $i < 10; $i++ ) {

            $user = new User();

            $user
                ->setEmail($faker->email )
                ->setRegistrationDate( $faker->dateTimeBetween('-1 year', 'now') )
                ->setPseudonym( $faker->userName )
                ->setPassword( $this->encoder->hashPassword($user, 'test') )
            ;

            $manager->persist( $user );
        }

        // Création de 200 article (avec une boucle)
        for ( $i = 0; $i < 200; $i++ ) {

            $article = new Article();

            $article
                ->setTitle( $faker->sentence(10) )
                ->setContent( $faker->paragraph(15) )
                ->setPublicationDate( $faker->dateTimeBetween('-1 year', 'now') )
                ->setAuthor( $admin ) // Bat sera l'author de tous les article
                ->setSlug( $this->sluger->slug( $article->getTitle() )->lower() )
            ;

            $manager->persist( $article );
        }

        $manager->flush();
    }
}
