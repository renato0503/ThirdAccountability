<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTeamMember extends Model {
    protected $table = 'project_team_members';
    protected $fillable = ['project_id','funcao','quantidade','descricao','ordem'];
    protected function casts(): array {
        return ['quantidade' => 'integer', 'ordem' => 'integer'];
    }
    public function project() { return $this->belongsTo(Project::class); }
}
