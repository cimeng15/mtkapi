<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_id')->unique()->comment('ID guru/siswa (NIP/NIS/NISN) dipakai sebagai username hotspot');
            $table->string('name');
            $table->enum('type', ['guru', 'siswa', 'staff'])->default('siswa');
            $table->string('class')->nullable()->comment('Kelas / rombel untuk siswa');
            $table->string('department')->nullable()->comment('Jurusan / bagian untuk guru & staff');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
