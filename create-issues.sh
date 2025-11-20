#!/bin/bash

###############################################################################
# Script to create GitHub issues for the HydroSense project using gh CLI
#
# Prerequisites:
#   - gh CLI must be installed (https://cli.github.com/)
#   - You must be authenticated: gh auth login
#
# Usage:
#   ./create-issues.sh
###############################################################################

set -e

REPO="Oipnet/HydroSense"

echo "🚀 Starting GitHub issues creation for HydroSense"
echo "Repository: $REPO"
echo ""

# Check if gh is installed
if ! command -v gh &> /dev/null; then
    echo "❌ Error: gh CLI is not installed"
    echo "Please install it from: https://cli.github.com/"
    exit 1
fi

# Check if authenticated
if ! gh auth status &> /dev/null; then
    echo "❌ Error: Not authenticated with GitHub"
    echo "Please run: gh auth login"
    exit 1
fi

echo "✓ gh CLI is installed and authenticated"
echo ""

# Function to create a label if it doesn't exist
create_label_if_needed() {
    local label_name=$1
    local label_color=$2
    
    if gh label list --repo "$REPO" | grep -q "^${label_name}"; then
        echo "  ✓ Label \"$label_name\" already exists"
    else
        gh label create "$label_name" --color "$label_color" --repo "$REPO" 2>/dev/null && \
            echo "  ✓ Created label \"$label_name\"" || \
            echo "  ✗ Failed to create label \"$label_name\""
    fi
}

echo "📝 Step 1: Creating labels..."

# Create all necessary labels with colors
create_label_if_needed "epic:setup" "0E8A16"
create_label_if_needed "epic:backend" "1D76DB"
create_label_if_needed "epic:frontend" "FBCA04"
create_label_if_needed "epic:infra" "D93F0B"
create_label_if_needed "epic:ia" "8B4789"
create_label_if_needed "backend" "0075CA"
create_label_if_needed "frontend" "F9D0C4"
create_label_if_needed "infra" "E99695"
create_label_if_needed "ia" "C5DEF5"

echo ""
echo "✅ Labels creation completed"
echo ""
echo "📋 Step 2: Creating issues..."
echo ""

SUCCESS_COUNT=0
FAIL_COUNT=0

# Function to create an issue
create_issue() {
    local title=$1
    local body=$2
    local labels=$3
    local issue_num=$4
    
    echo "Creating issue $issue_num/24: $title"
    
    if gh issue create --repo "$REPO" --title "$title" --body "$body" --label "$labels" > /dev/null 2>&1; then
        echo "  ✓ Successfully created"
        ((SUCCESS_COUNT++))
    else
        echo "  ✗ Failed to create"
        ((FAIL_COUNT++))
    fi
    
    # Small delay to avoid rate limiting
    sleep 0.5
}

# Issue 1: EPIC-1
create_issue "[EPIC-1] Initialiser le monorepo" \
"## Description

Créer la structure de base du monorepo avec les dossiers backend, frontend et infra.

## Objectif

- Avoir un monorepo propre pour HydroSense, avec une structure claire.

## Tâches

