@extends('layouts.dashboard')

@section('title', 'Surat Keluar')

@section('content')
    @php($hasFilters = collect($filters)->contains(fn ($value) => filled($value)))

    <header class="rs-page-header d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="rs-page-title h3 mb-1">Surat Keluar</h1>
            <p class="rs-page-description text-body-secondary mb-0">Catat dan kelola dokumen surat keluar final setiap divisi.</p>
        </div>
        @can('create', App\Models\OutgoingLetter::class)
            <a
                class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2"
                href="{{ route('outgoing-letters.create') }}"
                data-testid="outgoing-letter-create-link"
            >
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Tambah Surat Keluar</span>
            </a>
        @endcan
    </header>

    <section class="card rs-card shadow-sm mb-4" aria-label="Pencarian dan filter surat keluar">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('outgoing-letters.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-6">
                    <label class="form-label" for="search">Pencarian</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Kode, nomor surat, tujuan, atau perihal"
                    >
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="division_id">Divisi</label>
                    <select class="form-select" id="division_id" name="division_id">
                        <option value="">Semua Divisi</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected(($filters['division_id'] ?? null) == $division->id)>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="letter_date">Tanggal Surat</label>
                    <input
                        class="form-control"
                        id="letter_date"
                        name="letter_date"
                        type="date"
                        value="{{ $filters['letter_date'] ?? '' }}"
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
                            href="{{ route('outgoing-letters.index') }}"
                        >
                            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card rs-card shadow-sm overflow-hidden" aria-label="Daftar surat keluar">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 rs-table rs-outgoing-table">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Kode Sistem</th>
                        <th scope="col">Nomor Surat</th>
                        <th scope="col">Tanggal Surat</th>
                        <th scope="col">Tujuan</th>
                        <th scope="col">Perihal</th>
                        <th scope="col">Divisi</th>
                        <th class="text-center" scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($outgoingLetters as $outgoingLetter)
                        <tr>
                            <td class="fw-semibold text-body-emphasis">{{ $outgoingLetter->reference_code ?: '-' }}</td>
                            <td>{{ $outgoingLetter->letter_number ?: '-' }}</td>
                            <td>{{ $outgoingLetter->letter_date?->format('d-m-Y') ?? '-' }}</td>
                            <td>{{ $outgoingLetter->recipient_name ?: '-' }}</td>
                            <td>{{ $outgoingLetter->subject ?: '-' }}</td>
                            <td>{{ $outgoingLetter->division?->name ?? '-' }}</td>
                            <td class="text-center">
                                <div class="rs-outgoing-actions d-flex flex-wrap justify-content-center align-items-center gap-2">
                                    <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('outgoing-letters.show', $outgoingLetter) }}">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                        <span>Detail</span>
                                    </a>
                                    <a
                                        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                        href="{{ route('outgoing-letters.preview', $outgoingLetter) }}"
                                        target="_blank"
                                        rel="noopener"
                                        data-testid="outgoing-letter-preview-link"
                                    >
                                        <i class="fa-solid fa-file" aria-hidden="true"></i>
                                        <span>Preview</span>
                                    </a>
                                    <a
                                        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                        href="{{ route('outgoing-letters.download', $outgoingLetter) }}"
                                        data-testid="outgoing-letter-download-link"
                                    >
                                        <i class="fa-solid fa-download" aria-hidden="true"></i>
                                        <span>Download</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-5 text-center text-body-secondary" data-testid="outgoing-letter-empty-state">
                                <i class="fa-regular fa-folder-open d-block fs-2 mb-2" aria-hidden="true"></i>
                                {{ $hasFilters ? 'Tidak ada surat keluar yang sesuai pencarian atau filter.' : 'Belum ada surat keluar yang dicatat.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($outgoingLetters->hasPages())
            <div class="card-footer bg-body p-3">
                {{ $outgoingLetters->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
@endsection
