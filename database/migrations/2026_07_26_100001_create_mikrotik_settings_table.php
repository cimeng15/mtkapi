<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Router Utama');
            $table->string('host');
            $table->unsignedInteger('port')->default(8728);
            $table->string('username');
            $table->text('password')->nullable();
            $table->boolean('use_ssl')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('dns_name')->nullable()->comment('Nama DNS/hostname portal hotspot untuk voucher');
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_settings');
    }
};
