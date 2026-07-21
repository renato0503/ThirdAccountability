import { Injectable } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class AuditService {
  private get collection() {
    return admin.firestore().collection('auditLogs');
  }

  async findAll(page = 1, limit = 30, filters?: {
    userId?: string; acao?: string; entidade?: string;
    startDate?: string; endDate?: string;
  }) {
    let query: admin.firestore.Query = this.collection.orderBy('timestamp', 'desc');

    if (filters?.userId) query = query.where('userId', '==', filters.userId);
    if (filters?.acao) query = query.where('acao', '==', filters.acao);
    if (filters?.entidade) query = query.where('entidade', '==', filters.entidade);

    const snapshot = await query.offset((page - 1) * limit).limit(limit).get();
    const logs = snapshot.docs.map((d) => ({ id: d.id, ...d.data() }));
    const total = (await this.collection.count().get()).data().count;

    // Filtragem por data em memoria (Firestore nao suporta range em campos diferentes)
    let filtered = logs;
    if (filters?.startDate) {
      const start = new Date(filters.startDate);
      filtered = filtered.filter((l: any) => l.timestamp?.toDate?.() >= start);
    }
    if (filters?.endDate) {
      const end = new Date(filters.endDate);
      filtered = filtered.filter((l: any) => l.timestamp?.toDate?.() <= end);
    }

    return { logs: filtered, total, page, limit, totalPages: Math.ceil(total / limit) };
  }

  async log(data: { userId: string; userEmail?: string; acao: string; entidade: string; entidadeId?: string; dados?: any; ip?: string }) {
    await this.collection.add({
      ...data,
      dados: data.dados ? JSON.stringify(data.dados) : null,
      timestamp: admin.firestore.FieldValue.serverTimestamp(),
    });
  }
}
