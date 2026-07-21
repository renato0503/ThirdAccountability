<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_contracted_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo_contratacao', ['PF', 'PJ'])->default('PF');
            $table->text('descricao');
            $table->integer('periodo_execucao')->nullable();
            $table->enum('unidade_periodo', ['dia', 'semana', 'mes', 'ano'])->default('mes');
            $table->enum('tipo_pagamento', ['mensal', 'unico'])->default('unico');
            $table->decimal('valor', 12, 2)->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('project_contracted_services');
    }
};
