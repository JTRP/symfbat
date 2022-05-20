# Projet Le Blog de Batman

## Installation

```
git clone https://github.com/JTRP/symfbat.git
```

### Modifier les paramètre d'environment dans le fichier .env pour les faire correspondre à votre environnement ( Accès base de donnée, clés Google Recaptcha, ect... )

```
#Accès base de donnée à modifier
DATABASE_URL="mysql://xxxx:xxxx@127.0.0.1:3306/xxxx?serverVersion=xxxx&charset=utf8mb4"

# Clés Google Recaptcha à modifier
GOOGLE_RECAPTCHA_SITE_KEY=XXXXXXXXXXXXXXXXXXXXXXXXX
GOOGLE_RECAPTCHA_PRIVATE_KEY=XXXXXXXXXXXXXXXXXXXXXXXXX
```

### Déplacer le terminal dans le dossier cloné du projet

```
cd symbat
```

### Taper les commandes suivantes :

```
composer insall
symfony console doctrine:database:create
symfony console make:migration
symfony console make:migrations:migrate
symfony console doctrine:fixtures:load
symfony console assets:install public
```

Les fixtures créeront :
* Un compte admin ( email : a@a.a, Mot de passe : test )
* 10 comptes utilisateurs ( email aléatoire, mot de passe : test )
* 200 articles
* Entre 0 et 18 commentaires par articles
### Démarrer le serveur Symfony :

```
symfony serve
```
