import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class ExpensesService {
  private sub(projectId: string) {
    return admin.firestore().collection('projects').doc(projectId).collection('expenses');
  }

  async findAll(projectId: string, filters?: { status?: string }) {
    let query: admin.firestore.Query = this.sub(projectId).orderBy('dataGasto', 'desc');
    if (filters?.status) query = query.where('status', '==', filters.status);
    const snap = await query.get();
    return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
  }

  async findById(projectId: string, expenseId: string) {
    const doc = await this.sub(projectId).doc(expenseId).get();
    if (!doc.exists) throw new NotFoundException('Despesa nao encontrada');
    return { id: doc.id, ...doc.data() };
  }

  async create(projectId: string, data: any) {
    const ref = await this.sub(projectId).add({
      ...data,
      status: 'PENDENTE',
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(projectId, ref.id);
  }

  async update(projectId: string, expenseId: string, data: any) {
    const doc = await this.sub(projectId).doc(expenseId).get();
    if (!doc.exists) throw new NotFoundException('Despesa nao encontrada');
    await this.sub(projectId).doc(expenseId).update({ ...data, updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    return this.findById(projectId, expenseId);
  }

  async updateStatus(projectId: string, expenseId: string, status: string) {
    const valid = ['PENDENTE', 'APROVADO', 'REPROVADO', 'PAGO'];
    if (!valid.includes(status)) throw new Error('Status invalido');
    return this.update(projectId, expenseId, { status });
  }

  async delete(projectId: string, expenseId: string) {
    await this.sub(projectId).doc(expenseId).delete();
  }
}
