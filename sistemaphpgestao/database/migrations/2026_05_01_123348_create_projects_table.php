<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('funding_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nome');
            $table->string('codigo')->nullable();
            $table->text('descricao')->nullable();
            $table->text('objetivo_geral')->nullable();
            $table->text('objetivos_especificos')->nullable();
            $table->text('publico_alvo')->nullable();
            $table->text('justificativa')->nullable();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->decimal('valor_recebido', 15, 2)->default(0);
            $table->decimal('valor_executado', 15, 2)->default(0);
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->string('status')->default('RASCUNHO');
            $table->string('responsavel')->nullable();
            $table->string('local_execucao')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('projects'); }
};
