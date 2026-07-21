<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('fonte')->nullable()->after('codigo');
            $table->string('parlamentar')->nullable()->after('fonte');
            $table->string('secretaria')->nullable()->after('parlamentar');
            $table->string('secretaria_outro')->nullable()->after('secretaria');
            $table->text('metodologia')->nullable()->after('justificativa');
        });
    }
    public function down(): void {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['fonte','parlamentar','secretaria','secretaria_outro','metodologia']);
        });
    }
};
