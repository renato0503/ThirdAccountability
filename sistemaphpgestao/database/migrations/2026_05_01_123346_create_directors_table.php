<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('directors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->string('cpf')->nullable();
            $table->string('cargo')->nullable();
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->date('mandato_inicio')->nullable();
            $table->date('mandato_fim')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('directors'); }
};
