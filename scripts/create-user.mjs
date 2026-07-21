import { GoogleAuth } from 'google-auth-library';

const PROJECT_ID = process.env.FIREBASE_PROJECT_ID || 'gestaosetor3';
const API_KEY = process.env.FIREBASE_API_KEY; // Leia de .env ou variavel de ambiente

async function getToken() {
  const auth = new GoogleAuth({
    scopes: ['https://www.googleapis.com/auth/cloud-platform',
             'https://www.googleapis.com/auth/firebase'],
  });
  const client = await auth.getClient();
  const token = await client.getAccessToken();
  return token.token;
}

async function callFirestore(token, uid, data) {
  const url = `https://firestore.googleapis.com/v1/projects/${PROJECT_ID}/databases/(default)/documents/users/${uid}`;
  const body = { fields: {} };
  for (const [k, v] of Object.entries(data)) {
    if (v === null) body.fields[k] = { nullValue: null };
    else if (typeof v === 'boolean') body.fields[k] = { booleanValue: v };
    else if (k.includes('At')) body.fields[k] = { timestampValue: new Date(v).toISOString() };
    else body.fields[k] = { stringValue: String(v) };
  }
  const res = await fetch(url, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
    body: JSON.stringify(body),
  });
  return res.ok;
}

async function signUpUser(email, password) {
  const url = `https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=${API_KEY}`;
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password, returnSecureToken: true }),
  });
  const data = await res.json();
  if (!res.ok && data.error?.message !== 'EMAIL_EXISTS') {
    throw new Error(`signUp error: ${data.error?.message}`);
  }
  return data.localId;
}

async function setCustomClaims(uid, claims) {
  const token = await getToken();
  const url = `https://identitytoolkit.googleapis.com/v1/accounts:update?key=${API_KEY}`;
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      localId: uid,
      customAttributes: JSON.stringify(claims),
    }),
  });
  const data = await res.json();
  if (!res.ok) {
    // Try with OAuth2 token
    const url2 = `https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}/accounts:update`;
    const res2 = await fetch(url2, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify({
        localId: uid,
        customAttributes: JSON.stringify(claims),
      }),
    });
    const data2 = await res2.json();
    if (!res2.ok) {
      console.log('Claims via REST falhou. Usando Admin SDK diretamente...');
      return false;
    }
    return true;
  }
  return true;
}

async function run() {
  const email = 'cleitonxadrez@gmail.com';
  const password = '123456';
  const uid = 'tCB5bDUuxwhbSKRG8oU5c0aMWjw2';
  const name = 'Cleiton Xadrez';
  const role = 'ADMIN_GERAL';

  console.log(`Criando usuario ${email}...`);

  // 1. Create Firebase Auth user (email/password)
  try {
    const localId = await signUpUser(email, password);
    console.log(`✓ Usuario criado no Auth (localId: ${localId})`);
  } catch (e) {
    console.log(`✗ Erro Auth: ${e.message}`);
  }

  // 2. Set custom claims via Admin SDK - use Firebase Admin
  try {
    const token = await getToken();
    // Try the admin endpoint with Bearer token
    const url = `https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}/accounts:update`;
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify({
        localId: uid,
        customAttributes: JSON.stringify({ role, institution_id: null }),
      }),
    });
    const data = await res.json();
    if (res.ok) {
      console.log('✓ Custom claims setados via Admin REST');
    } else {
      console.log('✗ Claims error:', data.error?.message);
    }
  } catch (e) {
    console.log('✗ Erro claims:', e.message);
  }

  // 3. Create Firestore document
  try {
    const token = await getToken();
    const ok = await callFirestore(token, uid, {
      uid, email, name, photoURL: '',
      role, institutionId: null, ativo: true,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
    });
    if (ok) console.log('✓ Documento Firestore criado');
    else console.log('✗ Erro Firestore');
  } catch (e) {
    console.log('✗ Erro Firestore:', e.message);
  }

  console.log('\n✅ Concluido!');
  process.exit(0);
}

run().catch((e) => { console.error(e); process.exit(1); });
