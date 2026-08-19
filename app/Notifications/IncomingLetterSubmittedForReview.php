<?php

namespace App\Notifications;

use App\Models\IncomingLetter;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncomingLetterSubmittedForReview extends Notification
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
        $letterDate = $this->incomingLetter->letter_date?->locale('id')->translatedFormat('d F Y') ?? '-';
        $priority = ucfirst($this->incomingLetter->priority ?: '-');

        return (new MailMessage)
            ->subject('[SIRAPI] Surat Masuk Menunggu Pemeriksaan')
            ->greeting("Halo, {$notifiable->name}.")
            ->line('Ada Surat Masuk yang menunggu pemeriksaan di SIRAPI.')
            ->line('Pengirim: '.($this->incomingLetter->sender_name ?: '-'))
            ->line('Perihal: '.($this->incomingLetter->subject ?: '-'))
            ->line("Prioritas: {$priority}")
            ->line("Tanggal surat: {$letterDate}")
            ->action('Lihat Surat di SIRAPI', route('incoming-letters.show', $this->incomingLetter))
            ->line('Silakan masuk ke SIRAPI untuk melihat detail dan dokumen surat.');
    }
}
