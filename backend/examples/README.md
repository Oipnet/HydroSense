# Exemples de fichiers CSV

Ce dossier contient des fichiers d'exemple pour tester l'import CSV de mesures.

## 📁 Fichiers disponibles

### `measurements_sample.csv`

Fichier CSV **valide** avec 5 mesures correctement formatées.

**Contenu :**

```csv
measuredAt;ph;ec;waterTemp
2024-11-20T10:30:00;6.5;1.8;22.5
2024-11-20T14:00:00;6.8;1.9;23.0
2024-11-20T18:30:00;6.6;1.85;22.8
2024-11-21T09:00:00;6.7;1.95;23.2
2024-11-21T12:30:00;6.9;2.0;23.5
```

**Utilisation :**

```bash
curl -X POST http://localhost:8000/api/reservoirs/1/measurements/import \
  -F "file=@backend/examples/measurements_sample.csv"
```

### `measurements_with_errors.csv`

Fichier CSV **avec erreurs** pour tester la gestion des erreurs.

**Contenu :**

```csv
measuredAt;ph;ec;waterTemp
2024-11-20T10:30:00;6.5;1.8;22.5
invalid-date;6.8;1.9;23.0
2024-11-20T18:30:00;invalid;1.85;22.8
2024-11-21T09:00:00;6.7;;
2024-11-21T12:30:00;6.9;2.0;23.5
```

**Erreurs présentes :**

-   Ligne 3 : Date invalide
-   Ligne 4 : Valeur pH invalide

**Résultat attendu :**

-   3 mesures importées (lignes 2, 5, 6)
-   2 erreurs reportées (lignes 3, 4)

## 📝 Format CSV requis

**Séparateur :** `;` (point-virgule)

**En-tête obligatoire :**

```
measuredAt;ph;ec;waterTemp
```

**Formats acceptés :**

-   **measuredAt** : ISO 8601 (`2024-11-20T10:30:00`, `2024-11-20 10:30:00`, `2024-11-20`)
-   **ph** : Float (ex: `6.5`, `6,5`)
-   **ec** : Float (ex: `1.8`, `1,8`)
-   **waterTemp** : Float (ex: `22.5`, `22,5`)

**Règles :**

-   Au moins une valeur (ph, ec, ou waterTemp) doit être renseignée
-   Les valeurs vides sont acceptées (ex: `;;` pour ec et waterTemp vides)
-   Le séparateur décimal peut être `.` ou `,`

## 🧪 Tester avec ces fichiers

### 1. Import du fichier valide

```bash
curl -X POST http://localhost:8000/api/reservoirs/1/measurements/import \
  -F "file=@backend/examples/measurements_sample.csv"
```

**Réponse attendue :**

```json
{
    "success": true,
    "imported": 5,
    "skipped": 0,
    "errors": []
}
```

### 2. Import du fichier avec erreurs

```bash
curl -X POST http://localhost:8000/api/reservoirs/1/measurements/import \
  -F "file=@backend/examples/measurements_with_errors.csv"
```

**Réponse attendue :**

```json
{
    "success": true,
    "imported": 3,
    "skipped": 2,
    "errors": [
        "Line 3: Invalid date format for measuredAt: \"invalid-date\" (expected ISO 8601 format)",
        "Line 4: Invalid numeric value for ph: \"invalid\""
    ]
}
```

## 📖 Documentation complète

Pour plus d'informations, consultez :

-   **[Guide de test complet](../docs/TESTING-CSV-IMPORT.md)**
-   **[Documentation de l'implémentation](../docs/EPIC-2-CSV-IMPORT-IMPLEMENTATION.md)**

---

**Dernière mise à jour :** 20 novembre 2024
