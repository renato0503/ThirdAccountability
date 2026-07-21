<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_research_results', function (Blueprint $table) {
            $table->string('cnpj_fornecedor')->nullable()->after('buyer_cnpj');
            $table->string('item_descricao')->nullable()->after('original_description');
            $table->string('anexo_path')->nullable()->after('source_url');
            $table->text('observacoes')->nullable()->after('selection_justification');
        });
    }

    public function down(): void
    {
        Schema::table('price_research_results', function (Blueprint $table) {
            $table->dropColumn(['cnpj_fornecedor', 'item_descricao', 'anexo_path', 'observacoes']);
        });
    }
};
