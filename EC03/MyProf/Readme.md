Stack Technique & Architecture
Architecture & Code
POO (Programmation Orientée Objet) : Code intégralement structuré en classes pour une maintenance facilitée.

Modèle MVC Strict : Séparation claire entre la logique métier (Modèles), l'interface utilisateur (Vues Twig) et la gestion des requêtes (Contrôleurs).

Symfony Framework : Utilisation de composants robustes pour la sécurité et le routage.

Double Persistance (Polyglot Persistence)
Le projet utilise deux types de bases de données pour optimiser les performances selon le cas d'usage :

SQL (MySQL) : Gestion des données relationnelles structurées (Utilisateurs, Inscriptions, Ateliers).

NoSQL (MongoDB) : Stockage flexible pour les données non structurées ou à forte volumétrie (Avis, Commentaires, Logs).

 Environnement & Virtualisation
Le projet est entièrement "Dockerisé" pour garantir un environnement de développement identique à la production.

Services Docker Compose :
PHP-FPM : Interpréteur PHP 8.2+ optimisé.

Nginx : Serveur web haute performance.

MySQL : Base de données relationnelle.

MongoDB : Base de données NoSQL.

Lancement rapide :
Bash

# Construire et lancer les containers
docker-compose up -d --build

# Installer les dépendances
docker-compose exec php composer install

Sécurité & Variables d'Environnement
La gestion des données sensibles est sécurisée via le système de fichiers .env de Symfony :

.env : Contient les variables par défaut (non sensibles).

.env.local : (Ignoré par Git) Contient les secrets machine (mots de passe BDD).

Secrets Vault : Utilisation de php bin/console secrets:set pour chiffrer les clés API et les accès critiques en production.

Ops & Maintenance (Backup & Restore)
Des scripts Shell automatisés sont disponibles dans le dossier /scripts pour la sauvegarde des données :

Backup : ./scripts/backup.sh (Génère un dump SQL et un export JSON MongoDB).

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