<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama paket, mis: Guru Unlimited, Siswa 3 Jam');
            $table->string('mikrotik_profile')->comment('Nama user-profile di RouterOS hotspot');
            $table->string('rate_limit')->nullable()->comment('Limit kecepatan, mis: 2M/1M');
            $table->string('session_timeout')->nullable()->comment('Durasi sesi, mis: 3h, 1d');
            $table->unsignedInteger('shared_users')->default(1)->comment('Jumlah perangkat bersamaan');
            $table->string('data_limit')->nullable()->comment('Kuota data, mis: 500M, kosong=unlimited');
            $table->unsignedBigInteger('price')->default(0)->comment('Harga voucher (Rp)');
            $table->enum('for_type', ['umum', 'guru', 'siswa', 'staff'])->default('umum');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
