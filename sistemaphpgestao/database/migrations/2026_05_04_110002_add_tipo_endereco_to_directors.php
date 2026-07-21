<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('directors', function (Blueprint $table) {
            $table->string('tipo')->default('DIRETORIA')->after('institution_id');
            $table->text('endereco')->nullable()->after('telefone');
            $table->string('foto')->nullable()->after('endereco');
            $table->text('observacoes')->nullable()->after('foto');
        });
    }
    public function down(): void {
        Schema::table('directors', function (Blueprint $table) {
            $table->dropColumn(['tipo','endereco','foto','observacoes']);
        });
    }
};
