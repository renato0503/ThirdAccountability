import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class FundingSourcesService {
  private get collection() {
    return admin.firestore().collection('fundingSources');
  }

  async findAll() {
    const snapshot = await this.collection.orderBy('nome', 'asc').get();
    return snapshot.docs.map((d) => ({ id: d.id, ...d.data() }));
  }

  async findById(id: string) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Fonte de recurso nao encontrada');
    return { id: doc.id, ...doc.data() };
  }

  async create(data: any) {
    const ref = await this.collection.add({
      ...data,
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    const doc = await ref.get();
    return { id: doc.id, ...doc.data() };
  }

  async update(id: string, data: any) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Fonte de recurso nao encontrada');
    await this.collection.doc(id).update({ ...data, updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    return this.findById(id);
  }

  async delete(id: string) {
    await this.collection.doc(id).delete();
  }
}
