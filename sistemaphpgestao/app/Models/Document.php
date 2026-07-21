<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model {
    protected $fillable = ['project_id','institution_id','goal_id','uploaded_by','nome','tipo','categoria','url','file_path','mime_type','tamanho','validade','status_analise'];
    protected function casts(): array { return ['validade'=>'date']; }

    public function project()     { return $this->belongsTo(Project::class); }
    public function institution() { return $this->belongsTo(Institution::class); }
    public function uploader()    { return $this->belongsTo(User::class,'uploaded_by'); }
}
