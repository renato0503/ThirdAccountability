import { GoogleAuth } from 'google-auth-library';
import { readFileSync, existsSync } from 'fs';

const UID = 'wFBPza3O3CRouLoybF2lHregGb52';
const EMAIL = 'gestor.renatorosa@gmail.com';
const NAME = 'Renato Rosa';
const ROLE = 'ADMIN_GERAL';
const PROJECT_ID = 'gestaosetor3';

async function getAccessToken() {
  // Try gcloud ADC first
  const adcPath = process.env.GOOGLE_APPLICATION_CREDENTIALS ||
    `${process.env.APPDATA}\\gcloud\\application_default_credentials.json`;

  try {
    const auth = new GoogleAuth({
      scopes: ['https://www.googleapis.com/auth/cloud-platform',
               'https://www.googleapis.com/auth/firebase'],
    });
    const client = await auth.getClient();
    const token = await client.getAccessToken();
    if (token?.token) return token.token;
  } catch (e) {
    console.log('ADC nao funcionou:', e.message);
  }

  return null;
}

async function run() {
  const token = await getAccessToken();
  if (!token) {
    console.log('Nao foi possivel obter token de acesso.');
    console.log('');
    console.log('Execute no terminal:');
    console.log('  gcloud auth application-default login');
    console.log('E tente novamente.');
    process.exit(1);
  }

  console.log('Token obtido com sucesso!');

  // 1. Set custom claims via Firebase Auth REST API
  try {
    const url = `https://identitytoolkit.googleapis.com/v1/accounts:update?key=AIzaSyDyUHPkkKqBjLFUSgk8iYb8eYM1YQ4kFt4`;
    const body = {
      localId: UID,
      customAttributes: JSON.stringify({ role: ROLE, institution_id: null }),
    };
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json();
    if (res.ok) {
      console.log('✓ Custom claims setados:', { role: ROLE, institution_id: null });
    } else {
      console.log('✗ Erro claims - pode precisar de token Admin SDK');
      console.log('  Resposta:', JSON.stringify(data));
    }
  } catch (e) {
    console.log('✗ Erro claims:', e.message);
  }

  // 2. Create Firestore document via REST API
  try {
    const url = `https://firestore.googleapis.com/v1/projects/${PROJECT_ID}/databases/(default)/documents/users/${UID}`;
    const body = {
      fields: {
        uid: { stringValue: UID },
        email: { stringValue: EMAIL },
        name: { stringValue: NAME },
        photoURL: { stringValue: '' },
        role: { stringValue: ROLE },
        institutionId: { nullValue: null },
        ativo: { booleanValue: true },
        createdAt: { timestampValue: new Date().toISOString() },
        updatedAt: { timestampValue: new Date().toISOString() },
      },
    };
    const res = await fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(body),
    });
    const data = await res.json();
    if (res.ok) {
      console.log('✓ Documento do usuario criado no Firestore');
    } else {
      console.log('✗ Erro Firestore:', JSON.stringify(data));
    }
  } catch (e) {
    console.log('✗ Erro Firestore:', e.message);
  }

  // 3. Verify
  try {
    const url = `https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=AIzaSyDyUHPkkKqBjLFUSgk8iYb8eYM1YQ4kFt4`;
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ localId: [UID] }),
    });
    const data = await res.json();
    if (res.ok && data.users?.[0]) {
      console.log('\n--- VERIFICACAO ---');
      console.log('UID:', UID);
      console.log('Email:', data.users[0].email);
      console.log('Claims:', data.users[0].providerUserInfo?.[0]?.customAttributes || data.users[0].customAttributes);
    }
  } catch (e) {
    console.log('✗ Erro verificacao:', e.message);
  }

  console.log('\n✅ Seed concluido!');
  process.exit(0);
}

run().catch((e) => {
  console.error('Erro fatal:', e);
  process.exit(1);
});
