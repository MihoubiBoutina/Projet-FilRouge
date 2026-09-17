# MyProf - Plateforme de gestion des ateliers

[![CI/CD](https://github.com/MihoubiBoutina/Projet-FilRouge/actions/workflows/ci.yml/badge.svg)](https://github.com/MihoubiBoutina/Projet-FilRouge/actions/workflows/ci.yml)

MyProf est une application Symfony dédiée à la gestion des ateliers de formation, des inscriptions, des avis et de la traçabilité des activités. Le projet a été enrichi au fil du développement avec une architecture hybride SQL/NoSQL, des mécanismes de sécurité avancés, une documentation MkDocs et une validation de la qualité par tests et revue technique.

---

## Sommaire

- [Vue d'ensemble](#vue-densemble)
- [Architecture technique](#architecture-technique)
- [Fonctionnalités métier](#fonctionnalités-métier)
- [Documentation du projet](#documentation-du-projet)
- [Sécurité et durcissement](#sécurité-et-durcissement)
- [Qualité, revue et rétrospective](#qualité-revue-et-rétrospective)
- [Tests et validation](#tests-et-validation)
- [CI/CD et automatisation](#cicd-et-automatisation)
- [Démarrage rapide](#démarrage-rapide)
- [Historique et état actuel](#historique-et-état-actuel)

---

## Vue d'ensemble

Le projet s'appuie sur :

- Symfony 7 pour l'API et la logique applicative
- MySQL pour les données transactionnelles
- MongoDB pour les données non structurées et la traçabilité
- Docker Compose pour le déploiement local
- Keycloak pour l'authentification et la gestion des identités
- MkDocs Material pour la documentation technique et qualité

Le système couvre des cas métiers réels comme :

- gestion des ateliers et des formateurs
- inscriptions et validation des capacités
- avis et commentaires
- journalisation/traçabilité des accès et visites
- sécurisation des identités et des secrets
- documentation technique, qualité et opérations

---

## Architecture technique

### Stack principale

| Composant           | Technologie                  | Rôle                                                            |
| :------------------ | :--------------------------- | :-------------------------------------------------------------- |
| Application backend | Symfony 7 / PHP 8.2          | API REST, logique métier, sécurité                              |
| Base relationnelle  | MySQL                        | Ateliers, inscriptions, utilisateurs, données transactionnelles |
| Base documentaire   | MongoDB                      | Avis, logs, visite, traçabilité NoSQL                           |
| Authentification    | Keycloak                     | SSO, JWT, gestion des rôles et MFA                              |
| Conteneurisation    | Docker Compose               | Environnement local et reproductible                            |
| Documentation       | MkDocs Material + PDF plugin | Site documentaire et export PDF                                 |
| CI                  | GitHub Actions               | Tests, build, publication et contrôle qualité                   |

### Modèle hybride SQL/NoSQL

- MySQL contient les données structurées et transactionnelles : ateliers, apprenants, formateurs, inscriptions, comptes, sessions.
- MongoDB reçoit les données de traçabilité et les documents à forte volumétrie : avis, logs de visite, événements d'audit.

Ce choix reflète une architecture orientée métier et performance, avec séparation claire entre éléments relationnels et éléments d'analyse/traçabilité.

### Règle métier ajoutée : capacité des ateliers

Une règle de validation a été ajoutée pour empêcher les inscriptions au-delà de la capacité définie d'un atelier. Cela a été couvert par des tests unitaires et d'intégration afin de sécuriser le flux de réservation et de prévenir une surcharge de capacité.

---

## Fonctionnalités métier

- Gestion des ateliers et des créneaux
- Gestion des apprenants et formateurs
- Inscriptions aux ateliers
- Contrôle des places restantes
- Avis et commentaires sur les formateurs/ateliers
- Consultation API REST
- Documentation Swagger/OpenAPI
- Traçabilité des visites via MongoDB
- Authentification centralisée via Keycloak

---

## Documentation du projet

La documentation du projet est structurée en plusieurs niveaux :

- [docs/index.md](docs/index.md) : page d'accueil du site MkDocs
- [docs/architecture.md](docs/architecture.md) : architecture technique et choix d'implémentation
- [docs/guide-qualite.md](docs/guide-qualite.md) : guide global de qualité du projet
- [docs/quality/code-review.md](docs/quality/code-review.md) : grille de revue de code et méthode de validation entre pairs
- [docs/quality/retrospective.md](docs/quality/retrospective.md) : rétrospective d'équipe et plan d'amélioration continue
- [docs/quality/root-cause-analysis.md](docs/quality/root-cause-analysis.md) : analyse de cause racine selon la méthode 5 Pourquoi
- [docs/troubleshooting.md](docs/troubleshooting.md) : support et résolution de problèmes
- [docs/security/iam-keycloak.md](docs/security/iam-keycloak.md) : IAM, rôles, MFA, JWT et responsabilité des accès
- [docs/security/secrets-kms.md](docs/security/secrets-kms.md) : gestion des clés et des secrets
- [docs/security/incident-response.md](docs/security/incident-response.md) : surveillance, détection et réponse aux incidents
- [docs/security/data-protection-hardening.md](docs/security/data-protection-hardening.md) : protection des données et durcissement de l'infrastructure
- [docs/adr/](docs/adr/) : décisions d'architecture formalisées

La documentation est centralisée avec MkDocs et un export PDF possible via le plugin `with-pdf`.

### Qualité et revue de code

Le projet inclut une vraie démarche qualité documentée dans le dossier [docs/quality/](docs/quality/) :

- le guide qualité explique les critères et les livrables attendus,
- la revue de code formalise les points à contrôler avant la fusion,
- la méthode 5 Pourquoi clarifie la cause racine d’un défaut,
- la rétrospective sert de support à l’amélioration continue.

Un lecteur qui reprend le projet doit donc commencer par [docs/guide-qualite.md](docs/guide-qualite.md), puis consulter les sous-documents de qualité selon le besoin : revue, RCA ou amélioration continue.

---

## Sécurité et durcissement

### 1. Sécurisation des données

Le projet applique une logique de protection des données dans les deux états principaux :

- en transit : HTTPS obligatoire, TLS 1.3, HSTS, redirection HTTP vers HTTPS
- au repos : chiffrement des volumes et données sensibles, utilisation de KMS / chiffrement AES-256 sur les éléments critiques

### 2. Sécurité IAM / accès

Le document IAM-Keycloak couvre :

- gestion des utilisateurs et rôles
- politiques RBAC/ABAC
- MFA obligatoire pour certains profils
- politique JWT et durée des tokens
- procédure de révocation et de désactivation des comptes

### 3. Secrets et clés

Le document secrets-kms traite :

- stockage hors des fichiers source et images Docker
- responsabilité de gestion des secrets
- rotation des clés
- utilisation de KMS ou de solutions équivalentes
- bonne pratique de séparation des responsabilités

### 4. Durcissement infrastructure

Les pratiques documentées incluent :

- conteneurs sans privilège root
- accès en lecture seule pour les fichiers système
- utilisation de `tmpfs` et blocage des capacités inutiles
- politique de chiffrement sur les objets de stockage
- désactivation de tout accès public
- versionnement des données

### 5. Réponse aux incidents

Le plan d'intervention couvre :

- collecte de logs
- détection et priorisation des événements
- investigation et analyse de cause racine
- communication interne et externe
- notification CNIL / organismes concernés si nécessaire
- plan de retour à un état sûr

---

## Qualité, revue et rétrospective

Le projet a intégré une démarche de qualité documentaire et technique :

- revue de code structurée sur critères de lisibilité, sécurité, typage PHP, tests et maintenabilité
- rétrospective d'équipe sous format 4L / Start-Stop-Continue
- analyse racine des incidents ou défauts de conception
- plan d'action associé à chaque retour d'expérience

Les documents qualité sont présents dans :

- [docs/quality/code-review.md](docs/quality/code-review.md)
- [docs/quality/retrospective.md](docs/quality/retrospective.md)
- [docs/quality/root-cause-analysis.md](docs/quality/root-cause-analysis.md)

---

## Tests et validation

Le projet est validé par une suite de tests ciblés sur la logique métier critique.

### Validation actuelle exécutée

Commande réalisée :

```bash
cd "c:\xampp\htdocs\filRouge\EC04\MyProf"; php vendor/bin/phpunit tests/Unit/AtelierCapacityTest.php tests/Integration/AtelierInscriptionCapacityIntegrationTest.php --testdox
```

Résultat vérifié :

- exit code : 0
- tests : OK
- aucun échec détecté

Cette validation couvre notamment la règle métier de capacité d'un atelier et son comportement lors d'une inscription supérieure au quota disponible.

### Types de tests présents

- tests unitaires pour la logique métier isolée
- tests d'intégration pour les flux réels d'inscription
- tests de contrôleur et de services selon les besoins applicatifs

---

## CI/CD et automatisation

Le dépôt comporte une pipeline GitHub Actions qui vérifie :

- installation des dépendances PHP et MongoDB
- exécution de la suite PHPUnit
- préparation de l'environnement de test
- construction des images Docker
- build de la documentation MkDocs

Un système de contrôle local a aussi été mis en place pour les bonnes pratiques de contribution :

- validation syntaxique PHP
- exécution des tests avant validation du commit
- documentation dans le dépôt et script de build de docs

### Build MkDocs

Le site documentaire est configuré dans [mkdocs.yml](mkdocs.yml). Il intègre :

- navigation structurée
- thématiques qualité, architecture, sécurité et dépannage
- export PDF de la documentation

Le point important à noter : la génération locale de PDF sous Windows peut nécessiter des dépendances systèmes spécifiques pour WeasyPrint. La chaîne CI est donc conçue pour fonctionner dans un environnement compatible Linux.

---

## Démarrage rapide

### Prérequis

- Docker Desktop / Docker Compose
- PHP 8.2+
- Composer 2.x
- Git

### Installation

```bash
composer install
```

```bash
docker compose up -d --build
```

### Base de données

```bash
php bin/console doctrine:migrations:migrate -n
```

### Lancer l'application

```bash
php bin/console server:start
```

ou via Docker selon la configuration locale.

### Documentation

```bash
mkdocs serve
```

ou :

```bash
mkdocs build --strict
```

---

## Historique et état actuel

Le projet est dans un état mature et documenté, avec :

- architecture documentée et formalisée
- sécurisation IAM, secrets et incidents
- durcissement de l'infrastructure et des données
- revue qualité et rétrospective intégrées dans les livrables
- validation fonctionnelle sur la règle de capacité
- documentation MkDocs opérationnelle et structurée

Le projet est donc prêt à être présenté comme une solution complète, sécurisée et documentée, alignée sur les exigences de qualité et de sécurité du module.

---

## Licence

Ce projet est livré pour usage académique et technique dans le cadre du fil rouge EC04.

| Base de DonnÃ©es    | Type de DonnÃ©es                            | RPO Target    | Outil de Sauvegarde          |
| :------------------ | :------------------------------------------ | :------------ | :--------------------------- |
| **MySQL / MariaDB** | Transactionnel (Inscriptions, Utilisateurs) | â‰¤ 1 heure   | `mariadb-dump` / `mysqldump` |
| **MongoDB**         | Non-structurÃ© (Avis, Logs de visite)       | â‰¤ 24 heures | `mongodump`                  |

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
