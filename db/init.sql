-- =============================================================
--  P01 · Plateforme Universitaire · Base de données
--  AVERTISSEMENT : application volontairement vulnérable (XSS)
--  Usage pédagogique uniquement — EMINES UM6P 2026
-- =============================================================

CREATE DATABASE IF NOT EXISTS universite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE universite;

-- ─── UTILISATEURS (hiérarchie : admin / professeur / etudiant) ───────────────
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(100)  NOT NULL,
    prenom        VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,
    role          ENUM('admin','professeur','etudiant') NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─── MESSAGES (champ `corps` NON ÉCHAPPÉ → XSS stocké) ──────────────────────
CREATE TABLE messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id   INT NOT NULL,
    destinataire_id INT NOT NULL,
    sujet           VARCHAR(255) NOT NULL,
    corps           TEXT NOT NULL,          -- ⚠️ affiché sans htmlspecialchars()
    lu              TINYINT(1) DEFAULT 0,
    date_envoi      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id)   REFERENCES users(id),
    FOREIGN KEY (destinataire_id) REFERENCES users(id)
);

-- ─── NOTES ──────────────────────────────────────────────────────────────────
CREATE TABLE notes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id  INT NOT NULL,
    matiere      VARCHAR(100) NOT NULL,
    note         DECIMAL(4,2) NOT NULL,
    semestre     VARCHAR(10)  NOT NULL,
    FOREIGN KEY (etudiant_id) REFERENCES users(id)
);

-- ─── EMPLOI DU TEMPS ────────────────────────────────────────────────────────
CREATE TABLE emploi_du_temps (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    matiere        VARCHAR(100) NOT NULL,
    professeur_id  INT NOT NULL,
    jour           ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi') NOT NULL,
    heure_debut    TIME NOT NULL,
    heure_fin      TIME NOT NULL,
    salle          VARCHAR(50)  NOT NULL,
    FOREIGN KEY (professeur_id) REFERENCES users(id)
);

-- ─── DONNÉES DE TEST ─────────────────────────────────────────────────────────

-- Comptes (mots de passe MD5 — intentionnellement faible)
INSERT INTO users (nom, prenom, email, password, role) VALUES
('Système',  'Admin',   'admin@uni.ma',    MD5('Admin@2026'),  'admin'),
('Benali',   'Youssef', 'ybenali@uni.ma',  MD5('Prof@2026'),   'professeur'),
('Ouali',    'Sara',    'souali@uni.ma',   MD5('Prof@2026'),   'professeur'),
('Alami',    'Karim',   'kalami@uni.ma',   MD5('Etud@2026'),   'etudiant'),
('Chraibi',  'Fatima',  'fchraibi@uni.ma', MD5('Etud@2026'),   'etudiant'),
('Idrissi',  'Omar',    'oidrissi@uni.ma', MD5('Etud@2026'),   'etudiant');

-- Notes semestrielles
INSERT INTO notes (etudiant_id, matiere, note, semestre) VALUES
(4, 'Mathématiques',  15.50, 'S1'),
(4, 'Informatique',   17.00, 'S1'),
(4, 'Physique',       13.00, 'S1'),
(4, 'Anglais',        14.50, 'S1'),
(5, 'Mathématiques',  14.00, 'S1'),
(5, 'Informatique',   16.50, 'S1'),
(5, 'Physique',       12.50, 'S1'),
(5, 'Anglais',        18.00, 'S1'),
(6, 'Mathématiques',  11.00, 'S1'),
(6, 'Informatique',   18.00, 'S1'),
(6, 'Physique',       15.00, 'S1'),
(6, 'Anglais',        13.50, 'S1');

-- Emploi du temps
INSERT INTO emploi_du_temps (matiere, professeur_id, jour, heure_debut, heure_fin, salle) VALUES
('Mathématiques',  2, 'Lundi',    '08:30', '10:30', 'A101'),
('Informatique',   3, 'Lundi',    '10:30', '12:30', 'B202'),
('Physique',       2, 'Mardi',    '14:00', '16:00', 'C303'),
('Anglais',        3, 'Mercredi', '08:30', '10:30', 'A102'),
('Mathématiques',  2, 'Jeudi',    '08:30', '10:30', 'A101'),
('Informatique',   3, 'Vendredi', '10:30', '12:30', 'B202'),
('Physique',       2, 'Vendredi', '14:00', '16:00', 'C303');

-- Messages de bienvenue (admin → étudiants)
INSERT INTO messages (expediteur_id, destinataire_id, sujet, corps) VALUES
(1, 4, 'Bienvenue sur UniPortail',
 'Bonjour Karim,\n\nBienvenue sur la plateforme universitaire UniPortail.\nConsultez vos notes, votre emploi du temps et votre messagerie depuis le menu.\n\nCordialement,\nAdministration'),
(1, 5, 'Bienvenue sur UniPortail',
 'Bonjour Fatima,\n\nBienvenue sur la plateforme universitaire UniPortail.\nConsultez vos notes, votre emploi du temps et votre messagerie depuis le menu.\n\nCordialement,\nAdministration'),
(1, 6, 'Bienvenue sur UniPortail',
 'Bonjour Omar,\n\nBienvenue sur la plateforme universitaire UniPortail.\nConsultez vos notes, votre emploi du temps et votre messagerie depuis le menu.\n\nCordialement,\nAdministration');
