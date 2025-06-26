# Guide de Développement FlexOffice

## Démarrage Rapide

### 1. Installation et Configuration
```bash
# Cloner le projet
git clone <repository-url>
cd FlexOffice

# Démarrer l'environnement Docker
docker-compose up -d

# Installer les dépendances
make composer-install
make npm-install

# Configurer la base de données
make db-migrate
make db-load-fixtures

# Vérifier l'installation
make db-check-fixtures
```

### 2. Accès à l'Application
- **Application** : http://localhost:8080
- **PHPMyAdmin** : http://localhost:8899 (root/pwd)
- **MailHog** : http://localhost:8025

### 3. Comptes de Test
Tous les comptes utilisent le mot de passe : **12345678**

| Rôle | Email | Nom | Fonctionnalités |
|------|-------|-----|-----------------|
| Admin | admin@flexoffice.com | Admin FlexOffice | Toutes les fonctionnalités |
| Host | host@flexoffice.com | Marie Dupont | Gestion d'espaces |
| Host | host2@flexoffice.com | Pierre Martin | Gestion d'espaces |
| Guest | guest@flexoffice.com | Jean Durand | Réservations |
| Guest | guest2@flexoffice.com | Sophie Bernard | Réservations |
| Guest | guest3@flexoffice.com | Thomas Petit | Réservations |

## Structure des Données

### Espaces Disponibles
1. **Tech Hub Paris Centre** (Paris) - 4 bureaux
2. **Business Center Champs-Élysées** (Paris) - 4 bureaux
3. **Creative Space Saint-Germain** (Paris) - 3 bureaux
4. **Innovation Lab Lyon** (Lyon) - 4 bureaux
5. **Mediterranean Workspace** (Marseille) - 2 bureaux

### Types de Bureaux
- **Standard Desk** : Bureau individuel (35-65€/jour)
- **Private Office** : Bureau privé 2-4 personnes (75-150€/jour)
- **Meeting Room** : Salle de réunion 6-8 personnes (100-120€/jour)
- **Conference Room** : Salle de conférence 12+ personnes (250€/jour)

### Équipements
- Écrans, claviers, souris
- Webcams et casques audio
- Stations d'accueil
- Imprimantes partagées
- Tableaux blancs
- Projecteurs et systèmes audio

## Commandes de Développement

### Base de Données
```bash
# Migrations
make db-make-migration    # Créer une migration
make db-migrate          # Appliquer les migrations
make db-diff            # Générer diff automatique

# Fixtures
make db-load-fixtures    # Charger les données de test
make db-check-fixtures   # Vérifier les données

# Reset complet
make db-drop            # Supprimer la base
make db-migrate         # Recréer la structure
make db-load-fixtures   # Recharger les données
```

### Cache et Assets
```bash
make cache-clear        # Vider le cache Symfony
make npm-build         # Compiler les assets
```

### Développement
```bash
# Commandes Symfony génériques
make sf cmd="debug:router"
make sf cmd="make:controller"
make sf cmd="make:form"

# Logs
docker-compose logs -f php
```

## Tests et Validation

### Scénarios de Test Recommandés

1. **Connexion et Rôles**
   - Tester la connexion avec chaque type de compte
   - Vérifier les restrictions d'accès par rôle

2. **Gestion des Espaces (Host)**
   - Créer un nouvel espace
   - Ajouter des bureaux à un espace
   - Modifier la disponibilité

3. **Réservations (Guest)**
   - Parcourir les espaces disponibles
   - Réserver un bureau pour une date future
   - Consulter l'historique des réservations

4. **Administration (Admin)**
   - Accéder au dashboard admin
   - Gérer tous les espaces et utilisateurs

### Données de Test Disponibles
- **10 réservations** avec différents statuts
- **Réservations passées et futures** pour tester l'historique
- **Espaces dans différentes villes** pour tester la géolocalisation
- **Variété d'équipements** pour tester les filtres

## Dépannage

### Problèmes Courants

1. **Erreur de base de données**
   ```bash
   make db-drop
   make db-migrate
   make db-load-fixtures
   ```

2. **Problème de cache**
   ```bash
   make cache-clear
   ```

3. **Assets non compilés**
   ```bash
   make npm-build
   ```

4. **Permissions Docker**
   ```bash
   sudo chown -R $USER:$USER .
   ```

### Vérification de l'État
```bash
# Vérifier les conteneurs
docker-compose ps

# Vérifier les données
make db-check-fixtures

# Vérifier les logs
docker-compose logs php
```

## Contribution

### Workflow de Développement
1. Créer une branche feature
2. Développer et tester localement
3. Ajouter/modifier les fixtures si nécessaire
4. Tester avec les comptes de test
5. Créer une pull request

### Bonnes Pratiques
- Utiliser les fixtures pour tester les nouvelles fonctionnalités
- Maintenir la cohérence des données de test
- Documenter les nouveaux comptes ou données ajoutés
- Tester avec différents rôles utilisateur
