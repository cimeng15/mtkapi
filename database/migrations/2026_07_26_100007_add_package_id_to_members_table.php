<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('type')
                  ->constrained('packages')->nullOnDelete()
                  ->comment('Paket kecepatan default anggota (untuk akun hotspot otomatis)');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_id');
        });
    }
};
