<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectContractedService extends Model {
    protected $table = 'project_contracted_services';
    protected $fillable = [
        'project_id','tipo_contratacao','descricao',
        'periodo_execucao','unidade_periodo','tipo_pagamento','valor','ordem',
    ];
    protected function casts(): array {
        return [
            'periodo_execucao' => 'integer',
            'valor' => 'float',
            'ordem' => 'integer',
        ];
    }
    public function project() { return $this->belongsTo(Project::class); }

    public function getTipoContratacaoLabelAttribute(): string {
        return match($this->tipo_contratacao) {
            'PF' => 'Pessoa Física',
            'PJ' => 'Pessoa Jurídica',
            default => $this->tipo_contratacao,
        };
    }

    public function getTipoPagamentoLabelAttribute(): string {
        return match($this->tipo_pagamento) {
            'mensal' => 'Mensal',
            'unico' => 'Pagamento Único',
            default => $this->tipo_pagamento,
        };
    }

    public function getUnidadePeriodoLabelAttribute(): string {
        return match($this->unidade_periodo) {
            'dia' => 'dia(s)',
            'semana' => 'semana(s)',
            'mes' => 'mês(es)',
            'ano' => 'ano(s)',
            default => $this->unidade_periodo,
        };
    }
}
