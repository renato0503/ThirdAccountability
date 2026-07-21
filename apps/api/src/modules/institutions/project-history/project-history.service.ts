import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class ProjectHistoryService {
  private subcollection(institutionId: string) {
    return admin
      .firestore()
      .collection('institutions')
      .doc(institutionId)
      .collection('projectHistories');
  }

  async findAll(institutionId: string) {
    const snapshot = await this.subcollection(institutionId)
      .orderBy('createdAt', 'desc')
      .get();
    return snapshot.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async create(institutionId: string, data: any) {
    const docRef = await this.subcollection(institutionId).add({
      ...data,
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    const doc = await docRef.get();
    return { id: doc.id, ...doc.data() };
  }

  async delete(institutionId: string, historyId: string) {
    const doc = await this.subcollection(institutionId).doc(historyId).get();
    if (!doc.exists) throw new NotFoundException('Historico nao encontrado');
    await this.subcollection(institutionId).doc(historyId).delete();
  }
}
