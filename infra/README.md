# Infrastructure HydroSense

Configuration Docker, CI/CD et outils de déploiement pour l'environnement HydroSense.

## 📁 Structure

```
infra/
├── docker/          # Configuration Docker
│   ├── Dockerfile.backend
│   ├── Dockerfile.frontend  
│   └── docker-compose.yml
└── ci/              # Scripts CI/CD
    ├── deploy.sh
    └── github-actions/
```

## 🐳 Docker

*Configuration Docker à venir lors de l'EPIC-4*

### Services
- **Backend** : Symfony + PostgreSQL
- **Frontend** : Nuxt 3 
- **Database** : PostgreSQL
- **Cache** : Redis (optionnel)

## 🚀 Déploiement

*Scripts et procédures de déploiement à définir lors de l'EPIC-4*

## 📋 Prérequis

- Docker & Docker Compose
- Accès aux registres de conteneurs (si applicable)