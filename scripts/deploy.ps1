# Script de deploy para Gestao Terceiro Setor
# Executar passos em ordem

Write-Host "=== DEPLOY GESTAO TERCEIRO SETOR ===" -ForegroundColor Cyan
Write-Host ""

# ─── PASSO 1: Autenticacao ───
Write-Host "PASSO 1: Autenticar no Google Cloud" -ForegroundColor Yellow
Write-Host "  Comando: gcloud auth login"
Write-Host "  Comando: gcloud config set project gestaosetor3"
Write-Host ""

# ─── PASSO 2: Service Account ───
Write-Host "PASSO 2: Criar Service Account para CI/CD" -ForegroundColor Yellow
Write-Host "  gcloud iam service-accounts create github-actions --display-name='GitHub Actions'"
Write-Host "  gcloud projects add-iam-policy-binding gestaosetor3 `
  --member='serviceAccount:github-actions@gestaosetor3.iam.gserviceaccount.com' `
  --role='roles/run.admin'"
Write-Host "  gcloud projects add-iam-policy-binding gestaosetor3 `
  --member='serviceAccount:github-actions@gestaosetor3.iam.gserviceaccount.com' `
  --role='roles/storage.admin'"
Write-Host "  gcloud projects add-iam-policy-binding gestaosetor3 `
  --member='serviceAccount:github-actions@gestaosetor3.iam.gserviceaccount.com' `
  --role='roles/iam.serviceAccountUser'"
Write-Host "  gcloud iam service-accounts keys create gcp-sa-key.json `
  --iam-account=github-actions@gestaosetor3.iam.gserviceaccount.com"
Write-Host ""

# ─── PASSO 3: Secret Manager ───
Write-Host "PASSO 3: Configurar Secrets no Secret Manager" -ForegroundColor Yellow
Write-Host "  echo -n 'gsk_sua_chave_aqui' | gcloud secrets create groq-api-key --data-file=-"
Write-Host "  gcloud secrets add-iam-policy-binding groq-api-key `
  --member='serviceAccount:github-actions@gestaosetor3.iam.gserviceaccount.com' `
  --role='roles/secretmanager.secretAccessor'"
Write-Host ""

# ─── PASSO 4: GitHub Secrets ───
Write-Host "PASSO 4: Configurar GitHub Secrets" -ForegroundColor Yellow
Write-Host "  gh secret set GCP_SA_KEY < gcp-sa-key.json"
Write-Host "  gh secret set FIREBASE_SERVICE_ACCOUNT < firebase-sa-key.json"
Write-Host ""

# ─── PASSO 5: Deploy Manual (alternativa) ───
Write-Host "PASSO 5: Deploy Manual Alternativo (sem CI/CD)" -ForegroundColor Yellow
Write-Host ""
Write-Host "  Opcao A - Cloud Run:"
Write-Host "    cd apps/api"
Write-Host "    gcloud builds submit --config=../../cloudbuild.yaml ."
Write-Host ""
Write-Host "  Opcao B - Firebase Cloud Functions:"
Write-Host "    (requer adaptacao dos controllers para functions)"
Write-Host ""
Write-Host "  Opcao C - Railway:"
Write-Host "    1. Conecte o repo ao Railway"
Write-Host "    2. Configure o build command: cd apps/api && pnpm install && pnpm run build"
Write-Host "    3. Configure o start command: node dist/main.js"
Write-Host "    4. Adicione as env vars no painel do Railway"
Write-Host ""

# ─── PASSO 6: Frontend ───
Write-Host "PASSO 6: Atualizar frontend com URL da API" -ForegroundColor Yellow
Write-Host "  Apos saber a URL da API (ex: https://gestao-api-xxxxx-uc.a.run.app):"
Write-Host "  Atualize apps/web/.env.production com:"
Write-Host "    VITE_API_URL=https://gestao-api-xxxxx-uc.a.run.app/api"
Write-Host "  Depois rebuild e redeploy:"
Write-Host "    cd apps/web && pnpm run build"
Write-Host "    cd ../.. && firebase deploy --only hosting --project gestaosetor3"
Write-Host ""

Write-Host "=== FIM ===" -ForegroundColor Cyan
