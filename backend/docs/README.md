# Documentation Backend HydroSense

Ce dossier contient toute la documentation technique du backend HydroSense.

## 📚 Documents disponibles

### 🌱 EPIC-2 : Journal de Culture (JournalEntry)

**Guide rapide** : [README-JOURNAL-ENTRY.md](./README-JOURNAL-ENTRY.md)

1. **[EPIC-2-JOURNAL-ENTRY-IMPLEMENTATION.md](./EPIC-2-JOURNAL-ENTRY-IMPLEMENTATION.md)**

    - Documentation technique complète (500+ lignes)
    - Architecture et sécurité multi-niveaux
    - Modèle de données et relations
    - Validation et lifecycle callbacks
    - Guide de test complet

2. **[TESTING-JOURNAL-ENTRY-API.md](./TESTING-JOURNAL-ENTRY-API.md)**

    - Scripts de test PowerShell prêts à l'emploi
    - Tests de sécurité (cross-user)
    - Tests de validation
    - Script automatisé complet

3. **[QUICKSTART-JOURNAL-ENTRY.md](./QUICKSTART-JOURNAL-ENTRY.md)**

    - Démarrage rapide (5 minutes)
    - Configuration et test immédiat
    - Troubleshooting

4. **[DIAGRAMS-JOURNAL-ENTRY.md](./DIAGRAMS-JOURNAL-ENTRY.md)**

    - Schémas d'architecture ASCII
    - Diagrammes de flux
    - Cas d'utilisation visuels

5. **[SYNTHESE-JOURNAL-ENTRY.md](./SYNTHESE-JOURNAL-ENTRY.md)**

    - Synthèse complète de l'implémentation
    - Statistiques et métriques
    - Checklist de déploiement

6. **Fichiers supplémentaires** :
    - [INDEX-JOURNAL-ENTRY.md](./INDEX-JOURNAL-ENTRY.md) - Index de tous les fichiers
    - [CHANGELOG-JOURNAL-ENTRY.md](./CHANGELOG-JOURNAL-ENTRY.md) - Historique des changements
    - [COMMIT-MESSAGE-JOURNAL-ENTRY.md](./COMMIT-MESSAGE-JOURNAL-ENTRY.md) - Message de commit Git
    - [README-COMPLETE-JOURNAL-ENTRY.md](./README-COMPLETE-JOURNAL-ENTRY.md) - Résumé visuel
    - [ISSUE-12-COMPLETE.md](./ISSUE-12-COMPLETE.md) - Rapport pour GitHub issue #12

### 🏭 EPIC-2 : Gestion des Fermes et Réservoirs

7. **[EPIC-2-FARM-RESERVOIR-IMPLEMENTATION.md](./EPIC-2-FARM-RESERVOIR-IMPLEMENTATION.md)**
    - Documentation complète de la gestion des fermes et réservoirs
    - Architecture et sécurité par utilisateur
    - Modèle de données (Farm ↔ Reservoir)
    - QueryExtensions pour le filtrage automatique
    - Guide de test avec scénarios multi-utilisateurs

### 📊 EPIC-2 : Mesures (Measurements)

8. **[EPIC-2-MEASUREMENT-IMPLEMENTATION.md](./EPIC-2-MEASUREMENT-IMPLEMENTATION.md)**

    - Gestion des mesures de pH, EC, température
    - Import CSV et API REST
    - Architecture complète

9. **[EPIC-2-MEASUREMENT-SUMMARY.md](./EPIC-2-MEASUREMENT-SUMMARY.md)**

    - Résumé de l'implémentation des mesures

10. **[TESTING-MEASUREMENT-API.md](./TESTING-MEASUREMENT-API.md)**
    - Guide de test des endpoints de mesure

### 📥 EPIC-2 : Import CSV des Mesures

11. **[EPIC-2-CSV-IMPORT-IMPLEMENTATION.md](./EPIC-2-CSV-IMPORT-IMPLEMENTATION.md)**

    -   Documentation complète de l'implémentation de l'import CSV
    -   Architecture et choix techniques
    -   Schéma de base de données
    -   Format CSV attendu
    -   Gestion des erreurs
    -   Exemples d'utilisation

