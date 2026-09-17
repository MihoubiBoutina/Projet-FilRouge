# Checklist de revue de code

À cocher dans **Files changed**.

## Bloquants

- [ ] Le besoin métier et le périmètre du diff sont compréhensibles.
- [ ] Les entrées sont validées et les erreurs renvoient un statut cohérent.
- [ ] Les accès et secrets ne sont pas exposés.
- [ ] Les relations `formateurId`, `apprenantId` et `atelierId` sont vérifiées avant persistance.
- [ ] Les cas nominal, limite et erreur sont couverts.
- [ ] La CI PHPUnit est verte.
- [ ] Une migration ou une configuration nécessaire est incluse.
- [ ] La documentation obligatoire est mise à jour.

## Suggestions

- [ ] Les noms et signatures rendent l'intention évidente.
- [ ] La logique peut évoluer sans duplication.
- [ ] Les messages d'erreur sont exploitables.
- [ ] Le test contient des assertions ciblées.
- [ ] Les commandes et chemins ajoutés sont documentés.

## Décision

- [ ] Approuver.
- [ ] Demander des changements bloquants.
- [ ] Commenter avec des suggestions non bloquantes.
