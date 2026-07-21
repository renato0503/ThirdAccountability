<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalProof extends Model {
    protected $fillable = [
        'goal_id','fotos','descricao','link_video','anexo_path','anexo_nome',
    ];

    protected function casts(): array {
        return ['fotos' => 'array'];
    }

    public function goal() { return $this->belongsTo(Goal::class); }

    public function isComplete(): bool {
        $fotos = $this->fotos ?? [];
        return count($fotos) >= 5 && !empty(trim((string)$this->descricao));
    }
}
