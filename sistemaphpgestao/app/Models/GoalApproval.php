<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalApproval extends Model {
    protected $fillable = [
        'goal_id','avaliador_numero','avaliador_nome','user_id',
        'aprovado','observacoes','aprovado_em',
    ];

    protected function casts(): array {
        return [
            'aprovado'    => 'boolean',
            'aprovado_em' => 'datetime',
        ];
    }

    public function goal() { return $this->belongsTo(Goal::class); }
    public function user() { return $this->belongsTo(User::class); }
}
