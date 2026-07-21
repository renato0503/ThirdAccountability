<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceResearch extends Model {
    protected $table = 'price_researches';

    protected $fillable = [
        'institution_id','project_id','user_id',
        'search_term','category','quantity','unit',
        'sources','state','city','date_start','date_end','notes',
        'min_price','max_price','average_price','median_price',
        'selected_reference_price','reference_type','justification',
        'status','searched_at',
    ];

    protected function casts(): array {
        return [
            'sources'                  => 'array',
            'date_start'               => 'date',
            'date_end'                 => 'date',
            'searched_at'              => 'datetime',
            'quantity'                 => 'float',
            'min_price'                => 'float',
            'max_price'                => 'float',
            'average_price'            => 'float',
            'median_price'             => 'float',
            'selected_reference_price' => 'float',
        ];
    }

    public function institution() { return $this->belongsTo(Institution::class); }
    public function project()     { return $this->belongsTo(Project::class); }
    public function user()        { return $this->belongsTo(User::class); }
    public function results()     { return $this->hasMany(PriceResearchResult::class); }
    public function selectedResults() {
        return $this->hasMany(PriceResearchResult::class)->where('selected', true);
    }

    public function getStatusLabelAttribute(): string {
        return match($this->status) {
            'RASCUNHO'        => 'Rascunho',
            'BUSCADA'         => 'Buscada',
            'COM_RESULTADOS'  => 'Com Resultados',
            'SEM_RESULTADOS'  => 'Sem Resultados',
            'SELECIONADA'     => 'Selecionada',
            'FINALIZADA'      => 'Finalizada',
            'CANCELADA'       => 'Cancelada',
            default           => $this->status,
        };
    }

    public function getStatusColorAttribute(): string {
        return match($this->status) {
            'RASCUNHO'        => 'secondary',
            'BUSCADA'         => 'info',
            'COM_RESULTADOS'  => 'primary',
            'SEM_RESULTADOS'  => 'warning',
            'SELECIONADA'     => 'success',
            'FINALIZADA'      => 'success',
            'CANCELADA'       => 'danger',
            default           => 'secondary',
        };
    }

    public function getReferenceTypeLabelAttribute(): string {
        return match($this->reference_type) {
            'MENOR'   => 'Menor preço',
            'MAIOR'   => 'Maior preço',
            'MEDIA'   => 'Média',
            'MEDIANA' => 'Mediana',
            'MANUAL'  => 'Valor manual',
            'ITEM'    => 'Item selecionado',
            default   => '—',
        };
    }

    public static function statusList(): array {
        return ['RASCUNHO','BUSCADA','COM_RESULTADOS','SEM_RESULTADOS','SELECIONADA','FINALIZADA','CANCELADA'];
    }

    public static function unitList(): array {
        return ['unidade','pacote','caixa','metro','litro','kg','serviço','outro'];
    }

    public static function sourceList(): array {
        return ['PNCP','RADAR_TCE_MT'];
    }
}
