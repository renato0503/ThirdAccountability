import { Injectable } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class DiagnosticsService {
  async checkAll(): Promise<{
    status: string;
    checks: Record<string, { status: string; message: string; latency?: number }>;
  }> {
    const checks: Record<string, any> = {};

    // Firebase Auth
    try {
      const start = Date.now();
      await admin.auth().listUsers(1);
      checks.firebaseAuth = {
        status: 'ok',
        message: 'Firebase Auth respondendo',
        latency: Date.now() - start,
      };
    } catch (e: any) {
      checks.firebaseAuth = { status: 'error', message: e.message };
    }

    // Firestore
    try {
      const start = Date.now();
      await admin.firestore().collection('users').limit(1).get();
      checks.firestore = {
        status: 'ok',
        message: 'Firestore respondendo',
        latency: Date.now() - start,
      };
    } catch (e: any) {
      checks.firestore = { status: 'error', message: e.message };
    }

    // Firebase Storage
    try {
      const start = Date.now();
      const bucket = admin.storage().bucket();
      const [exists] = await bucket.exists();
      checks.firebaseStorage = {
        status: exists ? 'ok' : 'error',
        message: exists ? 'Storage bucket acessivel' : 'Bucket nao encontrado',
        latency: Date.now() - start,
      };
    } catch (e: any) {
      checks.firebaseStorage = { status: 'error', message: e.message };
    }

    // Project config
    checks.project = {
      status: 'ok',
      message: `Project ID: ${admin.app().options.projectId}`,
    };

    const allOk = Object.values(checks).every((c: any) => c.status === 'ok');

    return { status: allOk ? 'healthy' : 'degraded', checks };
  }
}
