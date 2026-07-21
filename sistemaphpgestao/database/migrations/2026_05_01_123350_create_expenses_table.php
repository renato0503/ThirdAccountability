<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('categoria')->nullable();
            $table->string('fornecedor')->nullable();
            $table->string('cnpj_fornecedor')->nullable();
            $table->text('descricao');
            $table->date('data_despesa')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->decimal('valor', 15, 2);
            $table->string('forma_pagamento')->nullable();
            $table->string('numero_nf')->nullable();
            $table->string('status')->default('PENDENTE');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('expenses'); }
};
