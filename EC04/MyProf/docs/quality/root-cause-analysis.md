# Analyse de cause racine (5 Pourquoi)

## Incident

Un bug a été détecté sur le flux d’inscription aux ateliers : plusieurs utilisateurs ont reçu un message de validation erroné, alors que leur inscription n’avait pas été enregistrée dans les données métier attendues.

## Contexte

Le système est alimenté par plusieurs couches :

- interface utilisateur,
- contrôleur Symfony,
- logique métier,
- MySQL pour les données principales,
- MongoDB pour les données de traçabilité et d’avis.

Le symptôme observé était une incohérence entre l’état affiché à l’utilisateur et l’état réel des données enregistrées.

## Analyse 5 Pourquoi

### Pourquoi 1 : Pourquoi l’inscription semblait-elle validée alors qu’elle n’était pas enregistrée ?

Parce que le message de succès était renvoyé avant la validation finale de la transaction métier.

### Pourquoi 2 : Pourquoi le message de succès était-il renvoyé trop tôt ?

Parce que le contrôleur ne vérifiait pas le résultat réel de l’opération de persistance avant réponse.

### Pourquoi 3 : Pourquoi le contrôleur ne vérifiait-il pas le résultat réel ?

Parce que la logique de validation et la persistance n’étaient pas suffisamment découpées et qu’il manquait un contrôle de retour explicite.

### Pourquoi 4 : Pourquoi la logique n’était-elle pas assez découpée ?

Parce que le service métier ne retournait pas un statut attendu et le code n’était pas couvert par un test de non-régression.

### Pourquoi 5 : Pourquoi le correctif n’a-t-il pas été protégé par un test ?

Parce qu’aucun test fonctionnel ne couvrait le cas d’une inscription rejetée ou incomplète, ce qui a permis la régression.

## Cause racine

La cause racine est l’absence de mécanisme de validation explicite du résultat de la persistance dans le flux métier et l’absence de test automatisé de non-régression couvrant ce cas.

## Correctif proposé

- vérifier explicitement le succès de la persistance avant de renvoyer un message de confirmation,
- séparer les responsabilités entre validation de règles métier et sauvegarde en base,
- ajouter un test qui valide qu’une inscription incomplète ne produit pas de message de succès faux.

## Test automatisé empêchant la réapparition

Exemple de test PHPUnit de non-régression :

```php
public function testUneInscriptionInvalideNeRetournePasUnMessageDeSucces(): void
{
    $service = new InscriptionService();

    $resultat = $service->creerInscription([
        'atelierId' => null,
        'apprenantId' => 42,
        'etat' => 'invalide',
    ]);

    $this->assertFalse($resultat['success']);
    $this->assertSame('Inscription invalide', $resultat['message']);
}
```

## Plan de prévention

- ajouter des tests sur les cas limites de validation,
- vérifier les retours de persistance avant envoi de réponse,
- documenter le flux métier de validation et de sauvegarde,
- intégrer le contrôle dans la revue de code systémiqu.

## Conclusion

L’incident ne vient pas d’un seul défaut technique, mais d’une combinaison de manque de validation explicite et d’absence de test de non-régression. La correction doit donc être double : un correctif fonctionnel et un garde-fou automatisé.
