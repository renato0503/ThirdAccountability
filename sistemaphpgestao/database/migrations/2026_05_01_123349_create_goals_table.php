<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('indicador')->nullable();
            $table->integer('quantidade_prevista')->nullable();
            $table->integer('quantidade_realizada')->default(0);
            $table->string('unidade_medida')->nullable();
            $table->decimal('valor_previsto', 15, 2)->nullable();
            $table->date('prazo')->nullable();
            $table->string('responsavel')->nullable();
            $table->string('status')->default('PENDENTE');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('goals'); }
};
