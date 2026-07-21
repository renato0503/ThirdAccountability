import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class DocumentsService {
  private sub(projectId: string) {
    return admin.firestore().collection('projects').doc(projectId).collection('documents');
  }

  async findAll(projectId: string) {
    const snap = await this.sub(projectId).orderBy('createdAt', 'desc').get();
    return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
  }

  async findById(projectId: string, docId: string) {
    const doc = await this.sub(projectId).doc(docId).get();
    if (!doc.exists) throw new NotFoundException('Documento nao encontrado');
    return { id: doc.id, ...doc.data() };
  }

  async create(projectId: string, data: any) {
    const ref = await this.sub(projectId).add({
      ...data,
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(projectId, ref.id);
  }

  async update(projectId: string, docId: string, data: any) {
    const doc = await this.sub(projectId).doc(docId).get();
    if (!doc.exists) throw new NotFoundException('Documento nao encontrado');
    await this.sub(projectId).doc(docId).update({ ...data, updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    return this.findById(projectId, docId);
  }

  async delete(projectId: string, docId: string) {
    const doc = await this.sub(projectId).doc(docId).get();
    if (!doc.exists) throw new NotFoundException('Documento nao encontrado');
    const data = doc.data() as any;
    if (data.storagePath) {
      try { await admin.storage().bucket().file(data.storagePath).delete(); } catch {}
    }
    await this.sub(projectId).doc(docId).delete();
  }
}
