<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj')->unique();
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->string('site')->nullable();
            $table->text('endereco')->nullable();
            $table->string('municipio')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('area_atuacao')->nullable();
            $table->text('dados_bancarios')->nullable();
            $table->string('representante_legal')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('institutions'); }
};
