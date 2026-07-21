<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Director extends Model {
    protected $fillable = [
        'institution_id','tipo','nome','cpf','cargo','email','telefone',
        'endereco','foto','observacoes','mandato_inicio','mandato_fim',
    ];
    protected function casts(): array {
        return ['mandato_inicio'=>'date','mandato_fim'=>'date'];
    }
    public function institution() { return $this->belongsTo(Institution::class); }
}
