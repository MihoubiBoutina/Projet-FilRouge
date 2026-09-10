# ðŸŽ“ MyProf - Plateforme de Gestion des Ateliers

[![CI/CD](https://github.com/MihoubiBoutina/Projet-FilRouge/actions/workflows/ci.yml/badge.svg)](https://github.com/MihoubiBoutina/Projet-FilRouge/actions/workflows/ci.yml)

Une API REST robuste pour la gestion des ateliers de formation, des apprenants et des avis. Construite avec **Symfony 7**, **MySQL**, **MongoDB** et **Docker**.

---

## ðŸ“‹ Table des matiÃ¨res

- [PrÃ©requis](#-prÃ©requis)
- [Installation](#-installation)
- [DÃ©marrage](#-dÃ©marrage)
- [Endpoints API](#-endpoints-api)
- [Tests](#-tests)
- [CI/CD & QualitÃ© du code](#-cicd--qualitÃ©-du-code)
- [Architecture](#-architecture)
- [SÃ©curitÃ© & Durcissement Infrastructure](#ï¸-sÃ©curitÃ©--durcissement-infrastructure)
- [RÃ©silience, Sauvegardes & Plan de Reprise](#-rÃ©silience-sauvegardes--plan-de-reprise-drp)

---

## ðŸ›  PrÃ©requis

Avant de commencer, assurez-vous d'avoir installÃ© :

| Logiciel           | Version | VÃ©rification               |
| ------------------ | ------- | -------------------------- |
| **Docker Engine**  | 20.10+  | `docker --version`         |
| **Docker Compose** | 2.0+    | `docker-compose --version` |
| **PHP**            | 8.2+    | `php --version`            |
| **Composer**       | 2.5+    | `composer --version`       |
| **Git**            | 2.30+   | `git --version`            |

**Ressources minimales :**

- 2GB RAM
- 1GB d'espace disque libre
- Ports disponibles : 8000 (API), 3306 (MySQL), 27017 (MongoDB), 31415 (Keycloak SSO)

---

## ðŸ“¥ Installation

### 1ï¸âƒ£ Cloner le projet

```bash
git clone <votre-repo-url>
cd MyProf
```

### 2ï¸âƒ£ Configurer les variables d'environnement

```bash
# Copier le fichier .env.example
cp .env .env.local
```

VÃ©rifiez que `.env.local` contient :

```env
APP_ENV=dev
DATABASE_URL="mysql://root:root@mysql:3306/myprof"
MONGODB_URL="mongodb://root:root@mongodb:27017/myprof?authSource=admin"
```

### 3ï¸âƒ£ Installer les dÃ©pendances

```bash
composer install
```

### 4ï¸âƒ£ Construire et dÃ©marrer Docker

```bash
docker-compose up -d --build
```

VÃ©rifiez que les containers sont actifs :

```bash
docker-compose ps
```

### 5ï¸âƒ£ Initialiser la base de donnÃ©es

```bash
# CrÃ©er la base de donnÃ©es MySQL
symfony console doctrine:database:create

# ExÃ©cuter les migrations
symfony console doctrine:migrations:migrate -n

# Charger les fixtures (donnÃ©es de test)
symfony console doctrine:fixtures:load -n
```

âœ… **Installation terminÃ©e en < 5 minutes !**

---

## ðŸš€ DÃ©marrage

### Lancer le serveur de dÃ©veloppement

```bash
symfony server:start
```

Ou directement avec Docker :

```bash
docker-compose up -d
```

âœ… L'application est accessible sur : **http://127.0.0.1:8000**

### ðŸ” Services d'Authentification

**Authentification SSO :** Le portail d'authentification Keycloak est accessible sur **[http://127.0.0.1:31415](http://127.0.0.1:31415)** pour la gÃ©nÃ©ration et la validation des jetons JWT (OAuth2 / OIDC).

### ArrÃªter l'application

```bash
symfony server:stop
# ou
docker-compose down
```

---

## ðŸ“¡ Endpoints API

### ðŸ“š Documentation Interactive

AccÃ©dez Ã  **Swagger UI** pour une documentation interactive :

```
http://127.0.0.1:8000/api/doc
```

### ðŸŽ¯ Ateliers

#### Lister tous les ateliers

```http
GET /api/ateliers
```

**RÃ©ponse :**

```json
{
    "ateliers": [
        {
            "id": 1,
            "titre": "Initiation Ã  PHP",
            "description": "Une formation complÃ¨te en PHP",
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

**ParamÃ¨tres de recherche (optionnels) :**

- `titre` (string) - Filtrer par titre
- `duree` (integer) - DurÃ©e exacte en heures
- `sort` (string) - Tri des rÃ©sultats (ex: `date`)

**RÃ©ponse (avec liens HATEOAS pour la navigation) :**

```json
{
    "ateliers": [
        {
            "id": 1,
            "titre": "Initiation Ã  PHP",
            "description": "Une formation complÃ¨te en PHP",
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

#### Voir le dÃ©tail d'un atelier (et dÃ©clencher la traÃ§abilitÃ© NoSQL)

```http
GET /api/ateliers/{id}
```

_Note : L'appel Ã  cette route enregistre automatiquement la visite (IP, date) de faÃ§on asynchrone et rapide dans un document `LogVisite` via MongoDB._

---

### ðŸ‘¨â€ðŸ« Formateurs

#### Rechercher les formateurs

```http
GET /api/formateurs?nom=Dupont
```

**ParamÃ¨tres :**

- `nom` (string) - Nom du formateur Ã  rechercher (optionnel)

**RÃ©ponse :**

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

### â­ Avis

#### Lister tous les avis

```http
GET /api/avis
```

#### Filtrer les avis par note

```http
GET /api/avis?note=5
```

**ParamÃ¨tres :**

- `note` (integer) - Filtrer par note (1-5)

**RÃ©ponse :**

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
                "titre": "Initiation Ã  PHP"
            }
        }
    ]
}
```

---

## ðŸ§ª Tests

### Lancer tous les tests

```bash
symfony console phpunit
```

### Lancer les tests d'un fichier spÃ©cifique

```bash
symfony console phpunit tests/Controller/Api/AtelierApiControllerTest.php
```

### ExÃ©cuter les tests avec couverture de code

```bash
symfony console phpunit --coverage-html coverage/
```

Consultez le rapport HTML : `coverage/index.html`

### Types de tests implÃ©mentÃ©s

**ðŸ“Œ Tests Fonctionnels** (dans `tests/Controller/Api/`)

- Testent les endpoints API complÃ¨tement avec la base de donnÃ©es
- Exemples : `AtelierApiControllerTest.php`, `AvisApiControllerTest.php`
- VÃ©rifient les codes HTTP, les formats JSON, les filtres

**ðŸ“Œ Tests Unitaires** (dans `tests/Unit/`)

- Testent la logique mÃ©tier isolÃ©e avec des **mocks**
- Exemple : `AtelierApiControllerUnitTest.php`
- Utilisent des mocks du repository pour Ã©viter la base de donnÃ©es

**ðŸ“Œ Examples des Mocks** (dans `tests/Unit/Repository/`)

- DÃ©monstration complÃ¨te des patterns de mock
- Callbacks, exceptions, retours multiples, etc.

### Tester l'API avec Postman

1. Ouvrez **Postman**
2. Allez Ã  **File** â†’ **Import**
3. Entrez l'URL : `http://127.0.0.1:8000/api/doc.json`
4. Postman importera automatiquement toutes vos routes

### ðŸ“š Documentation dÃ©taillÃ©e

Consultez **[TESTING.md](TESTING.md)** pour la documentation complÃ¨te :

- Comment utiliser les mocks
- Templates de tests
- 15+ exemples pratiques
- Bonnes pratiques et patterns
- IntÃ©gration CI/CD

---

## ðŸ”„ CI/CD & QualitÃ© du code

### Workflow Git

Le dÃ©veloppement suit un workflow basÃ© sur les branches :

1. CrÃ©er une branche dÃ©diÃ©e depuis `main` (`feature/...`, `fix/...` ou `docs/...`).
2. DÃ©velopper et valider localement avec `vendor/bin/phpunit`.
3. Ouvrir une Pull Request vers `main`.
4. Attendre la revue de code et la rÃ©ussite de la CI avant le merge.
5. Fusionner dans `main`, puis crÃ©er un tag annotÃ© pour une version livrÃ©e.

### ContrÃ´les automatisÃ©s

Le workflow [`.github/workflows/ci.yml`](../../.github/workflows/ci.yml) s'exÃ©cute sur chaque push vers `main` ou `develop`, ainsi que sur chaque Pull Request vers `main`. Il :

- installe les dÃ©pendances avec Composer et PHP 8.2 ;
- active l'extension PHP MongoDB `1.20.1`, compatible avec `composer.lock` ;
- dÃ©marre un service MongoDB `mongo:7` pour les tests fonctionnels ;
- prÃ©pare une base SQLite dÃ©diÃ©e Ã  l'environnement `test` ;
- exÃ©cute la suite PHPUnit ;
- construit l'image Docker ;
- publie l'image dans GitHub Container Registry (`ghcr.io`) aprÃ¨s un push hors Pull Request, avec un nom de dÃ©pÃ´t normalisÃ© en minuscules.

Le badge en haut de ce document reflÃ¨te le statut du dernier pipeline GitHub Actions.

### ContrÃ´le local avant commit

Le hook versionnÃ© [`tools/hooks/pre-commit`](../../tools/hooks/pre-commit) vÃ©rifie la syntaxe des fichiers PHP indexÃ©s et exÃ©cute les tests unitaires avant chaque commit. Pour l'activer dans un clone local :

```bash
git config core.hooksPath tools/hooks
```

Un commit est bloquÃ© si la syntaxe PHP ou les tests Ã©chouent. La CI conserve en complÃ©ment l'exÃ©cution de la suite PHPUnit complÃ¨te.

### Protection de la branche principale

La branche `main` est protÃ©gÃ©e sur GitHub. Les rÃ¨gles suivantes sont exigÃ©es avant tout merge :

- **Interdiction du push direct :** Tout dÃ©veloppement passe obligatoirement par une Pull Request depuis une branche `feature/*` ou `fix/*`.
- **Revue de code :** Au moins **1 approbation (Code Review)** par un pair est exigÃ©e.
- **Validation CI/CD :** L'intÃ©gration est conditionnÃ©e par la rÃ©ussite du pipeline GitHub Actions.

Ce cadre garantit que toute modification intÃ©grÃ©e a Ã©tÃ© relue et validÃ©e automatiquement.

### Tags de version

AprÃ¨s le merge et la validation de `main`, crÃ©er puis pousser un tag annotÃ© :

```bash
git checkout main
git pull origin main
git tag -a v1.0.1 -m "Release v1.0.1 CI/CD"
git push origin v1.0.1
```

Les tags suivent le versionnage sÃ©mantique (`vMAJEUR.MINEUR.CORRECTIF`) et identifient les versions livrÃ©es.

---

## ðŸ— Architecture

### Structure du projet

```
MyProf/
â”œâ”€â”€ src/
â”‚   â”œâ”€â”€ Controller/          # ContrÃ´leurs (Web + API)
â”‚   â”‚   â””â”€â”€ api/             # Routes API
â”‚   â”œâ”€â”€ Entity/              # EntitÃ©s MySQL (ORM Doctrine)
â”‚   â”œâ”€â”€ Document/            # Documents MongoDB (ODM)
â”‚   â”œâ”€â”€ Repository/          # RequÃªtes Ã  la BDD
â”‚   â”œâ”€â”€ Service/             # Logique mÃ©tier
â”‚   â””â”€â”€ Form/                # Formulaires
â”œâ”€â”€ tests/
â”‚   â”œâ”€â”€ Controller/Api/      # Tests fonctionnels des endpoints
â”‚   â”‚   â”œâ”€â”€ AtelierApiControllerTest.php
â”‚   â”‚   â””â”€â”€ AvisApiControllerTest.php
â”‚   â”œâ”€â”€ Unit/                # Tests unitaires avec mocks
â”‚   â”‚   â”œâ”€â”€ AtelierApiControllerUnitTest.php
â”‚   â”‚   â””â”€â”€ Repository/
â”‚   â”‚       â””â”€â”€ AtelierRepositoryMockExampleTest.php
â”‚   â””â”€â”€ bootstrap.php
â”œâ”€â”€ config/
â”‚   â”œâ”€â”€ packages/            # Configuration des bundles
â”‚   â”œâ”€â”€ routes.yaml          # Routes de l'application
â”‚   â””â”€â”€ services.yaml        # Services
â”œâ”€â”€ templates/               # Vues Twig (HTML)
â”œâ”€â”€ migrations/              # Migrations MySQL
â”œâ”€â”€ public/                  # Assets (CSS, JS, images)
â”œâ”€â”€ docker-compose.yaml      # Configuration Docker
â”œâ”€â”€ composer.json            # DÃ©pendances PHP
â”œâ”€â”€ phpunit.xml.dist         # Configuration PHPUnit
â”œâ”€â”€ TESTING.md               # Guide complet des tests
â””â”€â”€ .env                     # Variables d'environnement
```

### Stack Technique

| Composant                   | Technologie     | Version |
| --------------------------- | --------------- | ------- |
| **Framework**               | Symfony         | 7.x     |
| **Base de donnÃ©es (SQL)**   | MySQL           | 8.0     |
| **Base de donnÃ©es (NoSQL)** | MongoDB         | 6.0     |
| **PHP**                     | FPM             | 8.2     |
| **Serveur Web**             | Nginx           | 1.25    |
| **Authentification SSO**    | Keycloak        | Latest  |
| **Conteneurisation**        | Docker          | 20.10+  |
| **Documentation API**       | Swagger/OpenAPI | 3.0     |

### ModÃ¨le de donnÃ©es Hybride (SQL / NoSQL)

**MySQL (DonnÃ©es relationnelles & robustes) :**

- **Users** (Apprenants & Formateurs) : SÃ©curitÃ© et gestion des droits (intÃ¨gre une fonctionnalitÃ© de traÃ§abilitÃ© persistante de type `lastSessionId` capturÃ©e lors du login).
- **Ateliers** : Structure centrale de la proposition de valeur.
- **Inscriptions** : Liaisons fortes et transactions.

**MongoDB (DonnÃ©es non structurÃ©es & recherches haute performance) :**

- **Avis & Commentaires** : Exploitation maximale des recherches sur gros volumes sans jointures coÃ»teuses (`Avis`).
- **Analytiques / TraÃ§abilitÃ©** : Sauvegarde ultra-rapide des passages (`LogVisite`) enregistrant massivement les logs de flux des visites sur les Ateliers, tirant parti de la rapiditÃ© d'Ã©criture NoSQL.

---

## ï¿½ï¸ SÃ©curitÃ© & Durcissement Infrastructure

L'architecture conteneurisÃ©e applique les rÃ¨gles de durcissement et le principe de dÃ©fense en profondeur (ConformitÃ© CIS Docker & Zero Trust) :

- **Docker Rootless :** Le moteur Docker et l'ensemble des conteneurs s'exÃ©cutent en espace utilisateur non-privilÃ©giÃ© (`Rootless: true`), Ã©liminant les risques d'escalade de privilÃ¨ges.

- **SystÃ¨me de fichiers Immuable (`read_only: true`) :** Verrouillage du FS des conteneurs pour bloquer toute injection de code malveillant ou modification non autorisÃ©e.

- **Volumes Volatiles (`tmpfs`) :** Redirection des Ã©critures temporaires (logs, sockets SQL/NoSQL) uniquement en mÃ©moire vive volatile, Ã©vitant toute persistance accidentelle de donnÃ©es sensibles.

- **Isolation RÃ©seau Strict (Docker Bridge) :**
    - `prof-sr1-frontend` : Flux Web exposÃ©s et accessibles publiquement.
    - `profs-sr-backend` : Interconnexion API / Keycloak, accÃ¨s restreint.
    - `profs-sr-bddSQL` / `profs-sr-bddNOSQL` : RÃ©seaux de persistance Ã©tanches (accÃ¨s direct depuis le frontend strictement interdit).

- **Bridage des Ressources (cgroups) :** Limitation stricte de l'allocation RAM/CPU par conteneur pour prÃ©venir les attaques DoS locales et la consommation excessive de ressources.

---

## ï¿½ðŸ” SÃ©curitÃ©

### ðŸ›¡ï¸ Protection contre les Injections (SQLi & NoSQLi)

- **SQL Injection (SQLi) :** NeutralisÃ©e par l'utilisation systÃ©matique de l'ORM Doctrine et des requÃªtes prÃ©parÃ©es paramÃ©trÃ©es.
- **NoSQL Injection (NoSQLi) :** ContrÃ´le des opÃ©rateurs MongoDB via une validation stricte du schÃ©ma JSON des payloads entrants.

### Variables sensibles

Les variables sensibles sont gÃ©rÃ©es via `.env.local` (ignorÃ© par Git) :

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

## ï¿½ RÃ©silience, Sauvegardes & Plan de Reprise (DRP)

La stratÃ©gie de sauvegarde et de poursuite d'activitÃ© s'appuie sur des mÃ©triques prÃ©cises :

| Base de DonnÃ©es     | Type de DonnÃ©es                             | RPO Target  | Outil de Sauvegarde          |
| :------------------ | :------------------------------------------ | :---------- | :--------------------------- |
| **MySQL / MariaDB** | Transactionnel (Inscriptions, Utilisateurs) | â‰¤ 1 heure   | `mariadb-dump` / `mysqldump` |
| **MongoDB**         | Non-structurÃ© (Avis, Logs de visite)        | â‰¤ 24 heures | `mongodump`                  |

### Objectifs de RÃ©silience

- **RTO Global (Recovery Time Objective) :** â‰¤ 4 heures.
- **ImmuabilitÃ© Locale Anti-Ransomware :** ExÃ©cution du script automatisÃ© `deploy.sh`. AprÃ¨s extraction des archives compressÃ©es, le dossier `/backups/` subit un verrouillage dÃ©fensif des droits en lecture seule (`chmod 400`), empÃªchant toute altÃ©ration ou suppression par un processus applicatif compromis.

### StratÃ©gie de Sauvegarde

- **FrÃ©quence MySQL :** Sauvegardes horaires via `mariadb-dump` avec compression gzip.
- **FrÃ©quence MongoDB :** Sauvegardes quotidiennes via `mongodump` (fenÃªtre hors-pointe).
- **Stockage :** Archives versionnÃ©es dans `/backups/` avec horodatage et checksums SHA256.
- **RÃ©tention :** 30 jours minimum pour les sauvegardes MySQL, 90 jours pour MongoDB.
- **VÃ©rification :** Tests de restauration mensuels sur l'environnement de staging.

---

## ï¿½ðŸ› Debugging

### Voir tous les routes enregistrÃ©es

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

## ðŸ¤– Utilisation de l'Intelligence Artificielle (IA)

Dans le cadre du dÃ©veloppement et de la refonte architecturale de l'EC04, des outils d'Intelligence Artificielle ont Ã©tÃ© ponctuellement mobilisÃ©s en mode _pair-programming_.

### ðŸ›  Outils UtilisÃ©s

- **Agent IA (LLM Assistant) :** IntÃ©grÃ© Ã  l'environnement pour accompagner le dÃ©coupage technique et le dÃ©veloppement backend.

### ðŸŽ¯ PÃ©rimÃ¨tre d'Utilisation

L'IA a Ã©tÃ© cadrÃ©e sur des cibles d'assistance Ã  forte valeur ajoutÃ©e :

- **Documentation OpenAPI (Swagger) :** GÃ©nÃ©ration automatique des attributs PHP 8 (`#[OA\Get]`, `#[OA\Post]`, schemas JSON) pour chaque endpoint.
- **Normalisation REST & HATEOAS :** Refonte des URL (fusion des `/search` dans la route principale) et injection des hyperliens de navigation `_links` dans les rÃ©ponses API.
- **Architecture BDD Hybride :** Conception et intÃ©gration combinÃ©e au sein du mÃªme contrÃ´leur du systÃ¨me SQL (Mise Ã  jour des logs d'authentification `lastSessionId`) et de la base NoSQL MongoDB (Sauvegarde asynchrone des traces via `LogVisite`).

### ðŸ—£ DÃ©marche d'IngÃ©nierie de Prompts (Contexte)

Pour obtenir du code de qualitÃ©, l'approche a ciblÃ© le macro-contexte plutÃ´t que la micro-gÃ©nÃ©ration :

- _Prompt Architectural :_ "Voici mes deux bases de donnÃ©es. L'objectif est d'assurer la traÃ§abilitÃ© des visites dans MongoDB et de sauvegarder la session dans MySQL SQL dans mon EC04. Fais-moi un plan."
- _Prompt d'Audit :_ "Est-ce que mon EC04 respecte les rÃ¨gles REST standards (nommage, verbes, liens), est bien documentÃ© via swagger et a une logique de test pertinente ?"

### ðŸ›¡ Validation et SÃ©curitÃ© du Code IA

Aucun fragment de code n'a Ã©tÃ© insÃ©rÃ© sans un "Security Gate" rigoureux :

1.  **VÃ©rification Active (Vulnerabilities) :** Toute tentative d'Ã©crire en SQL a Ã©tÃ© validÃ©e pour vÃ©rifier l'utilisation systÃ©matique de l'ORM Doctrine (Prepared Statements) afin d'annuler les risques d'Injections.
2.  **Alignement MÃ©tier :** Chaque proposition de l'IA fait d'abord l'objet d'un "Execution Plan" qui doit Ãªtre relu et validÃ© fonctionnellement.
3.  **Sanctuarisation par les Tests :** Le code issu de ces collaborations est contrÃ´lÃ© par PHPUnit et par le hook `pre-commit`. La suite complÃ¨te contient 57 tests et passe avec 99 assertions.

---

## ðŸ“ž Support & Contribution

Pour toute question ou contribution, ouvrez une [issue](https://github.com/MihoubiBoutina/Projet-FilRouge/issues) ou soumettez une Pull Request.

---

## ðŸ“„ Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de dÃ©tails.

---

## ðŸ“ Historique des modifications

- Normalisation REST et intÃ©gration HATEOAS dans les rÃ©ponses API.
- PrÃ©fixe `/api` rÃ©tabli sur les routes REST et validation des endpoints GET/POST.
- TraÃ§abilitÃ© hybride SQL/NoSQL : `lastSessionId` cÃ´tÃ© SQL et `LogVisite` cÃ´tÃ© MongoDB.
- Documentation Swagger disponible sur `/api/doc`.
- Workflow GitHub Actions Ã  la racine du dÃ©pÃ´t : PHPUnit puis Build & Publish vers GHCR.
- Extension MongoDB PHP explicitement installÃ©e en version `1.20.1` pour respecter les contraintes Composer.
- Service MongoDB `mongo:7` ajoutÃ© aux tests GitHub Actions.
- Nom de l'image GHCR normalisÃ© en minuscules pour respecter les rÃ¨gles Docker.
- Diagnostic de plateforme PHP ajoutÃ© avec `php --ini`, `php -m` et `php --ri mongodb`.
- Configuration Symfony de test dans `config/packages/test/framework.yaml`.
- Hook local versionnÃ© dans `tools/hooks/pre-commit` pour la syntaxe PHP et les tests unitaires.
- Environnement PHPUnit isolÃ© avec SQLite et transports Messenger synchrones dans `.env.test`.
- Tags annotÃ©s publiÃ©s : `v1.0.0` et `v1.0.1`.

## âœ… Ã‰tat des contrÃ´les

- Hook `pre-commit` : validÃ©, avec 6 tests unitaires et 10 assertions.
- Suite PHPUnit complÃ¨te : 57 tests, 99 assertions, tous passants.
- Build Docker local : Ã  exÃ©cuter avec Docker Desktop dÃ©marrÃ©.
- Publication GHCR : configurÃ©e dans le job `Build & Publish` aprÃ¨s rÃ©ussite du job PHPUnit.

**DerniÃ¨re mise Ã  jour :** septembre 2026
**Ã‰quipe :** DÃ©veloppement MyProf

