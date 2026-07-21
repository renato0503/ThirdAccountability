<?php
namespace App\Http\Controllers;

use App\Models\{Goal, Project, Activity, AuditLog, GoalProof, GoalApproval};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage};

class GoalController extends Controller {

    private array $statuses = [
        'PENDENTE'              => 'Pendente',
        'EM_ANDAMENTO'          => 'Em Andamento',
        'ENVIADA_ANALISE'       => 'Enviada p/ Análise',
        'APROVADA'              => 'Aprovada',
        'REPROVADA'             => 'Reprovada',
        'APROVADA_COM_RESSALVA' => 'Aprovada c/ Ressalva',
        'CONCLUIDA'             => 'Concluída',
        'CANCELADA'             => 'Cancelada',
    ];

    public function create(Project $projeto) {
        $statuses = $this->statuses;
        return view('goals.create', compact('projeto','statuses'));
    }

    public function store(Request $req, Project $projeto) {
        if (!Auth::user()->canEdit()) abort(403);
        $data = $req->validate([
            'numero'               => 'nullable|string|max:20',
            'tipo_meta'            => 'nullable|in:QUANTITATIVA,QUALITATIVA',
            'titulo'               => 'required|string|max:255',
            'descricao'            => 'nullable|string',
            'indicador'            => 'nullable|string',
            'afericao_meta'        => 'nullable|string',
            'quantidade_prevista'  => 'nullable|integer',
            'unidade_medida'       => 'nullable|string',
            'prazo'                => 'nullable|date',
            'data_inicio'          => 'nullable|date',
            'responsavel'          => 'nullable|string',
            'telefone_responsavel' => 'nullable|string',
            'email_responsavel'    => 'nullable|email',
            'status'               => 'nullable|string',
        ]);
        $data['project_id'] = $projeto->id;
        $data['status']     = $data['status'] ?? 'PENDENTE';
        $data['tipo_meta']  = $data['tipo_meta'] ?? 'QUANTITATIVA';
        Goal::create($data);
        return redirect()->route('projetos.show',['projeto'=>$projeto])->with('success','Meta criada!');
    }

    public function edit(Project $projeto, Goal $meta) {
        $statuses = $this->statuses;
        return view('goals.edit', compact('projeto','meta','statuses'));
    }

    public function update(Request $req, Project $projeto, Goal $meta) {
        if (!Auth::user()->canEdit()) abort(403);
        if ($meta->project_id !== $projeto->id) abort(404);
        $data = $req->validate([
            'numero'               => 'nullable|string|max:20',
            'tipo_meta'            => 'nullable|in:QUANTITATIVA,QUALITATIVA',
            'titulo'               => 'required|string|max:255',
            'descricao'            => 'nullable|string',
            'indicador'            => 'nullable|string',
            'afericao_meta'        => 'nullable|string',
            'quantidade_prevista'  => 'nullable|integer',
            'quantidade_realizada' => 'nullable|integer',
            'unidade_medida'       => 'nullable|string',
            'prazo'                => 'nullable|date',
            'data_inicio'          => 'nullable|date',
            'responsavel'          => 'nullable|string',
            'telefone_responsavel' => 'nullable|string',
            'email_responsavel'    => 'nullable|email',
            'status'               => 'nullable|string',
        ]);
        $meta->update($data);
        return redirect()->route('projetos.show',['projeto'=>$projeto])->with('success','Meta atualizada!');
    }

    public function destroy(Project $projeto, Goal $meta) {
        if (!Auth::user()->canEdit()) abort(403);
        if ($meta->project_id !== $projeto->id) abort(404);
        $meta->delete();
        return redirect()->route('projetos.show',['projeto'=>$projeto])->with('success','Meta excluída.');
    }

    public function sendToAnalysis(Request $req, Project $projeto, Goal $meta) {
        $meta->update(['status'=>'ENVIADA_ANALISE']);
        AuditLog::create(['user_id'=>Auth::id(),'acao'=>'UPDATE','entidade'=>'Goal','entidade_id'=>$meta->id,'dados'=>json_encode(['status'=>'ENVIADA_ANALISE'])]);
        return back()->with('success','Meta enviada para análise!');
    }

    public function storeActivity(Request $req, Project $projeto, Goal $meta) {
        if (!Auth::user()->canEdit()) abort(403);
        $data = $req->validate([
            'nome'               => 'required|string|max:255',
            'descricao'          => 'nullable|string',
            'data_inicio'        => 'nullable|date',
            'data_fim'           => 'nullable|date',
            'responsavel'        => 'nullable|string',
            'percentual_execucao'=> 'nullable|integer|min:0|max:100',
            'status'             => 'nullable|string',
        ]);
        $data['goal_id'] = $meta->id;
        $data['status']  = $data['status'] ?? 'PENDENTE';
        Activity::create($data);
        return back()->with('success','Atividade adicionada!');
    }

