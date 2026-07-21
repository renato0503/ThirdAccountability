<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('banco')->nullable()->after('dados_bancarios');
            $table->string('agencia')->nullable()->after('banco');
            $table->string('conta_corrente')->nullable()->after('agencia');
            $table->string('tipo_conta')->nullable()->after('conta_corrente');
            $table->string('chave_pix')->nullable()->after('tipo_conta');
            $table->text('historico_institucional')->nullable()->after('chave_pix');
            $table->text('descricao_estrutura_fisica')->nullable()->after('historico_institucional');
            $table->text('observacoes_compliance')->nullable()->after('descricao_estrutura_fisica');
        });
    }
    public function down(): void {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['banco','agencia','conta_corrente','tipo_conta','chave_pix','historico_institucional','descricao_estrutura_fisica','observacoes_compliance']);
        });
    }
};
