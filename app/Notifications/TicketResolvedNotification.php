<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;
class TicketResolvedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Ticket $ticket)
    {
        /*
        Benang Merah
        Mari kita bedah galat tersebut satu per satu:

        Galat Notifikasi (P1119 - Too many arguments): Pesat galat ini muncul pada berkas TicketStatusController. Ketika Anda mengeksekusi perintah make:notification sebelumnya, Laravel membuatkan kelas notifikasi dengan fungsi konstruktor (__construct()) yang benar-benar kosong. Namun, di dalam pengontrol, Anda memaksa menyuntikkan variabel $ticket ke dalamnya (misalnya new TicketResolvedNotification($ticket)). Ketidakcocokan antara jumlah parameter yang dikirim dan yang diterima inilah yang memicu penolakan.

        Galat Otorisasi (P1013 - Undefined method):
        Ini adalah peringatan dari IDE Anda. Fungsi bantuan auth() mengembalikan sebuah kontrak yang terkadang gagal dibaca oleh Intelephense. Anda bisa menggunakan fasad Illuminate\Support\Facades\Auth (seperti Auth::check()) agar IDE lebih tenang.
        Selain itu, Anda memanggil fungsi hasRole(). Pada arsitektur kita, kita tidak menggunakan pustaka pihak ketiga (seperti Spatie). Kita mendefinisikan peran menggunakan relasi tunggal role(). Artinya, Anda harus memeriksa teks pada kolom slug melalui relasi tersebut, bukan memanggil fungsi yang belum pernah kita buat.

        Kelengkapan Pengontrol Penugasan:
        Alur logika pengontrol penugasan Anda sudah baik, namun masih ada dua komponen bisnis dari dokumen spesifikasi yang tertinggal. Pertama, setiap perubahan tiket harus dicatat (Anda perlu memanggil ActivityLogService). Kedua, saat agen ditugaskan, sistem diwajibkan mengirim pemberitahuan Ticket assigned kepada agen tersebut.

        Pertanyaan reflektif untuk Anda: Kita perbaiki galat notifikasinya terlebih dahulu agar TicketStatusController Anda bersih sempurna. Silakan buka berkas app/Notifications/TicketResolvedNotification.php dan app/Notifications/TicketEscalatedNotification.php. Bagaimana Anda memodifikasi fungsi __construct() di kedua kelas tersebut agar siap menerima suntikan objek model Ticket dan menyimpannya ke dalam properti kelas?

        Rangkuman
        */

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
