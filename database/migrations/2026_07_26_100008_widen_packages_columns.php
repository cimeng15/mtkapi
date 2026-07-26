<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // rate-limit MikroTik bisa panjang (dengan burst), mis:
            // "2512k/5128k 2512k/10128k 2512k/2512k 5/5 7 2512k/5128k"
            $table->string('rate_limit', 191)->nullable()->change();
            $table->string('session_timeout', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('rate_limit', 50)->nullable()->change();
            $table->string('session_timeout', 50)->nullable()->change();
        });
    }
};
