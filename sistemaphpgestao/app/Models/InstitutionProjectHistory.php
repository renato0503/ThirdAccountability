<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InstitutionProjectHistory extends Model {
    protected $table = 'institution_project_histories';
    protected $fillable = [
        'institution_id','nome','programa_estadual','fonte','valor',
        'numero_convenio','numero_processo','numero_proposta',
        'data_assinatura','data_publicacao','vigencia','publicidade_parceria',
    ];
    protected function casts(): array {
        return ['data_assinatura'=>'date','data_publicacao'=>'date','valor'=>'float'];
    }
    public function institution() { return $this->belongsTo(Institution::class); }
}
