```
 ___  _     _ _ _  ___  _ _ _  __  ___ 
/ __|| |__ (_) | |/ __|| | | |/ _|| _ \
\__ \| / /| | | |\__ \| | | | |_||  _/
|___/|_\_\|_|_|_||___/|_____|\__||_|   
```

# SkillSwap

**Le site qui connecte les étudiants par leurs compétences**

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://php.net)
[![Symfony](https://img.shields.io/badge/Symfony-7.x-000000?logo=symfony&logoColor=white)](https://symfony.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

SkillSwap est une plateforme web permettant aux étudiants de s'échanger des compétences. Vous savez coder en Python ? Vous voulez apprendre Figma ? Trouvez le bon partenaire, planifiez une session, progressez ensemble et montez dans le classement.

---

## 📋 Sommaire

- [Fonctionnalités](#-fonctionnalités)
- [Stack technique](#-stack-technique)
- [Installation](#-installation)
- [Structure du projet](#-structure-du-projet)
- [API REST](#-api-rest)
- [Design System](#-design-system)
- [Guide Git](#-guide-git)
- [Déploiement VPS](#-déploiement-vps)
- [Contributeurs](#-contributeurs)

---

## ✨ Fonctionnalités

| Module | Description |
|--------|-------------|
| **Profil** | Compétences à enseigner / apprendre, disponibilités, bio |
| **Matching** | Algorithme de compatibilité (compétence 50% + créneaux 30% + réputation 20%) |
| **Sessions** | Cours rapide, Atelier, Club — avec confirmation, évaluation et avis |
| **Gamification** | Points, niveaux (Novice → Légende), badges, classement |
| **Feed** | Posts, likes, commentaires — réseau social communautaire |
| **API REST** | Architecture API-first pour future app mobile |

---

## 🛠 Stack technique

- **Backend** : PHP 8.2+ / Symfony 7 / Doctrine ORM
- **Base de données** : MySQL 8.0+
- **Authentification** : JWT (lexik/jwt-authentication-bundle) + sessions Symfony
- **Frontend** : HTML5, CSS3 (variables customs, Flexbox/Grid), JavaScript vanilla (Fetch API)
- **Fonts** : Plus Jakarta Sans + Inter (Google Fonts)

---

## 🚀 Installation

### Prérequis

- PHP 8.2+ avec extensions : `pdo_mysql`, `intl`, `openssl`, `mbstring`
- MySQL 8.0+
- Composer 2.x
- Git
- Symfony CLI (recommandé) : https://symfony.com/download

### Étapes

```bash
# 1. Cloner le dépôt
git clone https://github.com/zerkor/skillswapp.git
cd skillswapp

# 2. Installer les dépendances PHP
composer install

# 3. Configurer l'environnement
cp .env.example .env
# Éditez .env et renseignez :
# - DATABASE_URL avec vos identifiants MySQL
# - APP_SECRET (chaîne aléatoire de 32+ caractères)
# - JWT_PASSPHRASE (phrase secrète pour les clés JWT)

# 4. Générer les clés JWT
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:VOTRE_JWT_PASSPHRASE
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:VOTRE_JWT_PASSPHRASE

# 5. Créer la base de données
php bin/console doctrine:database:create

# 6. Exécuter les migrations
php bin/console doctrine:migrations:migrate

# 7. (Optionnel) Charger les données de démonstration
php bin/console doctrine:fixtures:load

# 8. Démarrer le serveur de développement
symfony serve
# Ou : php -S localhost:8000 -t public/

# L'application est accessible sur http://localhost:8000
```

### Variables d'environnement

| Variable | Description | Exemple |
|----------|-------------|---------|
| `APP_SECRET` | Secret Symfony (32+ chars) | `a3f8b2c1...` |
| `DATABASE_URL` | DSN MySQL | `mysql://user:pass@127.0.0.1:3306/skillswap?serverVersion=8.0` |
| `JWT_SECRET_KEY` | Chemin clé privée JWT | `%kernel.project_dir%/config/jwt/private.pem` |
| `JWT_PUBLIC_KEY` | Chemin clé publique JWT | `%kernel.project_dir%/config/jwt/public.pem` |
| `JWT_PASSPHRASE` | Passphrase clé JWT | `ma_phrase_secrete` |
| `JWT_TTL` | Durée de vie du token (secondes) | `86400` |
| `ALLOWED_EMAIL_DOMAIN` | Domaine email autorisé (vide = tous) | `univ-lyon.fr` |
| `MAILER_DSN` | DSN Mailer Symfony | `smtp://user:pass@smtp.mailtrap.io:587` |

---

## 📁 Structure du projet

```
skillswap/
├── config/                    # Configuration Symfony
│   ├── packages/              # Config bundles (doctrine, security, jwt...)
│   ├── jwt/                   # Clés JWT (non versionnées)
│   └── services.yaml          # Injection de dépendances
├── migrations/                # Migrations Doctrine
├── public/                    # Webroot
│   ├── index.php              # Point d'entrée
│   ├── css/                   # Design system CSS
│   │   ├── variables.css      # Variables CSS du design system
│   │   ├── reset.css          # Reset + base styles
│   │   ├── components.css     # Composants UI réutilisables
│   │   ├── layout.css         # Nav, footer, container
│   │   └── pages/             # CSS spécifique par page
│   ├── js/                    # JavaScript modules
│   │   ├── api.js             # Fetch wrapper + JWT
│   │   ├── auth.js            # Login/logout
│   │   ├── search.js          # Recherche et matching
│   │   ├── profile.js         # Profil et upload
│   │   ├── session.js         # Gestion sessions
│   │   └── feed.js            # Feed social
│   └── assets/images/         # Images statiques
├── src/
│   ├── Controller/
│   │   ├── Api/               # Controllers REST JSON (API-first)
│   │   └── Web/               # Controllers Twig (rendu HTML)
│   ├── Entity/                # Entités Doctrine
│   ├── Repository/            # Repositories Doctrine
│   ├── Service/               # Logique métier
│   │   ├── MatchingService    # Algorithme de compatibilité
│   │   ├── GamificationService # Points, niveaux, badges
│   │   └── MailService        # Emails transactionnels
│   ├── EventListener/         # Listeners (JWT, Security headers)
│   └── DataFixtures/          # Données de démonstration
├── templates/                 # Templates Twig
│   ├── base.html.twig         # Layout global
│   ├── home/
│   ├── auth/
│   ├── profile/
│   ├── search/
│   ├── session/
│   ├── feed/
│   └── leaderboard/
├── .env.example               # Template variables d'environnement
├── .gitignore
├── composer.json
└── README.md
```

---

## 🔌 API REST

Toutes les réponses suivent ce format :

```json
// Succès
{ "success": true, "data": {...}, "message": "..." }

// Erreur
{ "success": false, "error": "message", "code": 400 }
```

### Authentification

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `POST` | `/api/auth/register` | Inscription | Public |
| `POST` | `/api/auth/login` | Connexion → `{token, user}` | Public |
| `GET` | `/api/auth/verify/{token}` | Vérification email | Public |

### Utilisateurs

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/users/{id}` | Profil utilisateur | JWT |
| `PUT` | `/api/users/{id}` | Modifier profil | JWT (owner) |
| `POST` | `/api/users/{id}/avatar` | Upload photo | JWT (owner) |

### Compétences & Disponibilités

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/skills/search?q=&level=&type=` | Recherche | JWT |
| `POST` | `/api/skills` | Ajouter compétence | JWT |
| `DELETE` | `/api/skills/{id}` | Supprimer | JWT (owner) |
| `GET` | `/api/users/{id}/availabilities` | Liste disponibilités | JWT |
| `POST` | `/api/availabilities` | Ajouter disponibilité | JWT |
| `DELETE` | `/api/availabilities/{id}` | Supprimer | JWT (owner) |

### Matching

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/matching?skill=&level=` | Trouver des matchs | JWT |

Retourne une liste triée par score de compatibilité (0-100).

### Sessions

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/sessions` | Mes sessions | JWT |
| `POST` | `/api/sessions` | Proposer session | JWT |
| `PUT` | `/api/sessions/{id}/confirm` | Confirmer | JWT (tuteur) |
| `PUT` | `/api/sessions/{id}/decline` | Refuser | JWT |
| `PUT` | `/api/sessions/{id}/complete` | Marquer complète | JWT |
| `PUT` | `/api/sessions/{id}/cancel` | Annuler | JWT |

### Avis

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `POST` | `/api/reviews` | Publier un avis | JWT |

### Gamification

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/badges` | Liste des badges | JWT |
| `GET` | `/api/users/{id}/badges` | Badges d'un utilisateur | JWT |
| `GET` | `/api/leaderboard?limit=10` | Classement | JWT |

### Feed

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| `GET` | `/api/feed?page=1` | Feed paginé | JWT |
| `POST` | `/api/posts` | Publier un post | JWT |
| `POST` | `/api/posts/{id}/like` | Like/Unlike | JWT |
| `POST` | `/api/posts/{id}/comments` | Commenter | JWT |
| `DELETE` | `/api/posts/{id}` | Supprimer | JWT (owner) |

---

## 🎨 Design System

### Couleurs principales

| Couleur | Variable | Usage |
|---------|----------|-------|
| ![#2E75B6](https://via.placeholder.com/12/2E75B6/2E75B6.png) | `--color-primary` | Boutons, liens actifs |
| ![#06B6D4](https://via.placeholder.com/12/06B6D4/06B6D4.png) | `--color-accent` | Highlights, tags |
| ![#8B5CF6](https://via.placeholder.com/12/8B5CF6/8B5CF6.png) | `--color-secondary` | Gamification, badges |
| ![#10B981](https://via.placeholder.com/12/10B981/10B981.png) | `--color-success` | Succès, sessions confirmées |
| ![#F59E0B](https://via.placeholder.com/12/F59E0B/F59E0B.png) | `--color-warning` | Sessions en attente |
| ![#EF4444](https://via.placeholder.com/12/EF4444/EF4444.png) | `--color-error` | Erreurs, annulations |

### Typographie

- **Titres** : Plus Jakarta Sans (400–800)
- **Corps** : Inter (400–600)

### Niveaux de gamification

| Niveau | Score | Badge CSS |
|--------|-------|-----------|
| Novice | 0 pts | `.badge-niveau.novice` |
| Apprenti | 101 pts | `.badge-niveau.apprenti` |
| Mentor | 301 pts | `.badge-niveau.mentor` |
| Expert | 701 pts | `.badge-niveau.expert` |
| Légende | 1500 pts | `.badge-niveau.legende` |

---

## 🌿 Guide Git

### Branches

```
main          # Production stable
develop       # Développement actif
feature/*     # Nouvelles fonctionnalités
fix/*         # Corrections de bugs
hotfix/*      # Corrections urgentes en production
```

### Commits conventionnels

```
feat:     Nouvelle fonctionnalité
fix:      Correction de bug
style:    Changements CSS/UI (sans logique)
refactor: Refactoring sans nouvelle feature
docs:     Documentation
test:     Tests
chore:    Maintenance, dépendances
```

Exemples :
```bash
git commit -m "feat: ajout système de notation des sessions"
git commit -m "fix: correction calcul score matching"
git commit -m "style: amélioration responsive page profil"
```

---

## 🖥 Déploiement VPS

### Prérequis serveur

```bash
apt update && apt install -y nginx php8.2-fpm php8.2-mysql php8.2-intl \
  php8.2-mbstring php8.2-openssl php8.2-zip composer git mysql-server certbot \
  python3-certbot-nginx
```

### Configuration Nginx

```nginx
server {
    listen 80;
    server_name skillswap.fr www.skillswap.fr;
    root /var/www/skillswap/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ /\.(ht|env|git) {
        deny all;
    }
}
```

### Déploiement

```bash
# Cloner sur le serveur
cd /var/www
git clone https://github.com/zerkor/skillswapp.git skillswap
cd skillswap

# Production
composer install --no-dev --optimize-autoloader
cp .env.example .env
# Configurer .env pour la production

# Générer les clés JWT
mkdir config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout

# Base de données
php bin/console doctrine:database:create --env=prod
php bin/console doctrine:migrations:migrate --env=prod

# Optimisations
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Permissions
chown -R www-data:www-data /var/www/skillswap
chmod -R 755 /var/www/skillswap

# SSL Let's Encrypt
certbot --nginx -d skillswap.fr -d www.skillswap.fr
```

---

## 👥 Contributeurs

- **[zerkor](https://github.com/zerkor)** — Fondateur & développeur principal

---

## 📄 Licence

MIT — voir [LICENSE](LICENSE)
