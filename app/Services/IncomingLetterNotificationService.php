<?php

namespace App\Services;

use App\Models\IncomingLetter;
use App\Models\User;
use App\Notifications\IncomingLetterForwardedToDivision;
use App\Notifications\IncomingLetterSubmittedForReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class IncomingLetterNotificationService
{
    public function notifySubmittedForReviewAfterCommit(int $incomingLetterId): void
    {
        DB::afterCommit(fn () => $this->sendSubmittedForReview($incomingLetterId));
    }

    public function notifyForwardedToDivisionAfterCommit(int $incomingLetterId): void
    {
        DB::afterCommit(fn () => $this->sendForwardedToDivision($incomingLetterId));
    }

    private function sendSubmittedForReview(int $incomingLetterId): void
    {
        $this->runSafely($incomingLetterId, 'submitted_for_review', function () use ($incomingLetterId) {
            $incomingLetter = IncomingLetter::query()->findOrFail($incomingLetterId);
            $recipients = User::query()
                ->where('is_active', true)
                ->where(function (Builder $query) {
                    $query->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', 'pimpinan'))
                        ->orWhere(function (Builder $divisionHeadQuery) {
                            $divisionHeadQuery
                                ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', 'ketua_divisi'))
                                ->whereHas('division', fn (Builder $divisionQuery) => $divisionQuery->where('code', 'SDM'));
                        });
                })
                ->get();

            foreach ($recipients as $recipient) {
                $this->notifySafely(
                    $recipient,
                    new IncomingLetterSubmittedForReview($incomingLetter),
                    $incomingLetterId,
                    'submitted_for_review',
                );
            }
        });
    }

    private function sendForwardedToDivision(int $incomingLetterId): void
    {
        $this->runSafely($incomingLetterId, 'forwarded_to_division', function () use ($incomingLetterId) {
            $incomingLetter = IncomingLetter::query()
                ->with('destinationDivision:id,name')
                ->findOrFail($incomingLetterId);

            if ($incomingLetter->destination_division_id === null) {
                throw new RuntimeException('Surat masuk tidak memiliki divisi tujuan.');
            }

            $recipients = User::query()
                ->where('is_active', true)
                ->where('division_id', $incomingLetter->destination_division_id)
                ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', 'ketua_divisi'))
                ->get();

            foreach ($recipients as $recipient) {
                $this->notifySafely(
                    $recipient,
                    new IncomingLetterForwardedToDivision($incomingLetter),
                    $incomingLetterId,
                    'forwarded_to_division',
                );
            }
        });
    }

    private function notifySafely(
        User $recipient,
        Notification $notification,
        int $incomingLetterId,
        string $event,
    ): void {
        try {
            $recipient->notify($notification);
        } catch (Throwable $exception) {
            $this->logFailure($incomingLetterId, $event, $exception, $recipient->id);
        }
    }

    private function runSafely(int $incomingLetterId, string $event, callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $exception) {
            $this->logFailure($incomingLetterId, $event, $exception);
        }
    }

    private function logFailure(
        int $incomingLetterId,
        string $event,
        Throwable $exception,
        ?int $recipientId = null,
    ): void {
        Log::error('Email notifikasi Surat Masuk gagal dikirim.', [
            'incoming_letter_id' => $incomingLetterId,
            'notification_event' => $event,
            'recipient_user_id' => $recipientId,
            'exception_class' => $exception::class,
        ]);
    }
}
