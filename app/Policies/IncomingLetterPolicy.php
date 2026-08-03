<?php

namespace App\Policies;

use App\Models\IncomingLetter;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IncomingLetterPolicy
{
    public function review(User $user, IncomingLetter $incomingLetter): Response
    {
        $isPimpinan = $user->role?->slug === 'pimpinan';
        $isSdmDivisionHead = $user->role?->slug === 'ketua_divisi'
            && $user->division?->code === 'SDM';

        if (! $isPimpinan && ! $isSdmDivisionHead) {
            return Response::deny('Anda tidak berhak memeriksa surat masuk.');
        }

        if ($incomingLetter->status !== IncomingLetter::STATUS_MENUNGGU_PEMERIKSAAN) {
            return Response::deny('Surat masuk tidak berada pada status menunggu pemeriksaan.');
        }

        if ($incomingLetter->review()->exists()) {
            return Response::deny('Surat masuk sudah diperiksa.');
        }

        return Response::allow();
    }

    public function assign(User $user, IncomingLetter $incomingLetter): Response
    {
        if (! $user->is_active) {
            return Response::deny('Akun Anda tidak aktif.');
        }

        if ($user->role?->slug !== 'ketua_divisi') {
            return Response::deny('Anda tidak berhak menugaskan surat masuk.');
        }

        if ($user->division_id === null
            || $user->division_id !== $incomingLetter->destination_division_id) {
            return Response::deny('Surat masuk hanya dapat ditugaskan oleh Ketua Divisi tujuan.');
        }

        if ($incomingLetter->status !== IncomingLetter::STATUS_DITERUSKAN_KE_DIVISI) {
            return Response::deny('Surat masuk tidak berada pada status diteruskan ke divisi.');
        }

        if (! $incomingLetter->review()->exists()) {
            return Response::deny('Surat masuk belum memiliki hasil pemeriksaan.');
        }

        if ($incomingLetter->assignment()->exists()) {
            return Response::deny('Surat masuk sudah memiliki penugasan.');
        }

        return Response::allow();
    }
}
