# Analyse de cause racine (5 Pourquoi)

## Incident

Un bug a été détecté sur le flux d’inscription aux ateliers : une inscription supplémentaire a été ajoutée alors que la capacité maximale était atteinte, ce qui a produit un comportement incohérent entre le message métier et la réalité de la règle de gestion.

## Contexte

Le système MyProf gère les inscriptions aux ateliers via la classe Atelier du backend. La logique de capacité est centralisée dans la méthode `canAcceptNewInscription()`, et les règles métier sont couvertes par les tests de capacité du projet.

Le symptôme observé était qu’une inscription au-delà de la capacité pouvait être traitée comme une erreur générique au lieu d’être identifiée explicitement comme une règle métier bloquante.

## Analyse 5 Pourquoi

### Pourquoi 1 : Pourquoi une inscription au-delà de la capacité a-t-elle été acceptée ou mal signalée ?

Parce que la règle métier “atelier complet” n’était pas représentée par une exception explicite.

### Pourquoi 2 : Pourquoi la règle métier n’était-elle pas représentée par une exception explicite ?

Parce que le code utilisait une exception générique `\LogicException`, ce qui ne permettait pas de distinguer le cas métier “atelier complet” d’une erreur technique.

### Pourquoi 3 : Pourquoi une exception générique a-t-elle été utilisée ?

Parce que la logique de validation de capacité n’était pas exprimée sous forme de contrat métier propre, ni transformée en exception métier dédiée.

### Pourquoi 4 : Pourquoi cette logique n’était-elle pas exprimée de façon métier ?

Parce que le code de la classe Atelier vérifiait seulement une condition, sans créer un type d’erreur spécifique pour ce cas.

### Pourquoi 5 : Pourquoi le correctif n’a-t-il pas été protégé par un test métier de non-régression ?

Parce que les tests existants ne couvraient pas suffisamment le cas où la capacité est atteinte et où l’exception métier doit être levée avec un comportement cohérent.

## Cause racine

La cause racine est l’absence d’une exception métier dédiée pour le cas “atelier complet”, combinée à une couverture insuffisante des cas limites de capacité.

## Correctif proposé

- remplacer l’exception générique par une exception métier dédiée : `AtelierCompletException` ;
- lever cette exception au moment où la capacité maximale est atteinte ;
- mapper l’erreur vers un code HTTP 409 Conflict ;
- maintenir un test de non-régression sur le cas limite.

## Correction appliquée

Le correctif est situé dans le backend métier et dans le gestionnaire global des exceptions API.

Le cas métier est désormais explicitement traité comme une erreur métier et non comme un échec technique interne.

## Test automatisé empêchant la réapparition

Les tests de capacité sont présents dans les fichiers de tests unitaires du projet.

Ils vérifient notamment :

- l’atelier accepte une inscription si la capacité n’est pas atteinte,
- l’atelier refuse une inscription si la capacité est atteinte,
- le dépassement de capacité déclenche bien une exception explicite.

## Plan de prévention

- conserver les tests de capacité comme garde-fou fonctionnel,
- utiliser des exceptions métier spécifiques pour les règles métier,
- distinguer clairement exception technique et exception métier,
- documenter le cas “atelier complet” dans le guide de qualité et la revue de code.

## Conclusion

L’incident vient d’un manque de modélisation explicite de la règle métier “atelier complet” et d’une couverture test insuffisante sur les cas limites. Le correctif consiste à formaliser cette règle en exception métier et à la sécuriser par des tests de non-régression.
