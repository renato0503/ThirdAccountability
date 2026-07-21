<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_report_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->boolean('inc_informacoes')->default(true);
            $table->boolean('inc_despesas')->default(true);
            $table->boolean('inc_metas')->default(true);
            $table->boolean('inc_comprovacao')->default(true);
            $table->boolean('inc_diligencias')->default(true);
            $table->boolean('inc_prestacao_contas')->default(true);
            $table->timestamps();
            $table->unique('project_id');
        });
    }
    public function down(): void { Schema::dropIfExists('project_report_selections'); }
};
