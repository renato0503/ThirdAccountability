<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Activity extends Model {
    protected $fillable = ['goal_id','nome','descricao','data_inicio','data_fim','responsavel','percentual_execucao','status'];
    protected function casts(): array { return ['data_inicio'=>'date','data_fim'=>'date']; }
    public function goal() { return $this->belongsTo(Goal::class); }
}
