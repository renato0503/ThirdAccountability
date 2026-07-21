<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceResearchResult extends Model {
    protected $fillable = [
        'price_research_id','source','external_id',
        'original_description','unit_price','quantity','unit','total_price',
        'buyer_name','buyer_cnpj','city','state',
        'process_number','contract_number','bid_number','ata_number',
        'purchase_date','source_url','raw_payload','similarity_score',
        'selected','selection_justification',
    ];

    protected function casts(): array {
        return [
            'raw_payload'      => 'array',
            'purchase_date'    => 'date',
            'unit_price'       => 'float',
            'quantity'         => 'float',
            'total_price'      => 'float',
            'similarity_score' => 'float',
            'selected'         => 'boolean',
        ];
    }

    public function priceResearch() { return $this->belongsTo(PriceResearch::class); }

    public function getSourceLabelAttribute(): string {
        return match($this->source) {
            'PNCP'         => 'PNCP',
            'RADAR_TCE_MT' => 'Radar TCE-MT',
            'MANUAL'       => 'Manual',
            default        => $this->source,
        };
    }
}
