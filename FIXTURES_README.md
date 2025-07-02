# Fixtures FlexOffice

Ce fichier documente les données de test créées par les fixtures pour l'application FlexOffice.

## Comptes Utilisateurs

Tous les utilisateurs ont le mot de passe : **12345678**

### Admin
- **Email** : admin@flexoffice.com
- **Nom** : Admin FlexOffice
- **Rôle** : ROLE_ADMIN
- **Accès** : Toutes les fonctionnalités d'administration

### Hosts (Propriétaires d'espaces)
1. **Marie Dupont**
   - **Email** : host@flexoffice.com
   - **Rôle** : ROLE_HOST
   - **Espaces gérés** : Tech Hub Paris Centre, Business Center Champs-Élysées, Mediterranean Workspace

2. **Pierre Martin**
   - **Email** : host2@flexoffice.com
   - **Rôle** : ROLE_HOST
   - **Espaces gérés** : Creative Space Saint-Germain, Innovation Lab Lyon

### Guests (Utilisateurs)
1. **Jean Durand**
   - **Email** : guest@flexoffice.com
   - **Rôle** : ROLE_GUEST

2. **Sophie Bernard**
   - **Email** : guest2@flexoffice.com
   - **Rôle** : ROLE_GUEST

3. **Thomas Petit**
   - **Email** : guest3@flexoffice.com
   - **Rôle** : ROLE_GUEST

## Espaces de Travail

### 1. Tech Hub Paris Centre
- **Adresse** : 123 Rue de Rivoli, 75001 Paris
- **Host** : Marie Dupont
- **Disponibilité** : Lundi à Vendredi
- **Bureaux** : 4 (Standard, Privé, Salle de réunion)

### 2. Business Center Champs-Élysées
- **Adresse** : 45 Avenue des Champs-Élysées, 75008 Paris
- **Host** : Marie Dupont
- **Disponibilité** : Lundi à Samedi
- **Bureaux** : 4 (Executive, Suite privée, Salle de conférence)

### 3. Creative Space Saint-Germain
- **Adresse** : 78 Boulevard Saint-Germain, 75006 Paris
- **Host** : Pierre Martin
- **Disponibilité** : Lundi à Vendredi
- **Bureaux** : 3 (Créatifs, Studio design)

### 4. Innovation Lab Lyon
- **Adresse** : 12 Rue de la République, 69002 Lyon
- **Host** : Pierre Martin
- **Disponibilité** : 7j/7
- **Bureaux** : 4 (Lab stations, Pod innovation, Hub collaboration)

### 5. Mediterranean Workspace
- **Adresse** : 34 La Canebière, 13001 Marseille
- **Host** : Marie Dupont
- **Disponibilité** : Lundi à Vendredi
- **Bureaux** : 2 (Vue mer, Bureau terrasse)

## Types de Bureaux

- **Standard Desk** (Type 0) : Bureau standard pour 1 personne
- **Private Office** (Type 1) : Bureau privé pour 2-4 personnes
- **Meeting Room** (Type 2) : Salle de réunion pour 6-8 personnes
- **Conference Room** (Type 3) : Salle de conférence pour 12+ personnes

## Équipements Disponibles

1. Écran 24" - Écran externe Full HD
2. Clavier mécanique - Clavier ergonomique
3. Souris sans fil - Souris optique
4. Webcam HD - Pour visioconférences
5. Casque audio - Avec microphone intégré
6. Station d'accueil - USB-C pour portables
7. Imprimante - Laser couleur partagée
8. Tableau blanc - Magnétique avec marqueurs
9. Projecteur - Full HD pour présentations
10. Système audio - Pour salles de réunion

## Réservations de Test

Les fixtures créent plusieurs réservations d'exemple :
- **Réservations futures** : Pour tester les fonctionnalités de réservation
- **Réservations passées** : Pour l'historique
- **Différents statuts** : Confirmées, en attente, annulées

## Commandes Utiles

```bash
# Charger les fixtures (supprime les données existantes)
symfony console doctrine:fixtures:load

# Charger les fixtures sans confirmation
symfony console doctrine:fixtures:load --no-interaction

# Ajouter des fixtures sans supprimer les données existantes
symfony console doctrine:fixtures:load --append
```

## Structure des Prix

- **Tech Hub Paris** : 35€/jour (standard), 85€/jour (privé), 120€/jour (réunion)
- **Business Center** : 65€/jour (executive), 150€/jour (suite), 250€/jour (conférence)
- **Creative Space** : 40€/jour (créatif), 95€/jour (studio)
- **Innovation Lab** : 30€/jour (lab), 75€/jour (pod), 100€/jour (collaboration)
- **Mediterranean** : 45€/jour (vue mer), 90€/jour (terrasse)
