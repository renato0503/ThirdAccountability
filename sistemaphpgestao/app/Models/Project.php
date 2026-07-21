<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model {
    protected $fillable = [
        'institution_id','funding_source_id','nome','codigo','numero_proposta',
        'fonte','parlamentar','secretaria','secretaria_outro',
        'descricao','objetivo_geral','objetivos_especificos',
        'publico_alvo','justificativa','metodologia',
        'capacidade_tecnica','municipios_alcancados','quantidade_publico',
        'data_local_horario','descricao_servico',
        'funcao_osc','recolhimento_impostos',
        'riscos_identificados','plano_mitigacao',
        'resultados_esperados','plano_divulgacao',
        'outros_patrocinadores','quais_patrocinadores',
        'data_assinatura','nome_presidente','assinatura_path',
        'valor_total','valor_recebido','valor_executado',
        'data_inicio','data_fim','status','responsavel','local_execucao',
    ];
    protected function casts(): array {
        return [
            'data_inicio'=>'date','data_fim'=>'date','data_assinatura'=>'date',
            'valor_total'=>'float','valor_recebido'=>'float','valor_executado'=>'float',
            'quantidade_publico'=>'integer',
            'outros_patrocinadores'=>'boolean',
        ];
    }

    public function institution()       { return $this->belongsTo(Institution::class); }
    public function fundingSource()     { return $this->belongsTo(FundingSource::class); }
    public function goals()             { return $this->hasMany(Goal::class); }
    public function expenses()          { return $this->hasMany(Expense::class); }
    public function documents()         { return $this->hasMany(Document::class); }
    public function diligences()        { return $this->hasMany(Diligence::class); }
    public function accountingReports() { return $this->hasMany(AccountingReport::class); }
    public function budgetItems()       { return $this->hasMany(BudgetItem::class); }
    public function executionLocations(){ return $this->hasMany(ProjectExecutionLocation::class); }
    public function specificObjectives(){ return $this->hasMany(ProjectSpecificObjective::class)->orderBy('ordem'); }
    public function priceResearches()   { return $this->hasMany(PriceResearch::class); }
    public function teamMembers()       { return $this->hasMany(ProjectTeamMember::class)->orderBy('ordem'); }
    public function contractedServices(){ return $this->hasMany(ProjectContractedService::class)->orderBy('ordem'); }
    public function capabilityPhotos()  { return $this->hasMany(ProjectCapabilityPhoto::class)->orderBy('ordem'); }
    public function reportSelection()   { return $this->hasOne(ProjectReportSelection::class); }
    public function notifications()     { return $this->hasMany(ProjectNotification::class)->orderByDesc('data_notificacao'); }
    public function complianceSelection() { return $this->hasOne(ProjectComplianceSelection::class); }

    public function getStatusLabelAttribute(): string {
        return match($this->status) {
            'RASCUNHO'           => 'Rascunho',
            'EM_ANALISE'         => 'Em Análise',
            'APROVADO'           => 'Aprovado',
            'EM_EXECUCAO'        => 'Em Execução',
            'SUSPENSO'           => 'Suspenso',
            'FINALIZADO'         => 'Finalizado',
            'PRESTACAO_CONTAS'   => 'Prestação de Contas',
            'PRESTACAO_APROVADA' => 'Aprovada',
            'PRESTACAO_REPROVADA'=> 'Reprovada',
            default              => $this->status,
        };
    }
    public function getStatusColorAttribute(): string {
        return match($this->status) {
            'EM_EXECUCAO'        => 'green',
            'APROVADO'           => 'blue',
            'FINALIZADO'         => 'gray',
            'SUSPENSO'           => 'red',
            'EM_ANALISE'         => 'yellow',
            'PRESTACAO_CONTAS'   => 'purple',
            default              => 'gray',
        };
    }
}
