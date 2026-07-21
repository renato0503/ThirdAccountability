<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diligence extends Model {
    protected $fillable = ['project_id','goal_id','tipo','descricao','responsavel','prazo','status','resposta'];
    protected function casts(): array { return ['prazo'=>'date']; }

    public function project() { return $this->belongsTo(Project::class); }
    public function goal()    { return $this->belongsTo(Goal::class); }

    public function getStatusLabelAttribute(): string {
        return match($this->status) {
            'ABERTA'    => 'Aberta',
            'RESPONDIDA'=> 'Respondida',
            'ENCERRADA' => 'Encerrada',
            default     => $this->status,
        };
    }
}
