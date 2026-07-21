import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class AccountingService {
  private sub(projectId: string) {
    return admin.firestore().collection('projects').doc(projectId).collection('accountingReports');
  }

  async findAll(projectId: string) {
    const snap = await this.sub(projectId).orderBy('createdAt', 'desc').get();
    return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
  }

  async findById(projectId: string, reportId: string) {
    const doc = await this.sub(projectId).doc(reportId).get();
    if (!doc.exists) throw new NotFoundException('Relatorio nao encontrado');
    return { id: doc.id, ...doc.data() };
  }

  async create(projectId: string, data: any) {
    const ref = await this.sub(projectId).add({
      ...data,
      status: 'PENDENTE',
      fotos: [],
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(projectId, ref.id);
  }

  async update(projectId: string, reportId: string, data: any) {
    const doc = await this.sub(projectId).doc(reportId).get();
    if (!doc.exists) throw new NotFoundException('Relatorio nao encontrado');
    await this.sub(projectId).doc(reportId).update({ ...data, updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    return this.findById(projectId, reportId);
  }

  async addPhoto(projectId: string, reportId: string, photoPath: string) {
    const doc = await this.sub(projectId).doc(reportId).get();
    if (!doc.exists) throw new NotFoundException('Relatorio nao encontrado');
    const data = doc.data() as any;
    const fotos = [...(data.fotos || []), photoPath];
    await this.sub(projectId).doc(reportId).update({ fotos, updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    return this.findById(projectId, reportId);
  }

  async removePhoto(projectId: string, reportId: string, photoIndex: number) {
    const doc = await this.sub(projectId).doc(reportId).get();
    if (!doc.exists) throw new NotFoundException('Relatorio nao encontrado');
    const data = doc.data() as any;
    const fotos = (data.fotos || []).filter((_: any, i: number) => i !== photoIndex);
    await this.sub(projectId).doc(reportId).update({ fotos, updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    return this.findById(projectId, reportId);
  }

  async delete(projectId: string, reportId: string) {
    await this.sub(projectId).doc(reportId).delete();
  }
}
