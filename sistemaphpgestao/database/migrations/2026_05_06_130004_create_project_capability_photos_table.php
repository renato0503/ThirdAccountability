<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_capability_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('legenda')->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('project_capability_photos');
    }
};
