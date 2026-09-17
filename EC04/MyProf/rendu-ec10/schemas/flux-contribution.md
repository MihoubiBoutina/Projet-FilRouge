# Source du schéma de contribution

```mermaid
flowchart LR
 A[Branche feature] --> B[Pull Request]
 B --> C[CI : PHPUnit + MkDocs]
 C --> D[Revue humaine et checklist]
 D --> E[Fusion main et publication]
```

L'export lisible fourni avec le guide est `flux-contribution.svg`.
