<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProjectSpecificObjective extends Model {
    protected $table = 'project_specific_objectives';
    protected $fillable = ['project_id','objetivo','ordem'];
    public function project() { return $this->belongsTo(Project::class); }
}
