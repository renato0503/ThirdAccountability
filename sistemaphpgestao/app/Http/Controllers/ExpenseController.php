<?php
namespace App\Http\Controllers;

use App\Models\{Expense, Project, Goal, AuditLog};
use App\Http\Controllers\Concerns\NormalizesBrlNumbers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller {
    use NormalizesBrlNumbers;

    public function index(Request $req) {
        $user = Auth::user();
        $q = Expense::with(['project','goal'])->orderBy('data_despesa','desc');
        if (!$user->isAdmin() && $user->institution_id) {
            $q->whereHas('project',fn($p)=>$p->where('institution_id',$user->institution_id));
        }
        if ($s = $req->status) $q->where('status',$s);
        if ($pid = $req->project_id) $q->where('project_id',$pid);

        $expenses = $q->paginate(20)->withQueryString();
        $projects = $user->isAdmin()
            ? Project::orderBy('nome')->get()
            : Project::where('institution_id',$user->institution_id)->orderBy('nome')->get();

        $totalAprovado   = Project::when(!$user->isAdmin() && $user->institution_id, fn($q)=>$q->where('institution_id',$user->institution_id))->sum('valor_total');
        $totalRecebido   = Project::when(!$user->isAdmin() && $user->institution_id, fn($q)=>$q->where('institution_id',$user->institution_id))->sum('valor_recebido');
        $totalExecutado  = Project::when(!$user->isAdmin() && $user->institution_id, fn($q)=>$q->where('institution_id',$user->institution_id))->sum('valor_executado');

        return view('expenses.index', compact('expenses','projects','totalAprovado','totalRecebido','totalExecutado'));
    }

    public function create(Request $req) {
        $user = Auth::user();
        if (!$user->canEdit()) abort(403);
        $projects = $user->isAdmin()
            ? Project::orderBy('nome')->get()
            : Project::where('institution_id',$user->institution_id)->orderBy('nome')->get();
        $selectedProject = $req->project_id ? Project::find($req->project_id) : null;
        $goals = $selectedProject ? $selectedProject->goals()->get() : collect();
        $categorias = ['Pessoal','Material de Consumo','Equipamentos','Serviços de Terceiros','Diárias e Viagens','Outros'];
        $formasPagamento = ['PIX','TED','Boleto','Cartão','Dinheiro','Cheque'];
        return view('expenses.create', compact('projects','goals','categorias','formasPagamento','selectedProject'));
    }

    public function store(Request $req) {
        if (!Auth::user()->canEdit()) abort(403);
        $this->normalizeBrl($req, ['valor']);
        $data = $req->validate([
            'project_id'      => 'required|exists:projects,id',
            'goal_id'         => 'nullable|exists:goals,id',
            'categoria'       => 'nullable|string',
            'fornecedor'      => 'nullable|string',
            'cnpj_fornecedor' => 'nullable|string',
            'descricao'       => 'required|string',
            'data_despesa'    => 'nullable|date',
            'data_pagamento'  => 'nullable|date',
            'valor'           => 'required|numeric|min:0',
            'forma_pagamento' => 'nullable|string',
            'numero_nf'       => 'nullable|string',
        ]);
        $data['status'] = 'PENDENTE';
        $expense = Expense::create($data);

        $total = Expense::where('project_id',$data['project_id'])->whereIn('status',['APROVADO','PAGO'])->sum('valor');
        Project::find($data['project_id'])->update(['valor_executado'=>$total]);

        AuditLog::create(['user_id'=>Auth::id(),'acao'=>'CREATE','entidade'=>'Expense','entidade_id'=>$expense->id,'dados'=>json_encode(['descricao'=>$expense->descricao,'valor'=>$expense->valor])]);
        return redirect()->route('financeiro.index')->with('success','Despesa registrada!');
    }

    public function show(Expense $despesa) {
        $despesa->load(['project','goal']);
        $expense = $despesa;
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $despesa) {
        $expense = $despesa;
        $user = Auth::user();
        if (!$user->canEdit()) abort(403);
        $projects = $user->isAdmin() ? Project::orderBy('nome')->get() : Project::where('institution_id',$user->institution_id)->orderBy('nome')->get();
        $goals = $expense->project->goals()->get();
        $categorias = ['Pessoal','Material de Consumo','Equipamentos','Serviços de Terceiros','Diárias e Viagens','Outros'];
        $formasPagamento = ['PIX','TED','Boleto','Cartão','Dinheiro','Cheque'];
        $statuses = ['PENDENTE','APROVADO','PAGO','REJEITADO'];
        return view('expenses.edit', compact('expense','projects','goals','categorias','formasPagamento','statuses'));
    }

    public function update(Request $req, Expense $despesa) {
        if (!Auth::user()->canEdit()) abort(403);
        $this->normalizeBrl($req, ['valor']);
        $data = $req->validate([
            'project_id'      => 'required|exists:projects,id',
            'goal_id'         => 'nullable|exists:goals,id',
            'categoria'       => 'nullable|string',
            'fornecedor'      => 'nullable|string',
            'cnpj_fornecedor' => 'nullable|string',
            'descricao'       => 'required|string',
            'data_despesa'    => 'nullable|date',
            'data_pagamento'  => 'nullable|date',
            'valor'           => 'required|numeric|min:0',
            'forma_pagamento' => 'nullable|string',
            'numero_nf'       => 'nullable|string',
            'status'          => 'required|string',
        ]);
        $despesa->update($data);
        $total = Expense::where('project_id',$data['project_id'])->whereIn('status',['APROVADO','PAGO'])->sum('valor');
        Project::find($data['project_id'])->update(['valor_executado'=>$total]);
        return redirect()->route('financeiro.index')->with('success','Despesa atualizada!');
    }

    public function destroy(Expense $despesa) {
        if (!Auth::user()->canEdit()) abort(403);
        $pid = $despesa->project_id;
        $despesa->delete();
        $total = Expense::where('project_id',$pid)->whereIn('status',['APROVADO','PAGO'])->sum('valor');
        Project::find($pid)->update(['valor_executado'=>$total]);
        return back()->with('success','Despesa excluída.');
    }

    public function updateStatus(Request $req, Expense $expense) {
        $req->validate(['status' => 'required|in:PENDENTE,APROVADO,PAGO,REJEITADO']);
        $expense->update(['status'=>$req->status]);
        $total = Expense::where('project_id',$expense->project_id)->whereIn('status',['APROVADO','PAGO'])->sum('valor');
        Project::find($expense->project_id)->update(['valor_executado'=>$total]);
        return back()->with('success','Status atualizado!');
    }
}
