import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class InstitutionsService {
  private get collection() {
    return admin.firestore().collection('institutions');
  }

  async findAll(page = 1, limit = 15, search?: string) {
    let query: admin.firestore.Query = this.collection
      .orderBy('razaoSocial', 'asc');

    if (search) {
      const end = search.replace(/.$/, (c) => String.fromCharCode(c.charCodeAt(0) + 1));
      query = query
        .where('razaoSocial', '>=', search.toUpperCase())
        .where('razaoSocial', '<', end.toUpperCase());
    }

    const snapshot = await query
      .offset((page - 1) * limit)
      .limit(limit)
      .get();

    const institutions = snapshot.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
    const total = (await this.collection.count().get()).data().count;

    return {
      institutions,
      total,
      page,
      limit,
      totalPages: Math.ceil(total / limit),
    };
  }

  async findById(id: string) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Instituicao nao encontrada');
    return { id: doc.id, ...doc.data() };
  }

  async create(data: any, userId: string) {
    const docRef = this.collection.doc();
    const institution = {
      ...data,
      cnpj: data.cnpj?.replace(/\D/g, ''),
      active: true,
      createdBy: userId,
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    };
    await docRef.set(institution);
    return { id: docRef.id, ...institution };
  }

  async update(id: string, data: any) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Instituicao nao encontrada');

    const updateData = {
      ...data,
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    };
    if (data.cnpj) updateData.cnpj = data.cnpj.replace(/\D/g, '');

    await this.collection.doc(id).update(updateData);
    return this.findById(id);
  }

  async delete(id: string) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Instituicao nao encontrada');
    await this.collection.doc(id).delete();
  }

  async toggleActive(id: string) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Instituicao nao encontrada');
    const current = doc.data() as any;
    await this.collection.doc(id).update({
      active: !current.active,
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(id);
  }
}
