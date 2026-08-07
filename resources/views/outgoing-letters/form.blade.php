@extends('layouts.dashboard')

@section('title', 'Tambah Surat Keluar')

@section('content')
    <header class="rs-page-header mb-4">
        <h1 class="rs-page-title h3 mb-1">Tambah Surat Keluar</h1>
        <p class="rs-page-description text-body-secondary mb-0">
            Catat dokumen final. Setelah disimpan, surat keluar langsung menjadi data hanya-baca.
        </p>
    </header>

    <form
        class="card rs-card rs-form-card shadow-sm"
        method="POST"
        action="{{ route('outgoing-letters.store') }}"
        enctype="multipart/form-data"
        novalidate
    >
        @csrf

        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="letter_number">Nomor Surat</label>
                    <input
                        class="form-control @error('letter_number') is-invalid @enderror"
                        id="letter_number"
                        name="letter_number"
                        type="text"
                        value="{{ old('letter_number') }}"
                        maxlength="100"
                        required
                    >
                    @error('letter_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="letter_date">Tanggal Surat</label>
                    <input
                        class="form-control @error('letter_date') is-invalid @enderror"
                        id="letter_date"
                        name="letter_date"
                        type="date"
                        value="{{ old('letter_date') }}"
                        required
                    >
                    @error('letter_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="recipient_name">Tujuan</label>
                    <input
                        class="form-control @error('recipient_name') is-invalid @enderror"
                        id="recipient_name"
                        name="recipient_name"
                        type="text"
                        value="{{ old('recipient_name') }}"
                        maxlength="255"
                        required
                    >
                    @error('recipient_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="recipient_address">Alamat Tujuan</label>
                    <textarea
                        class="form-control @error('recipient_address') is-invalid @enderror"
                        id="recipient_address"
                        name="recipient_address"
                        rows="3"
                        maxlength="2000"
                    >{{ old('recipient_address') }}</textarea>
                    @error('recipient_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="subject">Perihal</label>
                    <input
                        class="form-control @error('subject') is-invalid @enderror"
                        id="subject"
                        name="subject"
                        type="text"
                        value="{{ old('subject') }}"
                        maxlength="255"
                        required
                    >
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="document">Dokumen</label>
                    <input
                        class="form-control @error('document') is-invalid @enderror"
                        id="document"
                        name="document"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                        data-outgoing-letter-document
                        required
                    >
                    <div class="form-text">PDF, JPG, JPEG, atau PNG. Ukuran maksimum 5 MB.</div>
                    @error('document')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="alert alert-danger d-none mt-3 mb-0" role="alert" data-outgoing-document-error></div>

            <section
                class="card border mt-4 d-none"
                aria-label="Preview dokumen"
                data-outgoing-document-preview-area
            >
                <div class="card-header bg-body d-flex flex-column flex-sm-row justify-content-between gap-2">
                    <strong>Preview Dokumen</strong>
                    <span class="small text-body-secondary" data-outgoing-document-name>-</span>
                </div>
                <div class="card-body p-3">
                    <dl class="row g-2 small mb-3">
                        <div class="col-12 col-sm-6">
                            <dt class="text-body-secondary">Tipe file</dt>
                            <dd class="mb-0" data-outgoing-document-type>-</dd>
                        </div>
                        <div class="col-12 col-sm-6">
                            <dt class="text-body-secondary">Ukuran file</dt>
                            <dd class="mb-0" data-outgoing-document-size>-</dd>
                        </div>
                    </dl>
                    <div class="rs-document-preview" data-outgoing-document-preview-content></div>
                </div>
            </section>

            <div class="d-grid d-sm-flex flex-wrap gap-2 mt-4">
                <button class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2" type="submit">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    <span>Simpan Surat Keluar</span>
                </button>
                <a
                    class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2"
                    href="{{ route('outgoing-letters.index') }}"
                >
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <span>Batal</span>
                </a>
            </div>
        </div>
    </form>
@endsection
