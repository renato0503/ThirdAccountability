<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AccountingReport extends Model {
    protected $fillable = [
        'project_id','status','observacoes',
        'relatorio_texto','links_videos','fotos',
        'data_envio','data_aprovacao',
    ];

    protected function casts(): array {
        return [
            'data_envio'     => 'date',
            'data_aprovacao' => 'date',
            'fotos'          => 'array',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }

    public function getStatusLabelAttribute(): string {
        return match($this->status) {
            'RASCUNHO'  => 'Rascunho',
            'ENVIADA'   => 'Enviada',
            'EM_ANALISE'=> 'Em Análise',
            'APROVADA'  => 'Aprovada',
            'REPROVADA' => 'Reprovada',
            default     => $this->status,
        };
    }
}