- [ ] Créer les dossiers \`backend/\`, \`frontend/\`, \`infra/\`.
- [ ] Ajouter un \`.gitignore\` à la racine.
- [ ] Ajouter un \`README.md\` à la racine décrivant brièvement le projet.

## Acceptance criteria

- [ ] Le dépôt contient les trois dossiers.
- [ ] Le README racine décrit le projet et la structure.
- [ ] Le \`.gitignore\` couvre au minimum les artefacts PHP, Node, et les dossiers de build." \
"epic:setup,infra" 1

# Issue 2: EPIC-2
create_issue "[EPIC-2] Initialiser backend Symfony + API Platform" \
"## Description

Installer Symfony 7, API Platform et la base de config pour l'API.

## Objectif

- Avoir un projet Symfony 7 avec API Platform installé dans \`/backend\`.

## Tâches

- [ ] Créer un projet Symfony 7 dans \`/backend\`.
- [ ] Installer API Platform.
- [ ] Installer et configurer le bundle JWT (LexikJWT).
- [ ] Configurer CORS pour autoriser les appels depuis le frontend Nuxt.
- [ ] Ajouter un README spécifique au backend.

## Acceptance criteria

- [ ] L'URL \`/api\` affiche l'interface API Platform.
- [ ] Le bundle JWT est installé et configuré.
- [ ] La configuration CORS permet au frontend d'appeler l'API en dev." \
"epic:backend,backend" 2

# Issue 3: EPIC-2
create_issue "[EPIC-2] User + Authentification JWT" \
"## Description

Créer l'entité User et la mécanique de login JWT.

## Objectif

- Permettre à un utilisateur de se connecter et de récupérer son profil.

## Tâches

- [ ] Créer l'entité \`User\` (id, email, password, roles, name).
- [ ] Configurer le password hasher.
- [ ] Créer l'endpoint \`/api/auth/login\` qui retourne un JWT.
- [ ] Créer l'endpoint \`/api/me\` qui renvoie les infos de l'utilisateur connecté.

## Acceptance criteria

- [ ] Un user peut se connecter avec email/password.
- [ ] Un token JWT est retourné au login.
- [ ] \`GET /api/me\` retourne bien l'utilisateur connecté." \
"epic:backend,backend" 3

# Issue 4: EPIC-2
create_issue "[EPIC-2] Entités Farm & Reservoir" \
"## Description

Gérer les exploitations et les bacs nutriments.

## Objectif

- Modéliser les fermes et les réservoirs liés à un utilisateur.

## Tâches

- [ ] Créer l'entité \`Farm\` (id, name, owner=User).
- [ ] Créer l'entité \`Reservoir\` (id, name, volumeLiters, createdAt, relation à Farm).
- [ ] Ajouter les annotations \`ApiResource\` sur \`Farm\` et \`Reservoir\`.
- [ ] Ajouter la sécurité : un user ne peut voir que ses farms et reservoirs.

## Acceptance criteria

- [ ] CRUD API Platform fonctionnel pour Farm et Reservoir.
- [ ] Un utilisateur ne peut ni lire ni modifier les farms/reservoirs d'un autre user." \
"epic:backend,backend" 4

# Issue 5: EPIC-2
create_issue "[EPIC-2] Entité CultureProfile (référentiel)" \
"## Description

Créer un référentiel de profiles de culture (plages pH, EC, etc.).

## Objectif

- Fournir des profiles de plantes avec plages idéales pour l'analyse future.

## Tâches

- [ ] Créer l'entité \`CultureProfile\` (name, phMin, phMax, ecMin, ecMax, waterTempMin, waterTempMax).
- [ ] Ajouter \`ApiResource\` en lecture seule.
- [ ] Ajouter des fixtures pour plusieurs cultures (laitue, basilic, fraises, micro-pousses, etc.).

## Acceptance criteria

- [ ] \`GET /api/culture_profiles\` retourne une liste de profiles.
- [ ] Les champs min/max sont correctement typés et exposés." \
"epic:backend,backend" 5

# Issue 6: EPIC-2
create_issue "[EPIC-2] Entité Measurement (mesures pH/EC/temp)" \
"## Description

Enregistrer les mesures manuelles ou importées par réservoir.

## Objectif

- Stocker les mesures de pH, EC et température de l'eau.

## Tâches

- [ ] Créer l'entité \`Measurement\` (reservoir, measuredAt, ph, ec, waterTemp, source).
- [ ] Exposer comme \`ApiResource\`.
- [ ] Ajouter un filtre par date (\`from\`, \`to\`).
- [ ] Ajouter un endpoint pour créer une mesure liée à un réservoir.

## Acceptance criteria

- [ ] Les mesures peuvent être créées et lues via l'API.
- [ ] Le filtrage par période fonctionne." \
"epic:backend,backend" 6

# Issue 7: EPIC-2
create_issue "[EPIC-2] Import CSV des mesures" \
"## Description

Permettre d'importer des mesures depuis un fichier CSV.

## Objectif

- Importer facilement un historique de mesures pour un réservoir.

## Tâches

- [ ] Créer un endpoint \`POST /api/reservoirs/{id}/measurements/import\`.
- [ ] Définir le format CSV (par ex : \`measuredAt;ph;ec;waterTemp\`).
- [ ] Parser le fichier et créer des \`Measurement\`.
- [ ] Gérer les erreurs de format proprement.

## Acceptance criteria

- [ ] Un fichier CSV valide importe plusieurs mesures.
- [ ] Les erreurs sont renvoyées avec un message clair." \
"epic:backend,backend" 7

# Issue 8: EPIC-2
create_issue "[EPIC-2] Entité Alert + moteur d'analyse simple" \
"## Description

Générer des alertes en fonction des mesures et des plages CultureProfile.

## Objectif

- Créer des alertes automatiques quand une mesure sort des plages définies.

## Tâches

- [ ] Créer l'entité \`Alert\` (reservoir, type, severity, createdAt, resolvedAt).
- [ ] Créer un service \`AnomalyDetector\` qui analyse une Measurement et retourne éventuelles Alert.
- [ ] Créer un Processor API Platform qui, lors de la création de Measurement, appelle l'\`AnomalyDetector\` et persiste les Alert nécessaires.

## Acceptance criteria

- [ ] Une mesure hors plage (pH, EC, Temp) génère une alerte.
- [ ] Les alertes sont accessibles via l'API." \
"epic:backend,backend" 8

# Issue 9: EPIC-2
create_issue "[EPIC-2] Entité JournalEntry (journal de culture)" \
"## Description

Permettre d'ajouter des notes et photos par réservoir.

## Objectif

- Enregistrer les notes journalières et observations de culture.

## Tâches

- [ ] Créer l'entité \`JournalEntry\` (reservoir, content, createdAt, photoUrl).
- [ ] Exposer les endpoints CRUD via API Platform.
- [ ] Ajouter un mécanisme simple d'upload d'image (local ou S3-like).

## Acceptance criteria

- [ ] Il est possible de créer, lister et supprimer des entrées de journal pour un réservoir.
- [ ] Le champ photoUrl est rempli lors d'un upload." \
"epic:backend,backend" 9

# Issue 10: EPIC-2
create_issue "[EPIC-2] Endpoint Dashboard (vue synthèse backend)" \
"## Description

Fournir un endpoint de synthèse pour le dashboard global.

## Objectif

- Avoir une route unique renvoyant l'état global de la ferme pour l'utilisateur.

## Tâches

- [ ] Créer un Provider custom API Platform sur \`/api/dashboard\`.
- [ ] Retourner : nombre de réservoirs, dernières mesures par réservoir, nombre d'alertes critiques ouvertes.

## Acceptance criteria

- [ ] L'endpoint renvoie un JSON structuré prêt à consommer côté frontend." \
"epic:backend,backend" 10

# Issue 11: EPIC-2
create_issue "[EPIC-2] OpenAPI propre et documenté" \
"## Description

Nettoyer la spec OpenAPI pour faciliter la génération de client et l'usage par une IA.

## Objectif

- Avoir un OpenAPI 3 propre, complet et bien documenté.

## Tâches

- [ ] Ajouter descriptions et examples sur les ressources principales (User, Farm, Reservoir, Measurement, Alert, JournalEntry).
- [ ] Vérifier les noms des schémas.
- [ ] Générer le fichier \`openapi.json\` exposé publiquement.

## Acceptance criteria

- [ ] Le fichier openapi.json est généré et exploitable.
- [ ] Les exemples permettent de comprendre rapidement les payloads." \
"epic:backend,backend,ia" 11

# Issue 12: EPIC-3
create_issue "[EPIC-3] Initialiser Nuxt 3 + Tailwind + Pinia" \
"## Description

Setup du frontend Nuxt 3.

## Objectif

- Avoir une base Nuxt 3 fonctionnelle.

## Tâches

- [ ] Créer le projet Nuxt dans \`/frontend\`.
- [ ] Ajouter TypeScript.
- [ ] Ajouter TailwindCSS.
- [ ] Ajouter Pinia.
- [ ] Configurer \`.env\` pour l'URL de l'API backend.

## Acceptance criteria

- [ ] \`npm run dev\` démarre l'app.
- [ ] Tailwind et Pinia sont bien fonctionnels." \
"epic:frontend,frontend" 12

# Issue 13: EPIC-3
create_issue "[EPIC-3] Générer le client API depuis OpenAPI" \
"## Description

Utiliser api-platform/client-generator pour générer le client Nuxt.

## Objectif

- Consommer l'API backend via un client généré automatiquement à partir d'OpenAPI.

## Tâches

- [ ] Utiliser \`@api-platform/client-generator\` avec le openapi.json du backend.
- [ ] Générer le client Nuxt (composables, types).
- [ ] Intégrer le client dans le code (dossier \`composables/api\` par exemple).

## Acceptance criteria

- [ ] Une requête de test vers \`/api/me\` fonctionne via le client généré." \
"epic:frontend,frontend" 13

# Issue 14: EPIC-3
create_issue "[EPIC-3] Auth (login + middleware)" \
"## Description

Gérer l'authentification côté Nuxt.

## Objectif

- Permettre à l'utilisateur de se connecter et protéger les routes \`/app/*\`.

## Tâches

- [ ] Créer la page \`/login\`.
- [ ] Créer un store \`useAuthStore\` pour gérer le JWT.
- [ ] Ajouter un middleware global qui redirige vers \`/login\` si non authentifié.

## Acceptance criteria

- [ ] Un utilisateur peut se connecter depuis \`/login\`.
- [ ] L'accès à \`/app/*\` redirige vers \`/login\` si non connecté." \
"epic:frontend,frontend" 14

# Issue 15: EPIC-3
create_issue "[EPIC-3] Page Liste des Réservoirs" \
"## Description

Lister les réservoirs de l'utilisateur.

## Objectif

- Afficher une liste des réservoirs avec quelques infos clés.

## Tâches

- [ ] Créer la page \`/app/reservoirs\`.
- [ ] Récupérer les réservoirs via le client API.
- [ ] Afficher name, volume, cultureProfile, statut global (OK/WARN/CRIT si possible).

## Acceptance criteria

- [ ] La page liste les réservoirs du user connecté.
- [ ] Un clic sur un réservoir renvoie vers sa page de détail." \
"epic:frontend,frontend" 15

# Issue 16: EPIC-3
create_issue "[EPIC-3] Page Détail d'un Réservoir" \
"## Description

Page de détail avec onglets (overview, mesures, alertes, journal).

## Objectif

- Fournir une vue centrale pour un réservoir.

## Tâches

- [ ] Créer la page \`/app/reservoirs/[id]\`.
- [ ] Ajouter des onglets : Vue d'ensemble, Mesures, Alertes, Journal.
- [ ] Charger les données du réservoir via le client API.

## Acceptance criteria

- [ ] L'URL \`/app/reservoirs/{id}\` affiche les infos du réservoir et les onglets." \
"epic:frontend,frontend" 16

# Issue 17: EPIC-3
create_issue "[EPIC-3] Onglet Mesures" \
"## Description

Graphiques et saisie des mesures.

## Objectif

- Visualiser et ajouter des mesures de pH/EC/température.

## Tâches

- [ ] Afficher des graphiques (Chart.js ou équivalent) pour pH, EC, Temp sur une période.
- [ ] Ajouter un formulaire pour créer une nouvelle measurement.
- [ ] Ajouter un formulaire d'upload CSV pour import.

## Acceptance criteria

- [ ] Les courbes s'affichent pour les mesures existantes.
- [ ] Ajouter une mesure met à jour les courbes.
- [ ] Import CSV ajoute plusieurs mesures." \
"epic:frontend,frontend" 17

# Issue 18: EPIC-3
create_issue "[EPIC-3] Onglet Alerts" \
"## Description

Affichage et gestion des alertes d'un réservoir.

## Objectif

- Permettre de visualiser et marquer les alertes comme résolues.

## Tâches

- [ ] Lister les alertes pour le réservoir.
- [ ] Afficher type et severity.
- [ ] Bouton \"Marquer comme résolue\" qui appelle l'API.

## Acceptance criteria

- [ ] Les alertes s'affichent clairement.
- [ ] Une alerte peut être marquée comme résolue et disparaît de la liste active." \
"epic:frontend,frontend" 18

# Issue 19: EPIC-3
create_issue "[EPIC-3] Onglet Journal" \
"## Description

Afficher et créer des entrées de journal.

## Objectif

- Permettre à l'utilisateur de documenter sa culture.

## Tâches

- [ ] Lister les \`JournalEntry\` liés au réservoir.
- [ ] Formulaire pour ajouter une nouvelle note (texte + photo optionnelle).

## Acceptance criteria

- [ ] Les notes existantes sont visibles.
- [ ] Une nouvelle note apparaît après soumission du formulaire." \
"epic:frontend,frontend" 19

# Issue 20: EPIC-3
create_issue "[EPIC-3] Dashboard global frontend" \
"## Description

Page d'accueil /app/dashboard avec synthèse.

## Objectif

- Avoir une vue d'ensemble de l'état de la ferme pour l'utilisateur.

## Tâches

- [ ] Créer la page \`/app/dashboard\`.
- [ ] Consommer l'endpoint \`/api/dashboard\`.
- [ ] Afficher nombre de réservoirs, alertes critiques, dernières mesures.

## Acceptance criteria

- [ ] Le dashboard affiche les données de synthèse et se charge sans erreur." \
"epic:frontend,frontend" 20

# Issue 21: EPIC-4
create_issue "[EPIC-4] Docker Compose backend + Postgres" \
"## Description

Fournir un environnement Docker pour le backend.

## Objectif

- Simplifier le setup backend avec Docker.

## Tâches

- [ ] Créer un \`docker-compose.yml\` avec services : PHP/Symfony, Postgres, Adminer (optionnel).
- [ ] Documenter dans le README backend comment lancer l'environnement.

## Acceptance criteria

- [ ] \`docker compose up\` démarre l'API Symfony et la base de données." \
"epic:infra,infra" 21

# Issue 22: EPIC-4
create_issue "[EPIC-4] Dockerfile de build Nuxt 3 (production)" \
"## Description

Permettre de builder et servir le frontend Nuxt en production.

## Objectif

- Avoir une image Docker pour servir le frontend en mode prod.

## Tâches

- [ ] Créer un Dockerfile multi-stage pour Nuxt 3 (build + runtime).
- [ ] Documenter la commande de build et de run.

## Acceptance criteria

- [ ] Une image Docker Nuxt 3 peut être buildée et lancée, et sert l'app correctement." \
"epic:infra,infra" 22

# Issue 23: EPIC-5
create_issue "[EPIC-5] Améliorer descriptions OpenAPI pour usage IA" \
"## Description

Adapter la spec OpenAPI pour faciliter la génération de code par IA.

## Objectif

- Rendre les schémas et descriptions auto-explicites pour une IA.

## Tâches

- [ ] Ajouter des descriptions claires aux schémas importants.
- [ ] Ajouter des examples de requêtes et réponses.

## Acceptance criteria

- [ ] Les principales opérations peuvent être comprises sans lire le code backend." \
"epic:ia,ia,backend" 23

# Issue 24: EPIC-5
create_issue "[EPIC-5] Ajouter docstrings sur Processors & Providers" \
"## Description

Ajouter de la documentation dans le code pour guider l'IA.

## Objectif

- Faciliter la maintenance assistée par IA.

## Tâches

- [ ] Ajouter des docblocks/docstrings explicites sur les Processors, Providers et services critiques (AnomalyDetector, etc.).

## Acceptance criteria

- [ ] Chaque classe clé a une docstring qui décrit son rôle, ses inputs et ses outputs." \
"epic:ia,ia,backend" 24

echo ""
echo "============================================================"
echo "📊 Summary:"
echo "  ✓ Successfully created: $SUCCESS_COUNT issues"
if [ $FAIL_COUNT -gt 0 ]; then
    echo "  ✗ Failed: $FAIL_COUNT issues"
fi
echo "============================================================"
echo ""
echo "✨ Done! Check your issues at:"
echo "   https://github.com/$REPO/issues"
echo ""
