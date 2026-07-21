import { Injectable } from '@nestjs/common';
import * as admin from 'firebase-admin';

@Injectable()
export class DashboardService {
  async getStats(institutionId?: string) {
    const firestore = admin.firestore();

    let projectsQuery: admin.firestore.Query = firestore.collection('projects');
    if (institutionId) projectsQuery = projectsQuery.where('institutionId', '==', institutionId);

    const [projectsSnap, allProjects] = await Promise.all([
      projectsQuery.get(),
      institutionId
        ? firestore.collection('projects').where('institutionId', '==', institutionId).get()
        : firestore.collection('projects').get(),
    ]);

    const projects = allProjects.docs.map((d) => ({ id: d.id, ...d.data() })) as any[];
    const activeProjects = projects.filter((p) => ['EM_ANALISE', 'APROVADO', 'EM_EXECUCAO'].includes(p.status));

    const totalExpenses = projects.reduce((sum, p) => {
      return sum + (Array.isArray(p.expenses) ? p.expenses.length : 0);
    }, 0);

    const totalBudget = projects.reduce((sum, p) => sum + (p.valorTotal || 0), 0);
    const totalReceived = projects.reduce((sum, p) => sum + (p.valorRecebido || 0), 0);

    const institutions = await firestore.collection('institutions').count().get();
    const users = await firestore.collection('users').count().get();

    return {
      totalProjects: projects.length,
      activeProjects: activeProjects.length,
      totalBudget,
      totalReceived,
      totalExpenses,
      totalInstitutions: institutions.data().count,
      totalUsers: users.data().count,
    };
  }
}
