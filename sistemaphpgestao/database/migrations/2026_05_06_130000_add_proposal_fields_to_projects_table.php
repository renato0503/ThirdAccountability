<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('numero_proposta')->nullable()->after('codigo');
            $table->text('capacidade_tecnica')->nullable()->after('metodologia');
            $table->text('municipios_alcancados')->nullable()->after('capacidade_tecnica');
            $table->integer('quantidade_publico')->nullable()->after('municipios_alcancados');
            $table->text('data_local_horario')->nullable()->after('quantidade_publico');
            $table->text('descricao_servico')->nullable()->after('data_local_horario');
            $table->text('funcao_osc')->nullable()->after('descricao_servico');
            $table->text('recolhimento_impostos')->nullable()->after('funcao_osc');
            $table->text('riscos_identificados')->nullable()->after('recolhimento_impostos');
            $table->text('plano_mitigacao')->nullable()->after('riscos_identificados');
            $table->text('resultados_esperados')->nullable()->after('plano_mitigacao');
            $table->text('plano_divulgacao')->nullable()->after('resultados_esperados');
            $table->boolean('outros_patrocinadores')->default(false)->after('plano_divulgacao');
            $table->text('quais_patrocinadores')->nullable()->after('outros_patrocinadores');
            $table->date('data_assinatura')->nullable()->after('quais_patrocinadores');
            $table->string('nome_presidente')->nullable()->after('data_assinatura');
            $table->string('assinatura_path')->nullable()->after('nome_presidente');
        });
    }
    public function down(): void {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'numero_proposta','capacidade_tecnica','municipios_alcancados','quantidade_publico',
                'data_local_horario','descricao_servico','funcao_osc','recolhimento_impostos',
                'riscos_identificados','plano_mitigacao','resultados_esperados','plano_divulgacao',
                'outros_patrocinadores','quais_patrocinadores','data_assinatura','nome_presidente','assinatura_path',
            ]);
        });
    }
};
