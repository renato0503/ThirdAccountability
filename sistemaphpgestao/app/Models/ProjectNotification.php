<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectNotification extends Model
{
    protected $fillable = [
        'project_id','titulo','data_notificacao','email','telefone','observacao','status','created_by',
    ];
    protected function casts(): array {
        return ['data_notificacao' => 'date'];
    }
    public function project() { return $this->belongsTo(Project::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
