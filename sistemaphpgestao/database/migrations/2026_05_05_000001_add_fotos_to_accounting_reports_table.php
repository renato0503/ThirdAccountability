<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('accounting_reports', function (Blueprint $table) {
            $table->json('fotos')->nullable()->after('links_videos')
                ->comment('Até 5 fotos (paths no disco público)');
        });
    }
    public function down(): void {
        Schema::table('accounting_reports', function (Blueprint $table) {
            $table->dropColumn('fotos');
        });
    }
};
