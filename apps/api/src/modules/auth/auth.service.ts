import { Injectable, UnauthorizedException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class AuthService {
  async verifyToken(token: string) {
    try {
      const decoded = await admin.auth().verifyIdToken(token);
      return decoded;
    } catch {
      throw new UnauthorizedException('Token invalido');
    }
  }

  async syncUser(uid: string) {
    const firestore = admin.firestore();
    const userRef = firestore.collection('users').doc(uid);
    const snapshot = await userRef.get();

    if (!snapshot.exists) {
      const firebaseUser = await admin.auth().getUser(uid);
      const userData = {
        uid: firebaseUser.uid,
        email: firebaseUser.email || '',
        name: firebaseUser.displayName || '',
        photoURL: firebaseUser.photoURL || '',
        role: 'GESTOR_PROJETO',
        institutionId: null,
        ativo: true,
        createdAt: admin.firestore.FieldValue.serverTimestamp(),
        updatedAt: admin.firestore.FieldValue.serverTimestamp(),
      };
      await userRef.set(userData);
      return userData;
    }

    return snapshot.data();
  }

  async updateUserRole(uid: string, role: string, institutionId?: string) {
    await admin.auth().setCustomUserClaims(uid, {
      role,
      institution_id: institutionId || null,
    });

    const firestore = admin.firestore();
    await firestore.collection('users').doc(uid).update({
      role,
      institutionId: institutionId || null,
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
  }
}
