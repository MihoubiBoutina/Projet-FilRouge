# 3. Revue de code

## Partie A — Processus et rôles

1. L'auteur crée une branche descriptive.
2. Il ouvre une Pull Request vers `main` avec le contexte, les tests et les risques.
3. La CI exécute les tests et la construction documentaire.
4. Un reviewer vérifie la checklist et classe chaque commentaire en **bloquant** ou **suggestion**.
5. L'auteur répond dans Files changed et pousse les corrections.
6. La fusion intervient après CI verte et approbation d'un reviewer autorisé.

L'auteur connaît le contexte et corrige. Le reviewer cherche les défauts, vérifie les preuves et peut demander des changements. La CI automatise les contrôles reproductibles ; elle ne remplace pas la décision humaine.

## Commentaires bloquants et suggestions

**Bloquant :** sécurité absente, erreur métier non testée, migration manquante, test rouge ou documentation indispensable absente.

**Suggestion :** amélioration de nommage, simplification locale ou précision documentaire sans risque fonctionnel immédiat.

## Exemple 1 — avant / après : capacité métier

**Avant :**

```php
if (!$this->canAcceptNewInscription()) {
    throw new \LogicException('Cet atelier est complet');
}
```

**Après appliqué :**

```php
if (!$this->canAcceptNewInscription()) {
    throw new AtelierCompletException($this->getId());
}
```

**Commentaire appliqué :** le cas « atelier complet » est maintenant représenté par `AtelierCompletException`, le subscriber API le mappe en HTTP 409 et le test d'intégration vérifie cette exception. La règle métier est donc explicite et maintenable.

## Exemple 2 — avant / après : création d'atelier

**Avant :**

```php
$atelier->setFormateur($formateur);
$entityManager->persist($atelier);
```

**Après :**

```php
$formateur = $formateurRepository->find($data['formateurId']);
if (!$formateur) {
    return $this->json(['error' => 'Formateur introuvable'], 404);
}

$atelier->setFormateur($formateur);
$entityManager->persist($atelier);
$entityManager->flush();
```

**Commentaire appliqué :** vérifier l'identifiant avant la persistance évite une relation invalide et rend l'erreur exploitable par le client. C'est bloquant car une donnée étrangère inexistante ne doit pas produire une création trompeuse.

## Checklist minimale

- [ ] objectif métier compris ;
- [ ] diff limité et lisible ;
- [ ] entrées, permissions et erreurs vérifiées ;
- [ ] cas nominal, limite et erreur testés ;
- [ ] migrations/configuration/documentation vérifiées ;
- [ ] CI verte et approbation humaine obtenue.
