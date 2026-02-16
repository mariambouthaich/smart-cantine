# 🏫 Smart cantine - Système de Gestion de Cantine Scolaire

## 📋 Description du Projet

 Smart cantine est une application web complète de gestion de cantine scolaire développée avec HTML, CSS, JavaScript, PHP et MySQL. Le système permet la gestion des repas, des commandes, des paiements et du suivi nutritionnel des élèves.

## ✨ Fonctionnalités Principales

### 🔐 Système d'Authentification
- **Trois types de comptes :**
  - **Administrateur** : Gestion complète du système
  - **Parent** : Gestion du compte de l'enfant
  - **Élève** : Commande de repas et suivi personnel
- Inscription avec sélection de classe (1ère à 6ème année)
- Connexion sécurisée avec hachage des mots de passe

### 👨‍🎓 Espace Élève/Parent

#### 📊 Tableau de Bord
- Vue d'ensemble du solde disponible
- Nombre de commandes en cours et historique
- Dépenses du mois en cours
- Accès rapide aux fonctionnalités principales

#### 🍽️ Commande de Repas
- **Sélection de plats par catégorie :**
  - Entrées (sélection unique)
  - Plats principaux (sélection unique)
  - Desserts (sélection multiple possible)
- Calcul automatique du montant total
- Récapitulatif en temps réel
- Sélection de la date de livraison

#### 📋 Gestion des Commandes
- Visualisation de toutes les commandes
- **Trois statuts de commande :**
  - 🟡 **En Attente** : Commande créée mais non payée
  - 🟢 **Payé** : Commande confirmée et payée
  - 🔴 **Annulé** : Commande annulée avec remboursement automatique
- Détails de chaque commande (plats, prix, date)
- **Annulation de commande :**
  - Possible uniquement pour les commandes "En Attente"
  - Remboursement automatique du montant sur le solde
  - Historique des remboursements conservé

#### 🏥 Suivi Nutritionnel
- Déclaration des allergies alimentaires
- Liste complète des allergènes disponibles :
  - Arachides, Fruits à coque, Lait, Œufs
  - Poisson, Crustacés, Soja, Blé/Gluten
  - Sésame, Moutarde
- Affichage visuel des allergies déclarées
- Conseils nutritionnels

#### 💰 Rechargement de Compte
- Ajout de crédit au compte (simulation)
- Montants prédéfinis : 100, 200, 300, 500, 1000 DH
- Limites : Min 50 DH, Max 5000 DH
- Historique des recharges

### 👨‍💼 Espace Administrateur

#### 📊 Dashboard Admin
- **Indicateurs de performance :**
  - Nombre total d'élèves inscrits
  - Revenus totaux de la cantine
  - Nombre de classes actives
  - Plats disponibles
- Top 10 des plats les plus commandés
- Liste des commandes récentes

#### 👥 Gestion des Élèves
- **Liste complète des élèves avec :**
  - ID, Nom, Prénom, Classe
  - Email, Type de compte
  - Solde actuel
  - Nombre d'allergies déclarées
- Statistiques par classe
- Consultation des allergies par élève

#### 🍽️ Gestion des Plats
- **Ajouter de nouveaux plats :**
  - Nom, Description
  - Type (Entrée/Plat Principal/Dessert)
  - Prix en DH
- **Modifier les plats existants :**
  - Mise à jour des informations
  - Activer/Désactiver la disponibilité
- **Supprimer des plats**
- Vue d'ensemble de tous les plats

#### 📈 Statistiques Détaillées
- **Analyse des plats commandés :**
  - Distribution par type de plat
  - Top 10 des plats les plus commandés avec nombre exact
  - Plats les moins commandés
- Données en temps réel

#### 🏥 Suivi Nutritionnel Admin
- **Statistiques des allergies :**
  - Nombre d'élèves allergiques par classe
  - Allergies les plus courantes
  - Liste complète des élèves avec allergies
- Consultation détaillée par élève

#### 💰 Gestion des Paiements
- **Vue d'ensemble financière :**
  - Total des transactions
  - Revenus de la cantine
  - Total des recharges
  - Montant net (revenus - remboursements)
- **Historique complet des transactions :**
  - Paiements (commandes)
  - Recharges de compte
  - Remboursements (annulations)
- Identification par élève avec classe et date

## 🛠️ Technologies Utilisées

### Frontend
- **HTML5** : Structure des pages
- **CSS3** : Stylisation avec variables CSS
- **JavaScript (Vanilla)** : Interactivité et manipulation du DOM

### Backend
- **PHP 7.4+** : Logique serveur
- **MySQL** : Base de données relationnelle
- **PDO** : Abstraction de base de données sécurisée

### Design
- Interface inspirée du style Smart cantine (image fournie)
- Palette de couleurs : Or (#d4a528), Noir (#1a1a1a)
- Design responsive et moderne
- Icônes emoji pour une meilleure UX

## 📁 Structure du Projet

```
cantine_scolaire/
│
├── index.php                          # Page de connexion
├── inscription.php                    # Page d'inscription
├── database.sql                       # Script de création de la BD
│
├── css/
│   └── style.css                      # Styles CSS principaux
│
├── js/
│   ├── login.js                       # Script pour la connexion
│   ├── inscription.js                 # Script pour l'inscription
│   └── menus.js                       # Script pour la sélection de plats
│
├── php/
│   ├── config.php                     # Configuration BD et sessions
│   ├── logout.php                     # Déconnexion
│   ├── get_commande_details.php      # API détails commande
│   └── get_allergies.php              # API allergies élève
│
├── Espace Élève/Parent:
│   ├── eleve_dashboard.php            # Dashboard élève
│   ├── menus.php                      # Commander des repas
│   ├── mes_commandes.php              # Historique commandes
│   ├── suivi_nutritionnel.php        # Gestion allergies
│   └── recharger_compte.php           # Rechargement du solde
│
└── Espace Admin:
    ├── admin_dashboard.php            # Dashboard admin
    ├── admin_eleves.php               # Gestion élèves
    ├── admin_plats.php                # Gestion plats
    ├── admin_statistiques.php         # Statistiques plats
    ├── admin_suivi_nutritionnel.php  # Suivi allergies
    └── admin_paiements.php            # Gestion paiements
```


## 🔒 Sécurité

- ✅ Hachage des mots de passe avec `password_hash()`
- ✅ Requêtes préparées (PDO) contre les injections SQL
- ✅ Protection CSRF avec sessions
- ✅ Validation des données côté serveur
- ✅ Contrôle d'accès basé sur les rôles


