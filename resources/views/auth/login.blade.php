<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <title>Masuk · Radarsurat</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-body-tertiary">
        <main class="container-fluid d-flex min-vh-100 align-items-center justify-content-center px-3 py-4">
            <div class="row w-100 mx-0 justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
                    <section class="card border-0 shadow-sm" aria-labelledby="login-title">
                        <div class="card-body p-4 p-sm-5">
                            <p class="mb-2 small fw-bold text-uppercase text-primary">Portal internal</p>
                            <h1 id="login-title" class="h2 mb-2 fw-bold text-primary">Masuk</h1>
                            <p class="mb-4 text-body-secondary">Masukkan email dan kata sandi untuk melanjutkan.</p>

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    <i class="fa-solid fa-circle-exclamation me-2" aria-hidden="true"></i>
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login.store') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="email">
                                        Email <span class="text-danger" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        id="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        name="email"
                                        type="email"
                                        value="{{ old('email') }}"
                                        autocomplete="email"
                                        required
                                        autofocus
                                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                        @error('email') aria-describedby="email-error" @enderror
                                    >
                                    @error('email')
                                        <div id="email-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="password">
                                        Kata sandi <span class="text-danger" aria-hidden="true">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input
                                            id="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            name="password"
                                            type="password"
                                            autocomplete="current-password"
                                            required
                                            data-password-input
                                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                            @error('password') aria-describedby="password-error" @enderror
                                        >
                                        <button
                                            class="btn btn-outline-secondary"
                                            type="button"
                                            data-password-toggle
                                            aria-controls="password"
                                            aria-label="Tampilkan kata sandi"
                                        >
                                            <i class="fa-solid fa-eye" aria-hidden="true" data-password-icon></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div id="password-error" class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check mb-4">
                                    <input id="remember" class="form-check-input" name="remember" type="checkbox" value="1">
                                    <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
                                </div>

                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="fa-solid fa-right-to-bracket me-2" aria-hidden="true"></i>
                                    Masuk
                                </button>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </main>

    </body>
</html>
