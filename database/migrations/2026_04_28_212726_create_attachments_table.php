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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            // relasi polimorfik (attachable_id, attachable_type)
            // ini akan hubngin berkas ke model lain (misal comment atau ticket)
            $table->morphs('attachable');

            // metadata teknis
            $table->string('file_path'); // lokasi fisik, misal 'upload/uuid.pdf'
            $table->string('file_name'); // nama file di sistem: '124asda-uuid.pdf'
            $table->string('original_name'); // nama file asli
            $table->string('mime_type'); // Keamanan: 'application/pdf'
            $table->bigInteger('file_size');  // Ukuran: dalam byte

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
