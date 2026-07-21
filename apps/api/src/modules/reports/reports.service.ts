import { Injectable } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class ReportsService {
  async generateProjectReport(projectId: string) {
    const firestore = admin.firestore();
    const projectDoc = await firestore.collection('projects').doc(projectId).get();
    if (!projectDoc.exists) throw new Error('Projeto nao encontrado');
    const project = { id: projectDoc.id, ...projectDoc.data() } as any;

    const [goals, expenses, diligences] = await Promise.all([
      projectDoc.ref.collection('goals').orderBy('numero', 'asc').get(),
      projectDoc.ref.collection('expenses').orderBy('dataGasto', 'desc').get(),
      projectDoc.ref.collection('diligences').orderBy('createdAt', 'desc').get(),
    ]);

    return {
      project: {
        nome: project.nome,
        codigo: project.codigo,
        status: project.status,
        valorTotal: project.valorTotal || 0,
        valorRecebido: project.valorRecebido || 0,
        valorExecutado: project.valorExecutado || 0,
        dataInicio: project.dataInicio || '',
        dataFim: project.dataFim || '',
      },
      goals: goals.docs.map((d) => ({ id: d.id, ...d.data() })),
      expenses: expenses.docs.map((d) => ({ id: d.id, ...d.data() })),
      diligences: diligences.docs.map((d) => ({ id: d.id, ...d.data() })),
    };
  }
}
