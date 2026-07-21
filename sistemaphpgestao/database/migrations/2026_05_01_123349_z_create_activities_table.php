<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->string('responsavel')->nullable();
            $table->integer('percentual_execucao')->default(0);
            $table->string('status')->default('PENDENTE');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('activities'); }
};
