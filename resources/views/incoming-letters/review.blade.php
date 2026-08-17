@extends('layouts.dashboard')

@section('title', 'Periksa dan Teruskan Surat')

@section('content')
    @php
        $statusLabels = [
            'baru_diterima' => 'Baru Diterima',
            'menunggu_pemeriksaan' => 'Menunggu Pemeriksaan',
            'selesai' => 'Selesai',
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
    @endphp

    <header class="rs-page-header mb-4">
        <h1 class="rs-page-title h3 mb-1">Periksa dan Teruskan Surat</h1>
        <p class="rs-page-description text-body-secondary mb-2">Nomor agenda {{ $incomingLetter->agenda_number }}</p>
        <span class="badge rs-badge-soft-warning">
            {{ $statusLabels[$incomingLetter->status] ?? $incomingLetter->status }}
        </span>
    </header>

    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-7">
            <section class="card rs-card shadow-sm mb-4" aria-label="Preview dokumen surat masuk">
                <div class="card-header bg-body py-3">
                    <h2 class="h5 mb-0">Preview Dokumen</h2>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="rs-document-preview">
                        @if ($incomingLetter->document_mime_type === 'application/pdf')
                            <object
                                class="rs-document-frame"
                                data="{{ route('incoming-letters.preview', $incomingLetter) }}"
                                type="application/pdf"
                                data-testid="incoming-letter-review-preview"
                            >
                                <p class="mb-0">Dokumen tidak dapat ditampilkan pada browser ini. Silakan buka preview dokumen.</p>
                            </object>
                        @elseif (str_starts_with($incomingLetter->document_mime_type, 'image/'))
                            <img
                                class="rs-document-image"
                                src="{{ route('incoming-letters.preview', $incomingLetter) }}"
                                alt="Preview {{ $incomingLetter->original_document_name }}"
                                data-testid="incoming-letter-review-preview"
                            >
                        @else
                            <p class="mb-0">Dokumen tidak dapat ditampilkan pada browser ini. Silakan buka preview dokumen.</p>
                        @endif
                    </div>
                </div>
            </section>

            <section class="card rs-card shadow-sm" aria-label="Informasi surat masuk">
                <div class="card-header bg-body py-3">
                    <h2 class="h5 mb-0">Informasi Surat</h2>
                </div>
                <div class="card-body p-3 p-md-4">
                    <dl class="row g-3 mb-0 rs-detail-list">
                        @foreach ([
                            ['Nomor Agenda', $incomingLetter->agenda_number ?: '-'],
                            ['Nomor Surat', $incomingLetter->letter_number ?: '-'],
                            ['Pengirim', $incomingLetter->sender_name ?: '-'],
                            ['Tujuan Surat', $incomingLetter->addressed_to ?: '-'],
                            ['Tanggal Surat', $incomingLetter->letter_date?->format('d-m-Y') ?? '-'],
                            ['Tanggal Diterima', $incomingLetter->received_date?->format('d-m-Y') ?? '-'],
                            ['Media Penerimaan', $receivedViaLabels[$incomingLetter->received_via] ?? ($incomingLetter->received_via ?: '-')],
                            ['Perihal', $incomingLetter->subject ?: '-'],
                            ['Prioritas', $priorityLabels[$incomingLetter->priority] ?? $incomingLetter->priority],
                        ] as [$label, $value])
                            <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                                <dt class="rs-detail-label small text-body-secondary">{{ $label }}</dt>
                                <dd>{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-5">
            <section class="card rs-card shadow-sm" aria-label="Form pemeriksaan surat masuk">
                <div class="card-header bg-body py-3">
                    <h2 class="h5 mb-0">Tujuan Penerusan</h2>
                </div>
                <div class="card-body p-3 p-md-4">
                    <form
                        method="POST"
                        action="{{ route('incoming-letters.review.store', $incomingLetter) }}"
                        data-confirmation
                        data-confirmation-title="Teruskan Surat"
                        data-confirmation-message="Surat akan diteruskan ke divisi yang dipilih dan pemeriksaan tidak dapat diulang."
                        data-confirmation-action-label="Teruskan Surat"
                        data-confirmation-variant="primary"
                        data-confirmation-icon="fa-share-from-square"
                        data-testid="incoming-letter-review-form"
                    >
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="destination_division_id">Divisi Tujuan <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden"> (wajib)</span></label>
                            <select
                                class="form-select @error('destination_division_id') is-invalid @enderror"
                                id="destination_division_id"
                                name="destination_division_id"
                                required
                            >
                                <option value="">Pilih Divisi Tujuan</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" @selected(old('destination_division_id') == $division->id)>
                                        {{ $division->name }}{{ $division->code ? ' ('.$division->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('destination_division_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="review_note">Catatan Pemeriksa <span class="text-body-secondary">(opsional)</span></label>
                            <textarea
                                class="form-control @error('review_note') is-invalid @enderror"
                                id="review_note"
                                name="review_note"
                                rows="6"
                                maxlength="2000"
                            >{{ old('review_note') }}</textarea>
                            <div class="form-text">Maksimal 2000 karakter.</div>
                            @error('review_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid d-sm-flex flex-wrap gap-2">
                            <a
                                class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2"
                                href="{{ route('incoming-letters.show', $incomingLetter) }}"
                            >
                                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                                <span>Kembali</span>
                            </a>
                            <button class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2" type="submit">
                                <i class="fa-solid fa-share-from-square" aria-hidden="true"></i>
                                <span>Teruskan Surat</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
