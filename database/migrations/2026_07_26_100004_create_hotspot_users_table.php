<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('comment')->nullable();
            $table->enum('status', ['active', 'disabled'])->default('active');
            $table->boolean('synced')->default(false)->comment('Sudah tersinkron ke MikroTik?');
            $table->timestamp('synced_at')->nullable();
            $table->string('mikrotik_id')->nullable()->comment('.id record di RouterOS');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_users');
    }
};
