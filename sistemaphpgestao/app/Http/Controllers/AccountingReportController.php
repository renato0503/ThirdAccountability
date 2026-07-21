<?php
namespace App\Http\Controllers;

use App\Models\{AccountingReport, Project, Goal, AuditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage};

class AccountingReportController extends Controller {
    public function index(Request $req) {
        $user = Auth::user();
        $q = AccountingReport::with(['project.institution'])->orderBy('updated_at','desc');
        if (!$user->isAdmin() && $user->institution_id) {
            $q->whereHas('project',fn($p)=>$p->where('institution_id',$user->institution_id));
        }
        if ($req->status && $req->status !== 'TODOS') $q->where('status', $req->status);
        $reports  = $q->paginate(20)->withQueryString();
        $statuses = ['RASCUNHO','ENVIADA','EM_ANALISE','APROVADA','REPROVADA'];
        return view('accounting.index', compact('reports','statuses'));
    }

    public function create() {
        $user = Auth::user();
        $projects = $user->isAdmin()
            ? Project::orderBy('nome')->get()
            : Project::where('institution_id',$user->institution_id)->orderBy('nome')->get();
        return view('accounting.create', compact('projects'));
    }

    public function store(Request $req) {
        $data = $req->validate([
            'project_id'     => 'required|exists:projects,id',
            'observacoes'    => 'nullable|string',
            'relatorio_texto'=> 'nullable|string',
            'links_videos'   => 'nullable|string',
        ]);
        $data['status'] = 'RASCUNHO';
        $report = AccountingReport::create($data);
        return redirect()->route('prestacao-contas.show',['prestacao_conta'=>$report])->with('success','Prestação de contas criada!');
    }

    private function authorizeReport(AccountingReport $report): void {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->institution_id && $report->project->institution_id !== $user->institution_id) abort(403);
    }

    public function show(AccountingReport $prestacao_conta) {
        $this->authorizeReport($prestacao_conta);
        $prestacao_conta->load([
            'project.institution',
            'project.expenses',
            'project.goals' => fn($q) => $q->orderBy('numero'),
        ]);
        // Metas aprovadas para exibição
        $metasAprovadas = $prestacao_conta->project->goals
            ->whereIn('status', ['APROVADA','APROVADA_COM_RESSALVA']);

        return view('accounting.show', [
            'report'         => $prestacao_conta,
            'metasAprovadas' => $metasAprovadas,
        ]);
    }

    public function edit(AccountingReport $prestacao_conta) {
        $this->authorizeReport($prestacao_conta);
        $statuses = ['RASCUNHO','ENVIADA','EM_ANALISE','APROVADA','REPROVADA'];
        return view('accounting.edit', ['report'=>$prestacao_conta,'statuses'=>$statuses]);
    }

    public function update(Request $req, AccountingReport $prestacao_conta) {
        $this->authorizeReport($prestacao_conta);
        $data = $req->validate([
            'status'         => 'required|in:RASCUNHO,ENVIADA,EM_ANALISE,APROVADA,REPROVADA',
            'observacoes'    => 'nullable|string',
            'relatorio_texto'=> 'nullable|string',
            'links_videos'   => 'nullable|string',
            'data_envio'     => 'nullable|date',
            'data_aprovacao' => 'nullable|date',
            'fotos.*'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        // Handle photos upload (up to 5)
        if ($req->hasFile('fotos')) {
            $existingFotos = $prestacao_conta->fotos ?? [];
            $newFotos = [];
            $slot = 0;
            foreach ($req->file('fotos') as $foto) {
                if ($foto && $slot < 5) {
                    $path = $foto->store('prestacao_fotos','public');
                    $newFotos[] = $path;
                    $slot++;
                }
            }
            if (!empty($newFotos)) {
                $merged = array_merge($existingFotos, $newFotos);
                $data['fotos'] = array_slice($merged, 0, 5);
            }
        }

        $prestacao_conta->update($data);
        AuditLog::create(['user_id'=>Auth::id(),'acao'=>'UPDATE','entidade'=>'AccountingReport','entidade_id'=>$prestacao_conta->id,'dados'=>json_encode(['status'=>$prestacao_conta->status])]);
        return redirect()->route('prestacao-contas.show',['prestacao_conta'=>$prestacao_conta])->with('success','Prestação atualizada!');
    }

    public function removePhoto(Request $req, AccountingReport $prestacao_conta) {
        $index = (int) $req->index;
        $fotos = $prestacao_conta->fotos ?? [];
        if (isset($fotos[$index])) {
            Storage::disk('public')->delete($fotos[$index]);
            array_splice($fotos, $index, 1);
            $prestacao_conta->update(['fotos' => array_values($fotos)]);
        }
        return back()->with('success','Foto removida.');
    }

    public function destroy(AccountingReport $prestacao_conta) {
        $this->authorizeReport($prestacao_conta);
        // Remove fotos
        if ($fotos = $prestacao_conta->fotos) {
            foreach ($fotos as $foto) {
                Storage::disk('public')->delete($foto);
            }
        }
        $prestacao_conta->delete();
        return redirect()->route('prestacao-contas.index')->with('success','Prestação excluída.');
    }
}
