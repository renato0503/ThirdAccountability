<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model {
    protected $fillable = [
        'project_id','numero','tipo_meta','titulo','descricao','indicador','afericao_meta',
        'quantidade_prevista','quantidade_realizada','unidade_medida',
        'valor_previsto','prazo','data_inicio',
        'responsavel','telefone_responsavel','email_responsavel','status',
    ];
    protected function casts(): array {
        return ['prazo'=>'date','data_inicio'=>'date','valor_previsto'=>'float'];
    }

    public function project()    { return $this->belongsTo(Project::class); }
    public function activities() { return $this->hasMany(Activity::class); }
    public function expenses()   { return $this->hasMany(Expense::class); }
    public function documents()  { return $this->hasMany(Document::class); }
    public function diligences() { return $this->hasMany(Diligence::class); }
    public function proof()      { return $this->hasOne(GoalProof::class); }
    public function approvals()  { return $this->hasMany(GoalApproval::class)->orderBy('avaliador_numero'); }

    public const TOTAL_AVALIADORES = 5;

    public function approvedCount(): int {
        return $this->approvals->where('aprovado', true)->count();
    }

    public function approvalPercent(): int {
        $count = $this->approvedCount();
        return (int) round(($count / self::TOTAL_AVALIADORES) * 100);
    }

    public function fullyApproved(): bool {
        return $this->approvedCount() >= self::TOTAL_AVALIADORES;
    }

    public function complianceStatus(): array {
        $proof = $this->proof;
        $hasProof = $proof && $proof->isComplete();
        $approved = $this->approvedCount();

        if ($this->fullyApproved()) {
            return ['label' => 'Aprovada', 'icon' => 'check-circle-fill', 'color' => 'success', 'percent' => 100];
        }
        if (!$hasProof) {
            return ['label' => 'Aguardando Comprovação', 'icon' => 'hourglass-split', 'color' => 'warning', 'percent' => 0];
        }
        $percent = $this->approvalPercent();
        return ['label' => 'Aguardando Diligência', 'icon' => 'search', 'color' => 'info', 'percent' => $percent];
    }

    public function getStatusLabelAttribute(): string {
        return match($this->status) {
            'PENDENTE'              => 'Pendente',
            'EM_ANDAMENTO'          => 'Em Andamento',
            'ENVIADA_ANALISE'       => 'Enviada p/ Análise',
            'APROVADA'              => 'Aprovada',
            'REPROVADA'             => 'Reprovada',
            'APROVADA_COM_RESSALVA' => 'Aprovada c/ Ressalva',
            'CONCLUIDA'             => 'Concluída',
            'CANCELADA'             => 'Cancelada',
            default                 => $this->status,
        };
    }

    public function getStatusColorAttribute(): string {
        return match($this->status) {
            'PENDENTE'              => 'warning',
            'EM_ANDAMENTO'          => 'info',
            'ENVIADA_ANALISE'       => 'primary',
            'APROVADA'              => 'success',
            'REPROVADA'             => 'danger',
            'APROVADA_COM_RESSALVA' => 'warning',
            'CONCLUIDA'             => 'success',
            'CANCELADA'             => 'secondary',
            default                 => 'secondary',
        };
    }
}
