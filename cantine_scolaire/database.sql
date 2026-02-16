-- Base de données pour la cantine scolaire MiamiSchool
CREATE DATABASE IF NOT EXISTS cantine_scolaire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cantine_scolaire;

-- Table des utilisateurs (admin, parents, élèves)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    type_compte ENUM('admin', 'parent', 'eleve') NOT NULL,
    classe ENUM('1ere', '2eme', '3eme', '4eme', '5eme', '6eme') NULL,
    solde DECIMAL(10,2) DEFAULT 0.00,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type_compte),
    INDEX idx_classe (classe)
);

-- Table des allergies
CREATE TABLE IF NOT EXISTS allergies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_allergie VARCHAR(100) NOT NULL UNIQUE
);

-- Table de liaison utilisateurs-allergies
CREATE TABLE IF NOT EXISTS utilisateur_allergies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    allergie_id INT NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (allergie_id) REFERENCES allergies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_allergie (utilisateur_id, allergie_id)
);

-- Table des plats
CREATE TABLE IF NOT EXISTS plats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    type_plat ENUM('entree', 'plat_principal', 'dessert') NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    disponible BOOLEAN DEFAULT TRUE,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type_plat)
);

-- Table des menus journaliers
CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_menu DATE NOT NULL UNIQUE,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table de liaison menus-plats
CREATE TABLE IF NOT EXISTS menu_plats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (plat_id) REFERENCES plats(id) ON DELETE CASCADE,
    UNIQUE KEY unique_menu_plat (menu_id, plat_id)
);

-- Table des commandes
CREATE TABLE IF NOT EXISTS commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    menu_id INT NOT NULL,
    montant_total DECIMAL(10,2) NOT NULL,
    statut ENUM('en_attente', 'paye', 'annule') DEFAULT 'en_attente',
    date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_livraison DATE NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_statut (statut),
    INDEX idx_date (date_livraison)
);

-- Table des détails de commande
CREATE TABLE IF NOT EXISTS commande_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    plat_id INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (plat_id) REFERENCES plats(id) ON DELETE CASCADE
);

-- Table des paiements
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    type_operation ENUM('recharge', 'paiement', 'remboursement') NOT NULL,
    commande_id INT NULL,
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE SET NULL,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_type (type_operation)
);

-- Insertion de données de test

-- Admin par défaut (mot de passe: admin123)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, type_compte) VALUES
('Admin', 'Principal', 'admin@miamischool.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Quelques allergies communes
INSERT INTO allergies (nom_allergie) VALUES
('Arachides'),
('Fruits à coque'),
('Lait'),
('Œufs'),
('Poisson'),
('Crustacés'),
('Soja'),
('Blé/Gluten'),
('Sésame'),
('Moutarde');

-- Exemples de plats
INSERT INTO plats (nom, description, type_plat, prix, disponible) VALUES
-- Entrées
('Salade Verte', 'Salade fraîche avec vinaigrette', 'entree', 15.00, TRUE),
('Carottes Râpées', 'Carottes fraîches assaisonnées', 'entree', 12.00, TRUE),
('Soupe du Jour', 'Soupe maison variée', 'entree', 18.00, TRUE),
('Salade de Tomates', 'Tomates fraîches avec huile d\'olive', 'entree', 15.00, TRUE),

-- Plats principaux
('Poulet Rôti', 'Poulet avec pommes de terre', 'plat_principal', 45.00, TRUE),
('Poisson Grillé', 'Poisson frais avec légumes', 'plat_principal', 50.00, TRUE),
('Couscous Royal', 'Couscous avec viandes et légumes', 'plat_principal', 55.00, TRUE),
('Tajine de Poulet', 'Tajine marocain traditionnel', 'plat_principal', 48.00, TRUE),
('Pizza Margherita', 'Pizza avec tomates et fromage', 'plat_principal', 40.00, TRUE),
('Spaghetti Bolognaise', 'Pâtes avec sauce à la viande', 'plat_principal', 42.00, TRUE),

-- Desserts
('Yaourt Nature', 'Yaourt frais', 'dessert', 10.00, TRUE),
('Fruit de Saison', 'Fruit frais du jour', 'dessert', 12.00, TRUE),
('Compote de Pommes', 'Compote maison', 'dessert', 10.00, TRUE),
('Gâteau au Chocolat', 'Gâteau fait maison', 'dessert', 15.00, TRUE),
('Crème Caramel', 'Dessert onctueux', 'dessert', 14.00, TRUE);

-- Créer des menus pour les 7 prochains jours
INSERT INTO menus (date_menu, actif) VALUES
(CURDATE(), TRUE),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), TRUE),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), TRUE),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), TRUE),
(DATE_ADD(CURDATE(), INTERVAL 4 DAY), TRUE),
(DATE_ADD(CURDATE(), INTERVAL 5 DAY), TRUE),
(DATE_ADD(CURDATE(), INTERVAL 6 DAY), TRUE);

-- Associer des plats aux menus (exemple pour aujourd'hui)
INSERT INTO menu_plats (menu_id, plat_id) 
SELECT 1, id FROM plats WHERE type_plat = 'entree' LIMIT 3;

INSERT INTO menu_plats (menu_id, plat_id) 
SELECT 1, id FROM plats WHERE type_plat = 'plat_principal' LIMIT 4;

INSERT INTO menu_plats (menu_id, plat_id) 
SELECT 1, id FROM plats WHERE type_plat = 'dessert' LIMIT 3;
