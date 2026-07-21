/**
 * Script para criar usuario admin no Firestore + Custom Claims.
 *
 * Uso:
 *   1. Configure GOOGLE_APPLICATION_CREDENTIALS ou rode com 
 *      `firebase login` ja autenticado
 *   2. npx ts-node scripts/seed-admin.ts
 */

import * as admin from 'firebase-admin';

const UID = 'wFBPza3O3CRouLoybF2lHregGb52';
const EMAIL = 'gestor.renatorosa@gmail.com';
const NAME = 'Renato Rosa';
const ROLE = 'ADMIN_GERAL';

async function seed() {
  // Inicializa Firebase Admin (usa application default credentials)
  if (!admin.apps.length) {
    admin.initializeApp({
      projectId: 'gestaosetor3',
      credential: admin.credential.applicationDefault(),
    });
  }

  console.log('Conectado ao Firebase project:', admin.app().options.projectId);

  // 1. Set custom claims
  console.log(`Definindo custom claims para UID ${UID}...`);
  await admin.auth().setCustomUserClaims(UID, {
    role: ROLE,
    institution_id: null,
  });
  console.log('Custom claims atualizados:', { role: ROLE, institution_id: null });

  // 2. Criar/atualizar documento no Firestore
  const userRef = admin.firestore().collection('users').doc(UID);
  const userData = {
    uid: UID,
    email: EMAIL,
    name: NAME,
    photoURL: '',
    role: ROLE,
    institutionId: null,
    ativo: true,
    createdAt: admin.firestore.FieldValue.serverTimestamp(),
    updatedAt: admin.firestore.FieldValue.serverTimestamp(),
  };

  await userRef.set(userData, { merge: true });
  console.log('Documento do usuario criado/atualizado no Firestore');

  // 3. Verificar
  const userRecord = await admin.auth().getUser(UID);
  console.log('\n--- Verificacao ---');
  console.log('Email:', userRecord.email);
  console.log('Claims:', userRecord.customClaims);
  console.log('UID:', userRecord.uid);

  const doc = await userRef.get();
  console.log('Firestore doc:', doc.data());

  console.log('\n✅ Seed concluido com sucesso!');
  process.exit(0);
}

seed().catch((err) => {
  console.error('❌ Erro:', err);
  process.exit(1);
});
