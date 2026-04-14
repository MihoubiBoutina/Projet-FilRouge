Présentation du projet

MyProf est un site web vitrine développé dans le cadre d’un projet pédagogique.
Il a pour objectif de présenter une plateforme fictive mettant en relation des formateurs qualifiés et des apprenants motivés autour d’ateliers de formation interactifs.

Le site est conçu comme une page unique (one-page), avec une navigation fluide entre les sections et une attention particulière portée à l’ergonomie, au responsive design et aux bonnes pratiques du web.




 Objectifs du projet

Concevoir une interface claire et moderne

Mettre en pratique les bases du développement web

Proposer une navigation fluide et intuitive

Intégrer des interactions utilisateur simples (menu, formulaire, popup)

Respecter les critères de performance, d’accessibilité et de SEO




 Technologies utilisées

HTML5 : structure sémantique du site

CSS3 : mise en page, design responsive, animations

JavaScript (Vanilla) : interactions dynamiques (menu, popup, scroll)

Google Fonts (Roboto) : typographie

MDN Web Docs : documentation de référence

Google Chrome DevTools : tests, responsive et debug

Lighthouse : audit de performance, accessibilité et SEO




 Structure du projet
MyProf/
│── index.html        # Structure HTML
│── index.css         # Styles et responsive design
│── index.js          # Interactions JavaScript
│── image/            # Images (professeurs, élèves, bannière)
│── README.md         # Documentation du projet



 Fonctionnalités principales

Navigation par ancres avec défilement fluide

Menu responsive avec bouton burger (mobile / tablette)

Header fixe avec effet sticky au scroll

Section de présentation de la plateforme

Mise en avant des professeurs sous forme de cartes

Témoignages d’apprenants

Formulaire de contact (version vitrine)

Fenêtre modale d’inscription apprenant (<dialog>)

Message de confirmation après inscription




 Responsive Design

Le site est entièrement responsive et s’adapte aux différents supports :

Ordinateur

Tablette

Smartphone

Des media queries ont été mises en place pour ajuster :

La disposition des sections

La navigation

La taille des textes et des boutons




 Accessibilité

Plusieurs bonnes pratiques ont été intégrées :

Utilisation de balises HTML sémantiques

Attributs aria-label

Focus clavier visible

Contrastes de couleurs lisibles

Formulaires avec validation native HTML




 Performance et optimisation

Un audit Lighthouse a mis en évidence l’impact des images sur la performance.
Des optimisations ont été mises en place ou prévues :

Redimensionnement des images aux tailles réelles d’affichage

Conversion vers des formats modernes (WebP)

Chargement différé des images (loading="lazy")

Ces actions permettent d’améliorer le temps de chargement et le score de performance.