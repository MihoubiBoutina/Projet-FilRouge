# 4. Stratégie de tests automatisés

## Pyramide appliquée

- **Unitaires :** règles isolées de capacité dans `tests/Unit`.
- **Intégration :** persistance et relation atelier/formateur dans `tests/Integration`.
- **Fonctionnels API :** codes HTTP, JSON, validation et erreurs dans `tests/Functional/Api` et `tests/API`.

## Règles d'écriture

Chaque fonctionnalité modifiée comporte :

1. un scénario nominal ;
2. un cas limite ;
3. un scénario d'erreur ;
4. des assertions sur le statut et la donnée utile ;
5. des dépendances isolées quand le test est unitaire.

## Exemple réel commenté

```php
public function testAtelierCompletRefuseUneNouvelleInscription(): void
{
    $atelier = $this->createAtelierWithCapacity(1);
    $atelier->addInscriptionAtelier($this->createInscription());

    $this->expectException(AtelierCompletException::class);
    $atelier->addInscriptionAtelier($this->createInscription());
}
```

Le test protège le cas limite qui avait motivé l'analyse 5 Pourquoi.

## Seuil et CI

Le workflow de qualité lance PHPUnit avec couverture et le script `tools/coverage-check.php` impose un seuil de 70 %. Le seuil est un minimum de protection ; une règle métier critique doit aussi avoir un test dédié, même si la moyenne globale est suffisante.
