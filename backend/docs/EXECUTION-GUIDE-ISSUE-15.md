# 🎯 GUIDE D'EXÉCUTION - Issue #15

## Commandes à exécuter dans l'ordre

### 📍 Étape 1 : Se placer dans le dossier frontend

```powershell
cd C:\Users\pinf54\Documents\Boulot\HydroSense\frontend
```

---

### 📦 Étape 2 : Installer les dépendances

```powershell
npm install
```

**Durée estimée** : 1-2 minutes  
**Résultat attendu** : Installation de Nuxt 4, Pinia, TailwindCSS, TypeScript, etc.

---

### ⚙️ Étape 3 : Créer le fichier .env

```powershell
Copy-Item .env.example .env
```

Vérifier le contenu de `.env` :

```env
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000
NODE_ENV=development
```

---

### 🚀 Étape 4 : Lancer le serveur de développement

```powershell
npm run dev
```

**Résultat attendu** :

```
Nuxt 4.2.1 with Nitro 2.x.x

  ➜ Local:   http://localhost:3000/
  ➜ Network: use --host to expose

ℹ Using Tailwind CSS from ~/assets/css/main.css
✔ Vite client built in XXXms
✔ Nitro built in XXXms
```

---

### ✅ Étape 5 : Tester l'application

#### A. Ouvrir dans le navigateur

```
http://localhost:3000
```

#### B. Vérifications visuelles

-   [ ] Page "HydroSense Frontend" s'affiche
-   [ ] Badge vert "Configuration initiale réussie" visible
-   [ ] Card "Configuration API" affiche `http://localhost:8000`
-   [ ] Card "Pinia Store Demo" avec compteur visible

#### C. Test interactif Pinia

1. Cliquer sur **"+ Increment"** → le compteur augmente
2. Cliquer sur **"- Decrement"** → le compteur diminue
3. Cliquer sur **"Reset"** → le compteur revient à 0
4. Vérifier que "Double" se met à jour automatiquement

#### D. Test TailwindCSS

-   [ ] Boutons ont des couleurs (bleu/vert)
-   [ ] Cards ont des ombres portées
-   [ ] Layout responsive fonctionne
-   [ ] Header et footer présents

---

### 🔍 Étape 6 : Vérifier les types TypeScript

Dans un **nouveau terminal** (garder `npm run dev` actif) :

```powershell
cd C:\Users\pinf54\Documents\Boulot\HydroSense\frontend
npm run typecheck
```

**Résultat attendu** :

```
✔ Type checking completed without errors
```

---

### 📸 Étape 7 : Captures d'écran (optionnel)

Prendre des screenshots de :

1. Page d'accueil complète
2. Counter Pinia en action
3. Console du navigateur (pas d'erreur)
4. Terminal avec `npm run dev` actif

---

## 🎨 Personnalisation (optionnel)

### Changer l'URL de l'API

Éditer `frontend/.env` :

```env
NUXT_PUBLIC_API_BASE_URL=http://localhost:8080
```

Puis recharger la page.

### Changer le thème Tailwind

Éditer `frontend/tailwind.config.ts` :

```typescript
colors: {
  primary: {
    500: '#votre-couleur',
    // ...
  }
}
```

---

## 🐛 Dépannage rapide

### Problème : Port 3000 déjà utilisé

```powershell
npm run dev -- --port 3001
```

### Problème : Erreurs de modules

```powershell
Remove-Item -Recurse -Force node_modules, .nuxt
npm install
```

### Problème : Types non reconnus

```powershell
npm run postinstall
```

---

## 📝 Une fois validé

### A. Commiter les changements

```bash
git add .
git commit -m "feat: Initialize Nuxt 4 frontend with TypeScript, Pinia, and TailwindCSS (#15)"
```

### B. Pousser vers GitHub

```bash
git push origin 15-epic-3-initialiser-nuxt-4
```

### C. Créer la Pull Request

Titre : `feat: Initialize Nuxt 4 frontend with TypeScript, Pinia, and TailwindCSS (#15)`

Description : Utiliser le contenu de `backend/docs/COMMIT-MESSAGE-NUXT4.md`

---

## 📚 Documentation créée

Pour référence future :

1. **README.md** : Documentation complète du projet frontend
2. **QUICKSTART.md** : Guide de démarrage rapide (3 étapes)
3. **docs/ISSUE-15-NUXT4-SETUP.md** : Guide d'implémentation détaillé
4. **docs/ISSUE-15-COMPLETE.md** : Résumé de ce qui a été fait
5. **docs/COMMIT-MESSAGE-NUXT4.md** : Message de commit complet

---

## 🎯 Résultat final

✅ **Frontend Nuxt 4 opérationnel**  
✅ **TypeScript strict configuré**  
✅ **Pinia pour state management**  
✅ **TailwindCSS avec thème custom**  
✅ **API composable prêt**  
✅ **Layout et page de test**  
✅ **Documentation complète**

**Prêt pour l'issue #16 : Génération du client OpenAPI** 🚀
