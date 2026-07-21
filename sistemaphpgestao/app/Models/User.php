<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasFactory, Notifiable;

    public const ROLES = [
        'ADMIN_GERAL'        => 'Acesso total ao sistema',
        'ADMIN_INSTITUICAO'  => 'Gerencia uma instituição',
        'GESTOR_PROJETO'     => 'Gerencia projetos',
        'FISCAL_PROJETO'     => 'Diligências (somente visualização e aprovação)',
        'CONSELHO_FISCAL_1'  => 'Diligências, Prestação de Contas e Relatório (visualização e aprovação das diligências)',
        'CONSELHO_FISCAL_2'  => 'Diligências, Prestação de Contas e Relatório (visualização e aprovação das diligências)',
        'CONSELHO_FISCAL_3'  => 'Diligências, Prestação de Contas e Relatório (visualização e aprovação das diligências)',
        'FISCAL_EXTERNO'     => 'Apenas visualização',
    ];

    protected $fillable = ['name','email','password','role','institution_id','active'];
    protected $hidden   = ['password','remember_token'];
    protected function casts(): array {
        return ['email_verified_at'=>'datetime','password'=>'hashed','active'=>'boolean'];
    }

    public function institution() { return $this->belongsTo(Institution::class); }
    public function auditLogs()   { return $this->hasMany(AuditLog::class); }

    public function isAdmin()         { return $this->role === 'ADMIN_GERAL'; }
    public function isInstAdmin()     { return $this->role === 'ADMIN_INSTITUICAO'; }
    public function isGestorProjeto() { return $this->role === 'GESTOR_PROJETO'; }
    public function isFiscalProjeto() { return $this->role === 'FISCAL_PROJETO'; }
    public function isConselhoFiscal(){ return in_array($this->role, ['CONSELHO_FISCAL_1','CONSELHO_FISCAL_2','CONSELHO_FISCAL_3'], true); }
    public function isFiscalExterno() { return $this->role === 'FISCAL_EXTERNO'; }

    public function canEdit(): bool
    {
        return in_array($this->role, ['ADMIN_GERAL','ADMIN_INSTITUICAO','GESTOR_PROJETO'], true);
    }

    public function canApprove(): bool
    {
        return in_array($this->role, ['ADMIN_GERAL','ADMIN_INSTITUICAO','FISCAL_PROJETO','CONSELHO_FISCAL_1','CONSELHO_FISCAL_2','CONSELHO_FISCAL_3'], true);
    }
}
