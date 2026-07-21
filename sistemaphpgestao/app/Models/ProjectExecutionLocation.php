<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProjectExecutionLocation extends Model {
    protected $table = 'project_execution_locations';
    protected $fillable = ['project_id','cidade','estado'];
    public function project() { return $this->belongsTo(Project::class); }
}
