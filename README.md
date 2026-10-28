# InnovShop

Plateforme e-commerce de produits technologiques, développée avec **Symfony 8** dans le cadre d'un projet de formation.

- **Site en ligne :** _à compléter après déploiement_
- **Tableau Kanban :** _lien Trello à compléter_

## Fonctionnalités

**Front-office**
- Accueil : 3 derniers produits ajoutés et 3 produits à la une
- Catalogue avec recherche, filtre par catégorie, fourchette de prix et tri
- Fiche produit : galerie d'images, caractéristiques, choix d'une option (couleur, taille…)
- Panier en session, mis à jour en AJAX (une ligne par ajout, sans gestion de quantité)
- Inscription, connexion (« se souvenir de moi »), déconnexion
- Commande en 3 étapes (panier → livraison → confirmation), réservée aux utilisateurs connectés
- Email récapitulatif après la commande, email à chaque changement de statut
- Espace client : tableau de bord avec objectif de dépenses mensuel, historique et suivi des commandes, facture PDF, modification du profil, de l'adresse et du mot de passe
- Pages d'erreur 404 / 403 personnalisées

**Back-office (EasyAdmin, `/admin`, réservé à `ROLE_ADMIN`)**
- Tableau de bord : chiffre d'affaires sur 14 jours, commandes en attente, dernières commandes
- Gestion des produits (options, images), des catégories
- Gestion des commandes : liste filtrable, détail, changement de statut
- Gestion des clients : coordonnées, rôle administrateur, historique des commandes

## Stack technique

| Couche | Outils |
| --- | --- |
| Back-end | PHP 8.4+, Symfony 8.1, Doctrine ORM, MySQL / MariaDB |
| Front-end | Twig, Bootstrap 5, JavaScript (fetch), AssetMapper |
| Administration | EasyAdmin 5 |
| Emails / PDF | Symfony Mailer, Dompdf |

## Installation en local

Prérequis : PHP 8.4 ou plus, Composer, Symfony CLI, MySQL ou MariaDB (XAMPP, MAMP…).

```bash
# 1. Récupérer le projet
git clone https://github.com/Mberben/innovshop.git
cd innovshop

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données dans un fichier .env.local (non versionné)
echo 'DATABASE_URL="mysql://root:@127.0.0.1:3306/innovshop?serverVersion=10.4.28-MariaDB&charset=utf8mb4"' > .env.local

# 4. Créer la base, les tables et les données de démonstration
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# 5. Lancer le serveur
symfony server:start
```

Adapter `DATABASE_URL` à votre configuration (utilisateur, mot de passe, version : `serverVersion=8.0` pour MySQL 8).

### Comptes de démonstration

| Email | Mot de passe | Rôle |
| --- | --- | --- |
| admin@innovshop.fr | Admin1234! | Administrateur (accès `/admin`) |
| client@innovshop.fr | Client1234! | Client avec commandes |

### Emails en local

Par défaut, `MAILER_DSN=null://null` : aucun email ne part réellement. Les emails envoyés sont visibles dans le profiler Symfony (`/_profiler`, requête concernée, onglet **E-mails**).

## Déploiement en production

```bash
git clone https://github.com/Mberben/innovshop.git && cd innovshop

# .env.local de production (jamais versionné)
APP_ENV=prod
APP_SECRET=une_longue_chaine_aleatoire
DATABASE_URL="mysql://utilisateur:motdepasse@hote:3306/base?serverVersion=10.11-MariaDB&charset=utf8mb4"
MAILER_DSN=smtp://utilisateur:motdepasse@serveur-smtp:587

composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction   # données de démo (optionnel)
php bin/console importmap:install
php bin/console asset-map:compile
php bin/console cache:clear
```

La racine web du serveur doit pointer vers le dossier `public/`.

## Organisation du code

| Dossier | Contenu |
| --- | --- |
| `src/Entity` | Entités Doctrine et règles de validation |
| `src/Enum` | Statuts de commande |
| `src/Controller` | Contrôleurs du site ; `Admin/` pour EasyAdmin |
| `src/Service` | `CartService` : logique du panier |
| `src/Mailer` | Emails de commande |
| `src/EventListener` | Email automatique au changement de statut |
| `src/Security/Voter` | Accès aux commandes réservé à leur propriétaire |
| `src/Form` | Formulaires |
| `src/Twig` | Filtre `price`, fonction `cart_count()` |
| `src/DataFixtures` | Données de démonstration |
| `templates/` | Vues Twig |
| `assets/` | CSS et JavaScript (panier AJAX, galerie) |

## Bonnes pratiques mises en œuvre

- Logique métier dans des services (`CartService`, `OrderMailer`), contrôleurs courts
- Validation des données par attributs `Assert` sur les entités
- Secrets dans `.env.local`, jamais versionnés
- Configuration par attributs PHP (`#[Route]`, `#[IsGranted]`, `#[ORM\...]`)
- Voter pour les droits liés à un objet, enum PHP pour les statuts
- Prix stockés en centimes (entiers) pour des calculs exacts
- Protection CSRF, mots de passe hashés, échappement automatique Twig
