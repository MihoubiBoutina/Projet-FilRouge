# 🎓 MyProf - Plateforme de Gestion des Ateliers

Une API REST robuste pour la gestion des ateliers de formation, des apprenants et des avis. Construite avec **Symfony 7**, **MySQL**, **MongoDB** et **Docker**.

---

## 📋 Table des matières

- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Démarrage](#-démarrage)
- [Endpoints API](#-endpoints-api)
- [Tests](#-tests)
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

#### Rechercher les ateliers

```http
GET /api/search?titre=PHP&duree=8&places_min=10&sort=titre&order=ASC
```

**Paramètres :**

- `titre` (string) - Titre de l'atelier
- `duree` (integer) - Durée en heures
- `places_min` (integer) - Nombre minimum de places
- `sort` (string) - Tri par : `date`, `titre`, `place` (défaut: `date`)
- `order` (string) - Ordre : `ASC`, `DESC` (défaut: `ASC`)

---

### 👨‍🏫 Formateurs

#### Rechercher les formateurs

```http
GET /api/formateurs/search?nom=Dupont
```

**Paramètres :**

- `nom` (string) - Nom du formateur à rechercher

**Réponse :**

```json
[
    {
        "id": 1,
        "nom": "Dupont",
        "prenom": "Jean",
        "email": "jean.dupont@example.com"
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

### Modèle de données

**MySQL (Données relationnelles) :**

- Users (Apprenants & Formateurs)
- Ateliers
- Inscriptions

**MongoDB (Données non structurées) :**

- Avis & Commentaires

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

il faut mettre mon swagger
breacking change quand je commit
changent log
