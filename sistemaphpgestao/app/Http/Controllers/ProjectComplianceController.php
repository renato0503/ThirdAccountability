<?php
namespace App\Http\Controllers;

use App\Models\{Project, ProjectComplianceSelection, AuditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectComplianceController extends Controller {

    public function save(Request $req, Project $projeto) {
        $sel = ProjectComplianceSelection::firstOrNew(['project_id' => $projeto->id]);
        $sel->fill([
            'project_id'           => $projeto->id,
            'inc_informacoes'      => $req->boolean('inc_informacoes'),
            'inc_despesas'         => $req->boolean('inc_despesas'),
            'inc_metas'            => $req->boolean('inc_metas'),
            'inc_comprovacao'      => $req->boolean('inc_comprovacao'),
            'inc_diligencias'      => $req->boolean('inc_diligencias'),
            'inc_prestacao_contas' => $req->boolean('inc_prestacao_contas'),
        ])->save();

        return back()->with('success', 'Seleção do compliance salva.');
    }

    public function exportPdf(Request $req, Project $projeto) {
        $projeto->load([
            'institution','fundingSource','specificObjectives','executionLocations',
            'teamMembers','contractedServices','capabilityPhotos',
            'goals.activities','goals.proof','goals.approvals',
            'expenses','diligences.goal',
        ]);

        $sel = $projeto->complianceSelection;

        $sections = [
            'inc_informacoes'      => $req->boolean('inc_informacoes',      $sel->inc_informacoes      ?? true),
            'inc_despesas'         => $req->boolean('inc_despesas',         $sel->inc_despesas         ?? false),
            'inc_metas'            => $req->boolean('inc_metas',            $sel->inc_metas            ?? false),
            'inc_comprovacao'      => $req->boolean('inc_comprovacao',      $sel->inc_comprovacao      ?? false),
            'inc_diligencias'      => $req->boolean('inc_diligencias',      $sel->inc_diligencias      ?? false),
            'inc_prestacao_contas' => $req->boolean('inc_prestacao_contas', $sel->inc_prestacao_contas ?? false),
        ];

        AuditLog::create([
            'user_id'    => Auth::id(),
            'acao'       => 'EXPORT',
            'entidade'   => 'Project',
            'entidade_id'=> $projeto->id,
            'dados'      => json_encode(['tipo'=>'compliance'] + $sections),
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.project-report', [
            'project'     => $projeto,
            'sections'    => $sections,
            'reportTitle' => 'Compliance',
        ])->setPaper('a4', 'portrait');

        $codigo = preg_replace('/[^A-Za-z0-9-]/', '_', $projeto->codigo ?? $projeto->id);
        return $pdf->download("compliance-projeto-{$codigo}-".now()->format('YmdHis').'.pdf');
    }
}
