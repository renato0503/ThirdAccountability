<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nome');
            $table->string('tipo')->nullable();
            $table->string('categoria')->nullable();
            $table->string('url')->nullable();
            $table->string('tamanho')->nullable();
            $table->date('validade')->nullable();
            $table->string('status_analise')->default('PENDENTE');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('documents'); }
};
