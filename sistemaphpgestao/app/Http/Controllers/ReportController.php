<?php
namespace App\Http\Controllers;

use App\Models\{Project, Expense, Diligence, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class ReportController extends Controller {
    public function index() {
        $user   = Auth::user();
        $filter = $user->isAdmin() ? [] : ['institution_id' => $user->institution_id];

        $stats = [
            'total_aprovado'  => Project::where($filter)->sum('valor_total'),
            'total_recebido'  => Project::where($filter)->sum('valor_recebido'),
            'total_executado' => Project::where($filter)->sum('valor_executado'),
            'saldo'           => Project::where($filter)->sum('valor_recebido') - Project::where($filter)->sum('valor_executado'),
            'por_status'      => Project::where($filter)->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total','status'),
            'por_categoria'   => Expense::when($filter, fn($q) => $q->whereHas('project', fn($p) => $p->where($filter)))
                ->select('categoria', DB::raw('sum(valor) as total'))->groupBy('categoria')->orderByDesc('total')->get(),
        ];

        $projects = Project::with('institution')->where($filter)->orderBy('nome')->get();

        return view('reports.index', compact('stats', 'projects'));
    }

    public function export(Request $req) {
        $tipo   = $req->tipo ?? 'projetos';
        $format = 'csv';
        $user   = Auth::user();
        $filter = $user->isAdmin() ? [] : ['institution_id'=>$user->institution_id];

        if ($format === 'csv') {
            $filename = "relatorio-{$tipo}-" . now()->format('YmdHis') . '.csv';
            $headers  = ['Content-Type'=>'text/csv; charset=UTF-8','Content-Disposition'=>"attachment; filename=\"$filename\""];

            $callback = function() use ($tipo, $filter) {
                $out = fopen('php://output','w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

                if ($tipo === 'projetos') {
                    fputcsv($out, ['Nome','Código','Instituição','Status','Valor Total','Valor Recebido','Valor Executado','Responsável','Data Início','Data Fim'], ';');
                    Project::with('institution')->where($filter)->orderBy('nome')->chunk(100, function($rows) use ($out) {
                        foreach ($rows as $p) {
                            fputcsv($out, [$p->nome,$p->codigo,$p->institution?->razao_social,$p->status,number_format($p->valor_total,2,',','.'),number_format($p->valor_recebido,2,',','.'),number_format($p->valor_executado,2,',','.'),$p->responsavel,$p->data_inicio?->format('d/m/Y'),$p->data_fim?->format('d/m/Y')], ';');
                        }
                    });
                } elseif ($tipo === 'financeiro') {
                    fputcsv($out, ['Descrição','Projeto','Fornecedor','Categoria','Data','Valor','Status'], ';');
                    Expense::with('project')->when($filter, fn($q)=>$q->whereHas('project',fn($p)=>$p->where($filter)))->orderBy('data_despesa','desc')->chunk(100, function($rows) use ($out) {
                        foreach ($rows as $e) {
                            fputcsv($out, [$e->descricao,$e->project?->nome,$e->fornecedor,$e->categoria,$e->data_despesa?->format('d/m/Y'),number_format($e->valor,2,',','.'),$e->status], ';');
                        }
                    });
                } elseif ($tipo === 'diligencias') {
                    fputcsv($out, ['Projeto','Tipo','Descrição','Responsável','Prazo','Status','Resposta'], ';');
                    Diligence::with('project')->when($filter, fn($q)=>$q->whereHas('project',fn($p)=>$p->where($filter)))->orderBy('prazo')->chunk(100, function($rows) use ($out) {
                        foreach ($rows as $d) {
                            fputcsv($out, [$d->project?->nome,$d->tipo,$d->descricao,$d->responsavel,$d->prazo?->format('d/m/Y'),$d->status,$d->resposta], ';');
                        }
                    });
                }
                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        return redirect()->route('relatorios.index');
    }

    public function pdf(Request $req, string $tipo)
    {
        $user   = Auth::user();
        $filter = $user->isAdmin() ? [] : ['institution_id' => $user->institution_id];

        $stats = [
            'total_aprovado'  => Project::where($filter)->sum('valor_total'),
            'total_recebido'  => Project::where($filter)->sum('valor_recebido'),
            'total_executado' => Project::where($filter)->sum('valor_executado'),
            'saldo'           => Project::where($filter)->sum('valor_recebido') - Project::where($filter)->sum('valor_executado'),
        ];

        $data = ['stats' => $stats];

        if ($tipo === 'projetos') {
            $data['titulo']   = 'Relatório de Projetos';
            $data['subtitulo'] = 'Visão geral de todos os projetos';
            $data['projetos'] = Project::with('institution')->where($filter)->orderBy('nome')->get();
        } elseif ($tipo === 'financeiro') {
            $data['titulo']    = 'Relatório Financeiro';
            $data['subtitulo'] = 'Despesas registradas no sistema';
            $data['despesas']  = Expense::with('project')
                ->when($filter, fn($q) => $q->whereHas('project', fn($p) => $p->where($filter)))
                ->orderBy('data_despesa', 'desc')->get();
        } else {
            $data['titulo']   = 'Relatório Geral';
            $data['projetos'] = Project::with('institution')->where($filter)->orderBy('nome')->get();
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.relatorio', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download("relatorio-{$tipo}-" . now()->format('YmdHis') . '.pdf');
    }
}
