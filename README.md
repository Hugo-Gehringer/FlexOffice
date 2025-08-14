# FlexOffice - Documentation des Fonctionnalités

[![codecov](https://codecov.io/gh/Hugo-Gehringer/FlexOffice/graph/badge.svg?token=GQMX4Q9DUU)](https://codecov.io/gh/Hugo-Gehringer/FlexOffice)

## Vue d'ensemble

FlexOffice est une application de gestion d'espaces de travail flexibles permettant aux utilisateurs de réserver des bureaux dans différents espaces. L'application propose différentes fonctionnalités selon le rôle de l'utilisateur.

## Architecture Technique

- **Framework**
  - Symfony 7.2
  - PHP 8.2+

- **Frontend**
  - Twig pour les templates
  - Tailwind CSS pour le style
  - Flowbite pour les composants UI

- **Base de Données**
  - Doctrine ORM
  - Migrations pour la gestion des schémas

- **Autres Fonctionnalités**
  - Système de notifications avec php-flasher/flasher-symfony


## Installation et Commandes

### Prérequis

- Docker et Docker Compose
- Git
- Make (optionnel, pour utiliser les commandes du Makefile)

### Installation

1. **Cloner le dépôt**

```bash
git clone <url-du-dépôt>
cd FlexOffice
```

2. **Démarrer les conteneurs Docker**

```bash
docker-compose up -d
```

3. **Installer les dépendances**

```bash
# Avec Make
make composer-install
make npm-install
make tailwind-build

# Sans Make
docker-compose exec app composer install
docker-compose exec app npm install
docker-compose exec app php bin/console tailwind:build
```

4. **Configurer la base de données**

```bash
# Avec Make
make db-migrate
make db-load-fixtures

# Sans Make
docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec app php bin/console doctrine:fixtures:load --no-interaction
```

5. **Compiler les assets**

```bash
# Avec Make
make asset-compile

# Sans Make
docker-compose exec app php bin/console asset-map:compile
```


### Accès à l'application

Après l'installation, l'application sera disponible aux adresses suivantes :

- Application web : http://localhost:8080
- PHPMyAdmin : http://localhost:8899 (Utilisateur : root, Mot de passe : pwd)

### Commandes de développement

#### Commandes Docker

```bash
# Démarrer les conteneurs
docker-compose up -d

# Arrêter les conteneurs
docker-compose down

# Voir les logs
docker-compose logs -f
```

#### Commandes Symfony

```bash
# Avec Make
make sf cmd="cache:clear"
make sf cmd="debug:router"
make sf cmd="make:entity"
make sf cmd="make:controller"
make sf cmd="make:migration"
make sf cmd="make:form"

# Sans Make
docker-compose exec app php bin/console cache:clear
docker-compose exec app php bin/console debug:router
docker-compose exec app php bin/console make:entity
docker-compose exec app php bin/console make:controller
docker-compose exec app php bin/console make:migration
docker-compose exec app php bin/console make:form
```

#### Commandes de base de données

```bash
# Créer une migration
make db-make-migration
# ou
docker-compose exec app php bin/console make:migration

# Exécuter les migrations
make db-migrate
# ou
docker-compose exec app php bin/console doctrine:migrations:migrate

# Générer un diff de migration
make db-diff
# ou
docker-compose exec app php bin/console doctrine:migrations:diff

# Charger les fixtures
make db-load-fixtures
# ou
docker-compose exec app php bin/console doctrine:fixtures:load

# Vérifier les fixtures chargées
make db-check-fixtures

```

#### Commandes de cache

```bash
# Vider le cache
make cache-clear
# ou
docker-compose exec app php bin/console cache:clear
```

#### Commandes Composer

```bash
# Installer les dépendances
make composer-install
# ou
docker-compose exec app composer install

# Mettre à jour les dépendances
make composer-update
# ou
docker-compose exec app composer update
```

#### Commandes d'assets

```bash
# Compiler les assets
docker-compose exec app php bin/console asset-map:compile

# Supprimer les assets compilés et recompiler
docker-compose exec app rm -rf public/assets && php bin/console asset-map:compile
```

## Rôles Utilisateurs

L'application dispose de trois rôles principaux :

1. **Invité (Guest)** - Peut réserver des espaces
2. **Hôte (Host)** - Peut créer et gérer des espaces
3. **Administrateur (Admin)** - A accès à toutes les pages et opérations CRUD

## Fonctionnalités par Rôle

### Invité (Guest)

Les utilisateurs avec le rôle "Guest" peuvent :

- **Consulter les espaces disponibles**
  - Voir la liste de tous les espaces disponibles
  - Consulter les détails d'un espace spécifique
  - Voir les bureaux disponibles dans chaque espace

- **Gérer les réservations**
  - Réserver un bureau disponible
  - Consulter leurs réservations actuelles
  - Annuler leurs réservations existantes
  - Voir l'historique de leurs réservations

- **Gérer leur profil**
  - Modifier leurs informations personnelles
  - Consulter leur tableau de bord personnel

### Hôte (Host)

Les utilisateurs avec le rôle "Host" disposent de toutes les fonctionnalités des invités, plus :

- **Créer et gérer des espaces**
  - Créer de nouveaux espaces de travail
  - Ajouter des informations détaillées (nom, description, adresse)
  - Modifier les informations des espaces existants
  - Consulter la liste de leurs espaces

- **Gérer les bureaux**
  - Ajouter des bureaux à leurs espaces
  - Définir les caractéristiques des bureaux (type, capacité, description)
  - Modifier les informations des bureaux existants
  - Supprimer des bureaux

- **Suivre les réservations**
  - Voir les réservations pour leurs espaces
  - Consulter les statistiques d'occupation

### Administrateur (Admin)

Les utilisateurs avec le rôle "Admin" ont accès à toutes les fonctionnalités de l'application, y compris :

- **Gestion complète des utilisateurs**
  - Voir tous les utilisateurs enregistrés
  - Modifier les informations des utilisateurs
  - Changer les rôles des utilisateurs
  - Désactiver/activer des comptes

- **Gestion complète des espaces**
  - Accéder à tous les espaces, quel que soit le propriétaire
  - Modifier ou supprimer n'importe quel espace
  - Approuver les nouveaux espaces (si nécessaire)

- **Gestion complète des réservations**
  - Voir toutes les réservations
  - Modifier ou annuler n'importe quelle réservation
  - Générer des rapports sur l'utilisation

- **Tableau de bord administrateur**
  - Voir les statistiques globales (nombre d'utilisateurs, d'espaces, de réservations)
  - Accéder aux journaux d'activité
  - Gérer les paramètres de l'application

### Système de Favoris

- **Gestion des favoris**
  - Les utilisateurs connectés peuvent ajouter/retirer des espaces de leurs favoris
  - Bouton cœur interactif sur les pages de listing et de détail des espaces
  - Page dédiée `/mes-favoris` pour consulter tous les espaces favoris
  - Pagination des favoris (10 éléments par page)
  - Interface AJAX pour une expérience fluide sans rechargement de page

- **Fonctionnalités techniques**
  - Entité `Favorite` pour gérer les relations utilisateur-espace
  - Service `FavoriteService` pour la logique métier
  - Extension Twig pour vérifier l'état des favoris dans les templates
  - Tests unitaires complets pour garantir la fiabilité

## Entités Principales

1. **User** - Informations sur les utilisateurs et leurs rôles
2. **Space** - Espaces de travail disponibles à la réservation
3. **Desk** - Bureaux individuels dans les espaces
4. **Address** - Adresses associées aux espaces
5. **Reservation** - Réservations des bureaux par les utilisateurs
6. **Favorite** - Relations de favoris entre utilisateurs et espaces

## Administration

L'application inclut un panneau d'administration complet accessible aux utilisateurs avec le rôle `ROLE_ADMIN`.

### Fonctionnalités d'Administration

- **Dashboard** : Vue d'ensemble avec statistiques
- **Gestion des Utilisateurs** : CRUD complet des utilisateurs
- **Gestion des Espaces** :
  - Vue d'ensemble avec statistiques (total espaces, bureaux, villes, hosts)
  - Création, modification et suppression d'espaces
  - Gestion des disponibilités et des adresses
  - Visualisation des détails (host, bureaux, réservations)
- **Gestion des Réservations** : Suivi et gestion de toutes les réservations

### Accès Administration

- **URL** : `/admin`
- **Compte de test** : `admin@flexoffice.com` / `12345678`
- **Pages disponibles** :
  - `/admin` - Dashboard principal
  - `/admin/users` - Gestion des utilisateurs
  - `/admin/spaces` - Gestion des espaces
  - `/admin/reservations` - Gestion des réservations