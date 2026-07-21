<?php
namespace App\Http\Controllers;

use App\Models\{Diligence, Project, Goal, AuditLog};
use App\Mail\DiligenciaNovaEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Mail};

class DiligenceController extends Controller {
    public function index(Request $req) {
        $user = Auth::user();
        $q = Diligence::with(['project','goal'])->orderBy('created_at','desc');
        if (!$user->isAdmin() && $user->institution_id) {
            $q->whereHas('project',fn($p)=>$p->where('institution_id',$user->institution_id));
        }
        if ($s = $req->status) $q->where('status',$s);

        // Metas aguardando análise (para destaque no topo)
        $metasEmAnalise = Goal::with('project')
            ->where('status','ENVIADA_ANALISE')
            ->when(!$user->isAdmin() && $user->institution_id, fn($q2) =>
                $q2->whereHas('project', fn($p) => $p->where('institution_id', $user->institution_id))
            )
            ->orderBy('updated_at','desc')
            ->get();

        $diligences = $q->paginate(20)->withQueryString();
        return view('diligences.index', compact('diligences','metasEmAnalise'));
    }

    public function create() {
        $user = Auth::user();
        if (!$user->canEdit()) abort(403);
        $projects = $user->isAdmin()
            ? Project::orderBy('nome')->get()
            : Project::where('institution_id',$user->institution_id)->orderBy('nome')->get();
        $tipos = ['Financeira','Técnica','Documental','Jurídica','Outra'];
        return view('diligences.create', compact('projects','tipos'));
    }

    public function store(Request $req) {
        if (!Auth::user()->canEdit()) abort(403);
        $data = $req->validate([
            'project_id' => 'required|exists:projects,id',
            'goal_id'    => 'nullable|exists:goals,id',
            'tipo'       => 'nullable|string',
            'descricao'  => 'required|string',
            'responsavel'=> 'nullable|string',
            'prazo'      => 'nullable|date',
        ]);
        $data['status'] = 'ABERTA';
        $diligence = Diligence::create($data);

        if ($data['responsavel'] ?? null) {
            $responsavelUser = \App\Models\User::where('name', $data['responsavel'])
                ->orWhere('email', $data['responsavel'])
                ->first();
            if ($responsavelUser) {
                Mail::to($responsavelUser->email)->queue(new DiligenciaNovaEmail($diligence));
            }
        }

        return redirect()->route('diligencias.index')->with('success','Diligência criada!');
    }

    public function show(Diligence $diligencia) {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->institution_id && $diligencia->project->institution_id !== $user->institution_id) abort(403);
        $diligencia->load(['project','goal']);
        return view('diligences.show', ['diligence'=>$diligencia]);
    }

    public function edit(Diligence $diligencia) {
        $user2 = Auth::user();
        if (!$user2->canEdit()) abort(403);
        if (!$user2->isAdmin() && $user2->institution_id && $diligencia->project->institution_id !== $user2->institution_id) abort(403);
        $user = Auth::user();
        $projects = $user->isAdmin()
            ? Project::orderBy('nome')->get()
            : Project::where('institution_id',$user->institution_id)->orderBy('nome')->get();
        $tipos    = ['Financeira','Técnica','Documental','Jurídica','Outra'];
        $statuses = ['ABERTA','RESPONDIDA','ENCERRADA'];
        // Metas do projeto selecionado
        $metas = Goal::where('project_id', $diligencia->project_id)->orderBy('numero')->get();
        return view('diligences.edit', ['diligence'=>$diligencia,'projects'=>$projects,'tipos'=>$tipos,'statuses'=>$statuses,'metas'=>$metas]);
    }

    public function update(Request $req, Diligence $diligencia) {
        $user = Auth::user();
        if (!$user->canEdit()) abort(403);
        if (!$user->isAdmin() && $user->institution_id && $diligencia->project->institution_id !== $user->institution_id) abort(403);
        $data = $req->validate([
            'project_id' => 'required|exists:projects,id',
            'goal_id'    => 'nullable|exists:goals,id',
            'tipo'       => 'nullable|string',
            'descricao'  => 'required|string',
            'responsavel'=> 'nullable|string',
            'prazo'      => 'nullable|date',
            'status'     => 'required|string',
            'resposta'   => 'nullable|string',
        ]);
        $diligencia->update($data);
        AuditLog::create(['user_id'=>Auth::id(),'acao'=>'UPDATE','entidade'=>'Diligence','entidade_id'=>$diligencia->id,'dados'=>json_encode(['status'=>$diligencia->status])]);
        return redirect()->route('diligencias.show',['diligencia'=>$diligencia])->with('success','Diligência atualizada!');
    }

    public function destroy(Diligence $diligencia) {
        $user = Auth::user();
        if (!$user->canEdit()) abort(403);
        if (!$user->isAdmin() && $user->institution_id && $diligencia->project->institution_id !== $user->institution_id) abort(403);
        $diligencia->delete();
        return redirect()->route('diligencias.index')->with('success','Diligência excluída.');
    }

    /**
     * Registrar parecer de uma meta (Aprovada / Reprovada / Aprovada com ressalva)
     */
    public function parecer(Request $req, Goal $meta) {
        if (!Auth::user()->canApprove()) abort(403);
        $data = $req->validate([
            'novo_status'  => 'required|in:APROVADA,REPROVADA,APROVADA_COM_RESSALVA',
            'observacoes'  => 'nullable|string',
        ]);

        $meta->update([
            'status' => $data['novo_status'],
        ]);

        // Registrar diligência automática se houver observação
        if (!empty($data['observacoes'])) {
            Diligence::create([
                'project_id' => $meta->project_id,
                'goal_id'    => $meta->id,
                'tipo'       => 'Técnica',
                'descricao'  => $data['observacoes'],
                'status'     => 'ENCERRADA',
            ]);
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'acao'       => 'UPDATE',
            'entidade'   => 'Goal',
            'entidade_id'=> $meta->id,
            'dados'      => json_encode(['status' => $data['novo_status']]),
        ]);

        return redirect()->route('diligencias.index')->with('success','Parecer registrado: ' . $meta->status_label);
    }
}
