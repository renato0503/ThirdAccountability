import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class DirectorsService {
  private subcollection(institutionId: string) {
    return admin
      .firestore()
      .collection('institutions')
      .doc(institutionId)
      .collection('directors');
  }

  async findAll(institutionId: string) {
    const snapshot = await this.subcollection(institutionId)
      .orderBy('createdAt', 'asc')
      .get();
    return snapshot.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async create(institutionId: string, data: any) {
    const docRef = await this.subcollection(institutionId).add({
      ...data,
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    const doc = await docRef.get();
    return { id: doc.id, ...doc.data() };
  }

  async update(institutionId: string, directorId: string, data: any) {
    const doc = await this.subcollection(institutionId).doc(directorId).get();
    if (!doc.exists) throw new NotFoundException('Diretor nao encontrado');
    await this.subcollection(institutionId).doc(directorId).update({
      ...data,
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    const updated = await this.subcollection(institutionId).doc(directorId).get();
    return { id: updated.id, ...updated.data() };
  }

  async delete(institutionId: string, directorId: string) {
    const doc = await this.subcollection(institutionId).doc(directorId).get();
    if (!doc.exists) throw new NotFoundException('Diretor nao encontrado');
    await this.subcollection(institutionId).doc(directorId).delete();
  }
}
