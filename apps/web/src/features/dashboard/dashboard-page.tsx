import { useAuth } from '@/hooks/use-auth';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Building2, FolderKanban, DollarSign, Search } from 'lucide-react';

export function DashboardPage() {
  const { user } = useAuth();

  const stats = [
    { icon: FolderKanban, label: 'Projetos Ativos', value: '-', color: 'text-blue-600' },
    { icon: Building2, label: 'Instituições', value: '-', color: 'text-green-600' },
    { icon: DollarSign, label: 'Despesas Pendentes', value: '-', color: 'text-yellow-600' },
    { icon: Search, label: 'Pesquisas de Preço', value: '-', color: 'text-purple-600' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-3xl font-bold tracking-tight">
          Bem-vindo{user?.displayName ? `, ${user.displayName}` : ''}
        </h2>
        <p className="text-muted-foreground">Visao geral da sua instituicao</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {stats.map((stat) => (
          <Card key={stat.label}>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium">{stat.label}</CardTitle>
              <stat.icon className={`h-4 w-4 ${stat.color}`} />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stat.value}</div>
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">Atividades Recentes</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground">
              Nenhuma atividade recente.
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">Projetos em Execucao</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground">
              Nenhum projeto em execucao.
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
