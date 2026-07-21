import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class ProjectsService {
  private get collection() {
    return admin.firestore().collection('projects');
  }

  async findAll(page = 1, limit = 15, filters?: { status?: string; institutionId?: string; search?: string }) {
    let query: admin.firestore.Query = this.collection.orderBy('updatedAt', 'desc');

    if (filters?.institutionId) {
      query = query.where('institutionId', '==', filters.institutionId);
    }
    if (filters?.status) {
      query = query.where('status', '==', filters.status);
    }

    const snapshot = await query.offset((page - 1) * limit).limit(limit).get();
    let projects = snapshot.docs.map((d) => ({ id: d.id, ...d.data() }));

    if (filters?.search) {
      const s = filters.search.toLowerCase();
      projects = projects.filter((p: any) => p.nome?.toLowerCase().includes(s));
    }

    const total = (await this.collection.count().get()).data().count;
    return { projects, total, page, limit, totalPages: Math.ceil(total / limit) };
  }

  async findById(id: string) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Projeto nao encontrado');
    return { id: doc.id, ...doc.data() };
  }

  async create(data: any, userId: string) {
    const project = {
      ...data,
      codigo: data.codigo || '',
      valorExecutado: 0,
      createdBy: userId,
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    };
    const ref = await this.collection.add(project);
    return { id: ref.id, ...project };
  }

  async update(id: string, data: any) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Projeto nao encontrado');
    await this.collection.doc(id).update({
      ...data,
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(id);
  }

  async delete(id: string) {
    await this.collection.doc(id).delete();
  }

  async generateCode(year: number): Promise<string> {
    const snapshot = await this.collection
      .orderBy('codigo', 'desc')
      .limit(1)
      .get();

    let lastSeq = 0;
    if (!snapshot.empty) {
      const last: any = snapshot.docs[0].data();
      if (last.codigo) {
        const match = last.codigo.match(/^(\d+)\//);
        if (match) lastSeq = parseInt(match[1]!);
      }
    }

    const seq = String(lastSeq + 1).padStart(3, '0');
    return `${seq}/${year}`;
  }

  // ─── Subcollections helpers ───

  private sub(projectId: string, name: string) {
    return this.collection.doc(projectId).collection(name);
  }

  async findSub(projectId: string, name: string) {
    const snap = await this.sub(projectId, name).orderBy('ordem', 'asc').get();
    return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
  }

  async createSub(projectId: string, name: string, data: any) {
    const ref = await this.sub(projectId, name).add({
      ...data,
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    const doc = await ref.get();
    return { id: doc.id, ...doc.data() };
  }

  async updateSub(projectId: string, name: string, subId: string, data: any) {
    const doc = await this.sub(projectId, name).doc(subId).get();
    if (!doc.exists) throw new NotFoundException('Registro nao encontrado');
    await this.sub(projectId, name).doc(subId).update(data);
    const updated = await this.sub(projectId, name).doc(subId).get();
    return { id: updated.id, ...updated.data() };
  }

  async deleteSub(projectId: string, name: string, subId: string) {
    await this.sub(projectId, name).doc(subId).delete();
  }
}
