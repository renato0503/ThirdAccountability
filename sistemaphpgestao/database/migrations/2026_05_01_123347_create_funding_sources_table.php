<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('funding_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->string('tipo')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('responsavel')->nullable();
            $table->decimal('valor_aprovado', 15, 2)->default(0);
            $table->string('instrumento')->nullable();
            $table->string('numero')->nullable();
            $table->string('orgao_concedente')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->string('status')->default('VIGENTE');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('funding_sources'); }
};
