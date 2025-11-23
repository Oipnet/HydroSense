# Commandes utiles - Proxy Edge

## 🧪 Tests

### Test simple (ping)

```bash
curl http://localhost:3000/api/edge/ping
```

Attendu : `{ "ok": true }`

### Test avec authentification (GET)

```bash
# Dans le navigateur (console)
fetch('/api/edge/reservoirs')
  .then(r => r.json())
  .then(console.log)
```

### Test POST

```bash
# Dans le navigateur (console)
fetch('/api/edge/reservoirs', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'Test Reservoir',
    capacity: 1000
  })
})
  .then(r => r.json())
  .then(console.log)
```

### Suite de tests complète

```bash
# Dans le navigateur (console)
# Charger les tests
import('./tests/edge-proxy.test').then(tests => {
  tests.runAllTests();
});
```

## 🔍 Débogage

### Vérifier les logs Nuxt

```bash
# Les logs du serveur Nuxt (edge proxy)
npm run dev

# Regarder les logs "[Edge Proxy]" dans la console
```

### Vérifier les logs Symfony

```bash
# Backend logs
cd backend
docker compose logs -f backend
```

### Inspecter la session Better Auth

```bash
# Dans le navigateur (console)
fetch('/api/auth/session')
  .then(r => r.json())
  .then(console.log)
```

### Vérifier les cookies

```bash
# Dans le navigateur (console)
console.log(document.cookie);

# Doit contenir : better-auth-session=...
```

### Vérifier qu'aucun token n'est exposé

```bash
# Dans le navigateur (console)
console.log('localStorage:', localStorage);
console.log('sessionStorage:', sessionStorage);

# Les deux doivent être vides (pas de token)
```

## 🔄 Migration

### Trouver les appels directs à migrer

```bash
# Dans le dossier frontend
cd frontend

# Rechercher les appels directs
grep -r "api.hydrosense" app/
grep -r "apiBaseUrl" app/
grep -r "Authorization.*Bearer" app/

# Rechercher les tokens manuels
grep -r "localStorage.getItem.*token" app/
grep -r "sessionStorage.getItem.*token" app/
```

### Remplacer automatiquement (avec précaution)

```bash
# ⚠️ À utiliser avec précaution - tester d'abord !

# Exemple : remplacer les URLs hardcodées
find app/ -type f -name "*.vue" -o -name "*.ts" | xargs sed -i '' 's|https://api.hydrosense.local/api/|/api/edge/|g'
```

## 📊 Analyse

### Compter les appels Edge

```bash
# Logs Nuxt
grep "\[Edge Proxy\]" logs.txt | wc -l
```

### Vérifier les erreurs

```bash
# Erreurs dans les logs Nuxt
grep "\[Edge Proxy\] Error" logs.txt
```

## 🧹 Nettoyage

### Supprimer les anciens fichiers d'auth

```bash
# Après migration complète, supprimer :
rm app/composables/useAuthToken.ts  # Si existe
rm app/lib/auth-old.ts              # Si existe

# Vérifier qu'aucun import ne reste
grep -r "useAuthToken" app/
```

### Nettoyer localStorage

```bash
# Dans le navigateur (console)
localStorage.removeItem('token');
localStorage.removeItem('accessToken');
localStorage.removeItem('jwt');
```

## 📦 Build & Deploy

### Build de production

```bash
npm run build
```

### Vérifier la config en production

```bash
# Les variables d'environnement doivent être définies
echo $API_URL
echo $BETTER_AUTH_SECRET
echo $KEYCLOAK_CLIENT_ID
```

### Démarrer en production

```bash
npm run start
```

### Test en production

```bash
curl https://votre-domaine.com/api/edge/ping
```

## 🔐 Sécurité

### Vérifier les headers de sécurité

```bash
curl -I https://votre-domaine.com/api/edge/ping

# Devrait contenir :
# - Strict-Transport-Security
# - X-Content-Type-Options
# - X-Frame-Options
```

### Tester qu'aucun token n'est exposé

```bash
# Network tab du navigateur
# Filtrer par "XHR" ou "Fetch"
# Vérifier qu'aucun header Authorization n'est envoyé depuis le browser
```

## 📈 Performance

### Mesurer la latency

```bash
# Dans le navigateur (console)
console.time('edge-proxy');
fetch('/api/edge/reservoirs')
  .then(() => console.timeEnd('edge-proxy'));
```

### Load testing

```bash
# Avec Apache Bench
ab -n 1000 -c 10 http://localhost:3000/api/edge/ping

# Avec wrk
wrk -t4 -c100 -d30s http://localhost:3000/api/edge/ping
```

## 🛠️ Maintenance

### Mettre à jour la doc

```bash
# Éditer les fichiers
vim docs/EDGE-PROXY.md

# Commit
git add docs/
git commit -m "docs: mise à jour proxy edge"
```

### Backup de la config

```bash
# Sauvegarder .env
cp .env .env.backup

# Avec date
cp .env .env.backup.$(date +%Y%m%d)
```

## 🆘 Dépannage d'urgence

### Le proxy ne répond pas

```bash
# 1. Vérifier que Nuxt tourne
ps aux | grep nuxt

# 2. Relancer
npm run dev

# 3. Tester ping
curl http://localhost:3000/api/edge/ping
```

### Erreurs 401 en masse

```bash
# 1. Vérifier la session Better Auth
# Console navigateur :
fetch('/api/auth/session').then(r => r.json()).then(console.log)

# 2. Si session invalide, se reconnecter
# Aller sur /login
```

### Erreurs 500 "API base URL not configured"

```bash
# 1. Vérifier .env
cat .env | grep API_URL

# 2. Si manquant, ajouter
echo "API_URL=http://localhost:8000" >> .env

# 3. Relancer
npm run dev
```

### Backend Symfony ne répond pas

```bash
# 1. Vérifier le backend
cd backend
docker compose ps

# 2. Relancer si nécessaire
docker compose up -d

# 3. Vérifier les logs
docker compose logs -f backend
```

## 📚 Commandes de documentation

### Générer un PDF de la doc

```bash
# Avec pandoc
pandoc docs/EDGE-PROXY.md -o edge-proxy.pdf

# Avec mdpdf
mdpdf docs/EDGE-PROXY.md
```

### Générer la table des matières

```bash
# Avec markdown-toc
npx markdown-toc docs/EDGE-PROXY.md
```

### Linter la doc

```bash
# Vérifier les liens cassés
npx markdown-link-check docs/*.md

# Linter markdown
npx markdownlint docs/*.md
```

## 🎓 Formation

### Présenter le proxy à l'équipe

```bash
# 1. Ouvrir la doc
open docs/EDGE-PROXY-QUICKSTART.md

# 2. Faire une démo live
npm run dev
# → Montrer /api/edge/ping
# → Montrer un appel GET dans la console
# → Montrer DevTools Network tab

# 3. Montrer le code
code server/api/edge/[...path].ts
```

---

**Note :** Ces commandes sont à adapter selon votre environnement et vos besoins.
