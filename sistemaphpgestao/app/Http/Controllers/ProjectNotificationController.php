<?php
namespace App\Http\Controllers;

use App\Models\{Project, ProjectNotification, AuditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};

class ProjectNotificationController extends Controller
{
    public function store(Request $req, Project $projeto)
    {
        $user = Auth::user();
        if (!$user || !$user->canEdit()) abort(403);
        if (!$user->isAdmin() && $user->institution_id && (int)$user->institution_id !== (int)$projeto->institution_id) {
            abort(403);
        }
        try {
            $data = $req->validate([
                'titulo'            => 'required|string|max:255',
                'data_notificacao'  => 'required|date',
                'email'             => 'nullable|email|max:150',
                'telefone'          => 'nullable|string|max:30',
                'observacao'        => 'nullable|string',
            ]);
            $data['project_id'] = $projeto->id;
            $data['created_by'] = $user->id;
            $data['status']     = 'REGISTRADA';
            $n = ProjectNotification::create($data);
            AuditLog::create([
                'user_id'     => $user->id,
                'acao'        => 'CREATE',
                'entidade'    => 'ProjectNotification',
                'entidade_id' => $n->id,
            ]);
            return back()->with('success', 'Notificação registrada!')
                ->withFragment('tab-notificacoes');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->errors())->withInput()->withFragment('tab-notificacoes');
        } catch (\Throwable $e) {
            Log::error('Erro ao criar notificação: '.$e->getMessage());
            return back()->with('error', 'Não foi possível registrar a notificação.')->withInput();
        }
    }

    public function update(Request $req, Project $projeto, ProjectNotification $notificacao)
    {
        $user = Auth::user();
        if (!$user || !$user->canEdit()) abort(403);
        if ((int)$notificacao->project_id !== (int)$projeto->id) abort(403);
        if (!$user->isAdmin() && $user->institution_id && (int)$user->institution_id !== (int)$projeto->institution_id) {
            abort(403);
        }
        try {
            $data = $req->validate([
                'titulo'            => 'required|string|max:255',
                'data_notificacao'  => 'required|date',
                'email'             => 'nullable|email|max:150',
                'telefone'          => 'nullable|string|max:30',
                'observacao'        => 'nullable|string',
                'status'            => 'nullable|in:REGISTRADA,ENVIADA,RESPONDIDA,ARQUIVADA',
            ]);
            $notificacao->update($data);
            AuditLog::create([
                'user_id'     => $user->id,
                'acao'        => 'UPDATE',
                'entidade'    => 'ProjectNotification',
                'entidade_id' => $notificacao->id,
            ]);
            return back()->with('success', 'Notificação atualizada!')->withFragment('tab-notificacoes');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->errors())->withInput()->withFragment('tab-notificacoes');
        } catch (\Throwable $e) {
            Log::error('Erro ao atualizar notificação: '.$e->getMessage());
            return back()->with('error', 'Não foi possível atualizar.')->withInput();
        }
    }

    public function destroy(Project $projeto, ProjectNotification $notificacao)
    {
        $user = Auth::user();
        if (!$user || !$user->canEdit()) abort(403);
        if ((int)$notificacao->project_id !== (int)$projeto->id) abort(403);
        if (!$user->isAdmin() && $user->institution_id && (int)$user->institution_id !== (int)$projeto->institution_id) {
            abort(403);
        }
        $notificacao->delete();
        AuditLog::create([
            'user_id'     => $user->id,
            'acao'        => 'DELETE',
            'entidade'    => 'ProjectNotification',
            'entidade_id' => $notificacao->id,
        ]);
        return back()->with('success', 'Notificação removida.')->withFragment('tab-notificacoes');
    }
}
