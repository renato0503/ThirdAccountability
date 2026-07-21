<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->date('data_notificacao');
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->text('observacao')->nullable();
            $table->string('status')->default('REGISTRADA');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['project_id','data_notificacao']);
        });
    }
    public function down(): void { Schema::dropIfExists('project_notifications'); }
};
