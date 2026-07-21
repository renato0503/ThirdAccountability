import { NavLink } from 'react-router-dom';
import { cn } from '@/lib/utils';
import {
  LayoutDashboard,
  Building2,
  FolderKanban,
  DollarSign,
  FileText,
  Scale,
  Receipt,
  Search,
  MessageSquareText,
  Users,
  Settings,
  ShieldCheck,
  ClipboardList,
  Activity,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';

interface SidebarProps {
  collapsed: boolean;
  onToggle: () => void;
}

const menuItems = [
  { icon: LayoutDashboard, label: 'Dashboard', path: '/dashboard' },
  { icon: Building2, label: 'Instituições', path: '/instituicoes' },
  { icon: FolderKanban, label: 'Projetos', path: '/projetos' },
  { icon: DollarSign, label: 'Financeiro', path: '/financeiro' },
  { icon: FileText, label: 'Documentos', path: '/documentos' },
  { icon: Scale, label: 'Diligências', path: '/diligencias' },
  { icon: Receipt, label: 'Prestação de Contas', path: '/prestacao-contas' },
  { icon: Search, label: 'Pesquisa de Preços', path: '/pesquisa-precos' },
  { icon: MessageSquareText, label: 'Chat IA (Cotação)', path: '/pesquisa-precos/chat-ia' },
];

const adminItems = [
  { icon: Users, label: 'Usuários', path: '/usuarios' },
  { icon: ShieldCheck, label: 'Auditoria', path: '/auditoria' },
  { icon: ClipboardList, label: 'Relatórios', path: '/relatorios' },
  { icon: Settings, label: 'Configurações', path: '/configuracoes' },
  { icon: Activity, label: 'Diagnóstico', path: '/sistema/diagnostico' },
];

export function Sidebar({ collapsed, onToggle }: SidebarProps) {
  return (
    <aside
      className={cn(
        'flex flex-col border-r bg-sidebar-background transition-all duration-300',
        collapsed ? 'w-16' : 'w-64',
      )}
    >
      {/* Logo */}
      <div className={cn('flex h-14 items-center border-b px-4', collapsed && 'justify-center')}>
        {collapsed ? (
          <span className="text-lg font-bold text-sidebar-primary">G3</span>
        ) : (
          <span className="text-lg font-bold text-sidebar-primary">Gestão Terceiro</span>
        )}
      </div>

      {/* Toggle */}
      <Button
        variant="ghost"
        size="icon"
        className="absolute -right-3 top-4 z-10 h-6 w-6 rounded-full border shadow-sm"
        onClick={onToggle}
      >
        {collapsed ? <ChevronRight className="h-3 w-3" /> : <ChevronLeft className="h-3 w-3" />}
      </Button>

      {/* Navigation */}
      <nav className="flex-1 space-y-1 p-2">
        {menuItems.map((item) => (
          <NavLink
            key={item.path}
            to={item.path}
            className={({ isActive }) =>
              cn(
                'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                collapsed && 'justify-center px-2',
                isActive
                  ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                  : 'text-sidebar-foreground hover:bg-sidebar-accent/50',
              )
            }
            title={collapsed ? item.label : undefined}
          >
            <item.icon className="h-4 w-4 shrink-0" />
            {!collapsed && <span>{item.label}</span>}
          </NavLink>
        ))}

        <Separator className="my-2" />

        {adminItems.map((item) => (
          <NavLink
            key={item.path}
            to={item.path}
            className={({ isActive }) =>
              cn(
                'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                collapsed && 'justify-center px-2',
                isActive
                  ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                  : 'text-sidebar-foreground hover:bg-sidebar-accent/50',
              )
            }
            title={collapsed ? item.label : undefined}
          >
            <item.icon className="h-4 w-4 shrink-0" />
            {!collapsed && <span>{item.label}</span>}
          </NavLink>
        ))}
      </nav>
    </aside>
  );
}
