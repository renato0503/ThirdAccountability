<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('accounting_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('RASCUNHO');
            $table->text('observacoes')->nullable();
            $table->date('data_envio')->nullable();
            $table->date('data_aprovacao')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('accounting_reports'); }
};
