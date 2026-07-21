<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('price_researches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('search_term');
            $table->string('category')->nullable();
            $table->decimal('quantity', 15, 4)->nullable();
            $table->string('unit', 60)->nullable();

            $table->json('sources')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('city')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->text('notes')->nullable();

            $table->decimal('min_price', 15, 4)->nullable();
            $table->decimal('max_price', 15, 4)->nullable();
            $table->decimal('average_price', 15, 4)->nullable();
            $table->decimal('median_price', 15, 4)->nullable();

            $table->decimal('selected_reference_price', 15, 4)->nullable();
            $table->string('reference_type', 30)->nullable(); // MENOR, MAIOR, MEDIA, MEDIANA, MANUAL, ITEM
            $table->text('justification')->nullable();

            $table->string('status', 30)->default('RASCUNHO');
            $table->timestamp('searched_at')->nullable();
            $table->timestamps();

            $table->index('institution_id');
            $table->index('project_id');
            $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('price_researches'); }
};
