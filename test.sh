#!/bin/bash

echo "Membersihkan tembolok aplikasi..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "Menyiapkan pangkalan data untuk pengujian..."
php artisan migrate:fresh --env=testing

echo "Menjalankan rangkaian tes..."
./vendor/bin/pest

echo "Pengujian selesai."