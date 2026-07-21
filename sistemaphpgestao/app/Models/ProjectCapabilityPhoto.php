<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCapabilityPhoto extends Model {
    protected $table = 'project_capability_photos';
    protected $fillable = ['project_id','file_path','legenda','ordem'];
    protected function casts(): array {
        return ['ordem' => 'integer'];
    }
    public function project() { return $this->belongsTo(Project::class); }
}
