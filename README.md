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