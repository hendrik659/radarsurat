@extends('layouts.dashboard')

@section('title', 'Detail Surat Masuk')

@section('content')
    @php
        $isAdminSurat = auth()->user()?->role?->slug === 'admin_surat';
        $canManage = $isAdminSurat && $incomingLetter->status === 'baru_diterima';
        $statusLabels = [
            'baru_diterima' => 'Baru Diterima',
            'menunggu_pemeriksaan' => 'Menunggu Pemeriksaan',
        ];
        $priorityLabels = [
            'biasa' => 'Biasa',
            'segera' => 'Segera',
        ];
        $receivedViaLabels = [
            'email' => 'Email',
            'whatsapp' => 'WhatsApp',
            'fisik' => 'Fisik',
            'lainnya' => 'Lainnya',
        ];
        $formatFileSize = static function (?int $bytes): string {
            if ($bytes === null) {
                return '-';
            }

            if ($bytes >= 1024 * 1024) {
                return number_format($bytes / (1024 * 1024), 2, ',', '.').' MB';
            }

            return number_format($bytes / 1024, 1, ',', '.').' KB';
        };
    @endphp

    <header class="rs-page-header mb-4">
        <h1 class="rs-page-title h3 mb-1">Detail Surat Masuk</h1>
        <p class="rs-page-description text-body-secondary mb-2">Nomor agenda {{ $incomingLetter->agenda_number }}</p>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge {{ $incomingLetter->status === 'baru_diterima' ? 'text-bg-info' : 'text-bg-warning' }}">
                {{ $statusLabels[$incomingLetter->status] ?? $incomingLetter->status }}
            </span>
            <span class="badge {{ $incomingLetter->priority === 'segera' ? 'text-bg-danger' : 'text-bg-primary' }}">
                {{ $priorityLabels[$incomingLetter->priority] ?? $incomingLetter->priority }}
            </span>
        </div>
    </header>

    <section class="card rs-card shadow-sm mb-4" aria-label="Preview dokumen surat masuk">
        <div class="card-header bg-body d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 py-3">
            <h2 class="h5 mb-0">Preview Dokumen</h2>
            <a
                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center gap-2"
                href="{{ route('incoming-letters.download', $incomingLetter) }}"
                data-testid="incoming-letter-download-link"
            >
                <i class="fa-solid fa-download" aria-hidden="true"></i>
                <span>Download</span>
            </a>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="rs-document-preview">
                @if ($incomingLetter->document_mime_type === 'application/pdf')
                    <object
                        class="rs-document-frame"
                        data="{{ route('incoming-letters.preview', $incomingLetter) }}"
                        type="application/pdf"
                        data-testid="incoming-letter-preview"
                    >
                        <p class="mb-0">
                            Dokumen tidak dapat ditampilkan pada browser ini. Silakan unduh dokumen untuk melihatnya.
                        </p>
                    </object>
                @elseif (str_starts_with($incomingLetter->document_mime_type, 'image/'))
                    <img
                        class="rs-document-image"
                        src="{{ route('incoming-letters.preview', $incomingLetter) }}"
                        alt="Preview {{ $incomingLetter->original_document_name }}"
                        data-testid="incoming-letter-preview"
                    >
                @else
                    <p class="mb-0">Dokumen tidak dapat ditampilkan pada browser ini. Silakan unduh dokumen untuk melihatnya.</p>
                @endif
            </div>
        </div>
    </section>

    <section class="card rs-card shadow-sm mb-4" aria-label="Informasi surat masuk">
        <div class="card-header bg-body py-3">
            <h2 class="h5 mb-0">Informasi Surat</h2>
        </div>
        <div class="card-body p-3 p-md-4">
            <dl class="row g-3 mb-0 rs-detail-list">
                @foreach ([
                    ['Nomor Agenda', $incomingLetter->agenda_number ?: '-'],
                    ['Nomor Surat', $incomingLetter->letter_number ?: '-'],
                    ['Pengirim', $incomingLetter->sender_name ?: '-'],
                    ['Tujuan pada Surat', $incomingLetter->addressed_to ?: '-'],
                    ['Tanggal Surat', $incomingLetter->letter_date?->format('d-m-Y') ?? '-'],
                    ['Tanggal Diterima', $incomingLetter->received_date?->format('d-m-Y') ?? '-'],
                    ['Media Penerimaan', $receivedViaLabels[$incomingLetter->received_via] ?? ($incomingLetter->received_via ?: '-')],
                    ['Perihal', $incomingLetter->subject ?: '-'],
                    ['Prioritas', $priorityLabels[$incomingLetter->priority] ?? $incomingLetter->priority],
                    ['Divisi Tujuan', $incomingLetter->destinationDivision?->name ?? 'Belum ditentukan'],
                    ['Status', $statusLabels[$incomingLetter->status] ?? $incomingLetter->status],
                    ['Dibuat oleh', $incomingLetter->creator?->name ?? '-'],
                    ['Tanggal Dicatat', $incomingLetter->created_at?->format('d-m-Y H:i') ?? '-'],
                    ['Nama File', $incomingLetter->original_document_name ?: '-'],
                    ['Ukuran File', $formatFileSize($incomingLetter->document_size)],
                ] as [$label, $value])
                    <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                        <dt class="rs-detail-label small text-body-secondary">{{ $label }}</dt>
                        <dd>{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <div class="d-grid d-sm-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2" href="{{ route('incoming-letters.index') }}">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>Kembali</span>
        </a>
        <a class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center gap-2" href="{{ route('incoming-letters.download', $incomingLetter) }}">
            <i class="fa-solid fa-download" aria-hidden="true"></i>
            <span>Download</span>
        </a>
        @if ($canManage)
            <a
                class="btn btn-outline-warning d-inline-flex align-items-center justify-content-center gap-2"
                href="{{ route('incoming-letters.edit', $incomingLetter) }}"
                data-testid="incoming-letter-edit-link"
            >
                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                <span>Edit</span>
            </a>
            <form
                method="POST"
                action="{{ route('incoming-letters.submit-for-review', $incomingLetter) }}"
                data-incoming-letter-submit-form
                data-confirm-message="Kirim surat ini untuk pemeriksaan? Setelah dikirim, data surat tidak dapat diedit."
                data-testid="incoming-letter-submit-form"
            >
                @csrf
                @method('PATCH')
                <button class="btn btn-outline-success d-inline-flex align-items-center justify-content-center gap-2 w-100" type="submit">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    <span>Kirim untuk Pemeriksaan</span>
                </button>
            </form>
        @endif
    </div>
@endsection
