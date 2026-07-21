import { useState, useEffect } from 'react';
import { useAuth } from '@/hooks/use-auth';
import api from '@/lib/api';
import { formatCurrency } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Building2, FolderKanban, DollarSign, Users, TrendingUp, PiggyBank } from 'lucide-react';

export function DashboardPage() {
  const { user } = useAuth();
  const [stats, setStats] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => { load(); }, []);

  const load = async () => {
    try {
      const { data } = await api.get('/dashboard/stats');
      setStats(data);
    } catch { /* API not available yet */ }
    finally { setLoading(false); }
  };

  const cards = [
    { icon: FolderKanban, label: 'Projetos Ativos', value: stats?.activeProjects ?? '-', color: 'text-blue-600', sub: `Total: ${stats?.totalProjects ?? 0}` },
    { icon: Building2, label: 'Instituicoes', value: stats?.totalInstitutions ?? '-', color: 'text-green-600' },
    { icon: DollarSign, label: 'Orcamento Total', value: stats?.totalBudget ? formatCurrency(stats.totalBudget) : '-', color: 'text-purple-600' },
    { icon: PiggyBank, label: 'Total Recebido', value: stats?.totalReceived ? formatCurrency(stats.totalReceived) : '-', color: 'text-emerald-600' },
    { icon: TrendingUp, label: 'Execucao Financeira', value: stats?.totalBudget ? `${Math.round((stats.totalReceived / stats.totalBudget) * 100)}%` : '-', color: 'text-orange-600' },
    { icon: Users, label: 'Usuarios', value: stats?.totalUsers ?? '-', color: 'text-indigo-600' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-3xl font-bold tracking-tight">
          Bem-vindo{user?.displayName ? `, ${user.displayName}` : ''}
        </h2>
        <p className="text-muted-foreground">Visao geral do sistema</p>
      </div>

      {loading ? (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {[1,2,3,4,5,6].map((i) => (
            <Card key={i}><CardContent className="pt-6"><div className="h-20 animate-pulse rounded bg-muted" /></CardContent></Card>
          ))}
        </div>
      ) : (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {cards.map((card) => (
            <Card key={card.label}>
              <CardHeader className="flex flex-row items-center justify-between pb-2">
                <CardTitle className="text-sm font-medium">{card.label}</CardTitle>
                <card.icon className={`h-5 w-5 ${card.color}`} />
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold">{card.value}</div>
                {card.sub && <p className="text-xs text-muted-foreground mt-1">{card.sub}</p>}
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}
