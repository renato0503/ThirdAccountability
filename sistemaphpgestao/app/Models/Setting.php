<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model {
    protected $primaryKey = 'key';
    public $incrementing   = false;
    protected $keyType     = 'string';

    protected $fillable = ['key','value','group','label','description','is_secret'];

    protected $casts = ['is_secret' => 'boolean'];

    public static function get(string $key, mixed $default = null): mixed {
        $setting = static::find($key);
        if (!$setting) return $default;
        $value = $setting->value;
        if ($setting->is_secret && $value) {
            try { $value = Crypt::decryptString($value); } catch (\Throwable) { }
        }
        return $value ?? $default;
    }

    public static function set(string $key, mixed $value): void {
        $setting = static::find($key);
        if (!$setting) return;
        if ($setting->is_secret && $value !== null && $value !== '') {
            $value = Crypt::encryptString((string) $value);
        }
        $setting->update(['value' => $value]);
        Cache::forget("setting_{$key}");
    }

    public static function defaults(): array {
        return [
            // E-mail
            ['key'=>'mail_mailer',     'group'=>'email',  'label'=>'Driver de E-mail',          'description'=>'smtp, sendmail ou log (para testes)',    'is_secret'=>false, 'value'=>'log'],
            ['key'=>'mail_host',       'group'=>'email',  'label'=>'Servidor SMTP',              'description'=>'ex: smtp.gmail.com',                     'is_secret'=>false, 'value'=>''],
            ['key'=>'mail_port',       'group'=>'email',  'label'=>'Porta SMTP',                 'description'=>'587 (TLS) ou 465 (SSL)',                  'is_secret'=>false, 'value'=>'587'],
            ['key'=>'mail_username',   'group'=>'email',  'label'=>'Usuário SMTP',               'description'=>'Endereço de e-mail de envio',             'is_secret'=>false, 'value'=>''],
            ['key'=>'mail_password',   'group'=>'email',  'label'=>'Senha SMTP',                 'description'=>'Senha ou token de app',                   'is_secret'=>true,  'value'=>''],
            ['key'=>'mail_from',       'group'=>'email',  'label'=>'E-mail remetente (From)',    'description'=>'ex: noreply@suaong.org.br',               'is_secret'=>false, 'value'=>''],
            ['key'=>'mail_from_name',  'group'=>'email',  'label'=>'Nome remetente',             'description'=>'Nome exibido no e-mail',                  'is_secret'=>false, 'value'=>'Gestão Terceiro'],
            // Fase 2
            ['key'=>'asaas_token',     'group'=>'fase2',  'label'=>'Asaas — Token da API',       'description'=>'Token de produção ou sandbox',            'is_secret'=>true,  'value'=>''],
            ['key'=>'asaas_env',       'group'=>'fase2',  'label'=>'Asaas — Ambiente',           'description'=>'sandbox ou production',                   'is_secret'=>false, 'value'=>'sandbox'],
            ['key'=>'zapi_instance',   'group'=>'fase2',  'label'=>'Z-API — ID da Instância',    'description'=>'Encontrado no painel Z-API',              'is_secret'=>false, 'value'=>''],
            ['key'=>'zapi_token',      'group'=>'fase2',  'label'=>'Z-API — Token',              'description'=>'Token de autenticação',                   'is_secret'=>true,  'value'=>''],
            ['key'=>'d4sign_token',    'group'=>'fase2',  'label'=>'D4Sign — Token',             'description'=>'Token da API de assinatura eletrônica',   'is_secret'=>true,  'value'=>''],
            // Fase 3
            ['key'=>'google_maps_key', 'group'=>'fase3',  'label'=>'Google Maps — Chave',        'description'=>'API Key do Google Cloud Console',         'is_secret'=>true,  'value'=>''],
            ['key'=>'pluggy_client_id','group'=>'fase3',  'label'=>'Pluggy — Client ID',         'description'=>'ID do app Open Finance',                  'is_secret'=>false, 'value'=>''],
            ['key'=>'pluggy_secret',   'group'=>'fase3',  'label'=>'Pluggy — Client Secret',     'description'=>'Segredo do app Open Finance',             'is_secret'=>true,  'value'=>''],
        ];
    }
}
