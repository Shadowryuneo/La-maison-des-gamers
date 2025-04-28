# Déploiement d'un projet Symfony 7

## Pré-requis
- _>= PHP 8.2_
- _CLI de Symfony_

## Installation des dossiers var et vendor
```
    composer install
```

## Créer le fichier .env.local
A la racine du projet, créer le fichier **.env.local** et y placer le code suivant :

```
    APP_ENV=dev
    APP_SECRET=
    DATABASE_URL="mysql://identifiant:password@127.0.0.1:port/databaseName?serverVersion=Number&charset=utf8mb4"
    MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
    MAILER_DSN=null://null
```

### Configurer la variable DATABASE_URL

et ensuite lancer la ligne de commande suivante :
_Attention n'oubliez pas d'allumer le serveur contenant PhpMyAdmin__
```
    symfony console doctrine:database:create
```

## Les tables

Il y a 2 possibilités :

- Importer le fichier **gaming.sql** dans la base de données (contenant des créations de table ainsi que des insertions de données)

- Lancer la ligne de commande
```
    symfony console doctrine:migrations:migrate
```

## Allumer le serveur de Symfony
```
    symfony serve
```


