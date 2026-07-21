<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FundingSource extends Model {
    protected $fillable = ['institution_id','nome','tipo','cnpj','responsavel','valor_aprovado','instrumento','numero','orgao_concedente','data_inicio','data_fim','status'];
    protected function casts(): array { return ['data_inicio'=>'date','data_fim'=>'date','valor_aprovado'=>'float']; }
    public function institution() { return $this->belongsTo(Institution::class); }
    public function projects()    { return $this->hasMany(Project::class); }
}
