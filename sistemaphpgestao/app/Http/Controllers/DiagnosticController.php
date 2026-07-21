<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Auth, DB, Schema, Storage, Artisan, File};
use Illuminate\Http\Request;

/**
 * Endpoint de diagnóstico administrativo. Mostra estado de:
 *  - Conexão de banco
 *  - Colunas esperadas vs presentes na tabela `institutions`
 *  - Migrations pendentes
 *  - Status do symlink `public/storage`
 *  - Permissões de pastas críticas
 *  - Últimas linhas do log do Laravel
 *
 * Acesso restrito a ADMIN_GERAL.
 */
class DiagnosticController extends Controller
{
    public function index(Request $req)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) abort(403);

        $expectedCols = [
            'razao_social','nome_fantasia','cnpj','email','telefone','site','instagram',
            'endereco','cep','numero','complemento','bairro','municipio','estado','area_atuacao',
            'dados_bancarios','banco','agencia','conta_corrente','tipo_conta','chave_pix',
            'representante_legal','presidente_cpf','presidente_rg','presidente_rg_expedicao',
            'presidente_nascimento','presidente_telefone','presidente_email',
            'presidente_endereco','presidente_foto',
            'historico_institucional','descricao_estrutura_fisica','observacoes_compliance',
            'utilidade_publica_municipal','lei_municipal_numero','lei_municipal_data','lei_municipal_arquivo',
            'utilidade_publica_estadual','lei_estadual_numero','lei_estadual_data','lei_estadual_arquivo',
            'utilidade_publica_federal','lei_federal_numero','lei_federal_data','lei_federal_arquivo',
            'active','created_at','updated_at',
        ];

        // DB check
        $dbOk = false; $dbErr = null; $dbVersion = null;
        try {
            $dbVersion = DB::select('select version() as v')[0]->v ?? 'desconhecida';
            $dbOk = true;
        } catch (\Throwable $e) { $dbErr = $e->getMessage(); }

        // Colunas
        $presentCols = [];
        try { $presentCols = Schema::getColumnListing('institutions'); } catch (\Throwable $e) {}
        $missingCols = array_values(array_diff($expectedCols, $presentCols));
        $extraCols   = array_values(array_diff($presentCols, $expectedCols));

        // Migrations
        $migrationsRun = 0; $migrationsPending = [];
        try {
            $migrationsRun = DB::table('migrations')->count();
            $allFiles = collect(File::files(database_path('migrations')))
                ->map(fn($f) => pathinfo($f->getFilename(), PATHINFO_FILENAME))
                ->values()->all();
            $ran = DB::table('migrations')->pluck('migration')->all();
            $migrationsPending = array_values(array_diff($allFiles, $ran));
        } catch (\Throwable $e) {}

        // Storage symlink
        $publicStorage = public_path('storage');
        $symlinkOk = is_link($publicStorage) || is_dir($publicStorage);

        // Permissões
        $perms = [];
        foreach ([storage_path(), storage_path('app'), storage_path('logs'), base_path('bootstrap/cache')] as $p) {
            $perms[] = ['path'=>$p, 'exists'=>file_exists($p), 'writable'=>is_writable($p)];
        }

        // Últimas linhas do log
        $logFile = storage_path('logs/laravel.log');
        $logTail = '';
        if (file_exists($logFile) && is_readable($logFile)) {
            $size = filesize($logFile);
            $read = min($size, 12000);
            $logTail = file_get_contents($logFile, false, null, max(0, $size - $read), $read);
        }

        // Tabelas relacionadas críticas
        $relatedTables = [];
        foreach (['institutions','directors','funding_sources','projects','goals','expenses','documents','institution_project_histories','audit_logs','users'] as $t) {
            try {
                $relatedTables[$t] = [
                    'exists' => Schema::hasTable($t),
                    'count'  => Schema::hasTable($t) ? DB::table($t)->count() : null,
                ];
            } catch (\Throwable $e) {
                $relatedTables[$t] = ['exists'=>false, 'count'=>null, 'error'=>$e->getMessage()];
            }
        }

        return view('diagnostic.index', compact(
            'dbOk','dbErr','dbVersion','presentCols','missingCols','extraCols',
            'migrationsRun','migrationsPending','symlinkOk','publicStorage',
            'perms','logTail','relatedTables'
        ));
    }

    /**
     * Roda migrations pendentes (somente admin). Útil quando deploy não rodou artisan migrate.
     */
    public function migrate(Request $req)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) abort(403);

        $output = '';
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force'=>true]);
            $output = \Illuminate\Support\Facades\Artisan::output();
        } catch (\Throwable $e) {
            $output = 'ERRO: '.$e->getMessage();
        }
        return back()->with('diag_output', $output);
    }

    /**
     * Cria o symlink public/storage -> storage/app/public.
     */
    public function storageLink(Request $req)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) abort(403);

        $output = '';
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            $output = \Illuminate\Support\Facades\Artisan::output();
        } catch (\Throwable $e) {
            $output = 'ERRO: '.$e->getMessage();
        }
        return back()->with('diag_output', $output);
    }

    /**
     * Limpa todos os caches do framework.
     */
    public function clearCaches(Request $req)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) abort(403);

        $out = [];
        foreach (['config:clear','route:clear','view:clear','cache:clear'] as $cmd) {
            try { \Illuminate\Support\Facades\Artisan::call($cmd); $out[] = $cmd.': OK'; }
            catch (\Throwable $e) { $out[] = $cmd.': ERRO '.$e->getMessage(); }
        }
        return back()->with('diag_output', implode("\n", $out));
    }
}
