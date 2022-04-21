# Manuelle d'installation de l'application

## Prérequis :
**Vous devez avoir [Composer](https://getcomposer.org/download/), [Symfony CLI](https://symfony.com/download) et un serveur local [WampServer](https://www.wampserver.com/en/download-wampserver-64bits/) installés sur votre poste.**

## Procédure d'installation

1. **Démarer votre serveur local**

2. **Cloner le projet**

`git clone https://github.com/Gorgui2013/back-projet-edacy.git`

3. **Déplacer dans le projet**

`cd back-projet-edacy`

4. **Installer les dépendances**

`composer install`

5. **Ouvrer l'application sur un éditeur**

6. **Configuration de la base de données dans le fichier .env (ce fichier se trouve sur la racine du projet. Attention moi j'utilise le port 3308 et ma base de données est lemonde)**

```php
DATABASE_URL="mysql://username:password@127.0.0.1:3308/lemonde?serverVersion=5.7&charset=utf8mb4"
```

7. **Création de la base de données avec la commande suivante**

`symfony console doctrine:database:create`

8. **Migration de la base de données avec la commande suivante**

`symfony console doctrine:migrations:migrate`

9. **Démarage de l'application**

`symfony serve`
**ou**
`symfony server:start`
