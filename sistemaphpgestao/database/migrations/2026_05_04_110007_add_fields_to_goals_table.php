<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('goals', function (Blueprint $table) {
            $table->string('numero')->nullable()->after('project_id');
            $table->string('tipo_meta')->default('QUANTITATIVA')->after('numero');
            $table->date('data_inicio')->nullable()->after('prazo');
            $table->string('telefone_responsavel')->nullable()->after('responsavel');
            $table->string('email_responsavel')->nullable()->after('telefone_responsavel');
        });
    }
    public function down(): void {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['numero','tipo_meta','data_inicio','telefone_responsavel','email_responsavel']);
        });
    }
};