    public function saveProof(Request $req, Project $projeto, Goal $meta) {
        if (!Auth::user()->canEdit()) abort(403);
        if ($meta->project_id !== $projeto->id) abort(404);

        $data = $req->validate([
            'descricao'  => 'required|string',
            'link_video' => 'nullable|url|max:500',
            'fotos.*'    => 'nullable|image|mimes:jpg,jpeg|max:6144',
            'anexo'      => 'nullable|file|max:10240',
        ]);

        $proof = $meta->proof()->firstOrNew([]);
        $existingFotos = $proof->fotos ?? [];

        if ($req->hasFile('fotos')) {
            foreach ($req->file('fotos') as $idx => $foto) {
                if (!$foto) continue;
                if (count($existingFotos) >= 5) break;
                $path = $foto->store('goal_proofs/'.$meta->id, 'public');
                $existingFotos[] = $path;
            }
            $existingFotos = array_slice($existingFotos, 0, 5);
        }

        $proof->goal_id    = $meta->id;
        $proof->fotos      = $existingFotos;
        $proof->descricao  = $data['descricao'];
        $proof->link_video = $data['link_video'] ?? null;

        if ($req->hasFile('anexo')) {
            if ($proof->anexo_path) {
                Storage::disk('public')->delete($proof->anexo_path);
            }
            $file = $req->file('anexo');
            $proof->anexo_path = $file->store('goal_proofs/'.$meta->id.'/anexos', 'public');
            $proof->anexo_nome = $file->getClientOriginalName();
        }

        $proof->save();

        AuditLog::create([
            'user_id'   =>Auth::id(),
            'acao'      =>'UPDATE',
            'entidade'  =>'GoalProof',
            'entidade_id'=>$proof->id,
        ]);

        return back()->with('success','Comprovação salva para a Meta '.$meta->numero.'.');
    }

    public function deleteProofPhoto(Request $req, Project $projeto, Goal $meta) {
        if (!Auth::user()->canEdit()) abort(403);
        if ($meta->project_id !== $projeto->id) abort(404);
        $index = (int) $req->input('index');
        $proof = $meta->proof;
        if (!$proof) return back();
        $fotos = $proof->fotos ?? [];
        if (isset($fotos[$index])) {
            Storage::disk('public')->delete($fotos[$index]);
            array_splice($fotos, $index, 1);
            $proof->update(['fotos' => array_values($fotos)]);
        }
        return back()->with('success','Foto removida.');
    }

    public function deleteProofAttachment(Project $projeto, Goal $meta) {
        if (!Auth::user()->canEdit()) abort(403);
        if ($meta->project_id !== $projeto->id) abort(404);
        $proof = $meta->proof;
        if ($proof && $proof->anexo_path) {
            Storage::disk('public')->delete($proof->anexo_path);
            $proof->update(['anexo_path' => null, 'anexo_nome' => null]);
        }
        return back()->with('success','Anexo removido.');
    }

    public function approve(Request $req, Project $projeto, Goal $meta) {
        if (!Auth::user()->canApprove()) abort(403);
        if ($meta->project_id !== $projeto->id) abort(404);

        $data = $req->validate([
            'avaliador_numero' => 'required|integer|min:1|max:5',
            'avaliador_nome'   => 'nullable|string|max:255',
            'observacoes'      => 'nullable|string',
        ]);

        if (!$meta->proof || !$meta->proof->isComplete()) {
            return back()->with('error','Aprovação só permitida após Comprovação completa (5 fotos + descrição).');
        }

        $approval = GoalApproval::firstOrNew([
            'goal_id'          => $meta->id,
            'avaliador_numero' => $data['avaliador_numero'],
        ]);

        $alreadyApproved = $approval->aprovado;

        $approval->avaliador_nome = $data['avaliador_nome'] ?? Auth::user()->name;
        $approval->user_id        = Auth::id();
        $approval->aprovado       = true;
        $approval->observacoes    = $data['observacoes'] ?? null;
        $approval->aprovado_em    = $approval->aprovado_em ?? now();
        $approval->save();

        $meta->refresh()->load('approvals');
        if ($meta->fullyApproved() && $meta->status !== 'APROVADA') {
            $meta->update(['status' => 'APROVADA']);
        }

        AuditLog::create([
            'user_id'   =>Auth::id(),
            'acao'      =>$alreadyApproved ? 'UPDATE' : 'CREATE',
            'entidade'  =>'GoalApproval',
            'entidade_id'=>$approval->id,
            'dados'     =>json_encode(['avaliador'=>$data['avaliador_numero']]),
        ]);

        return back()->with('success','Aprovação registrada (Avaliador '.$data['avaliador_numero'].').');
    }

    public function unapprove(Request $req, Project $projeto, Goal $meta) {
        if (!Auth::user()->canApprove()) abort(403);
        if ($meta->project_id !== $projeto->id) abort(404);
        $num = (int) $req->input('avaliador_numero');
        GoalApproval::where('goal_id',$meta->id)->where('avaliador_numero',$num)->delete();
        $meta->refresh()->load('approvals');
        if (!$meta->fullyApproved() && $meta->status === 'APROVADA') {
            $meta->update(['status' => 'ENVIADA_ANALISE']);
        }
        return back()->with('success','Aprovação revertida (Avaliador '.$num.').');
    }

    public function sendToAccounting(Request $req, Project $projeto) {
        if (!Auth::user()->canEdit()) abort(403);
        $allApproved = $projeto->goals->every(fn($g) => $g->fullyApproved());
        if (!$allApproved) {
            return back()->with('error','Todas as metas precisam ter aprovação dos 5 avaliadores antes de enviar para Prestação de Contas.');
        }
        if ($projeto->status !== 'PRESTACAO_CONTAS') {
            $projeto->update(['status' => 'PRESTACAO_CONTAS']);
        }
        AuditLog::create([
            'user_id'    => Auth::id(),
            'acao'       => 'UPDATE',
            'entidade'   => 'Project',
            'entidade_id'=> $projeto->id,
            'dados'      => json_encode(['status'=>'PRESTACAO_CONTAS']),
        ]);
        return redirect()->route('projetos.show', ['projeto' => $projeto, 'tab' => 'prestacao'])
            ->with('success','Projeto enviado para Prestação de Contas.');
    }
}
