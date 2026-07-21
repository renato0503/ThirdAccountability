<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('price_research_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_research_id')->constrained()->cascadeOnDelete();
            $table->string('source', 40); // PNCP, RADAR_TCE_MT, MANUAL
            $table->string('external_id')->nullable();
            $table->text('original_description');
            $table->decimal('unit_price', 15, 4);
            $table->decimal('quantity', 15, 4)->nullable();
            $table->string('unit', 60)->nullable();
            $table->decimal('total_price', 15, 4)->nullable();

            $table->string('buyer_name')->nullable();
            $table->string('buyer_cnpj', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('process_number')->nullable();
            $table->string('contract_number')->nullable();
            $table->string('bid_number')->nullable();
            $table->string('ata_number')->nullable();
            $table->date('purchase_date')->nullable();

            $table->string('source_url', 1000)->nullable();
            $table->json('raw_payload')->nullable();
            $table->decimal('similarity_score', 5, 4)->nullable();

            $table->boolean('selected')->default(false);
            $table->text('selection_justification')->nullable();
            $table->timestamps();

            $table->index('price_research_id');
            $table->index('source');
            $table->index('selected');
        });
    }
    public function down(): void { Schema::dropIfExists('price_research_results'); }
};
