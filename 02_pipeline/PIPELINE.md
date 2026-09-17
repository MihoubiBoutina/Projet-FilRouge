# 02_pipeline — Documentation du Pipeline CI/CD

Ce dossier contient la configuration et l'explication du pipeline d'Intégration et de Déploiement Continus (CI/CD) mis en œuvre avec **GitHub Actions**.

---

## 🛠️ Structure du Pipeline

Le workflow est découpé en deux jobs distincts et séquentiels :

### 1. Job `test` (Validation et Qualité du code)

- **Déclenchement :** Sur chaque `push` et `pull_request` vers `main` ou `develop`.
- **Services :** Démarrage automatisé d'un conteneur `mongo:7` sur le port `27017` pour permettre les tests d'intégration avec le composant ODM/NoSQL.
- **Contrôles effectués :**
  1. Validation de la cohérence de `composer.json`.
  2. Installation des dépendances PHP avec verrouillage de la version de l'extension MongoDB (`1.20.1`).
  3. Génération du schéma de base de données de test SQLite en mémoire.
  4. Exécution de la suite complète de 57 tests PHPUnit (tests unitaires avec Mocks et tests fonctionnels d'API).

### 2. Job `build-and-publish` (Conteneurisation et Déploiement Registry)

- **Déclenchement :** Uniquement après le succès du job `test` et sur la branche `main` ou lors du dépôt d'un tag de version (`v*`).
- **Actions :**
  1. Authentification sécurisée sur **GitHub Container Registry (GHCR)** via `GITHUB_TOKEN`.
  2. Construction de l'image Docker applicative multi-stage (PHP-FPM + extensions).
  3. Taggage automatique de l'image (nom du dépôt normalisé en minuscules et versionnage sémantique).
  4. Publication (push) de l'image conteneurisée sur GHCR (`ghcr.io/mihoubiboutina/projet-filrouge`).

---

## 🔧 Incident d'Infrastructure et Résolution (Focus Alpine Linux)

### Problème rencontré

Lors de l'intégration initiale dans le pipeline de build, le build Docker sur base `php:8.2-fpm-alpine` échouait systématiquement lors de l'installation de l'extension PHP `mongodb`. Les commandes standards basées sur `apt-get` échouaient en raison de l'absence du gestionnaire Debian/Ubuntu sur Alpine Linux. De plus, les dépendances de compilation C (`gcc`, `make`, `openssl-dev`) étaient absentes du système d'exploitation minimaliste Alpine.

### Solution technique appliquée

Le processus d'installation de l'extension PHP dans le conteneur a été adapté à l'écosystème Alpine Linux :

1. Utilisation de `apk` (gestionnaire de paquets Alpine) pour injecter les dépendances de compilation virtuelles (`.build-deps autoconf g++ make openssl-dev`).
2. Utilisation de `pecl install mongodb-1.20.1` pour compiler nativement l'extension PHP au sein de l'environnement Alpine.
3. Activation de l'extension via `docker-php-ext-enable mongodb`.
4. Nettoyage des paquets de compilation temporaires (`apk del .build-deps`) afin de conserver une image légère et durcie pour la production.
