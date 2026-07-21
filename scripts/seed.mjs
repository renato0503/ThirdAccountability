import { initializeApp, getApps, cert } from 'firebase-admin/app';
import { getFirestore } from 'firebase-admin/firestore';
import { getAuth } from 'firebase-admin/auth';
import { readFileSync, existsSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));

const UID = 'wFBPza3O3CRouLoybF2lHregGb52';
const EMAIL = 'gestor.renatorosa@gmail.com';
const NAME = 'Renato Rosa';
const ROLE = 'ADMIN_GERAL';

// Tentar usar service account se existir
const saPaths = [
  process.env.GOOGLE_APPLICATION_CREDENTIALS,
  join(__dirname, 'service-account.json'),
  join(__dirname, '..', 'service-account.json'),
  'C:\\Users\\Renato\\.config\\gcloud\\application_default_credentials.json',
].filter(Boolean);

async function getCredentials() {
  for (const path of saPaths) {
    try {
      if (existsSync(path)) {
        const content = readFileSync(path, 'utf-8');
        const parsed = JSON.parse(content);
        if (parsed.client_email || parsed.private_key) {
          console.log('Usando credentials de:', path);
          return cert(parsed);
        }
      }
    } catch {}
  }

  // Tentar ler ADC do gcloud
  const adcPath = process.env.GOOGLE_APPLICATION_CREDENTIALS ||
    (process.platform === 'win32'
      ? `${process.env.USERPROFILE}\\.config\\gcloud\\application_default_credentials.json`
      : `${process.env.HOME}/.config/gcloud/application_default_credentials.json`);

  if (existsSync(adcPath)) {
    const content = readFileSync(adcPath, 'utf-8');
    const parsed = JSON.parse(content);
    if (parsed.client_email && parsed.private_key) {
      console.log('Usando ADC do gcloud');
      return cert(parsed);
    }
  }

  return undefined;
}

async function run() {
  const credential = await getCredentials();

  if (!credential) {
    console.log('Nenhum service account encontrado.');
    console.log('');
    console.log('Opcao 1: Baixe a service account do Firebase Console:');
    console.log('  1. Acesse https://console.firebase.google.com/project/gestaosetor3/settings/serviceaccounts/adminsdk');
    console.log('  2. Clique em "Gerar nova chave privada"');
    console.log('  3. Salve o arquivo como scripts/service-account.json');
    console.log('  4. Execute este script novamente');
    console.log('');
    console.log('Opcao 2: Use o gcloud:');
    console.log('  gcloud auth application-default login');
    console.log('  E execute este script novamente');
    process.exit(1);
  }

  if (getApps().length === 0) {
    initializeApp({ credential, projectId: 'gestaosetor3' });
    console.log('Firebase Admin inicializado - Projeto: gestaosetor3');
  }

  try {
    await getAuth().setCustomUserClaims(UID, { role: ROLE, institution_id: null });
    console.log('✓ Custom claims setados:', { role: ROLE, institution_id: null });
  } catch (e) {
    console.log('✗ Erro ao setar custom claims:', e.message);
  }

  try {
    const userRef = getFirestore().collection('users').doc(UID);
    await userRef.set({
      uid: UID,
      email: EMAIL,
      name: NAME,
      photoURL: '',
      role: ROLE,
      institutionId: null,
      ativo: true,
      createdAt: new Date(),
      updatedAt: new Date(),
    }, { merge: true });
    console.log('✓ Documento do usuario criado no Firestore');
  } catch (e) {
    console.log('✗ Erro ao criar documento:', e.message);
  }

  // Verificar
  try {
    const user = await getAuth().getUser(UID);
    console.log('\n--- VERIFICACAO ---');
    console.log('UID:', user.uid);
    console.log('Email:', user.email);
    console.log('Claims:', JSON.stringify(user.customClaims));
  } catch (e) {
    console.log('✗ Erro ao verificar:', e.message);
  }

  const doc = await getFirestore().collection('users').doc(UID).get();
  if (doc.exists) {
    console.log('Firestore doc:', JSON.stringify(doc.data(), null, 2));
  }

  console.log('\n✅ Seed concluido!');
  process.exit(0);
}

run().catch((e) => {
  console.error('Erro fatal:', e);
  process.exit(1);
});
