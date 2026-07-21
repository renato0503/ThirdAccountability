<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('institution_project_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->string('programa_estadual')->nullable();
            $table->string('fonte')->nullable();
            $table->decimal('valor', 15, 2)->nullable();
            $table->string('numero_convenio')->nullable();
            $table->string('numero_processo')->nullable();
            $table->string('numero_proposta')->nullable();
            $table->date('data_assinatura')->nullable();
            $table->date('data_publicacao')->nullable();
            $table->string('vigencia')->nullable();
            $table->string('publicidade_parceria')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('institution_project_histories');
    }
};
