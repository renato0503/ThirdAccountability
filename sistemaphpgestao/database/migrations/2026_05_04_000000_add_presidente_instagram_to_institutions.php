<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('instagram')->nullable()->after('site');
            $table->string('presidente_cpf')->nullable()->after('representante_legal');
            $table->string('presidente_telefone')->nullable()->after('presidente_cpf');
            $table->string('presidente_email')->nullable()->after('presidente_telefone');
            $table->text('presidente_endereco')->nullable()->after('presidente_email');
            $table->string('presidente_foto')->nullable()->after('presidente_endereco');
        });
    }
    public function down(): void {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['instagram','presidente_cpf','presidente_telefone','presidente_email','presidente_endereco','presidente_foto']);
        });
    }
};
