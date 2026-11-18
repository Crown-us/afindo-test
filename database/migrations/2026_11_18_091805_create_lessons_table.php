<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            // Urutan tampil di dalam course (1, 2, 3, ...). Tidak dibuat unique
            // supaya proses reorder (tukar posisi) tidak bentrok di tengah transaksi.
            $table->unsignedInteger('position');

            // Asumsi: 1 lesson = 1 video, jadi metadata video disimpan di sini.
            $table->string('video_path')->nullable();          // path relatif di disk private
            $table->string('video_original_name')->nullable();
            $table->string('video_mime', 100)->nullable();
            $table->unsignedBigInteger('video_size')->nullable(); // byte
            $table->string('video_status', 20)->default('pending')->index();
            $table->string('video_error')->nullable();

            $table->timestamps();

            $table->index(['course_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
