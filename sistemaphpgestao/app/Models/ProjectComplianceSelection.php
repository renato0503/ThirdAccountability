<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectComplianceSelection extends Model {
    protected $fillable = [
        'project_id',
        'inc_informacoes','inc_despesas','inc_metas',
        'inc_comprovacao','inc_diligencias','inc_prestacao_contas',
    ];

    protected function casts(): array {
        return [
            'inc_informacoes'      => 'boolean',
            'inc_despesas'         => 'boolean',
            'inc_metas'            => 'boolean',
            'inc_comprovacao'      => 'boolean',
            'inc_diligencias'      => 'boolean',
            'inc_prestacao_contas' => 'boolean',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
}
