# 🎓 MyProf - Plateforme de Gestion des Ateliers

[![CI/CD](https://github.com/MihoubiBoutina/Projet-FilRouge/actions/workflows/ci.yml/badge.svg)](https://github.com/MihoubiBoutina/Projet-FilRouge/actions/workflows/ci.yml)

Une API REST robuste pour la gestion des ateliers de formation, des apprenants et des avis. Construite avec **Symfony 7**, **MySQL**, **MongoDB** et **Docker**.

---

## 📋 Table des matières

- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Démarrage](#-démarrage)
- [Endpoints API](#-endpoints-api)
- [Tests](#-tests)
- [CI/CD & Qualité du code](#-cicd--qualité-du-code)
- [Architecture](#-architecture)

---

## 🛠 Prérequis

Avant de commencer, assurez-vous d'avoir installé :

| Logiciel           | Version | Vérification               |
| ------------------ | ------- | -------------------------- |
| **Docker**         | 20.10+  | `docker --version`         |
| **Docker Compose** | 2.0+    | `docker-compose --version` |
| **PHP**            | 8.2+    | `php --version`            |
| **Composer**       | 2.5+    | `composer --version`       |
| **Git**            | 2.30+   | `git --version`            |

**Ressources minimales :**

- 2GB RAM
- 1GB d'espace disque libre
- Port 8000, 3306, 27017 disponibles

---

## 📥 Installation

### 1️⃣ Cloner le projet

```bash
git clone <votre-repo-url>
cd MyProf
```

### 2️⃣ Configurer les variables d'environnement

```bash
# Copier le fichier .env.example
cp .env .env.local
```

Vérifiez que `.env.local` contient :

```env
APP_ENV=dev
DATABASE_URL="mysql://root:root@mysql:3306/myprof"
MONGODB_URL="mongodb://root:root@mongodb:27017/myprof?authSource=admin"
```

### 3️⃣ Installer les dépendances

```bash
composer install
```

### 4️⃣ Construire et démarrer Docker

```bash
docker-compose up -d --build
```

Vérifiez que les containers sont actifs :

```bash
docker-compose ps
```

### 5️⃣ Initialiser la base de données

```bash
# Créer la base de données MySQL
symfony console doctrine:database:create

# Exécuter les migrations
symfony console doctrine:migrations:migrate -n

# Charger les fixtures (données de test)
symfony console doctrine:fixtures:load -n
```

✅ **Installation terminée en < 5 minutes !**

---

## 🚀 Démarrage

### Lancer le serveur de développement

```bash
symfony server:start
```

Ou directement avec Docker :

```bash
docker-compose up -d
```

✅ L'application est accessible sur : **http://127.0.0.1:8000**

### Arrêter l'application

```bash
symfony server:stop
# ou
docker-compose down
```

---

## 📡 Endpoints API

### 📚 Documentation Interactive

Accédez à **Swagger UI** pour une documentation interactive :

```
http://127.0.0.1:8000/api/doc
```

### 🎯 Ateliers

#### Lister tous les ateliers

```http
GET /api/ateliers
```

**Réponse :**

```json
{
    "ateliers": [
        {
            "id": 1,
            "titre": "Initiation à PHP",
            "description": "Une formation complète en PHP",
            "startAt": "2026-04-15T10:00:00+02:00",
            "dureeHeure": 8,
            "place": 20,
            "formateurId": 1
        }
    ]
}
```

#### Lister et rechercher les ateliers

```http
GET /api/ateliers?titre=PHP&duree=8&sort=titre
```

**Paramètres de recherche (optionnels) :**

- `titre` (string) - Filtrer par titre
- `duree` (integer) - Durée exacte en heures
- `sort` (string) - Tri des résultats (ex: `date`)

**Réponse (avec liens HATEOAS pour la navigation) :**

```json
{
    "ateliers": [
        {
            "id": 1,
            "titre": "Initiation à PHP",
            "description": "Une formation complète en PHP",
            "startAt": "2026-04-15T10:00:00+02:00",
            "dureeHeure": 8,
            "formateurId": 1,
            "_links": {
                "self": {
                    "href": "/api/ateliers/1"
                }
            }
        }
    ]
}
```

#### Voir le détail d'un atelier (et déclencher la traçabilité NoSQL)

```http
GET /api/ateliers/{id}
```

_Note : L'appel à cette route enregistre automatiquement la visite (IP, date) de façon asynchrone et rapide dans un document `LogVisite` via MongoDB._

---

### 👨‍🏫 Formateurs

#### Rechercher les formateurs

```http
GET /api/formateurs?nom=Dupont
```

**Paramètres :**

- `nom` (string) - Nom du formateur à rechercher (optionnel)

**Réponse :**

```json
[
    {
        "id": 1,
        "nom": "Dupont",
        "prenom": "Jean",
        "_links": {
            "avis": {
                "href": "/api/avis?formateurId=1"
            }
        }
    }
]
```

---

### ⭐ Avis

#### Lister tous les avis

```http
GET /api/avis
```

#### Filtrer les avis par note

```http
GET /api/avis?note=5
```

**Paramètres :**

- `note` (integer) - Filtrer par note (1-5)

**Réponse :**

```json
{
    "avis": [
        {
            "id": "507f1f77bcf86cd799439011",
            "commentaire": "Excellent atelier !",
            "note": 5,
            "createdAt": "2026-04-10T14:30:00+02:00",
            "apprenant": {
                "id": 1,
                "nom": "Martin",
                "prenom": "Alice"
            },
            "atelier": {
                "id": 1,
                "titre": "Initiation à PHP"
            }
        }
    ]
}
```

---

## 🧪 Tests

### Lancer tous les tests

```bash
symfony console phpunit
```

### Lancer les tests d'un fichier spécifique

```bash
symfony console phpunit tests/Controller/Api/AtelierApiControllerTest.php
```

### Exécuter les tests avec couverture de code

```bash
symfony console phpunit --coverage-html coverage/
```

Consultez le rapport HTML : `coverage/index.html`

### Types de tests implémentés

**📌 Tests Fonctionnels** (dans `tests/Controller/Api/`)

- Testent les endpoints API complètement avec la base de données
- Exemples : `AtelierApiControllerTest.php`, `AvisApiControllerTest.php`
- Vérifient les codes HTTP, les formats JSON, les filtres

**📌 Tests Unitaires** (dans `tests/Unit/`)

- Testent la logique métier isolée avec des **mocks**
- Exemple : `AtelierApiControllerUnitTest.php`
- Utilisent des mocks du repository pour éviter la base de données

**📌 Examples des Mocks** (dans `tests/Unit/Repository/`)

- Démonstration complète des patterns de mock
- Callbacks, exceptions, retours multiples, etc.

### Tester l'API avec Postman

1. Ouvrez **Postman**
2. Allez à **File** → **Import**
3. Entrez l'URL : `http://127.0.0.1:8000/api/doc.json`
4. Postman importera automatiquement toutes vos routes

### 📚 Documentation détaillée

Consultez **[TESTING.md](TESTING.md)** pour la documentation complète :

- Comment utiliser les mocks
- Templates de tests
- 15+ exemples pratiques
- Bonnes pratiques et patterns
- Intégration CI/CD

---

## 🔄 CI/CD & Qualité du code

### Workflow Git

Le développement suit un workflow basé sur les branches :

1. Créer une branche dédiée depuis `main` (`feature/...`, `fix/...` ou `docs/...`).
2. Développer et valider localement avec `vendor/bin/phpunit`.
3. Ouvrir une Pull Request vers `main`.
4. Attendre la revue de code et la réussite de la CI avant le merge.
5. Fusionner dans `main`, puis créer un tag annoté pour une version livrée.

### Contrôles automatisés

Le workflow [`.github/workflows/ci.yml`](../../.github/workflows/ci.yml) s'exécute sur chaque push vers `main` ou `develop`, ainsi que sur chaque Pull Request vers `main`. Il :

- installe les dépendances avec Composer et PHP 8.2 ;
- exécute la suite PHPUnit ;
- construit l'image Docker ;
- publie l'image dans GitHub Container Registry (`ghcr.io`) après un push hors Pull Request.

Le badge en haut de ce document reflète le statut du dernier pipeline GitHub Actions.

### Protection de la branche principale

> ⚠️ **Note d'infrastructure (Dépôt Privé) :**
> L'application technique stricte des règles de protection de branche par GitHub nécessite un compte *Team/Enterprise* pour les dépôts privés. L'équipe applique donc ces contraintes de manière organisationnelle.

La branche `main` suit une politique stricte d'intégration. Les règles suivantes sont respectées avant tout merge :
- **Interdiction du push direct :** Tout développement passe obligatoirement par une Pull Request depuis une branche `feature/*` ou `fix/*`.
- **Revue de code :** Au moins **1 approbation (Code Review)** par un pair est exigée.
- **Validation CI/CD :** L'intégration est conditionnée par la réussite du pipeline GitHub Actions (les 57 tests PHPUnit doivent être au vert).

Ce cadre garantit que toute modification intégrée a été relue et validée automatiquement, simulant le comportement d'une branche protégée verrouillée.

### Tags de version

Après le merge et la validation de `main`, créer puis pousser un tag annoté :

```bash
git checkout main
git pull origin main
git tag -a v1.0.0 -m "Release v1.0.0 REST & NoSQL"
git push origin v1.0.0
```

Les tags suivent le versionnage sémantique (`vMAJEUR.MINEUR.CORRECTIF`) et identifient les versions livrées.

---

## 🏗 Architecture

### Structure du projet

```
MyProf/
├── src/
│   ├── Controller/          # Contrôleurs (Web + API)
│   │   └── api/             # Routes API
│   ├── Entity/              # Entités MySQL (ORM Doctrine)
│   ├── Document/            # Documents MongoDB (ODM)
│   ├── Repository/          # Requêtes à la BDD
│   ├── Service/             # Logique métier
│   └── Form/                # Formulaires
├── tests/
│   ├── Controller/Api/      # Tests fonctionnels des endpoints
│   │   ├── AtelierApiControllerTest.php
│   │   └── AvisApiControllerTest.php
│   ├── Unit/                # Tests unitaires avec mocks
│   │   ├── AtelierApiControllerUnitTest.php
│   │   └── Repository/
│   │       └── AtelierRepositoryMockExampleTest.php
│   └── bootstrap.php
├── config/
│   ├── packages/            # Configuration des bundles
│   ├── routes.yaml          # Routes de l'application
│   └── services.yaml        # Services
├── templates/               # Vues Twig (HTML)
├── migrations/              # Migrations MySQL
├── public/                  # Assets (CSS, JS, images)
├── docker-compose.yaml      # Configuration Docker
├── composer.json            # Dépendances PHP
├── phpunit.xml.dist         # Configuration PHPUnit
├── TESTING.md               # Guide complet des tests
└── .env                     # Variables d'environnement
```

### Stack Technique

| Composant                   | Technologie     | Version |
| --------------------------- | --------------- | ------- |
| **Framework**               | Symfony         | 7.x     |
| **Base de données (SQL)**   | MySQL           | 8.0     |
| **Base de données (NoSQL)** | MongoDB         | 6.0     |
| **PHP**                     | FPM             | 8.2     |
| **Serveur Web**             | Nginx           | 1.25    |
| **Conteneurisation**        | Docker          | 20.10+  |
| **Documentation API**       | Swagger/OpenAPI | 3.0     |

### Modèle de données Hybride (SQL / NoSQL)

**MySQL (Données relationnelles & robustes) :**

- **Users** (Apprenants & Formateurs) : Sécurité et gestion des droits (intègre une fonctionnalité de traçabilité persistante de type `lastSessionId` capturée lors du login).
- **Ateliers** : Structure centrale de la proposition de valeur.
- **Inscriptions** : Liaisons fortes et transactions.

**MongoDB (Données non structurées & recherches haute performance) :**

- **Avis & Commentaires** : Exploitation maximale des recherches sur gros volumes sans jointures coûteuses (`Avis`).
- **Analytiques / Traçabilité** : Sauvegarde ultra-rapide des passages (`LogVisite`) enregistrant massivement les logs de flux des visites sur les Ateliers, tirant parti de la rapidité d'écriture NoSQL.

---

## 🔐 Sécurité

### Variables sensibles

Les variables sensibles sont gérées via `.env.local` (ignoré par Git) :

```env
APP_SECRET=your-secret-key
DATABASE_URL=mysql://user:password@host:3306/dbname
MONGODB_URL=mongodb://user:password@host:27017/dbname
```

### Chiffrer les secrets en production

```bash
symfony console secrets:set DATABASE_PASSWORD
```

---

## 🐛 Debugging

### Voir tous les routes enregistrées

```bash
symfony console debug:router
```

### Analyser la configuration

```bash
symfony console debug:config nelmio_api_doc
```

### Voir les logs

```bash
tail -f var/log/dev.log
```

---

## 🤖 Utilisation de l'Intelligence Artificielle (IA)

Dans le cadre du développement et de la refonte architecturale de l'EC04, des outils d'Intelligence Artificielle ont été ponctuellement mobilisés en mode _pair-programming_.

### 🛠 Outils Utilisés

- **Agent IA (LLM Assistant) :** Intégré à l'environnement pour accompagner le découpage technique et le développement backend.

### 🎯 Périmètre d'Utilisation

L'IA a été cadrée sur des cibles d'assistance à forte valeur ajoutée :

- **Documentation OpenAPI (Swagger) :** Génération automatique des attributs PHP 8 (`#[OA\Get]`, `#[OA\Post]`, schemas JSON) pour chaque endpoint.
- **Normalisation REST & HATEOAS :** Refonte des URL (fusion des `/search` dans la route principale) et injection des hyperliens de navigation `_links` dans les réponses API.
- **Architecture BDD Hybride :** Conception et intégration combinée au sein du même contrôleur du système SQL (Mise à jour des logs d'authentification `lastSessionId`) et de la base NoSQL MongoDB (Sauvegarde asynchrone des traces via `LogVisite`).

### 🗣 Démarche d'Ingénierie de Prompts (Contexte)

Pour obtenir du code de qualité, l'approche a ciblé le macro-contexte plutôt que la micro-génération :

- _Prompt Architectural :_ "Voici mes deux bases de données. L'objectif est d'assurer la traçabilité des visites dans MongoDB et de sauvegarder la session dans MySQL SQL dans mon EC04. Fais-moi un plan."
- _Prompt d'Audit :_ "Est-ce que mon EC04 respecte les règles REST standards (nommage, verbes, liens), est bien documenté via swagger et a une logique de test pertinente ?"

### 🛡 Validation et Sécurité du Code IA

Aucun fragment de code n'a été inséré sans un "Security Gate" rigoureux :

1.  **Vérification Active (Vulnerabilities) :** Toute tentative d'écrire en SQL a été validée pour vérifier l'utilisation systématique de l'ORM Doctrine (Prepared Statements) afin d'annuler les risques d'Injections.
2.  **Alignement Métier :** Chaque proposition de l'IA fait d'abord l'objet d'un "Execution Plan" qui doit être relu et validé fonctionnellement.
3.  **Sanctuarisation par les Tests :** Le code issu de ces collaborations est systématiquement validé par la suite de **57 tests PHPUnit** fonctionnels et unitaires garantissant qu'aucune fonctionnalité historique n'a subi de régression (100% Passed).

---

## 📞 Support & Contribution

Pour toute question ou contribution, veuillez ouvrir une [issue](https://github.com/votreprojet/issues) ou soumettre une pull request.

---

## 📄 Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

**Dernière mise à jour :** April 2026
**Équipe :** Développement MyProf

Restore : ./scripts/restore.sh (Restaure l'état des deux bases de données).

Bash

# Exemple de lancement manuel d'une sauvegarde

chmod +x scripts/backup.sh
./scripts/backup.sh

Conventions de Nommage & Documentation
Nommage : CamelCase pour les classes, snake_case pour les variables Twig, PascalCase pour les entités.

Type Hinting : Utilisation systématique du typage PHP pour réduire les erreurs d'exécution.

Migrations : Gestion stricte des schémas SQL via Doctrine Migrations.

Installation
Clonez le dépôt : git clone https://github.com/votre-compte/myprof.git

Configurez votre .env.local.

Lancer Docker : docker-compose up -d.

Exécuter les migrations : php bin/console doctrine:migrations:migrate.

Accéder au site : http://localhost:8080.

## 🔄 Changements récents & Mises à jour (Changelog)

**Version Actuelle : Normalisation REST & Intégration NoSQL Poussée**

- **Breaking Changes :** Suppression de la route `/api/search` (désormais incluse directement dans l'index `/api/ateliers?parametres=...`).
- **Feature :** Implémentation du système formel HATEOAS pour l'API (utilisation de blocs `_links` guidant la navigation client).
- **Feature :** Traçabilité hybride : Traçage des visites sur MongoDB de manière optimisée dès l'appel d'un détail d'atelier (`GET /api/ateliers/{id}`), et sauvegarde SQL du `lastSessionId` unique au moment de la connexion d'un individu.
- **Documentation :** Intégration active de **Swagger UI** testable (`/api/doc`).
