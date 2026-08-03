@extends('layouts.dashboard')

@section('title', 'Tugaskan Surat kepada Anggota')

@section('content')
    @php
        $statusLabels = [
            'baru_diterima' => 'Baru Diterima',
            'menunggu_pemeriksaan' => 'Menunggu Pemeriksaan',
            'diteruskan_ke_divisi' => 'Diteruskan ke Divisi',
            'ditugaskan_ke_anggota' => 'Ditugaskan ke Anggota',
        ];
        $priorityLabels = [
            'biasa' => 'Biasa',
            'segera' => 'Segera',
        ];
    @endphp

    <header class="rs-page-header mb-4">
        <h1 class="rs-page-title h3 mb-1">Tugaskan Surat kepada Anggota</h1>
        <p class="rs-page-description text-body-secondary mb-2">Nomor agenda {{ $incomingLetter->agenda_number }}</p>
        <span class="badge text-bg-success">
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
                                data-testid="incoming-letter-assignment-preview"
                            >
                                <p class="mb-0">Dokumen tidak dapat ditampilkan pada browser ini. Silakan buka preview dokumen.</p>
                            </object>
                        @elseif (str_starts_with($incomingLetter->document_mime_type, 'image/'))
                            <img
                                class="rs-document-image"
                                src="{{ route('incoming-letters.preview', $incomingLetter) }}"
                                alt="Preview {{ $incomingLetter->original_document_name }}"
                                data-testid="incoming-letter-assignment-preview"
                            >
                        @else
                            <p class="mb-0">Dokumen tidak dapat ditampilkan pada browser ini. Silakan buka preview dokumen.</p>
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
                            ['Tujuan Surat', $incomingLetter->addressed_to ?: '-'],
                            ['Tanggal Diterima', $incomingLetter->received_date?->format('d-m-Y') ?? '-'],
                            ['Perihal', $incomingLetter->subject ?: '-'],
                            ['Prioritas', $priorityLabels[$incomingLetter->priority] ?? $incomingLetter->priority],
                            ['Divisi Tujuan', $incomingLetter->destinationDivision?->name ?? 'Belum ditentukan'],
                        ] as [$label, $value])
                            <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                                <dt class="rs-detail-label small text-body-secondary">{{ $label }}</dt>
                                <dd>{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </section>

            <section class="card rs-card shadow-sm" aria-label="Hasil pemeriksaan surat masuk">
                <div class="card-header bg-body py-3">
                    <h2 class="h5 mb-0">Hasil Pemeriksaan</h2>
                </div>
                <div class="card-body p-3 p-md-4">
                    <dl class="row g-3 mb-0 rs-detail-list">
                        <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                            <dt class="rs-detail-label small text-body-secondary">Diperiksa oleh</dt>
                            <dd>{{ $incomingLetter->review?->reviewer?->name ?? '-' }}</dd>
                        </div>
                        <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                            <dt class="rs-detail-label small text-body-secondary">Tanggal Pemeriksaan</dt>
                            <dd>{{ $incomingLetter->review?->reviewed_at?->format('d-m-Y H:i') ?? '-' }}</dd>
                        </div>
                        <div class="col-12 rs-detail-item">
                            <dt class="rs-detail-label small text-body-secondary">Catatan Pemeriksa</dt>
                            <dd class="text-break">
                                @if (filled($incomingLetter->review?->review_note))
                                    {!! nl2br(e($incomingLetter->review->review_note)) !!}
                                @else
                                    Tidak ada catatan.
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-5">
            <section class="card rs-card shadow-sm" aria-label="Form penugasan surat masuk">
                <div class="card-header bg-body py-3">
                    <h2 class="h5 mb-0">Data Penugasan</h2>
                </div>
                <div class="card-body p-3 p-md-4">
                    <form
                        method="POST"
                        action="{{ route('incoming-letters.assignment.store', $incomingLetter) }}"
                        data-incoming-letter-submit-form
                        data-confirm-message="Tugaskan surat ini kepada anggota yang dipilih? Setelah disimpan, anggota tersebut menjadi penanggung jawab surat."
                        data-testid="incoming-letter-assignment-form"
                    >
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="assigned_to">Anggota Divisi</label>
                            <select
                                class="form-select @error('assigned_to') is-invalid @enderror"
                                id="assigned_to"
                                name="assigned_to"
                                required
                            >
                                <option value="">Pilih Anggota Divisi</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}" @selected(old('assigned_to') == $member->id)>
                                        {{ $member->name }} — {{ $incomingLetter->destinationDivision?->name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="instruction">Instruksi <span class="text-body-secondary">(opsional)</span></label>
                            <textarea
                                class="form-control @error('instruction') is-invalid @enderror"
                                id="instruction"
                                name="instruction"
                                rows="6"
                                maxlength="2000"
                            >{{ old('instruction') }}</textarea>
                            <div class="form-text">Maksimal 2000 karakter.</div>
                            @error('instruction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="due_date">Batas Waktu <span class="text-body-secondary">(opsional)</span></label>
                            <input
                                class="form-control @error('due_date') is-invalid @enderror"
                                id="due_date"
                                name="due_date"
                                type="date"
                                value="{{ old('due_date') }}"
                            >
                            @error('due_date')
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
                                <i class="fa-solid fa-user-check" aria-hidden="true"></i>
                                <span>Tugaskan Anggota</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
