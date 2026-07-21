<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('goal_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->json('fotos')->nullable()->comment('5 fotos JPG (paths no disco público)');
            $table->text('descricao')->nullable();
            $table->string('link_video')->nullable();
            $table->string('anexo_path')->nullable();
            $table->string('anexo_nome')->nullable();
            $table->timestamps();
            $table->unique('goal_id');
        });
    }
    public function down(): void { Schema::dropIfExists('goal_proofs'); }
};
