import { Injectable } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class UsersService {
  private get collection() {
    return admin.firestore().collection('users');
  }

  async findAll(page = 1, limit = 15) {
    const snapshot = await this.collection
      .orderBy('createdAt', 'desc')
      .offset((page - 1) * limit)
      .limit(limit)
      .get();

    const users = snapshot.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
    const total = (await this.collection.count().get()).data().count;

    return { users, total, page, limit, totalPages: Math.ceil(total / limit) };
  }

  async findById(uid: string) {
    const doc = await this.collection.doc(uid).get();
    if (!doc.exists) return null;
    return { id: doc.id, ...doc.data() };
  }

  async update(uid: string, data: any) {
    await this.collection.doc(uid).update({
      ...data,
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(uid);
  }
}
