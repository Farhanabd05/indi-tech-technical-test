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
        /*
        Jika kita melihat fungsi up() pada berkas migrasi tersebut, pangkalan data saat ini hanya diinstruksikan untuk membuat kolom bawaan yaitu id dan timestamps. Apabila aplikasi mencoba mengeksekusi layanan penyimpanan log saat ini, sistem pangkalan data akan menolak kueri dan memunculkan galat karena kolom tujuannya tidak ditemukan.
        Pertanyaan reflektif untuk Anda: Silakan tinjau kembali Modul 1 pada dokumen rencana Anda terkait struktur kolom tabel activity_logs. Tipe data spesifik apa saja yang harus Anda tuliskan di dalam fungsi up() pada berkas migrasi tersebut untuk mengakomodasi kolom ticket_id, user_id, action, old_value, dan new_value? Sebagai petunjuk, ingatlah bahwa tidak semua aktivitas terikat pada pengguna tertentu, sehingga ada kolom yang perlu dibuat agar bisa menerima nilai kosong (nullable).
        */

        /*
        Perhatikan kembali baris kode pembentukan kolom kunci tamu ID pengguna. Di dalam sistem basis data relasional, sebuah kolom secara bawaan akan menuntut adanya nilai yang wajib diisi kecuali dideklarasikan sebaliknya secara eksplisit. Pada layanan log aktivitas yang sudah selesai dibuat sebelumnya, terdapat skenario ketika parameter variabel pengguna bernilai kosong akibat sebuah aksi dipicu otomatis oleh sistem. Jika baris kode migrasi tersebut dieksekusi apa adanya, basis data akan memblokir pencatatan otomatis tersebut karena ketiadaan identitas pengguna.

        Selain itu, mari pertimbangkan skenario ketika seorang agen mengundurkan diri dan datanya dihapus dari sistem. Perintah penghapusan berantai cascade akan membuat seluruh riwayat tindakan yang pernah dilakukan oleh agen tersebut ikut terhapus secara permanen dari log tiket. Hal ini akan merugikan operasional sistem karena jejak audit menjadi tidak utuh.

        Pertanyaan reflektif untuk Anda: Bagaimana Anda memodifikasi deklarasi kolom ID pengguna tersebut agar dapat menerima entitas kosong, sekaligus mengubah aturan penghapusannya agar riwayat log tetap utuh dan nilai identitas penggunanya otomatis diubah menjadi kosong ketika data pengguna asli terhapus dari pangkalan data utama?
        */
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
