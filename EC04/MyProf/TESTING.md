# 🧪 Guide des Tests et Mocks

## 🎯 Vue d'ensemble

Ce projet utilise **PHPUnit** pour les tests automatisés avec deux approches :

1. **Tests Fonctionnels** - Testent les routes API complètes
2. **Tests Unitaires** - Testent la logique métier avec des mocks

---

## 📁 Structure des tests

```
tests/
├── Controller/Api/              # Tests fonctionnels des endpoints
│   ├── AtelierApiControllerTest.php
│   └── AvisApiControllerTest.php
├── Unit/                        # Tests unitaires avec mocks
│   └── AtelierApiControllerUnitTest.php
└── bootstrap.php                # Configuration PHPUnit
```

---

## ▶️ Exécuter les tests

### Lancer tous les tests

```bash
symfony console phpunit
```

### Lancer un fichier de test spécifique

```bash
symfony console phpunit tests/Controller/Api/AtelierApiControllerTest.php
```

### Lancer un test spécifique

```bash
symfony console phpunit tests/Controller/Api/AtelierApiControllerTest.php::testListAteliers
```

### Lancer avec rapport de couverture

```bash
symfony console phpunit --coverage-html coverage/
```

Consultez le rapport : `coverage/index.html`

---

## 🔧 Tests Fonctionnels

Les tests fonctionnels testent l'API complète (avec base de données).

### Exemple : Tester le listing des ateliers

```php
public function testListAteliers(): void
{
    $client = static::createClient();
    $client->request('GET', '/api/ateliers');

    // Vérifications
    $this->assertResponseIsSuccessful();  // Code 200
    $this->assertResponseHeaderSame('content-type', 'application/json');

    $responseData = json_decode($client->getResponse()->getContent(), true);
    $this->assertArrayHasKey('ateliers', $responseData);
}
```

### Assertions courantes

| Assertion                                                      | Description                    |
| -------------------------------------------------------------- | ------------------------------ |
| `assertResponseIsSuccessful()`                                 | Vérifier le code 200           |
| `assertResponseStatusCodeSame(404)`                            | Vérifier un code spécifique    |
| `assertResponseHeaderSame('content-type', 'application/json')` | Vérifier les headers           |
| `assertArrayHasKey('key', $array)`                             | Vérifier la présence d'une clé |
| `assertCount(2, $array)`                                       | Vérifier le nombre d'éléments  |
| `assertEquals('value', $actual)`                               | Vérifier l'égalité             |

---

## 🎭 Tests avec Mocks

Les mocks permettent de tester la logique sans la base de données.

### Créer un mock

```php
// Créer un mock du repository
$repositoryMock = $this->createMock(AtelierRepository::class);

// Configurer le mock pour retourner une valeur
$repositoryMock->method('findAll')
    ->willReturn([]);

// Ou vérifier qu'une méthode est appelée
$repositoryMock->expects($this->once())
    ->method('findAll')
    ->willReturn([]);
```

### Exemple complet

```php
public function testSearchFiltersByTitle(): void
{
    // Créer le mock
    $repositoryMock = $this->createMock(AtelierRepository::class);

    // Configurer le mock
    $repositoryMock->expects($this->once())
        ->method('findByTitrePartial')
        ->with('PHP')
        ->willReturn([]);

    // Créer une requête
    $request = new Request(['titre' => 'PHP']);

    // Appeler le contrôleur
    $controller = new AtelierApiController();
    $response = $controller->search($request, $repositoryMock);

    // Vérifier le résultat
    $responseData = json_decode($response->getContent(), true);
    $this->assertEquals('PHP', $responseData['query']['titre']);
}
```

---

## 📋 Types de Mocks courants

### Mock simple

```php
$mock = $this->createMock(MyClass::class);
$mock->method('myMethod')->willReturn('value');
```

### Mock avec vérification d'appel

```php
$mock->expects($this->once())      // Appelé exactement 1 fois
    ->method('myMethod')
    ->with('param')                 // Avec ce paramètre
    ->willReturn('value');
```

### Mock avec exception

```php
$mock->method('myMethod')
    ->willThrowException(new Exception('Error'));
```

### Mock avec callback

```php
$mock->method('myMethod')
    ->will($this->returnCallback(function($param) {
        return $param * 2;
    }));
```

---

## 🛠️ Créer de nouveaux tests

### Template de test fonctionnel

```php
<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MyEndpointTest extends WebTestCase
{
    /**
     * @test
     */
    public function testMyEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/my-endpoint');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        // Vos assertions...
    }
}
```

### Template de test unitaire

```php
<?php

namespace App\Tests\Unit;

use App\MyClass;
use PHPUnit\Framework\TestCase;

class MyClassTest extends TestCase
{
    private MyClass $myClass;

    protected function setUp(): void
    {
        $this->myClass = new MyClass();
    }

    /**
     * @test
     */
    public function testMyMethod(): void
    {
        $result = $this->myClass->myMethod();
        $this->assertEquals('expected', $result);
    }
}
```

---

## 🧪 Tests avec Fixtures

Pour remplir la base de données de test avec des données :

```php
// Créer une fixture
$atelier = new Atelier();
$atelier->setTitre('PHP');
$entityManager->persist($atelier);
$entityManager->flush();

// Puis tester
$client->request('GET', '/api/ateliers');
$responseData = json_decode($client->getResponse()->getContent(), true);
$this->assertCount(1, $responseData['ateliers']);
```

---

## 📊 Rapport de couverture

Générer un rapport HTML de couverture de code :

```bash
symfony console phpunit --coverage-html coverage/
```

Ouvrez `coverage/index.html` dans votre navigateur.

---

## ⚠️ Bonnes pratiques

1. ✅ Un test = une responsabilité
2. ✅ Nommer les tests clairement : `testSearchFiltersByTitle`
3. ✅ Utiliser `@test` ou `test` pour les méthodes de test
4. ✅ Isoler les tests avec des mocks
5. ✅ Vérifier à la fois les cas positifs et négatifs
6. ✅ Garder les tests rapides
7. ✅ Documenter les cas complexes

---

## 🚀 CI/CD avec GitHub Actions

Exemple de workflow pour exécuter les tests :

```yaml
name: Tests

on: [push, pull_request]

jobs:
    test:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v2
            - uses: php-actions/composer@v6
            - run: vendor/bin/phpunit
```

---

## 📚 Ressources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Symfony Testing Guide](https://symfony.com/doc/current/testing.html)
- [WebTestCase Reference](https://symfony.com/doc/current/testing/webTestCase.html)

---

**Dernière mise à jour :** April 2026