12. **[TESTING-CSV-IMPORT.md](./TESTING-CSV-IMPORT.md)**
    -   Guide de test détaillé
    -   Exemples avec curl et HTTPie
    -   Cas de test (succès, erreurs, validations)
    -   Vérification des données importées
    -   Résolution de problèmes

### 🚨 EPIC-2 : Système d'Alertes

13. **[EPIC-2-ALERT-IMPLEMENTATION.md](./EPIC-2-ALERT-IMPLEMENTATION.md)**

    -   Système d'alertes automatiques
    -   Moteur d'analyse des mesures
    -   Configuration des seuils

14. **[EPIC-2-ALERT-SYSTEM-SUMMARY.md](./EPIC-2-ALERT-SYSTEM-SUMMARY.md)**

    -   Résumé du système d'alertes

15. **[README-ALERTS.md](./README-ALERTS.md)**

    -   Guide rapide des alertes

16. **[TESTING-ALERT-API.md](./TESTING-ALERT-API.md)**

    -   Tests des alertes

17. **[INSTALLATION-ALERTS.md](./INSTALLATION-ALERTS.md)**
    -   Installation du système d'alertes

### 🌾 Profils de Culture

18. **[EPIC-2-CultureProfile-IMPLEMENTATION.md](./EPIC-2-CultureProfile-IMPLEMENTATION.md)**
    -   Gestion des profils de culture
    -   Configuration des paramètres optimaux

### 🔌 EPIC-3 : Frontend Nuxt 4

19. **[ISSUE-15-NUXT4-SETUP.md](./ISSUE-15-NUXT4-SETUP.md)**

    -   Configuration Nuxt 4 + TypeScript + Pinia + TailwindCSS
    -   Architecture frontend complète

20. **[ISSUE-15-COMPLETE.md](./ISSUE-15-COMPLETE.md)**

    -   Résumé de l'implémentation Nuxt 4

21. **[COMMIT-MESSAGE-NUXT4.md](./COMMIT-MESSAGE-NUXT4.md)**

    -   Message de commit pour l'issue #15

22. **[ISSUE-16-OPENAPI-CLIENT.md](./ISSUE-16-OPENAPI-CLIENT.md)**

    -   Génération automatique du client API TypeScript
    -   Integration openapi-typescript + openapi-fetch
    -   Composables typés pour l'API

23. **[COMMIT-MESSAGE-OPENAPI-CLIENT.md](./COMMIT-MESSAGE-OPENAPI-CLIENT.md)**
    -   Message de commit pour l'issue #16

### 🏗️ Architecture

19. **[REFACTORING-STATE-PROCESSOR.md](./REFACTORING-STATE-PROCESSOR.md)**
    -   Documentation du refactoring Controller → State Processor
    -   Comparaison avant/après
    -   Avantages de l'architecture modernisée
    -   Guide de migration

## 🚀 Démarrage rapide

### Nouveau : Tester le Journal de Culture (JournalEntry)

**Le plus rapide** : [QUICKSTART-JOURNAL-ENTRY.md](./QUICKSTART-JOURNAL-ENTRY.md) (5 minutes)

1. Démarrer le serveur : `symfony server:start`
2. S'authentifier et obtenir un token JWT
3. Créer une entrée de journal : voir [TESTING-JOURNAL-ENTRY-API.md](./TESTING-JOURNAL-ENTRY-API.md)

### Tester la gestion des Fermes et Réservoirs

1. Démarrer le serveur : `symfony server:start`
2. Créer 2 utilisateurs de test
3. S'authentifier et créer des fermes : voir [EPIC-2-FARM-RESERVOIR-IMPLEMENTATION.md](./EPIC-2-FARM-RESERVOIR-IMPLEMENTATION.md#guide-de-test)

### Tester l'import CSV

