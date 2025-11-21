# Keycloak - Environnement de développement local

Ce dossier contient la configuration Docker Compose pour exécuter Keycloak localement à des fins de développement et de test d'intégration SSO/OIDC.

## 🎯 Objectif

Fournir une instance Keycloak locale pour :

- Tester l'authentification OIDC avec Better Auth (frontend Nuxt 4)
- Configurer des realms, clients et utilisateurs de test
- Valider l'intégration SSO avec le backend Symfony

## 🚀 Démarrage rapide

### Prérequis

- Docker et Docker Compose installés
- Port 8080 disponible sur votre machine

### Lancer Keycloak

```bash
cd infra/keycloak
docker compose up -d
```

### Arrêter Keycloak

```bash
docker compose down
```

### Arrêter et supprimer les données

```bash
docker compose down -v
```

## 🔐 Accès à l'administration

Une fois les conteneurs démarrés, Keycloak est accessible via :

- **URL** : http://localhost:8080
- **Console Admin** : http://localhost:8080/admin
- **Identifiant** : `admin`
- **Mot de passe** : `admin`

> ⚠️ **Attention** : Ces identifiants sont destinés uniquement au développement local. Ne jamais utiliser ces credentials en production.

## 📦 Architecture

Le stack Docker Compose comprend :

1. **PostgreSQL 15** : Base de données pour la persistance Keycloak

   - Database : `keycloak`
   - User : `keycloak`
   - Password : `keycloak`
   - Volume persistant : `keycloak_postgres_data`

2. **Keycloak (Quarkus)** : Serveur d'authentification
   - Image : `quay.io/keycloak/keycloak:latest`
   - Mode : `start-dev` (développement)
   - Port : `8080`
   - Dossier de montage optionnel : `./realms` (pour importer des realms)

## 🔧 Configuration

### Variables d'environnement

Les variables d'environnement principales sont définies dans `docker-compose.yml` :

**Keycloak Admin :**

- `KEYCLOAK_ADMIN` : admin
- `KEYCLOAK_ADMIN_PASSWORD` : admin

**Base de données :**

- `KC_DB` : postgres
- `KC_DB_URL_HOST` : postgres
- `KC_DB_URL_DATABASE` : keycloak
- `KC_DB_USERNAME` : keycloak
- `KC_DB_PASSWORD` : keycloak

**Développement :**

- `KC_HTTP_ENABLED` : true (HTTP activé en dev)
- `KC_HOSTNAME_STRICT` : false (pas de vérification stricte du hostname)

## 📝 Prochaines étapes

La configuration de Keycloak (realm, clients OIDC, utilisateurs de test) sera effectuée dans **l'issue #45 (KEYCLOAK-3)**.

## 🔍 Vérification

Pour vérifier que Keycloak fonctionne correctement :

```bash
# Vérifier les logs
docker compose logs -f keycloak

# Vérifier que les conteneurs sont en cours d'exécution
docker compose ps
```

Keycloak est prêt lorsque vous voyez dans les logs :

```
Running the server in development mode. DO NOT use this configuration in production.
```

## 🛠️ Dépannage

### Port 8080 déjà utilisé

Si le port 8080 est déjà occupé, vous pouvez le modifier dans `docker-compose.yml` :

```yaml
ports:
  - "8081:8080" # Utilisez 8081 au lieu de 8080
```

### Base de données corrompue

Si vous rencontrez des problèmes de base de données, supprimez le volume et recréez-le :

```bash
docker compose down -v
docker compose up -d
```

## 📚 Ressources

- [Documentation officielle Keycloak](https://www.keycloak.org/documentation)
- [Keycloak on Quarkus](https://www.keycloak.org/guides#getting-started)
- [API Platform + Keycloak](https://api-platform.com/docs/guides/security/)

---

**Epic** : EPIC-KEYCLOAK  
**Issue** : #44 - KEYCLOAK-2 Ajouter Keycloak en dev (Docker Compose)  
**Branche** : `43-keycloak-1-architecture-sso-diagramme-décisions`
