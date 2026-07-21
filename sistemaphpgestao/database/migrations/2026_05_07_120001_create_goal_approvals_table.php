<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('goal_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('avaliador_numero')->comment('1 a 5 — slot do avaliador');
            $table->string('avaliador_nome')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('aprovado')->default(false);
            $table->text('observacoes')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->timestamps();
            $table->unique(['goal_id','avaliador_numero']);
        });
    }
    public function down(): void { Schema::dropIfExists('goal_approvals'); }
};
