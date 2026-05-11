# Ticket Support System

Sistem manajemen tiket dukungan yang dibangun menggunakan kerangka kerja Laravel. Proyek ini memfasilitasi interaksi antara pelanggan, agen, supervisor, dan administrator.

## Tech Stack
- PHP 8.3
- Laravel (Versi Terbaru)
- MySQL
- Laravel Breeze dengan antarmuka Blade
- Tailwind CSS

## Installation Steps

Ikuti instruksi berikut untuk menjalankan proyek secara lokal di mesin Anda:

1. Unduh repositori ini ke dalam mesin lokal Anda.
2. Masuk ke dalam direktori utama proyek melalui antarmuka baris perintah.
3. Instal seluruh dependensi prapemrosesan PHP dan Node.js:
   ```bash
   composer install
   npm install
   ```
4. Salin berkas konfigurasi lingkungan dan hasilkan kunci aplikasi yang unik:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
5. Sesuaikan kredensial pangkalan data MySQL Anda pada berkas `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=root
   DB_PASSWORD=kata_sandi_anda
   ```
6. Rakit aset antarmuka statis sisi klien:
   ```bash
   npm run build
   ```
7. Jalankan perintah migrasi untuk membangun struktur pangkalan data:
   ```bash
   php artisan migrate
   ```
8. Nyalakan peladen pengembangan lokal:
   ```bash
   php artisan serve
   ```

## Konfigurasi Latar Belakang Queue dan Task Scheduling

Untuk memastikan fitur pengiriman notifikasi dan pemeriksaan tiket kedaluwarsa berjalan dengan semestinya, Anda harus menjalankan layanan latar belakang berikut:

### 1. Menjalankan Antrean Queue Worker
Sistem ini menggunakan fitur antrean untuk mengirim surel dan notifikasi agar tidak membebani waktu respons pengguna. Jalankan perintah berikut di tab terminal yang selalu aktif:

```bash
php artisan queue:work
```

### 2. Mengatur Penjadwal Waktu melalui Cron Job

Pemeriksaan tiket kedaluwarsa SLA dilakukan secara otomatis. Pada peladen Linux, buka penyunting jadwal dengan perintah 
```bash
crontab -e
```

Ketik angka **1** untuk memilih nano lalu tekan **Enter** di terminal Anda.\
Jendela penyunting teks `nano` akan terbuka (biasanya berisi banyak teks panduan berwarna dengan tanda `#` di depannya).\
Gunakan tombol panah bawah pada papan ketik (*keyboard*) Anda untuk memindahkan kursor terus ke baris paling bawah yang kosong.

lalu tambahkan baris berikut di baris paling bawah:
```bash
* * * * * cd /path/to/indi-tech-technical-test && php artisan schedule:run >> /dev/null 2>&1
```
Untuk menyimpan dokumen, tekan kombinasi tombol **Ctrl + O** (huruf O), lalu tekan **Enter** untuk mengonfirmasi nama berkasnya.\
Untuk menutup penyunting teks dan kembali ke terminal, tekan **Ctrl + X**.
