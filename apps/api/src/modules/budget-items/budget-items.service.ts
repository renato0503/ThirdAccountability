import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class BudgetItemsService {
  private sub(projectId: string) {
    return admin.firestore().collection('projects').doc(projectId).collection('budgetItems');
  }

  async findAll(projectId: string) {
    const snap = await this.sub(projectId).orderBy('createdAt', 'asc').get();
    return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
  }

  async create(projectId: string, data: any) {
    const ref = await this.sub(projectId).add({ ...data, createdAt: admin.firestore.FieldValue.serverTimestamp() });
    const doc = await ref.get();
    return { id: doc.id, ...doc.data() };
  }

  async delete(projectId: string, itemId: string) {
    await this.sub(projectId).doc(itemId).delete();
  }
}
