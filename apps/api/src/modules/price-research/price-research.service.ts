import { Injectable, NotFoundException } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class PriceResearchService {
  private get collection() {
    return admin.firestore().collection('priceResearches');
  }

  private resultsSub(researchId: string) {
    return this.collection.doc(researchId).collection('results');
  }

  async findAll(page = 1, limit = 15, filters?: { status?: string; institutionId?: string; search?: string }) {
    let query: admin.firestore.Query = this.collection.orderBy('updatedAt', 'desc');
    if (filters?.institutionId) query = query.where('institutionId', '==', filters.institutionId);
    if (filters?.status) query = query.where('status', '==', filters.status);

    const snapshot = await query.offset((page - 1) * limit).limit(limit).get();
    let items = snapshot.docs.map((d) => ({ id: d.id, ...d.data() }));

    if (filters?.search) {
      const s = filters.search.toLowerCase();
      items = items.filter((i: any) => i.searchTerm?.toLowerCase().includes(s));
    }

    const total = (await this.collection.count().get()).data().count;
    return { researches: items, total, page, limit, totalPages: Math.ceil(total / limit) };
  }

  async findById(id: string) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Pesquisa nao encontrada');
    const results = await this.resultsSub(id).orderBy('unitPrice', 'asc').get();
    return {
      id: doc.id,
      ...doc.data(),
      results: results.docs.map((r) => ({ id: r.id, ...r.data() })),
    };
  }

  async create(data: any, userId: string) {
    const ref = await this.collection.add({
      ...data,
      userId,
      status: 'RASCUNHO',
      minPrice: 0, maxPrice: 0, averagePrice: 0, medianPrice: 0,
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(ref.id);
  }

  async update(id: string, data: any) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Pesquisa nao encontrada');
    await this.collection.doc(id).update({ ...data, updatedAt: admin.firestore.FieldValue.serverTimestamp() });
    return this.findById(id);
  }

  async delete(id: string) {
    const results = await this.resultsSub(id).get();
    const batch = admin.firestore().batch();
    results.docs.forEach((d) => batch.delete(d.ref));
    batch.delete(this.collection.doc(id));
    await batch.commit();
  }

  async updateStats(id: string) {
    const results = await this.resultsSub(id).get();
    const prices = results.docs.map((d) => (d.data() as any).unitPrice).filter((p) => p > 0);
    const stats = this.calculateStats(prices);
    await this.collection.doc(id).update({
      ...stats,
      status: prices.length > 0 ? 'COM_RESULTADOS' : 'SEM_RESULTADOS',
      searchedAt: admin.firestore.FieldValue.serverTimestamp(),
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return stats;
  }

  async addResult(researchId: string, data: any) {
    const doc = await this.collection.doc(researchId).get();
    if (!doc.exists) throw new NotFoundException('Pesquisa nao encontrada');
    const ref = await this.resultsSub(researchId).add({
      ...data,
      selected: false,
      createdAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    await this.updateStats(researchId);
    return { id: ref.id, ...data };
  }

  async deleteResult(researchId: string, resultId: string) {
    await this.resultsSub(researchId).doc(resultId).delete();
    await this.updateStats(researchId);
  }

  async toggleSelect(researchId: string, resultId: string, selected: boolean, justification?: string) {
    await this.resultsSub(researchId).doc(resultId).update({
      selected,
      selectionJustification: justification || null,
    });
    const hasSelected = (await this.resultsSub(researchId).where('selected', '==', true).get()).size > 0;
    if (hasSelected) {
      const data = (await this.collection.doc(researchId).get()).data() as any;
      if (data.status === 'COM_RESULTADOS' || data.status === 'BUSCADA') {
        await this.collection.doc(researchId).update({ status: 'SELECIONADA', updatedAt: admin.firestore.FieldValue.serverTimestamp() });
      }
    }
    return { selected };
  }

  async setReference(id: string, type: string, justification: string, manualPrice?: number, resultId?: string) {
    const doc = await this.collection.doc(id).get();
    if (!doc.exists) throw new NotFoundException('Pesquisa nao encontrada');
    const data = doc.data() as any;

    let value = 0;
    const results = (await this.resultsSub(id).get()).docs.map((d) => (d.data() as any));

    switch (type) {
      case 'MENOR': value = Math.min(...results.map((r) => r.unitPrice).filter(Boolean)); break;
      case 'MAIOR': value = Math.max(...results.map((r) => r.unitPrice).filter(Boolean)); break;
      case 'MEDIA': { const p = results.map((r) => r.unitPrice).filter(Boolean); value = p.length ? p.reduce((a, b) => a + b, 0) / p.length : 0; break; }
      case 'MEDIANA': { const p = results.map((r) => r.unitPrice).filter(Boolean).sort((a, b) => a - b); const mid = Math.floor(p.length / 2); value = p.length ? (p.length % 2 ? p[mid]! : (p[mid - 1]! + p[mid]!) / 2) : 0; break; }
      case 'MANUAL': value = manualPrice || 0; break;
      case 'ITEM': {
        if (resultId) { const r = results.find((r) => r.id === resultId); value = r?.unitPrice || 0; }
        break;
      }
    }

    await this.collection.doc(id).update({
      referenceType: type,
      selectedReferencePrice: value,
      justification,
      status: value > 0 ? 'FINALIZADA' : data.status,
      updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    });
    return this.findById(id);
  }

  private calculateStats(prices: number[]) {
    if (!prices.length) return { minPrice: 0, maxPrice: 0, averagePrice: 0, medianPrice: 0 };
    const sorted = [...prices].sort((a, b) => a - b);
    const mid = Math.floor(sorted.length / 2);
    return {
      minPrice: sorted[0]!,
      maxPrice: sorted[sorted.length - 1]!,
      averagePrice: sorted.reduce((a, b) => a + b, 0) / sorted.length,
      medianPrice: sorted.length % 2 ? sorted[mid]! : (sorted[mid - 1]! + sorted[mid]!) / 2,
    };
  }
}
