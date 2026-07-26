@extends('layouts.dashboard')

@php($editing = isset($user))
@section('title', $editing ? 'Edit Pengguna' : 'Tambah Pengguna')

@push('styles')
    <style>
        .form-card { max-width: 900px; padding: 24px; border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 8px 26px rgba(31, 41, 55, .05); }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .field label { display: block; margin-bottom: 7px; color: #475569; font-size: 13px; font-weight: 700; }
        .field input, .field select { width: 100%; height: 42px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font: inherit; }
        .field-error { margin: 6px 0 0; color: #b91c1c; font-size: 13px; }
        .form-actions { display: flex; gap: 10px; margin-top: 24px; }
        .button { display: inline-flex; align-items: center; min-height: 42px; padding: 0 15px; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; font: inherit; font-weight: 700; text-decoration: none; }
        .button-primary { border-color: var(--primary); color: #fff; background: var(--primary); }
        .button-secondary { color: #475569; background: #fff; }
        @media (max-width: 680px) { .form-grid { grid-template-columns: 1fr; } }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <h1>{{ $editing ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h1>
        <p>{{ $editing ? 'Perbarui data akun tanpa menghapus riwayat pengguna.' : 'Buat akun pengguna baru.' }}</p>
    </div>

    <form class="form-card" method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="form-grid">
            @foreach ([
                ['name', 'Nama', 'text'],
                ['email', 'Email', 'email'],
                ['phone', 'Telepon', 'text'],
                ['employee_number', 'Nomor pegawai', 'text'],
                ['position', 'Jabatan', 'text'],
            ] as [$name, $label, $type])
                <div class="field">
                    <label for="{{ $name }}">{{ $label }}</label>
                    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $user->{$name} ?? '') }}" @required(in_array($name, ['name', 'email']))>
                    @error($name)<p class="field-error">{{ $message }}</p>@enderror
                </div>
            @endforeach

            <div class="field">
                <label for="role_id">Role</label>
                <select id="role_id" name="role_id" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id ?? null) == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="division_id">Divisi</label>
                <select id="division_id" name="division_id">
                    <option value="">-</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}" @selected(old('division_id', $user->division_id ?? null) == $division->id)>{{ $division->name }}</option>
                    @endforeach
                </select>
                @error('division_id')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="is_active">Status</label>
                <select id="is_active" name="is_active" @disabled($editing && auth()->user()->is($user))>
                    <option value="1" @selected((string) old('is_active', $user->is_active ?? 1) === '1')>Aktif</option>
                    <option value="0" @selected((string) old('is_active', $user->is_active ?? 1) === '0')>Tidak aktif</option>
                </select>
                @if ($editing && auth()->user()->is($user))
                    <input type="hidden" name="is_active" value="1">
                @endif
                @error('is_active')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="password">Kata sandi{{ $editing ? ' baru (opsional)' : '' }}</label>
                <input id="password" name="password" type="password" autocomplete="new-password" @required(! $editing)>
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Konfirmasi kata sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $editing)>
            </div>
        </div>
        <div class="form-actions">
            <button class="button button-primary" type="submit">Simpan</button>
            <a class="button button-secondary" href="{{ route('users.index') }}">Batal</a>
        </div>
    </form>
@endsection
