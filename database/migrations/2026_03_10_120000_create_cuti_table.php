<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->string('pegawai_id', 50)->index();
            $table->string('pegawai_nama');
            $table->string('jabatan')->nullable();
            $table->string('status', 50)->default('Pengajuan');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['tanggal', 'pegawai_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti');
    }
};
