# 🏫 MiamiSchool - Système de Gestion de Cantine Scolaire

## 📋 Description du Projet

MiamiSchool est une application web complète de gestion de cantine scolaire développée avec HTML, CSS, JavaScript, PHP et MySQL. Le système permet la gestion des repas, des commandes, des paiements et du suivi nutritionnel des élèves.

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
- Interface inspirée du style MiamiSchool (image fournie)
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

## 💾 Base de Données

### Tables Principales

1. **utilisateurs** : Gestion des comptes (admin, parents, élèves)
2. **allergies** : Liste des allergènes
3. **utilisateur_allergies** : Liaison utilisateurs-allergies
4. **plats** : Catalogue des plats disponibles
5. **menus** : Menus journaliers
6. **menu_plats** : Liaison menus-plats
7. **commandes** : Commandes passées
8. **commande_details** : Détails des plats commandés
9. **paiements** : Historique des transactions

### Relations
- Un utilisateur peut avoir plusieurs allergies
- Un menu contient plusieurs plats
- Une commande contient plusieurs plats
- Chaque transaction est liée à un utilisateur

## 🚀 Installation

### Prérequis
- Serveur web (Apache/Nginx)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- phpMyAdmin (optionnel)

### Étapes d'Installation

1. **Cloner ou télécharger le projet**
```bash
git clone [url-du-projet]
cd cantine_scolaire
```

2. **Configurer la base de données**
   - Créer une base de données MySQL nommée `cantine_scolaire`
   - Importer le fichier `database.sql`
   ```bash
   mysql -u root -p cantine_scolaire < database.sql
   ```

3. **Configurer la connexion**
   - Ouvrir `php/config.php`
   - Modifier les paramètres de connexion si nécessaire :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'cantine_scolaire');
   ```

4. **Démarrer le serveur**
   - Avec XAMPP/WAMP : Placer le projet dans htdocs/www
   - Avec serveur intégré PHP :
   ```bash
   php -S localhost:8000
   ```

5. **Accéder à l'application**
   - Ouvrir le navigateur : `http://localhost:8000/index.php`
   - Ou : `http://localhost/cantine_scolaire/index.php`

## 👤 Comptes de Test

### Administrateur
- **Email** : admin@miamischool.com
- **Mot de passe** : admin123

### Créer de Nouveaux Comptes
Utilisez la page d'inscription pour créer des comptes élèves/parents.

## 🔒 Sécurité

- ✅ Hachage des mots de passe avec `password_hash()`
- ✅ Requêtes préparées (PDO) contre les injections SQL
- ✅ Protection CSRF avec sessions
- ✅ Validation des données côté serveur
- ✅ Contrôle d'accès basé sur les rôles

## 🎨 Personnalisation

### Modifier les Couleurs
Dans `css/style.css`, modifiez les variables CSS :
```css
:root {
    --primary-color: #d4a528;      /* Couleur principale (or) */
    --secondary-color: #1a1a1a;    /* Couleur secondaire (noir) */
    --background-dark: #0d0d0d;    /* Fond sombre */
    --card-bg: #2a2412;            /* Fond des cartes */
}
```

### Ajouter des Allergies
Dans la base de données, table `allergies` :
```sql
INSERT INTO allergies (nom_allergie) VALUES ('Nouvelle allergie');
```

## 📱 Responsive Design

L'application est entièrement responsive et s'adapte aux écrans :
- 📱 Mobile (< 768px)
- 💻 Tablette (768px - 1024px)
- 🖥️ Desktop (> 1024px)

## 🔄 Workflow de Commande

1. **Élève/Parent** se connecte
2. Accède à "Menus & Commandes"
3. Sélectionne :
   - Une entrée
   - Un plat principal
   - Un ou plusieurs desserts (optionnel)
4. Choisit la date de livraison
5. Vérifie le récapitulatif et le total
6. Confirme la commande :
   - ✅ Si solde suffisant → Paiement automatique
   - ❌ Si solde insuffisant → Notification de recharge
7. Commande visible dans "Mes Commandes" avec statut "Payé"
8. Possibilité d'annuler (remboursement automatique)

## 💡 Fonctionnalités Avancées

### Gestion Intelligente du Solde
- Débit automatique lors de la commande
- Crédit automatique lors d'une annulation
- Vérification du solde avant validation

### Système de Notifications
- Alerte en cas de solde insuffisant
- Confirmation de commande réussie
- Notification de remboursement

### Statistiques en Temps Réel
- Mise à jour automatique des données
- Graphiques et tableaux dynamiques
- Top 10 des plats populaires

## 🐛 Dépannage

### Erreur de connexion à la base de données
- Vérifier les identifiants dans `php/config.php`
- S'assurer que MySQL est démarré
- Vérifier que la base de données existe

### Page blanche
- Activer l'affichage des erreurs PHP :
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```

### Problèmes de session
- Vérifier que `session_start()` est appelé
- Vérifier les permissions du dossier de sessions

## 📈 Améliorations Futures

- [ ] Intégration d'un système de paiement réel (Stripe, PayPal)
- [ ] Notifications par email
- [ ] Export des statistiques en PDF/Excel
- [ ] Application mobile (React Native)
- [ ] Système de réservation à l'avance
- [ ] Gestion des stocks de plats
- [ ] Photos des plats
- [ ] Avis et notes des plats
- [ ] Planification de menus hebdomadaires

## 👥 Contributeurs

Ce projet a été développé comme système de gestion de cantine scolaire complet.

## 📄 Licence

Ce projet est fourni à des fins éducatives et de démonstration.

## 📞 Support

Pour toute question ou problème, veuillez créer une issue sur le dépôt GitHub.

---

**Développé avec ❤️ pour MiamiSchool** 👑
