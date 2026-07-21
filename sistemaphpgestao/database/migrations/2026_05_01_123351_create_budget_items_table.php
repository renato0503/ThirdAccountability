<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('descricao');
            $table->string('categoria')->nullable();
            $table->integer('quantidade')->default(1);
            $table->decimal('valor_unitario', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->string('fonte')->nullable();
            $table->string('status_aprovacao')->default('PENDENTE');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('budget_items'); }
};
