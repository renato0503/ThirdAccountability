// Script que usa firebase-admin com ADC para configurar usuarios
import path from 'path';
import { fileURLToPath } from 'url';
import { createRequire } from 'module';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Import firebase-admin do workspace
const require = createRequire(import.meta.url);
const admin = require(path.resolve(__dirname, '..', 'apps', 'api', 'node_modules', 'firebase-admin'));

const USERS = [
  {
    uid: 'wFBPza3O3CRouLoybF2lHregGb52',
    email: 'gestor.renatorosa@gmail.com',
    name: 'Renato Rosa',
    role: 'ADMIN_GERAL',
    password: '123456',
  },
  {
    uid: 'tCB5bDUuxwhbSKRG8oU5c0aMWjw2',
    email: 'cleitonxadrez@gmail.com',
    name: 'Cleiton Xadrez',
    role: 'ADMIN_GERAL',
    password: '123456',
  },
];

async function run() {
  if (admin.apps.length === 0) {
    admin.initializeApp({
      projectId: 'gestaosetor3',
      credential: admin.credential.applicationDefault(),
    });
  }
  console.log('Firebase Admin SDK inicializado - Projeto:', admin.app().options.projectId);

  for (const u of USERS) {
    console.log(`\n--- Processando ${u.email} ---`);

    try {
      // 1. Verificar se usuario existe no Auth
      let userRecord;
      try {
        userRecord = await admin.auth().getUser(u.uid);
        console.log('  Usuario encontrado no Auth:', userRecord.email);
      } catch {
        console.log('  Usuario NAO encontrado, criando...');
        userRecord = await admin.auth().createUser({
          uid: u.uid,
          email: u.email,
          displayName: u.name,
          password: u.password,
        });
        console.log('  Usuario criado:', userRecord.uid);
      }

      // 2. Definir senha (para usuarios criados via Google)
      try {
        await admin.auth().updateUser(u.uid, {
          password: u.password,
        });
        console.log('  ✓ Senha definida');
      } catch (e) {
        console.log('  ✗ Erro ao definir senha:', e.message);
      }

      // 3. Custom claims
      try {
        await admin.auth().setCustomUserClaims(u.uid, {
          role: u.role,
          institution_id: null,
        });
        console.log('  ✓ Custom claims setados:', u.role);
      } catch (e) {
        console.log('  ✗ Erro claims:', e.message);
      }

      // 4. Firestore document
      try {
        await admin.firestore().collection('users').doc(u.uid).set({
          uid: u.uid,
          email: u.email,
          name: u.name,
          photoURL: '',
          role: u.role,
          institutionId: null,
          ativo: true,
          createdAt: admin.firestore.FieldValue.serverTimestamp(),
          updatedAt: admin.firestore.FieldValue.serverTimestamp(),
        }, { merge: true });
        console.log('  ✓ Documento Firestore criado/atualizado');
      } catch (e) {
        console.log('  ✗ Erro Firestore:', e.message);
      }

      // 5. Verificar
      const verified = await admin.auth().getUser(u.uid);
      console.log('  → Claims verificados:', JSON.stringify(verified.customClaims));
    } catch (e) {
      console.log(`  ✗ Erro fatal para ${u.email}:`, e.message);
    }
  }

  console.log('\n✅ Todos os usuarios configurados!');
  process.exit(0);
}

run().catch((e) => {
  console.error('Erro fatal:', e);
  process.exit(1);
});
