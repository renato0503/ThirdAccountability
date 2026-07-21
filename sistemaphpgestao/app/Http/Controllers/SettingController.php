<?php
namespace App\Http\Controllers;

use App\Models\{Setting, AuditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Crypt};

class SettingController extends Controller {
    public function index() {
        if (Auth::user()->role !== 'ADMIN_GERAL') abort(403);

        $settings = Setting::all()->keyBy('key');

        $grupos = [
            'email' => ['label' => 'E-mail (SMTP)', 'icon' => 'bi-envelope'],
            'fase2' => ['label' => 'Integrações Fase 2', 'icon' => 'bi-plug'],
            'fase3' => ['label' => 'Integrações Fase 3', 'icon' => 'bi-cpu'],
        ];

        return view('settings.index', compact('settings', 'grupos'));
    }

    public function update(Request $req) {
        if (Auth::user()->role !== 'ADMIN_GERAL') abort(403);

        $allSettings = Setting::all();

        foreach ($allSettings as $setting) {
            $value = $req->input($setting->key);

            if ($setting->is_secret && ($value === null || $value === '')) {
                continue;
            }

            if ($setting->is_secret && $value !== null && $value !== '') {
                $value = Crypt::encryptString($value);
                $setting->update(['value' => $value]);
            } else {
                $setting->update(['value' => $value ?? '']);
            }
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'acao'       => 'UPDATE',
            'entidade'   => 'Settings',
            'entidade_id'=> 0,
            'dados'      => json_encode(['updated' => 'system settings']),
            'ip'         => request()->ip(),
        ]);

        return redirect()->route('configuracoes.index')->with('success', 'Configurações salvas com sucesso!');
    }

    public function testEmail(Request $req) {
        if (Auth::user()->role !== 'ADMIN_GERAL') abort(403);

        $req->validate(['email_teste' => 'required|email']);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                'Teste de e-mail do sistema Gestão Terceiro. Se você recebeu esta mensagem, o servidor SMTP está configurado corretamente.',
                function ($m) use ($req) {
                    $m->to($req->email_teste)->subject('Teste de E-mail — Gestão Terceiro');
                }
            );
            return back()->with('success', 'E-mail de teste enviado para ' . $req->email_teste);
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao enviar: ' . $e->getMessage());
        }
    }
}
