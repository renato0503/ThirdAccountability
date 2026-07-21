<?php
namespace App\Http\Controllers;

use App\Models\{Institution, Project, Diligence, Goal};
use Illuminate\Support\Facades\{Auth, DB, Cache};

class DashboardController extends Controller {
    public function index() {
        $user    = Auth::user();
        $instId  = $user->institution_id;
        $isAdmin = $user->isAdmin();
        $cacheKey = 'dash_' . ($isAdmin ? 'admin' : $instId) . '_' . now()->format('YmdH');

        $stats = Cache::remember($cacheKey, 300, function() use ($isAdmin, $instId) {
            $projectQuery = Project::query();
            if (!$isAdmin && $instId) $projectQuery->where('institution_id', $instId);

            $summary = (clone $projectQuery)
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status="EM_EXECUCAO" THEN 1 ELSE 0 END) as em_execucao, SUM(valor_total) as vt, SUM(valor_recebido) as vr, SUM(valor_executado) as ve')
                ->first();

            $porStatus = (clone $projectQuery)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $pendencias = Diligence::where('status', 'ABERTA')
                ->when(!$isAdmin && $instId, fn($q) => $q->whereHas('project', fn($p) => $p->where('institution_id', $instId)))
                ->count();

            return [
                'total_instituicoes' => $isAdmin ? Institution::where('active', true)->count() : 1,
                'total_projetos'     => (int)($summary->total ?? 0),
                'projetos_execucao'  => (int)($summary->em_execucao ?? 0),
                'pendencias'         => $pendencias,
                'total_aprovado'     => (float)($summary->vt ?? 0),
                'total_recebido'     => (float)($summary->vr ?? 0),
                'total_executado'    => (float)($summary->ve ?? 0),
                'saldo'              => (float)(($summary->vr ?? 0) - ($summary->ve ?? 0)),
                'por_status'         => $porStatus,
            ];
        });

        // Convert por_status back to collection for the view
        $stats['por_status'] = collect($stats['por_status']);

        // Cache recent projects as plain arrays to avoid serialization issues
        $recentProjectsData = Cache::remember('recent_proj_' . ($isAdmin ? 'all' : $instId), 120, function() use ($isAdmin, $instId) {
            return Project::with('institution')
                ->when(!$isAdmin && $instId, fn($q) => $q->where('institution_id', $instId))
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get()
                ->map(fn($p) => [
                    'id'          => $p->id,
                    'nome'        => $p->nome,
                    'codigo'      => $p->codigo,
                    'status'      => $p->status,
                    'status_label'=> $p->status_label,
                    'status_color'=> $p->status_color,
                    'valor_total'     => $p->valor_total,
                    'valor_executado' => $p->valor_executado,
                    'institution_name'=> $p->institution?->nome_fantasia ?? $p->institution?->razao_social ?? '—',
                ])
                ->toArray();
        });

        // Fila de metas ordenadas por prazo (com fallback para projetos com data_fim)
        $goalsQueueData = Cache::remember('goals_queue_' . ($isAdmin ? 'all' : $instId), 120, function() use ($isAdmin, $instId) {
            return Goal::with('project.institution')
                ->whereNotIn('status', ['CONCLUIDA','CANCELADA','APROVADA'])
                ->whereNotNull('prazo')
                ->whereHas('project', function($q) use ($isAdmin, $instId) {
                    if (!$isAdmin && $instId) $q->where('institution_id', $instId);
                })
                ->orderBy('prazo', 'asc')
                ->take(15)
                ->get()
                ->map(function($g) {
                    $prazo  = $g->prazo ? \Carbon\Carbon::parse($g->prazo) : null;
                    $diff   = $prazo ? (int) now()->startOfDay()->diffInDays($prazo->startOfDay(), false) : null;
                    return [
                        'id'                => $g->id,
                        'project_id'        => $g->project_id,
                        'numero'            => $g->numero,
                        'titulo'            => $g->titulo,
                        'status_label'      => $g->status_label,
                        'status_color'      => $g->status_color,
                        'prazo'             => $prazo?->format('d/m/Y'),
                        'dias_restantes'    => $diff,
                        'project_nome'      => $g->project?->nome ?? '—',
                        'project_codigo'    => $g->project?->codigo,
                        'institution_name'  => $g->project?->institution?->nome_fantasia
                                            ?? $g->project?->institution?->razao_social
                                            ?? '—',
                    ];
                })
                ->toArray();
        });

        return view('dashboard', [
            'stats'           => $stats,
            'recentProjects'  => $recentProjectsData,
            'goalsQueue'      => $goalsQueueData,
        ]);
    }
}
