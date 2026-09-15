# ADR 0001 — Choix de l'architecture de persistance

- Statut : Accepté
- Date : 2026-09-14

## Contexte

La plateforme MyProf doit gérer plusieurs types de données et plusieurs besoins métier :

- les ateliers, les utilisateurs, les inscriptions et les rôles du système,
- les avis sur les ateliers et les feedbacks utilisateurs,
- les traces et journaux techniques ou d’activité,
- une API REST fiable, évolutive et facile à maintenir.

Le besoin de gestion des ateliers impose des relations structurées et des contraintes de cohérence. Par exemple, un atelier appartient à un formateur, peut avoir plusieurs inscriptions, et doit être exploitable par des filtres standardisés (titre, durée, date, capacité, etc.).

En parallèle, les avis et certaines informations d’activité ne suivent pas toujours un schéma rigide : ils peuvent évoluer, être annotés, ou contenir des éléments hétérogènes. De plus, les logs et traces techniques sont souvent volumineux, très diversifiés et rarement exploités via des requêtes relationnelles complexes.

Le système doit donc répondre à plusieurs exigences :

- intégrité transactionnelle pour les données métier critiques,
- simplicité de modélisation pour les relations entre entités principales,
- capacité à stocker des données non structurées ou peu structurées,
- bonne séparation des responsabilités entre systèmes de persistance,
- facilité d’évolution sans surcharge de schéma relationnel.

## Décision

Nous retenons une architecture de persistance en double base de données :

- MySQL pour les données relationnelles structurées de l’application,
- MongoDB pour les avis, les traces et les données non structurées ou semi-structurées.

### MySQL comme source de vérité transactionnelle

MySQL est utilisé pour stocker les données métier principales, notamment :

- les utilisateurs,
- les formateurs,
- les apprenants,
- les ateliers,
- les inscriptions,
- les relations entre ces entités.

Ce choix s’explique par les avantages suivants :

- support natif des contraintes de cohérence et des intégrités relationnelles,
- modélisation claire des données métier,
- requêtes SQL puissantes pour les filtres, tri et agrégations standardisées,
- stabilité et performance démontrée pour les systèmes transactionnels.

### MongoDB pour les données non structurées

MongoDB est utilisé pour stocker :

- les avis clients ou évaluations,
- les journaux d’activité,
- les traces techniques,
- les données à forte variabilité de structure.

Ce choix répond au besoin de flexibilité suivant :

- schéma évolutif sans migration lourde,
- stockage de documents hétérogènes,
- meilleure adéquation à des contenus dynamiques ou à des données d’observation,
- séparation claire entre les données métier centralisées et les données d’usage analytique ou d’exploitation.

### Justification stratégique

La double base de données ne vise pas à compliquer l’architecture, mais à aligner chaque donnée sur le bon moteur de stockage selon son comportement attendu :

- les données structurées et critiques vont vers MySQL,
- les données non structurées et évolutives vont vers MongoDB.

Cette séparation réduit les risques de sur-qualification du schéma relationnel pour des éléments naturellement flexibles, tout en maintenant un socle robuste pour les transactions essentielles.

## Conséquences

### Positives

- robustesse transactionnelle pour les opérations métier clés,
- meilleure clarté dans la modélisation des entités principales,
- capacité à stocker des données hétérogènes sans rigidifier le schéma SQL,
- meilleure séparation des responsabilités techniques,
- évolutivité facilitée des composants de logs et d’avis.

### Négatives

- complexité opérationnelle accrue : deux moteurs de données à maintenir,
- besoin de cohérence dans la gouvernance des données et la documentation des modèles,
- possible augmentation de la charge de développement pour les requêtes multi-sources,
- nécessité de définir clairement le périmètre de chaque base pour éviter les redondances.

### Contraintes de conception

- les entités métier majeures doivent rester centralisées dans MySQL,
- les données à forte variabilité doivent être utilisées uniquement dans les cas compatibles avec MongoDB,
- les services applicatifs doivent expliciter clairement la source de données utilisée,
- les règles de cohérence doivent être documentées pour éviter les inconsistances opérationnelles.

## Conclusion

Le choix de la double base de données est pertinent car il permet d’optimiser la persistance selon la nature des données. MySQL garantit la fiabilité et l’intégrité des données transactionnelles de l’application, tandis que MongoDB apporte la flexibilité nécessaire pour traiter des avis et des logs non structurés. Cette décision favorise un système plus équilibré, maintenable et adapté aux besoins réels de MyProf.
