# P01 — XSS Stocké · Plateforme Universitaire UniPortail

**EMINES UM6P · Benguerir · 2026 — Red Team**

> ⚠️ Application volontairement vulnérable — usage pédagogique uniquement.
> Voir [DISCLAIMER.md](DISCLAIMER.md).

---

## 🏗️ Architecture

```
p01-xss-universite/
├── app/                  ← Application cible (PHP 8.2 + Apache)
│   ├── index.php         ← Page de connexion
│   ├── dashboard.php     ← Tableau de bord (rôle-dépendant)
│   ├── notes.php         ← Relevé de notes
│   ├── emploi_du_temps.php
│   ├── messagerie.php    
│   ├── admin.php         ← Panneau admin
│   ├── config.php        ← Connexion BDD
│   └── layout.php        ← Template HTML commun
├── db/
│   └── init.sql          ← Schéma + données de test
├── attacker/
│   └── cookie_stealer.py    ← Interface C2 + collecteur de cookies
├── docker-compose.yml
└── DISCLAIMER.md
```

## 👥 Hiérarchie des rôles

| Rôle | Accès |
|------|-------|
| **Administrateur** | Tableau de bord global, gestion des utilisateurs, toutes les notes, messagerie |
| **Professeur** | Notes de tous les étudiants, emploi du temps, messagerie |
| **Étudiant** | Ses propres notes, emploi du temps, messagerie |

---

## 🚀 Lancement 

### Prérequis

- Docker Desktop installé et démarré
- Ports libres : `8080` (app), `8888` (attaquant), `3306` (MySQL)

### Commandes

```bash
# 1. Cloner / décompresser le projet
cd p01-xss-universite

# 2. Construire et lancer tous les conteneurs
docker compose up --build -d

# 3. Attendre ~15 secondes que MySQL démarre, puis ouvrir :
#    Application :  http://localhost:8080
#    Attaquant C2 : http://localhost:8888
```

### Arrêt

```bash
docker compose down          # Arrêter
docker compose down -v       # Arrêter + supprimer les données
```

---

## 🔑 Comptes de test

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Administrateur | admin@uni.ma | Admin@2026 |
| Professeur 1 | ybenali@uni.ma | Prof@2026 |
| Professeur 2 | souali@uni.ma | Prof@2026 |
| Étudiant 1 | kalami@uni.ma | Etud@2026 |
| Étudiant 2 | fchraibi@uni.ma | Etud@2026 |
| Étudiant 3 | oidrissi@uni.ma | Etud@2026 |

---

