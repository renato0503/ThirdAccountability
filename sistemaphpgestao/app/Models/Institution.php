<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model {
    protected $fillable = [
        'razao_social','nome_fantasia','cnpj','email','telefone','site','instagram',
        'endereco','cep','numero','complemento','bairro','municipio','estado','area_atuacao',
        'dados_bancarios','banco','agencia','conta_corrente','tipo_conta','chave_pix',
        'representante_legal','presidente_cpf','presidente_rg','presidente_rg_expedicao',
        'presidente_nascimento','presidente_telefone','presidente_email',
        'presidente_endereco','presidente_foto',
        'historico_institucional','descricao_estrutura_fisica','observacoes_compliance',
        'utilidade_publica_municipal','lei_municipal_numero','lei_municipal_data','lei_municipal_arquivo',
        'utilidade_publica_estadual','lei_estadual_numero','lei_estadual_data','lei_estadual_arquivo',
        'utilidade_publica_federal','lei_federal_numero','lei_federal_data','lei_federal_arquivo',
        'active',
    ];

    protected function casts(): array {
        return [
            'active'                    => 'boolean',
            'utilidade_publica_municipal' => 'boolean',
            'utilidade_publica_estadual'  => 'boolean',
            'utilidade_publica_federal'   => 'boolean',
            'lei_municipal_data'          => 'date',
            'lei_estadual_data'           => 'date',
            'lei_federal_data'            => 'date',
            'presidente_rg_expedicao'     => 'date',
            'presidente_nascimento'       => 'date',
        ];
    }

    public function directors()        { return $this->hasMany(Director::class); }
    public function diretoria()        { return $this->hasMany(Director::class)->where('tipo','DIRETORIA'); }
    public function conselhoFiscal()   { return $this->hasMany(Director::class)->where('tipo','CONSELHO_FISCAL'); }
    public function fundingSources()   { return $this->hasMany(FundingSource::class); }
    public function projects()         { return $this->hasMany(Project::class); }
    public function users()            { return $this->hasMany(User::class); }
    public function documents()        { return $this->hasMany(Document::class); }
    public function projectHistories() { return $this->hasMany(InstitutionProjectHistory::class); }
}
