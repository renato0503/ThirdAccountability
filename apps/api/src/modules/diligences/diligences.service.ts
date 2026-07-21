import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class DiligencesService {
  private sub(projectId: string) {
    return admin.firestore().collection('projects').doc(projectId).collection('diligences');
  }

  async findAll(projectId: string) {
    const snap = await this.sub(projectId).orderBy('createdAt', 'desc').get();
    return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
  }

  async findById(projectId: string, dilId: string) {
    const doc = await this.sub(projectId).doc(dilId).get();
    if (!doc.exists) throw new NotFoundException('Diligencia nao encontrada');
    return { id: doc.id, ...doc.data() };
  }

  async create(projectId: string, data: any) {
    const ref = await this.sub(projectId).add({
      ...data,
      status: 'ABERTA',
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(projectId, ref.id);
  }

  async update(projectId: string, dilId: string, data: any) {
    const doc = await this.sub(projectId).doc(dilId).get();
    if (!doc.exists) throw new NotFoundException('Diligencia nao encontrada');
    await this.sub(projectId).doc(dilId).update({ ...data, updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    return this.findById(projectId, dilId);
  }

  async respond(projectId: string, dilId: string, resposta: string, anexoPath?: string) {
    const doc = await this.sub(projectId).doc(dilId).get();
    if (!doc.exists) throw new NotFoundException('Diligencia nao encontrada');
    await this.sub(projectId).doc(dilId).update({
      resposta,
      anexoPath: anexoPath || null,
      dataResposta: admin.firestore.FieldValue.serverTimestamp(),
      status: 'RESPONDIDA',
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(projectId, dilId);
  }

  async close(projectId: string, dilId: string, parecer: string) {
    const doc = await this.sub(projectId).doc(dilId).get();
    if (!doc.exists) throw new NotFoundException('Diligencia nao encontrada');
    await this.sub(projectId).doc(dilId).update({
      parecer,
      dataParecer: admin.firestore.FieldValue.serverTimestamp(),
      status: 'FECHADA',
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(projectId, dilId);
  }

  async reopen(projectId: string, dilId: string) {
    const doc = await this.sub(projectId).doc(dilId).get();
    if (!doc.exists) throw new NotFoundException('Diligencia nao encontrada');
    await this.sub(projectId).doc(dilId).update({
      status: 'ABERTA',
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(projectId, dilId);
  }

  async delete(projectId: string, dilId: string) {
    await this.sub(projectId).doc(dilId).delete();
  }
}
