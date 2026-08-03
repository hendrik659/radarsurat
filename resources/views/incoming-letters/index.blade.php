@extends('layouts.dashboard')

@section('title', 'Surat Masuk')

@section('content')
    @php
        $isAdminSurat = auth()->user()?->role?->slug === 'admin_surat';
        $statusLabels = [
            'baru_diterima' => 'Baru Diterima',
            'menunggu_pemeriksaan' => 'Menunggu Pemeriksaan',
            'diteruskan_ke_divisi' => 'Diteruskan ke Divisi',
        ];
        $statusBadgeClasses = [
            'baru_diterima' => 'text-bg-info',
            'menunggu_pemeriksaan' => 'text-bg-warning',
            'diteruskan_ke_divisi' => 'text-bg-success',
        ];
        $priorityLabels = [
            'biasa' => 'Biasa',
            'segera' => 'Segera',
        ];
        $hasFilters = collect($filters)->contains(fn ($value) => filled($value));
    @endphp

    <header class="rs-page-header d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="rs-page-title h3 mb-1">Surat Masuk</h1>
            <p class="rs-page-description text-body-secondary mb-0">Kelola surat masuk yang diterima oleh Radarsurat.</p>
        </div>
        @if ($isAdminSurat)
            <a
                class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2"
                href="{{ route('incoming-letters.create') }}"
                data-testid="incoming-letter-create-link"
            >
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Tambah Surat Masuk</span>
            </a>
        @endif
    </header>

    <section class="card rs-card shadow-sm mb-4" aria-label="Pencarian dan filter surat masuk">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('incoming-letters.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-xl-4">
                    <label class="form-label" for="search">Pencarian</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Nomor agenda, nomor surat, pengirim, atau perihal"
                    >
                </div>
                <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua Status</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                    <label class="form-label" for="priority">Prioritas</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="">Semua Prioritas</option>
                        @foreach ($priorityLabels as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['priority'] ?? null) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                    <label class="form-label" for="destination_division_id">Divisi Tujuan</label>
                    <select class="form-select" id="destination_division_id" name="destination_division_id">
                        <option value="">Semua Divisi</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected(($filters['destination_division_id'] ?? null) == $division->id)>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                    <label class="form-label" for="received_date">Tanggal Diterima</label>
                    <input
                        class="form-control"
                        id="received_date"
                        name="received_date"
                        type="date"
                        value="{{ $filters['received_date'] ?? '' }}"
                    >
                </div>
                <div class="col-12">
                    <div class="d-grid d-sm-flex gap-2">
                        <button class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2" type="submit">
                            <i class="fa-solid fa-filter" aria-hidden="true"></i>
                            <span>Terapkan</span>
                        </button>
                        <a
                            class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2"
                            href="{{ route('incoming-letters.index') }}"
                        >
                            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card rs-card shadow-sm overflow-hidden" aria-label="Daftar surat masuk">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 rs-table rs-incoming-table">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nomor Agenda</th>
                        <th scope="col">Nomor Surat</th>
                        <th scope="col">Tanggal Diterima</th>
                        <th scope="col">Pengirim</th>
                        <th scope="col">Perihal</th>
                        <th scope="col">Divisi Tujuan</th>
                        <th scope="col">Prioritas</th>
                        <th scope="col">Status</th>
                        <th class="text-center" scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incomingLetters as $incomingLetter)
                        <tr>
                            <td class="fw-semibold text-body-emphasis">{{ $incomingLetter->agenda_number ?: '-' }}</td>
                            <td>{{ $incomingLetter->letter_number ?: '-' }}</td>
                            <td>{{ $incomingLetter->received_date?->format('d-m-Y') ?? '-' }}</td>
                            <td>{{ $incomingLetter->sender_name ?: '-' }}</td>
                            <td>{{ $incomingLetter->subject ?: '-' }}</td>
                            <td>{{ $incomingLetter->destinationDivision?->name ?? 'Belum ditentukan' }}</td>
                            <td>
                                <span class="badge {{ $incomingLetter->priority === 'segera' ? 'text-bg-danger' : 'text-bg-primary' }}">
                                    {{ $priorityLabels[$incomingLetter->priority] ?? $incomingLetter->priority }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $statusBadgeClasses[$incomingLetter->status] ?? 'text-bg-secondary' }}">
                                    {{ $statusLabels[$incomingLetter->status] ?? $incomingLetter->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="rs-incoming-actions d-flex flex-wrap justify-content-center align-items-center gap-2">
                                    <a
                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                        href="{{ route('incoming-letters.show', $incomingLetter) }}"
                                    >
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                        <span>Detail</span>
                                    </a>
                                    <a
                                        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                        href="{{ route('incoming-letters.preview', $incomingLetter) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <i class="fa-solid fa-file" aria-hidden="true"></i>
                                        <span>Preview</span>
                                    </a>
                                    <a
                                        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                        href="{{ route('incoming-letters.download', $incomingLetter) }}"
                                    >
                                        <i class="fa-solid fa-download" aria-hidden="true"></i>
                                        <span>Download</span>
                                    </a>
                                    @if ($isAdminSurat && $incomingLetter->status === 'baru_diterima')
                                        <a
                                            class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1"
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
                                            <button class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1" type="submit">
                                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                                                <span>Kirim untuk Pemeriksaan</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="rs-empty-state text-center text-body-secondary py-5" colspan="9">
                                <i class="fa-solid fa-envelope-open d-block fs-3 mb-2" aria-hidden="true"></i>
                                @if ($hasFilters)
                                    Surat Masuk yang dicari tidak ditemukan.
                                @else
                                    <span class="d-block mb-3">Belum ada Surat Masuk.</span>
                                    @if ($isAdminSurat)
                                        <a class="btn btn-sm btn-primary" href="{{ route('incoming-letters.create') }}">Tambah Surat Masuk</a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($incomingLetters->hasPages())
            <div class="card-footer bg-body d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 py-3">
                <span class="small text-body-secondary">
                    Halaman {{ $incomingLetters->currentPage() }} dari {{ $incomingLetters->lastPage() }}
                    ({{ $incomingLetters->total() }} surat masuk)
                </span>
                <div class="rs-pagination">
                    {{ $incomingLetters->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </section>
@endsection
