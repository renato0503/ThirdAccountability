import { Injectable } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class SettingsService {
  private get doc() {
    return admin.firestore().collection('settings').doc('global');
  }

  async get() {
    const snapshot = await this.doc.get();
    if (!snapshot.exists) return {};
    return { id: snapshot.id, ...snapshot.data() };
  }

  async update(data: any) {
    await this.doc.set(data, { merge: true });
    return this.get();
  }
}
