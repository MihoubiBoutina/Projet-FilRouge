#  MyProf — Plateforme de mise en relation Formateurs & Apprenants

MyProf est une application web développée avec **Symfony** et **Twig**.  
Elle permet de mettre en relation des **formateurs** et des **apprenants** autour d’**ateliers de formation**, avec un espace dédié pour chaque rôle.

Le projet met l’accent sur :
- une architecture MVC claire,
- des vues Twig réutilisables,
- une interface responsive (CSS + JavaScript),
- une gestion simple des données (version MVP sans base de données).

---

## Installation & Lancement du projet

### 1️ Prérequis
- PHP **8.1 ou supérieur**
- Composer
- (Optionnel) XAMPP / Apache

---

##  Méthodes de lancement du projet

Le projet **MyProf** peut être lancé de **deux manières différentes**, selon l’environnement utilisé.

---

### Méthode 1 — Serveur Apache (XAMPP)

Cette méthode est recommandée si vous utilisez **XAMPP** ou un environnement Apache classique.

#### Étapes :
1. Copier le dossier **MyProf** dans :


xampp/htdocs/

2. Démarrer **Apache** depuis le panneau de contrôle XAMPP.

3. Ouvrir un navigateur et accéder à l’adresse :

http://localhost/MyProf/public


 Le site est maintenant accessible via Apache.

---

###  Méthode 2 — Serveur PHP intégré (sans htdocs)

Cette méthode permet de lancer le projet **sans XAMPP**, directement avec PHP.

#### Étapes :
1. Ouvrir un terminal dans le dossier **MyProf**.

2. Installer les dépendances :
```bash
composer install
3 Lancer le serveur PHP intégré :

php -S localhost:8000 -t public

4 Ouvrir un navigateur et accéder à l’adresse :
http://localhost:8000

Le site est maintenant accessible via le serveur PHP.

MyProf/
├── public/
│   ├── css/            # Styles CSS
│   ├── js/             # JavaScript (interactions)
│   └── image/          # Images
│
├── src/
│   └── Controller/     # Contrôleurs Symfony
│
├── templates/
│   ├── base.html.twig  # Layout principal
│   ├── landing/
│   ├── inscription/
│   ├── formateurs/
│   ├── apprenants/
│   └── ateliers/
│
├── .env
├── composer.json
└── README.md




🎯 Fonctionnalités principales

Page d’accueil (landing page)

Inscription avec validation

Liste des formateurs

Catalogue des ateliers

Gestion des ateliers par les formateurs

Avis des apprenants

Menu responsive avec burger

Messages flash (succès / erreur)

🛠️ Technologies utilisées

PHP 8.1+

Symfony 6

Twig

HTML5 / CSS3

JavaScript (Vanilla JS)

Font Awesome

📱 Compatibilité navigateurs

Le site a été testé sur :

Google Chrome

Mozilla Firefox

Microsoft Edge

Les tests incluent :

affichage responsive (mobile / tablette / desktop),

vérification des formulaires,

interactions JavaScript (menu, messages flash).