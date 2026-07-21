<?php
namespace App\Http\Controllers;

use App\Models\{Institution, Director, InstitutionProjectHistory, AuditLog};
use App\Http\Controllers\Concerns\NormalizesBrlNumbers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage, Log, Schema};
use Barryvdh\DomPDF\Facade\Pdf;

class InstitutionController extends Controller
{
    use NormalizesBrlNumbers;

    public function index(Request $req)
    {
        $user = Auth::user();
        $q = Institution::withCount('projects');
        if (!$user->isAdmin() && $user->institution_id) {
            $q->where('id', $user->institution_id);
        }
        if ($s = $req->search) {
            $q->where(fn($x) => $x->where('razao_social', 'like', "%$s%")
                ->orWhere('nome_fantasia', 'like', "%$s%")
                ->orWhere('cnpj', 'like', "%$s%"));
        }
        $institutions = $q->orderBy('razao_social')->paginate(15)->withQueryString();
        return view('institutions.index', compact('institutions'));
    }

    public function create()
    {
        if (!Auth::user()->canEdit()) abort(403);
        return view('institutions.create');
    }

    public function store(Request $req)
    {
        if (!Auth::user()->canEdit()) abort(403);
        $data = $req->validate([
            'razao_social'            => 'required|string|max:255',
            'nome_fantasia'           => 'nullable|string|max:255',
            'cnpj'                    => 'required|string',
            'email'                   => 'nullable|email',
            'telefone'                => 'nullable|string',
            'site'                    => 'nullable|string',
            'instagram'               => 'nullable|string|max:255',
            'endereco'                => 'nullable|string',
            'cep'                     => 'nullable|string|max:9',
            'numero'                  => 'nullable|string|max:20',
            'complemento'             => 'nullable|string|max:100',
            'bairro'                  => 'nullable|string|max:100',
            'municipio'               => 'nullable|string',
            'estado'                  => 'nullable|string|max:2',
            'area_atuacao'            => 'nullable|string',
            'dados_bancarios'         => 'nullable|string',
            'banco'                   => 'nullable|string|max:100',
            'agencia'                 => 'nullable|string|max:20',
            'conta_corrente'          => 'nullable|string|max:30',
            'tipo_conta'              => 'nullable|string|max:30',
            'chave_pix'               => 'nullable|string|max:100',
            'representante_legal'     => 'nullable|string',
            'presidente_cpf'          => 'nullable|string|max:20',
            'presidente_rg'           => 'nullable|string|max:30',
            'presidente_rg_expedicao' => 'nullable|date',
            'presidente_nascimento'   => 'nullable|date',
            'presidente_telefone'     => 'nullable|string|max:20',
            'presidente_email'        => 'nullable|email',
            'presidente_endereco'     => 'nullable|string',
            'presidente_foto'         => 'nullable|image|max:2048',
        ]);
        $data['cnpj'] = preg_replace('/\D/', '', $data['cnpj']);

        if (Institution::where('cnpj', $data['cnpj'])->exists()) {
            return back()->withErrors(['cnpj' => 'CNPJ já cadastrado.'])->withInput();
        }
        if ($req->hasFile('presidente_foto')) {
            $data['presidente_foto'] = $req->file('presidente_foto')->store('presidente_fotos', 'public');
        }
        $inst = Institution::create($data);
        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => 'CREATE',
            'entidade'    => 'Institution',
            'entidade_id' => $inst->id,
            'dados'       => json_encode(['razao_social' => $inst->razao_social]),
        ]);
        return redirect()->route('instituicoes.show', ['instituico' => $inst])
            ->with('success', 'Instituição criada com sucesso!');
    }

    public function show(Institution $instituico)
    {
        try {
            $user = Auth::user();
            if ($user && !$user->isAdmin() && $user->institution_id
                && (int)$user->institution_id !== (int)$instituico->id) {
                abort(403, 'Você não tem permissão para visualizar esta instituição.');
            }

            $relations = ['diretoria', 'conselhoFiscal', 'fundingSources', 'projectHistories', 'documents'];
            foreach ($relations as $rel) {
                try {
                    $instituico->loadMissing($rel);
                } catch (\Throwable $e) {
                    Log::warning("Falha ao carregar relacao {$rel} da instituicao {$instituico->id}: " . $e->getMessage());
                }
            }

            try {
                $instituico->loadMissing(['projects' => function ($q) {
                    $q->orderBy('updated_at', 'desc');
                    try {
                        if (Schema::hasTable('goals'))    { $q->withCount('goals'); }
                        if (Schema::hasTable('expenses')) { $q->withCount('expenses'); }
                    } catch (\Throwable $e) {
                        Log::warning('withCount goals/expenses falhou: ' . $e->getMessage());
                    }
                }]);
            } catch (\Throwable $e) {
                Log::warning('Falha ao carregar projects da instituicao ' . $instituico->id . ': ' . $e->getMessage());
            }

            return view('institutions.show', ['institution' => $instituico]);
        } catch (\Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                throw $e;
            }
            Log::error('Erro 500 no show da instituicao ' . ($instituico->id ?? '?') . ': ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            if (config('app.debug')) {
                throw $e;
            }
            return redirect()->route('instituicoes.index')
                ->with('error', 'Não foi possível abrir a instituição. ('.class_basename($e).')');
        }
    }

    public function edit(Institution $instituico)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->canEdit()) abort(403);
            if (!$user->isAdmin() && $user->institution_id
                && (int)$user->institution_id !== (int)$instituico->id) {
                abort(403, 'Você não tem permissão para editar esta instituição.');
            }
            return view('institutions.edit', ['institution' => $instituico]);
        } catch (\Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                throw $e;
            }
            Log::error('Erro 500 no edit da instituicao ' . ($instituico->id ?? '?') . ': ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            if (config('app.debug')) { throw $e; }
            return redirect()->route('instituicoes.index')
                ->with('error', 'Não foi possível abrir o formulário de edição. ('.class_basename($e).')');
        }
    }

    public function update(Request $req, Institution $instituico)
    {
        $user = Auth::user();
        if (!$user || !$user->canEdit()) abort(403);
        if (!$user->isAdmin() && $user->institution_id
            && (int)$user->institution_id !== (int)$instituico->id) {
            abort(403, 'Você não tem permissão para atualizar esta instituição.');
        }

        $rules = [
            'razao_social'                => 'required|string|max:255',
            'nome_fantasia'               => 'nullable|string|max:255',
            'email'                       => 'nullable|email',
            'telefone'                    => 'nullable|string',
            'site'                        => 'nullable|string',
            'instagram'                   => 'nullable|string|max:255',
            'endereco'                    => 'nullable|string',
            'cep'                         => 'nullable|string|max:9',
            'numero'                      => 'nullable|string|max:20',
            'complemento'                 => 'nullable|string|max:100',
            'bairro'                      => 'nullable|string|max:100',
            'municipio'                   => 'nullable|string',
            'estado'                      => 'nullable|string|max:2',
            'area_atuacao'                => 'nullable|string',
            'dados_bancarios'             => 'nullable|string',
            'banco'                       => 'nullable|string|max:100',
            'agencia'                     => 'nullable|string|max:20',
            'conta_corrente'              => 'nullable|string|max:30',
            'tipo_conta'                  => 'nullable|string|max:30',
            'chave_pix'                   => 'nullable|string|max:100',
            'representante_legal'         => 'nullable|string',
            'presidente_cpf'              => 'nullable|string|max:20',
            'presidente_rg'               => 'nullable|string|max:30',
            'presidente_rg_expedicao'     => 'nullable|date',
            'presidente_nascimento'       => 'nullable|date',
            'presidente_telefone'         => 'nullable|string|max:20',
            'presidente_email'            => 'nullable|email',
            'presidente_endereco'         => 'nullable|string',
            'presidente_foto'             => 'nullable|image|max:2048',
            'historico_institucional'     => 'nullable|string',
            'descricao_estrutura_fisica'  => 'nullable|string',
            'observacoes_compliance'      => 'nullable|string',
            'utilidade_publica_municipal' => 'nullable|boolean',
            'lei_municipal_numero'        => 'nullable|string',
            'lei_municipal_data'          => 'nullable|date',
            'lei_municipal_arquivo'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'utilidade_publica_estadual'  => 'nullable|boolean',
            'lei_estadual_numero'         => 'nullable|string',
            'lei_estadual_data'           => 'nullable|date',
            'lei_estadual_arquivo'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'utilidade_publica_federal'   => 'nullable|boolean',
            'lei_federal_numero'          => 'nullable|string',
            'lei_federal_data'            => 'nullable|date',
            'lei_federal_arquivo'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'active'                      => 'nullable|boolean',
        ];

        if ($user->isAdmin()) {
            $rules['cnpj'] = 'nullable|string';
        }

        try {
            $data = $req->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->errors())->withInput();
        }

        $data['active'] = $req->boolean('active');

        if ($user->isAdmin() && !empty($data['cnpj'])) {
            $cnpjLimpo = preg_replace('/\D/', '', $data['cnpj']);
            $existe = Institution::where('cnpj', $cnpjLimpo)
                ->where('id', '!=', $instituico->id)->exists();
            if ($existe) {
                return back()->withErrors(['cnpj' => 'CNPJ já cadastrado em outra instituição.'])->withInput();
            }
            $data['cnpj'] = $cnpjLimpo;
        } else {
            unset($data['cnpj']);
        }

        $data['utilidade_publica_municipal'] = $req->boolean('utilidade_publica_municipal');
        $data['utilidade_publica_estadual']  = $req->boolean('utilidade_publica_estadual');
        $data['utilidade_publica_federal']   = $req->boolean('utilidade_publica_federal');

        if ($req->hasFile('presidente_foto')) {
            if ($instituico->presidente_foto) {
                Storage::disk('public')->delete($instituico->presidente_foto);
            }
            $data['presidente_foto'] = $req->file('presidente_foto')->store('presidente_fotos', 'public');
        } else {
            unset($data['presidente_foto']);
        }

        foreach (['lei_municipal_arquivo', 'lei_estadual_arquivo', 'lei_federal_arquivo'] as $field) {
            if ($req->hasFile($field)) {
                if ($instituico->$field) {
                    Storage::disk('public')->delete($instituico->$field);
                }
                $data[$field] = $req->file($field)->store('leis_utilidade', 'public');
            } else {
                unset($data[$field]);
            }
        }

        try {
            try {
                $existing = Schema::getColumnListing('institutions');
                if (!empty($existing)) {
                    $data = array_intersect_key($data, array_flip($existing));
                }
            } catch (\Throwable $e) {
                Log::warning('Nao foi possivel verificar colunas de institutions: ' . $e->getMessage());
            }

            $instituico->update($data);
            AuditLog::create([
                'user_id'     => Auth::id(),
                'acao'        => 'UPDATE',
                'entidade'    => 'Institution',
                'entidade_id' => $instituico->id,
            ]);
            return redirect()->route('instituicoes.show', ['instituico' => $instituico])
                ->with('success', 'Instituição atualizada!');
        } catch (\Throwable $e) {
            Log::error('Erro ao atualizar instituicao ' . $instituico->id . ': ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            if (config('app.debug')) { throw $e; }
            return back()->withInput()
                ->with('error', 'Não foi possível salvar as alterações. ('.class_basename($e).': '.$e->getMessage().')');
        }
    }

    public function destroy(Institution $instituico)
    {
        if (!Auth::user()->canEdit()) abort(403);
        $instituico->update(['active' => false]);
        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => 'DELETE',
            'entidade'    => 'Institution',
            'entidade_id' => $instituico->id,
        ]);
        return redirect()->route('instituicoes.index')->with('success', 'Instituição desativada.');
    }

    public function activate(Institution $instituico)
    {
        $instituico->update(['active' => true]);
        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => 'UPDATE',
            'entidade'    => 'Institution',
            'entidade_id' => $instituico->id,
            'dados'       => json_encode(['acao' => 'ativacao']),
        ]);
        return redirect()->route('instituicoes.index')->with('success', 'Instituição ativada com sucesso!');
    }

    public function forceDelete(Institution $instituico)
    {
        if (!Auth::user()->isAdmin()) abort(403);
        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => 'FORCE_DELETE',
            'entidade'    => 'Institution',
            'entidade_id' => $instituico->id,
        ]);
        $instituico->directors()->each(fn($d) => $d->foto ? Storage::disk('public')->delete($d->foto) : null);
        $instituico->directors()->delete();
        $instituico->projectHistories()->delete();
        $instituico->delete();
        return redirect()->route('instituicoes.index')->with('success', 'Instituição excluída permanentemente.');
    }

    public function storeDirector(Request $req, Institution $instituico)
    {
        if (!Auth::user()->canEdit()) abort(403);
        $data = $req->validate([
            'tipo'          => 'required|in:DIRETORIA,CONSELHO_FISCAL',
            'nome'          => 'required|string',
            'cpf'           => 'nullable|string',
            'cargo'         => 'nullable|string',
            'email'         => 'nullable|email',
            'telefone'      => 'nullable|string',
            'endereco'      => 'nullable|string',
            'observacoes'   => 'nullable|string',
            'mandato_inicio'=> 'nullable|date',
            'mandato_fim'   => 'nullable|date',
            'foto'          => 'nullable|image|max:2048',
        ]);
        if ($req->hasFile('foto')) {
            $data['foto'] = $req->file('foto')->store('diretores_fotos', 'public');
        }
        $data['institution_id'] = $instituico->id;
        $instituico->directors()->create($data);
        return back()->with('success', 'Membro adicionado!');
    }

    public function updateDirector(Request $req, Institution $instituico, Director $diretor)
    {
        if (!Auth::user()->canEdit()) abort(403);
        if ($diretor->institution_id !== $instituico->id) abort(403);
        $data = $req->validate([
            'nome'          => 'required|string',
            'cpf'           => 'nullable|string',
            'cargo'         => 'nullable|string',
            'email'         => 'nullable|email',
            'telefone'      => 'nullable|string',
            'endereco'      => 'nullable|string',
            'observacoes'   => 'nullable|string',
            'mandato_inicio'=> 'nullable|date',
            'mandato_fim'   => 'nullable|date',
            'foto'          => 'nullable|image|max:2048',
            'remover_foto'  => 'nullable|boolean',
        ]);
        $removerFoto = $req->boolean('remover_foto');
        unset($data['remover_foto']);

        if ($req->hasFile('foto')) {
            if ($diretor->foto) Storage::disk('public')->delete($diretor->foto);
            $data['foto'] = $req->file('foto')->store('diretores_fotos', 'public');
        } elseif ($removerFoto && $diretor->foto) {
            Storage::disk('public')->delete($diretor->foto);
            $data['foto'] = null;
        } else {
            unset($data['foto']);
        }

        $diretor->update($data);
        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => 'UPDATE',
            'entidade'    => 'Director',
            'entidade_id' => $diretor->id,
        ]);
        return back(303)->with('success', 'Membro atualizado!');
    }

    public function destroyDirector(Institution $instituico, Director $diretor)
    {
        if (!Auth::user()->canEdit()) abort(403);
        if ($diretor->institution_id !== $instituico->id) abort(403);
        if ($diretor->foto) Storage::disk('public')->delete($diretor->foto);
        $diretor->delete();
        return back()->with('success', 'Membro removido.');
    }

    public function storeProjectHistory(Request $req, Institution $instituico)
    {
        if (!Auth::user()->canEdit()) abort(403);
        $this->normalizeBrl($req, ['valor']);
        $data = $req->validate([
            'nome'                => 'required|string|max:255',
            'programa_estadual'   => 'nullable|string',
            'fonte'               => 'nullable|string',
            'valor'               => 'nullable|numeric',
            'numero_convenio'     => 'nullable|string',
            'numero_processo'     => 'nullable|string',
            'numero_proposta'     => 'nullable|string',
            'data_assinatura'     => 'nullable|date',
            'data_publicacao'     => 'nullable|date',
            'vigencia'            => 'nullable|string',
            'publicidade_parceria'=> 'nullable|string',
        ]);
        $data['institution_id'] = $instituico->id;
        $instituico->projectHistories()->create($data);
        return back()->with('success', 'Histórico adicionado!');
    }

    public function destroyProjectHistory(Institution $instituico, InstitutionProjectHistory $historia)
    {
        if (!Auth::user()->canEdit()) abort(403);
        if ($historia->institution_id !== $instituico->id) abort(403);
        $historia->delete();
        return back()->with('success', 'Registro removido.');
    }

    public function exportPdf(Institution $instituico)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->institution_id !== $instituico->id) abort(403);

        $instituico->load([
            'diretoria', 'conselhoFiscal', 'projectHistories',
            'projects' => fn($q) => $q->orderBy('nome'),
            'documents',
        ]);
        $pdf = Pdf::loadView('pdf.institution', ['institution' => $instituico])
            ->setPaper('a4', 'portrait');
        return $pdf->stream('relatorio-institucional-' . $instituico->id . '.pdf');
    }
}
