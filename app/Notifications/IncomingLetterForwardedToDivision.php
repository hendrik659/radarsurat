<?php

namespace App\Notifications;

use App\Models\IncomingLetter;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncomingLetterForwardedToDivision extends Notification
{
    public function __construct(public readonly IncomingLetter $incomingLetter) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $divisionName = $this->incomingLetter->destinationDivision?->name ?? '-';

        return (new MailMessage)
            ->subject('[SIRAPI] Surat Masuk untuk Divisi Anda')
            ->greeting("Halo, {$notifiable->name}.")
            ->line('Surat Masuk telah selesai diperiksa dan diteruskan ke divisi Anda.')
            ->line("Divisi tujuan: {$divisionName}")
            ->line('Pengirim: '.($this->incomingLetter->sender_name ?: '-'))
            ->line('Perihal: '.($this->incomingLetter->subject ?: '-'))
            ->action('Lihat Surat di SIRAPI', route('incoming-letters.show', $this->incomingLetter))
            ->line('Silakan masuk ke SIRAPI untuk melihat detail dan dokumen surat.');
    }
}
