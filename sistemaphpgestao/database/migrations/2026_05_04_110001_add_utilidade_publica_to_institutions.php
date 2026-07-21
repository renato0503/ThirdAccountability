<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('institutions', function (Blueprint $table) {
            $table->boolean('utilidade_publica_municipal')->default(false)->after('observacoes_compliance');
            $table->string('lei_municipal_numero')->nullable()->after('utilidade_publica_municipal');
            $table->date('lei_municipal_data')->nullable()->after('lei_municipal_numero');
            $table->string('lei_municipal_arquivo')->nullable()->after('lei_municipal_data');
            $table->boolean('utilidade_publica_estadual')->default(false)->after('lei_municipal_arquivo');
            $table->string('lei_estadual_numero')->nullable()->after('utilidade_publica_estadual');
            $table->date('lei_estadual_data')->nullable()->after('lei_estadual_numero');
            $table->string('lei_estadual_arquivo')->nullable()->after('lei_estadual_data');
            $table->boolean('utilidade_publica_federal')->default(false)->after('lei_estadual_arquivo');
            $table->string('lei_federal_numero')->nullable()->after('utilidade_publica_federal');
            $table->date('lei_federal_data')->nullable()->after('lei_federal_numero');
            $table->string('lei_federal_arquivo')->nullable()->after('lei_federal_data');
        });
    }
    public function down(): void {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn([
                'utilidade_publica_municipal','lei_municipal_numero','lei_municipal_data','lei_municipal_arquivo',
                'utilidade_publica_estadual','lei_estadual_numero','lei_estadual_data','lei_estadual_arquivo',
                'utilidade_publica_federal','lei_federal_numero','lei_federal_data','lei_federal_arquivo',
            ]);
        });
    }
};
