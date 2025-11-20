# 🚀 Guide de démarrage rapide - HydroSense Frontend

## Installation en 3 étapes

### 1️⃣ Installer les dépendances

```powershell
cd frontend
npm install
```

### 2️⃣ Configurer l'environnement

```powershell
Copy-Item .env.example .env
```

Vérifier que `.env` contient :

```env
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000
```

### 3️⃣ Lancer l'application

```powershell
npm run dev
```

Ouvrir : **http://localhost:3000**

---

## ✅ Validation rapide

### Page d'accueil

- ✅ Titre "HydroSense Frontend" visible
- ✅ Badge vert "Configuration initiale réussie"
- ✅ URL API affichée : `http://localhost:8000`

### Test Pinia

- ✅ Cliquer sur "Increment" → le compteur augmente
- ✅ Cliquer sur "Decrement" → le compteur diminue
- ✅ Le "Double" se met à jour automatiquement

### Test TailwindCSS

- ✅ Boutons colorés (bleu/vert)
- ✅ Cards avec ombres
- ✅ Layout responsive

---

## 📦 Scripts disponibles

```powershell
# Développement
npm run dev

# Build production
npm run build

# Preview production
npm run preview

# Type checking
npm run typecheck
```

---

## 🔧 Configuration

### API Backend

L'URL de l'API est configurée via la variable d'environnement :

```env
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000
```

Pour la changer, éditer le fichier `.env` puis relancer `npm run dev`.

### Utiliser l'API dans le code

```typescript
const api = useApi();

// GET
const farms = await api.get("/api/farms");

// POST
const newFarm = await api.post("/api/farms", { name: "Test" });
```

---

## 📚 Documentation complète

- **README.md** : Documentation complète du projet
- **docs/ISSUE-15-NUXT4-SETUP.md** : Guide d'implémentation détaillé

---

## 🆘 Problèmes courants

### Erreur de port

Si le port 3000 est déjà utilisé :

```powershell
npm run dev -- --port 3001
```

### Erreur de modules

Réinstaller les dépendances :

```powershell
rm -rf node_modules .nuxt
npm install
```

---

## 🎯 Prochaines étapes

1. Générer le client OpenAPI
2. Créer les pages Dashboard
3. Implémenter l'authentification
4. Développer les composants métier

---

✅ **Prêt à coder !**
