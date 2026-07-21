<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('users')->where('role', 'FINANCEIRO')->update(['role' => 'GESTOR_PROJETO']);
        DB::table('users')->where('role', 'READONLY')->update(['role' => 'FISCAL_EXTERNO']);
    }

    public function down(): void
    {
    }
};
