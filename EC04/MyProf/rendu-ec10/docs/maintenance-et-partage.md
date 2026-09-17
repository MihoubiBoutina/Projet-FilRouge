# 7. Maintenance et partage

## Organisation collaborative

La source de vérité est le dépôt Git. Toute modification documentaire passe par une branche et une Pull Request ; la revue vérifie les liens, les commandes et la cohérence avec le code.

## Responsabilités

- **Auteur de la modification :** met à jour la page et fournit la preuve de validation.
- **Reviewer :** vérifie l'exactitude et l'accessibilité du contenu.
- **Mainteneur :** valide la fusion et déclenche la publication.

## Cycle de vie

- mise à jour obligatoire lorsqu'une commande, une route ou une configuration change ;
- revue mensuelle des procédures de dépannage ;
- vérification trimestrielle des liens et versions ;
- archivage d'une décision obsolète dans un ADR plutôt que suppression silencieuse.

## Publication

MkDocs construit `site/` en mode strict. GitHub Actions publie le guide après fusion sur `main`. La publication est considérée réussie uniquement si le build strict et l'artefact Pages sont verts.
