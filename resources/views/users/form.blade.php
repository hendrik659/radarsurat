@extends('layouts.dashboard')

@php($editing = isset($user))
@section('title', $editing ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
    <header class="rs-page-header mb-4">
        <h1 class="rs-page-title h3 mb-1">{{ $editing ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h1>
        <p class="rs-page-description text-body-secondary mb-0">
            {{ $editing ? 'Perbarui data akun tanpa menghapus riwayat pengguna.' : 'Buat akun pengguna baru.' }}
        </p>
    </header>

    <form
        class="card rs-card rs-form-card shadow-sm"
        method="POST"
        action="{{ $editing ? route('users.update', $user) : route('users.store') }}"
    >
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                @foreach ([
                    ['name', 'Nama', 'text'],
                    ['email', 'Email', 'email'],
                    ['phone', 'Telepon', 'text'],
                    ['employee_number', 'Nomor pegawai', 'text'],
                    ['position', 'Jabatan', 'text'],
                ] as [$name, $label, $type])
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="{{ $name }}">{{ $label }}</label>
                        <input
                            class="form-control @error($name) is-invalid @enderror"
                            id="{{ $name }}"
                            name="{{ $name }}"
                            type="{{ $type }}"
                            value="{{ old($name, $user->{$name} ?? '') }}"
                            @required(in_array($name, ['name', 'email']))
                        >
                        @error($name)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                <div class="col-12 col-md-6">
                    <label class="form-label" for="role_id">Role</label>
                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id ?? null) == $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="division_id">Divisi</label>
                    <select class="form-select @error('division_id') is-invalid @enderror" id="division_id" name="division_id">
                        <option value="">-</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected(old('division_id', $user->division_id ?? null) == $division->id)>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('division_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="is_active">Status</label>
                    <select
                        class="form-select @error('is_active') is-invalid @enderror"
                        id="is_active"
                        name="is_active"
                        @disabled($editing && auth()->user()->is($user))
                    >
                        <option value="1" @selected((string) old('is_active', $user->is_active ?? 1) === '1')>Aktif</option>
                        <option value="0" @selected((string) old('is_active', $user->is_active ?? 1) === '0')>Tidak aktif</option>
                    </select>
                    @if ($editing && auth()->user()->is($user))
                        <input type="hidden" name="is_active" value="1">
                        <div class="form-text">Akun yang sedang digunakan harus tetap aktif.</div>
                    @endif
                    @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="password">Kata sandi{{ $editing ? ' baru (opsional)' : '' }}</label>
                    <input
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        @required(! $editing)
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="password_confirmation">Konfirmasi kata sandi</label>
                    <input
                        class="form-control"
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        @required(! $editing)
                    >
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <button class="btn btn-primary d-inline-flex align-items-center gap-2" type="submit">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    <span>Simpan</span>
                </button>
                <a class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" href="{{ route('users.index') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <span>Batal</span>
                </a>
            </div>
        </div>
    </form>
@endsection
