<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('cep', 9)->nullable()->after('endereco');
            $table->string('numero', 20)->nullable()->after('cep');
            $table->string('complemento', 100)->nullable()->after('numero');
            $table->string('bairro', 100)->nullable()->after('complemento');
            $table->string('presidente_rg', 30)->nullable()->after('presidente_cpf');
            $table->date('presidente_rg_expedicao')->nullable()->after('presidente_rg');
            $table->date('presidente_nascimento')->nullable()->after('presidente_rg_expedicao');
        });
    }

    public function down(): void {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['cep','numero','complemento','bairro','presidente_rg','presidente_rg_expedicao','presidente_nascimento']);
        });
    }
};
