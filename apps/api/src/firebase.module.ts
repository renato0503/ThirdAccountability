import { Module, Global, OnModuleInit } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Global()
@Module({
  providers: [
    {
      provide: 'FIREBASE_ADMIN',
      useFactory: () => {
        const serviceAccountPath = process.env.FIREBASE_SERVICE_ACCOUNT_PATH;
        const projectId = process.env.FIREBASE_PROJECT_ID || 'gestaosetor3';

        if (serviceAccountPath) {
          return admin.initializeApp({
            credential: admin.credential.applicationDefault(),
            projectId,
          });
        }

        // Fallback para desenvolvimento local com emuladores
        if (process.env.FIRESTORE_EMULATOR_HOST) {
          return admin.initializeApp({ projectId });
        }

        return admin.initializeApp({
          credential: admin.credential.applicationDefault(),
          projectId,
        });
      },
    },
  ],
  exports: ['FIREBASE_ADMIN'],
})
export class FirebaseModule implements OnModuleInit {
  onModuleInit() {
    const app = admin.app();
    console.log('Firebase Admin SDK inicializado - Projeto:', app.options.projectId);
  }
}
