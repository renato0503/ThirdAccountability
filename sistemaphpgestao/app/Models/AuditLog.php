<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model {
    protected $fillable = ['user_id','acao','entidade','entidade_id','dados','ip'];
    public function user() { return $this->belongsTo(User::class); }
}
