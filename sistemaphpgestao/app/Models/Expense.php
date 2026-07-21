<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model {
    protected $fillable = [
        'project_id','goal_id','categoria','fornecedor','cnpj_fornecedor',
        'descricao','data_despesa','data_pagamento','valor',
        'forma_pagamento','numero_nf','status'
    ];
    protected function casts(): array {
        return ['data_despesa'=>'date','data_pagamento'=>'date','valor'=>'float'];
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function goal()    { return $this->belongsTo(Goal::class); }

    public function getStatusLabelAttribute(): string {
        return match($this->status) {
            'PENDENTE'  => 'Pendente',
            'APROVADO'  => 'Aprovado',
            'PAGO'      => 'Pago',
            'REJEITADO' => 'Rejeitado',
            default     => $this->status,
        };
    }
    public function getStatusColorAttribute(): string {
        return match($this->status) {
            'PAGO'      => 'green',
            'APROVADO'  => 'blue',
            'REJEITADO' => 'red',
            default     => 'yellow',
        };
    }
}
