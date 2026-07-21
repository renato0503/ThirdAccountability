<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('accounting_reports', function (Blueprint $table) {
            $table->text('relatorio_texto')->nullable()->after('observacoes');
            $table->text('links_videos')->nullable()->after('relatorio_texto');
        });
    }
    public function down(): void {
        Schema::table('accounting_reports', function (Blueprint $table) {
            $table->dropColumn(['relatorio_texto','links_videos']);
        });
    }
};
