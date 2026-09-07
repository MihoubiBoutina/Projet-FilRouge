# Projet Fil Rouge

[![CI/CD](https://github.com/MihoubiBoutina/Projet-FilRouge/actions/workflows/ci.yml/badge.svg)](https://github.com/MihoubiBoutina/Projet-FilRouge/actions/workflows/ci.yml)

Projet de formation compose de plusieurs EC. L'application principale Symfony REST & NoSQL est dans `EC04/MyProf`.

## Documentation

- [Documentation complete de MyProf](EC04/MyProf/Readme.md)
- [Guide des tests](EC04/MyProf/TESTING.md)
- [Workflow CI/CD](.github/workflows/ci.yml)
- [Hook pre-commit](tools/hooks/pre-commit)

## CI/CD et qualite

Le workflow GitHub Actions execute PHPUnit, prepare SQLite pour l'environnement de test, utilise un service MongoDB et construit puis publie l'image Docker dans GHCR apres un push valide.

Avant un commit, activer le hook versionne :

```bash
git config core.hooksPath tools/hooks
```

Les tags de version publies sont `v1.0.0` et `v1.0.1`.

## Structure

- `EC01` a `EC03` et `EC05` a `EC10` : autres travaux du projet.
- `EC04/MyProf` : application Symfony principale.
- `.github/workflows/ci.yml` : pipeline CI/CD.
- `tools/hooks/pre-commit` : controles locaux avant commit.
