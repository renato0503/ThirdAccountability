<?php
namespace App\Http\Controllers;

use App\Models\{Document, Project, Institution, AuditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage};

class DocumentController extends Controller
{
    public function index(Request $req)
    {
        $user = Auth::user();
        $q = Document::with(['project', 'institution', 'uploader'])->orderBy('created_at', 'desc');

        if (!$user->isAdmin() && $user->institution_id) {
            $q->where(fn($x) => $x
                ->where('institution_id', $user->institution_id)
                ->orWhereHas('project', fn($p) => $p->where('institution_id', $user->institution_id))
            );
        }

        if ($c = $req->categoria) $q->where('categoria', $c);

        $documents  = $q->paginate(20)->withQueryString();
        $categorias = ['Certidão', 'Contrato', 'Relatório', 'NF/Recibo', 'Comprovante', 'Outro'];

        return view('documents.index', compact('documents', 'categorias'));
    }

    public function create()
    {
        $user         = Auth::user();
        $projects     = $user->isAdmin()
            ? Project::orderBy('nome')->get()
            : Project::where('institution_id', $user->institution_id)->orderBy('nome')->get();
        $institutions = $user->isAdmin()
            ? Institution::where('active', true)->orderBy('razao_social')->get()
            : Institution::where('id', $user->institution_id)->get();
        $categorias   = ['Certidão', 'Contrato', 'Relatório', 'NF/Recibo', 'Comprovante', 'Outro'];

        return view('documents.create', compact('projects', 'institutions', 'categorias'));
    }

    public function store(Request $req)
    {
        $logFile = storage_path('logs/document_store_debug.log');
        $log = function($msg) use ($logFile) {
            @file_put_contents($logFile, '['.date('Y-m-d H:i:s').'] '.$msg."\n", FILE_APPEND);
        };
        $log('=== STORE INICIADO ===');
        $log('user_id='.Auth::id().' email='.optional(Auth::user())->email);
        $log('all_input='.json_encode($req->except('arquivo'), JSON_UNESCAPED_UNICODE));
        $log('hasFile=' . ($req->hasFile('arquivo') ? 'sim' : 'nao'));
        if ($req->hasFile('arquivo')) {
            $f = $req->file('arquivo');
            $log('file: name='.$f->getClientOriginalName().' size='.$f->getSize().' mime='.$f->getMimeType().' valid='.($f->isValid()?'sim':'nao').' err='.$f->getError());
        }

        try {
            $data = $req->validate([
                'nome'           => 'required|string|max:255',
                'categoria'      => 'nullable|string',
                'validade'       => 'nullable|date',
                'institution_id' => 'nullable|exists:institutions,id',
                'project_id'     => 'nullable|exists:projects,id',
                'arquivo'        => 'nullable|file|max:20480',
            ]);
            $log('validacao OK');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $log('VALIDACAO FALHOU: '.json_encode($ve->errors(), JSON_UNESCAPED_UNICODE));
            throw $ve;
        }

        if ($req->hasFile('arquivo')) {
            try {
                $file              = $req->file('arquivo');
                $path              = $file->store('documentos', 'public');
                $log('file salvo em: '.$path);
                $data['file_path'] = $path;
                $data['url']       = Storage::url($path);
                $data['tamanho']   = $this->formatarTamanho($file->getSize());
                $data['tipo']      = strtoupper($file->getClientOriginalExtension());
                $data['mime_type'] = $file->getMimeType();
            } catch (\Throwable $e) {
                $log('ERRO no upload do arquivo: '.$e->getMessage().' em '.$e->getFile().':'.$e->getLine());
                throw $e;
            }
        }

        unset($data['arquivo']);
        $data['uploaded_by']    = Auth::id();
        $data['status_analise'] = 'PENDENTE';

        try {
            $doc = Document::create($data);
            $log('Document criado #'.$doc->id);
        } catch (\Throwable $e) {
            $log('ERRO no Document::create: '.$e->getMessage().' em '.$e->getFile().':'.$e->getLine());
            throw $e;
        }

        try {
            AuditLog::create([
                'user_id'    => Auth::id(),
                'acao'       => 'CREATE',
                'entidade'   => 'Document',
                'entidade_id'=> $doc->id,
                'dados'      => json_encode(['nome' => $doc->nome]),
                'ip'         => request()->ip(),
            ]);
            $log('AuditLog criado');
        } catch (\Throwable $e) {
            $log('ERRO no AuditLog::create: '.$e->getMessage().' em '.$e->getFile().':'.$e->getLine());
            // Não relançar — não queremos perder o documento por causa do log
        }

        $log('=== STORE FINALIZADO COM SUCESSO ===');
        return redirect()->route('documentos.index')->with('success', 'Documento cadastrado!');
    }

    public function show(Document $documento)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->institution_id) {
            $institutionMatch = $documento->institution_id === $user->institution_id
                || ($documento->project && $documento->project->institution_id === $user->institution_id);
            abort_unless($institutionMatch, 403);
        }
        $documento->load(['project', 'institution', 'uploader']);
        return view('documents.show', compact('documento'));
    }

    public function edit(Document $documento)
    {
        $user         = Auth::user();
        $projects     = $user->isAdmin()
            ? Project::orderBy('nome')->get()
            : Project::where('institution_id', $user->institution_id)->orderBy('nome')->get();
        $institutions = $user->isAdmin()
            ? Institution::where('active', true)->orderBy('razao_social')->get()
            : Institution::where('id', $user->institution_id)->get();
        $categorias   = ['Certidão', 'Contrato', 'Relatório', 'NF/Recibo', 'Comprovante', 'Outro'];

        return view('documents.edit', compact('documento', 'projects', 'institutions', 'categorias'));
    }

    public function update(Request $req, Document $documento)
    {
        $data = $req->validate([
            'nome'           => 'required|string|max:255',
            'categoria'      => 'nullable|string',
            'validade'       => 'nullable|date',
            'institution_id' => 'nullable|exists:institutions,id',
            'project_id'     => 'nullable|exists:projects,id',
            'arquivo'        => 'nullable|file|max:20480',
        ]);

        if ($req->hasFile('arquivo')) {
            if ($documento->file_path) {
                Storage::disk('public')->delete($documento->file_path);
            }
            $file              = $req->file('arquivo');
            $path              = $file->store('documentos', 'public');
            $data['file_path'] = $path;
            $data['url']       = Storage::url($path);
            $data['tamanho']   = $this->formatarTamanho($file->getSize());
            $data['tipo']      = strtoupper($file->getClientOriginalExtension());
            $data['mime_type'] = $file->getMimeType();
        }

        unset($data['arquivo']);
        $documento->update($data);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'acao'       => 'UPDATE',
            'entidade'   => 'Document',
            'entidade_id'=> $documento->id,
            'ip'         => request()->ip(),
        ]);

        return redirect()->route('documentos.index')->with('success', 'Documento atualizado!');
    }

    public function destroy(Document $documento)
    {
        if ($documento->file_path) {
            Storage::disk('public')->delete($documento->file_path);
        }
        $documento->delete();

        AuditLog::create([
            'user_id'    => Auth::id(),
            'acao'       => 'DELETE',
            'entidade'   => 'Document',
            'entidade_id'=> $documento->id,
            'ip'         => request()->ip(),
        ]);

        return back()->with('success', 'Documento excluído.');
    }

    private function formatarTamanho(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
