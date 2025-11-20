# Documentation Backend HydroSense

Ce dossier contient toute la documentation technique du backend HydroSense.

## 📚 Documents disponibles

### EPIC-2 : Import CSV des Mesures

1. **[EPIC-2-CSV-IMPORT-IMPLEMENTATION.md](./EPIC-2-CSV-IMPORT-IMPLEMENTATION.md)**

    - Documentation complète de l'implémentation de l'import CSV
    - Architecture et choix techniques
    - Schéma de base de données
    - Format CSV attendu
    - Gestion des erreurs
    - Exemples d'utilisation

2. **[TESTING-CSV-IMPORT.md](./TESTING-CSV-IMPORT.md)**

    - Guide de test détaillé
    - Exemples avec curl et HTTPie
    - Cas de test (succès, erreurs, validations)
    - Vérification des données importées
    - Résolution de problèmes

3. **[REFACTORING-STATE-PROCESSOR.md](./REFACTORING-STATE-PROCESSOR.md)**
    - Documentation du refactoring Controller → State Processor
    - Comparaison avant/après
    - Avantages de l'architecture modernisée
    - Guide de migration

## 🚀 Démarrage rapide

Pour tester l'import CSV :

1. Démarrer le serveur : `symfony server:start`
2. Créer un réservoir : voir [TESTING-CSV-IMPORT.md](./TESTING-CSV-IMPORT.md#créer-un-réservoir-de-test)
3. Importer un CSV : voir [TESTING-CSV-IMPORT.md](./TESTING-CSV-IMPORT.md#test-1--import-csv-valide)

## 📁 Structure de la documentation

```
docs/
├── README.md                              ← Vous êtes ici
├── EPIC-2-CSV-IMPORT-IMPLEMENTATION.md   ← Doc complète
├── TESTING-CSV-IMPORT.md                  ← Guide de test
└── REFACTORING-STATE-PROCESSOR.md         ← Doc refactoring
```

## 🔗 Liens utiles

-   [API Platform Documentation](https://api-platform.com/docs/)
-   [Symfony Documentation](https://symfony.com/doc/current/index.html)
-   [Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/current/)

---

**Dernière mise à jour :** 20 novembre 2024
