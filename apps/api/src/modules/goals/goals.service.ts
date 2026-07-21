import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class GoalsService {
  private sub(projectId: string) {
    return admin.firestore().collection('projects').doc(projectId).collection('goals');
  }

  async findAll(projectId: string) {
    const snap = await this.sub(projectId).orderBy('numero', 'asc').get();
    return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
  }

  async findById(projectId: string, goalId: string) {
    const doc = await this.sub(projectId).doc(goalId).get();
    if (!doc.exists) throw new NotFoundException('Meta nao encontrada');
    return { id: doc.id, ...doc.data() };
  }

  async create(projectId: string, data: any) {
    const ref = await this.sub(projectId).add({
      ...data,
      status: 'Pendente',
      percentualExecucao: 0,
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(projectId, ref.id);
  }

  async update(projectId: string, goalId: string, data: any) {
    const doc = await this.sub(projectId).doc(goalId).get();
    if (!doc.exists) throw new NotFoundException('Meta nao encontrada');
    await this.sub(projectId).doc(goalId).update({ ...data, updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    return this.findById(projectId, goalId);
  }

  async delete(projectId: string, goalId: string) {
    await this.sub(projectId).doc(goalId).delete();
  }

  async sendToAnalysis(projectId: string, goalId: string) {
    return this.update(projectId, goalId, { status: 'Em análise' });
  }

  async approve(projectId: string, goalId: string, userId: string, observacao?: string) {
    await this.sub(projectId).doc(goalId).update({ status: 'Concluída', updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    const approvalsSub = this.sub(projectId).doc(goalId).collection('approvals');
    await approvalsSub.add({
      aprovado: true,
      userId,
      observacao: observacao || '',
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(projectId, goalId);
  }

  async disapprove(projectId: string, goalId: string, userId: string, observacao?: string) {
    await this.sub(projectId).doc(goalId).update({ status: 'Pendente', updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    const approvalsSub = this.sub(projectId).doc(goalId).collection('approvals');
    await approvalsSub.add({
      aprovado: false,
      userId,
      observacao: observacao || '',
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(projectId, goalId);
  }

  async sendToAccounting(projectId: string) {
    const snap = await this.sub(projectId).where('status', '==', 'Concluída').get();
    const batch = admin.firestore().batch();
    snap.docs.forEach((d) => batch.update(d.ref, { status: 'Prestação de Contas', updatedAt: admin.firestore.FieldValue.serverTimestamp() }));
    await batch.commit();
    return { count: snap.size };
  }
}
