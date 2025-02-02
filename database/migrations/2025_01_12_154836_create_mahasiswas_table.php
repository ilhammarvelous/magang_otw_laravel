<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('nama')->nullable();
            $table->string('no_hp')->nullable();
            $table->enum('agama', ['Islam', 'Protestan', 'Katholik', 'Budha', 'Hindu', 'Konghucu'])->nullable();
            $table->enum('prodi', ['Teknik Informatika', 'Teknik Sipil', 'Teknik Industri', 'Teknik Elektro', 'Teknik Arsitektur', 'Teknik Mesin'])->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
