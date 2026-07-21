<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('url')->nullable();
            $table->string('tipo')->nullable();
            $table->date('data')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('proofs'); }
};