1. Créer une ferme et un réservoir
2. Importer un CSV : voir [TESTING-CSV-IMPORT.md](./TESTING-CSV-IMPORT.md#test-1--import-csv-valide)

## 📁 Structure de la documentation

```
docs/
├── README.md                                      ← Vous êtes ici
│
├── 🌱 Journal de Culture (JournalEntry)
│   ├── README-JOURNAL-ENTRY.md                   ← Guide rapide
│   ├── EPIC-2-JOURNAL-ENTRY-IMPLEMENTATION.md    ← Doc technique complète
│   ├── TESTING-JOURNAL-ENTRY-API.md              ← Scripts de test
│   ├── QUICKSTART-JOURNAL-ENTRY.md               ← Démarrage rapide
│   ├── DIAGRAMS-JOURNAL-ENTRY.md                 ← Schémas d'architecture
│   ├── SYNTHESE-JOURNAL-ENTRY.md                 ← Synthèse complète
│   ├── INDEX-JOURNAL-ENTRY.md                    ← Index des fichiers
│   ├── CHANGELOG-JOURNAL-ENTRY.md                ← Historique
│   ├── COMMIT-MESSAGE-JOURNAL-ENTRY.md           ← Message de commit
│   ├── README-COMPLETE-JOURNAL-ENTRY.md          ← Résumé visuel
│   └── ISSUE-12-COMPLETE.md                      ← Rapport GitHub
│
├── 🏭 Fermes & Réservoirs
│   └── EPIC-2-FARM-RESERVOIR-IMPLEMENTATION.md
│
├── 📊 Mesures
│   ├── EPIC-2-MEASUREMENT-IMPLEMENTATION.md
│   ├── EPIC-2-MEASUREMENT-SUMMARY.md
│   └── TESTING-MEASUREMENT-API.md
│
├── 📥 Import CSV
│   ├── EPIC-2-CSV-IMPORT-IMPLEMENTATION.md
│   └── TESTING-CSV-IMPORT.md
│
├── 🚨 Alertes
│   ├── EPIC-2-ALERT-IMPLEMENTATION.md
│   ├── EPIC-2-ALERT-SYSTEM-SUMMARY.md
│   ├── README-ALERTS.md
│   ├── TESTING-ALERT-API.md
│   └── INSTALLATION-ALERTS.md
│
├── 🌾 Profils de Culture
│   └── EPIC-2-CultureProfile-IMPLEMENTATION.md
│
├── 🔌 Frontend Nuxt 4
│   ├── ISSUE-15-NUXT4-SETUP.md
│   ├── ISSUE-15-COMPLETE.md
│   ├── COMMIT-MESSAGE-NUXT4.md
│   ├── ISSUE-16-OPENAPI-CLIENT.md
│   └── COMMIT-MESSAGE-OPENAPI-CLIENT.md
│
└── 🏗️ Architecture
    └── REFACTORING-STATE-PROCESSOR.md
```

## 🔗 Liens utiles

-   [API Platform Documentation](https://api-platform.com/docs/)
-   [Symfony Documentation](https://symfony.com/doc/current/index.html)
-   [Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/current/)

## 📊 Statistiques de la documentation

-   **Total de fichiers** : 30 documents
-   **Documentation JournalEntry** : 2500+ lignes
-   **Documentation Frontend** : 1000+ lignes
-   **Guides de test** : 6 fichiers
-   **Guides de démarrage** : 4 fichiers
-   **Documentation technique** : 13 fichiers

## 🎯 Par rôle

### Pour les développeurs

-   [QUICKSTART-JOURNAL-ENTRY.md](./QUICKSTART-JOURNAL-ENTRY.md) - Démarrer rapidement
-   [TESTING-JOURNAL-ENTRY-API.md](./TESTING-JOURNAL-ENTRY-API.md) - Tester l'API
-   [TESTING-CSV-IMPORT.md](./TESTING-CSV-IMPORT.md) - Tester l'import CSV

### Pour les architectes

-   [EPIC-2-JOURNAL-ENTRY-IMPLEMENTATION.md](./EPIC-2-JOURNAL-ENTRY-IMPLEMENTATION.md) - Architecture JournalEntry
-   [DIAGRAMS-JOURNAL-ENTRY.md](./DIAGRAMS-JOURNAL-ENTRY.md) - Schémas visuels
-   [REFACTORING-STATE-PROCESSOR.md](./REFACTORING-STATE-PROCESSOR.md) - Architecture générale

### Pour les chefs de projet

-   [README-JOURNAL-ENTRY.md](./README-JOURNAL-ENTRY.md) - Vue d'ensemble
-   [SYNTHESE-JOURNAL-ENTRY.md](./SYNTHESE-JOURNAL-ENTRY.md) - Synthèse complète
-   [ISSUE-12-COMPLETE.md](./ISSUE-12-COMPLETE.md) - Rapport d'implémentation

---

**Dernière mise à jour :** 20 novembre 2025
