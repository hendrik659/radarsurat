<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <title>Daftar - Radarsurat</title>
        <style>
            :root { --primary: #3182CE; --primary-dark: #2C5282; --primary-muted: #3B6B9B; --ink: #1f2433; --muted: #7c8395; --line: #dfe2e9; --canvas: #f6f7fb; }
            * { box-sizing: border-box; }
            body { min-width: 320px; min-height: 100vh; margin: 0; color: var(--ink); background: var(--canvas); font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
            .page { display: grid; min-height: 100vh; padding: 32px; place-items: center; }
            .register-card { width: min(100%, 560px); overflow: hidden; background: #fff; border: 1px solid rgba(222, 225, 234, .72); border-radius: 20px; box-shadow: 0 24px 60px rgba(49, 60, 95, .10); }
            .form-panel { display: flex; align-items: center; justify-content: center; padding: 48px; }
            .form-content { width: min(100%, 420px); }
            .eyebrow { margin: 0 0 10px; color: var(--primary); font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
            h1 { margin: 0; color: var(--primary); font-size: 39px; font-weight: 800; letter-spacing: -.055em; line-height: 1; }
            .subtitle { margin: 12px 0 28px; color: var(--muted); font-size: 15px; font-weight: 500; line-height: 1.55; }
            .alert { margin: 0 0 20px; padding: 12px 14px; color: #b3353b; border: 1px solid #ffd4d6; border-radius: 10px; background: #fff5f5; font-size: 14px; line-height: 1.45; }
            .field { margin-bottom: 17px; }
            label { display: block; margin-bottom: 8px; color: #272c38; font-size: 14px; font-weight: 650; }
            .required { color: #e0475d; }
            .input-wrap { position: relative; }
            input { width: 100%; height: 47px; padding: 0 15px; border: 1px solid var(--line); border-radius: 11px; outline: none; color: var(--ink); background: #fff; font: inherit; transition: border-color .18s, box-shadow .18s; }
            input[data-password-input] { padding-right: 52px; }
            input::placeholder { color: #b3b8c5; }
            input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(49, 130, 206, .12); }
            input[aria-invalid="true"] { border-color: #e05a66; }
            .password-toggle { position: absolute; top: 1px; right: 1px; display: grid; width: 45px; height: 45px; padding: 0; place-items: center; border: 0; border-left: 1px solid var(--line); border-radius: 0 10px 10px 0; color: #9499a5; background: transparent; cursor: pointer; }
            .password-toggle:hover { color: var(--primary); background: #fafbff; }
            .password-toggle:focus-visible { outline: 3px solid rgba(49, 130, 206, .35); outline-offset: -3px; }
            .submit-button { width: 100%; height: 48px; margin-top: 7px; border: 0; border-radius: 10px; color: #fff; background: var(--primary); box-shadow: 0 8px 16px rgba(49,130,206,.22); cursor: pointer; font-size: 14px; font-weight: 750; letter-spacing: .015em; text-transform: uppercase; transition: background .18s, transform .18s, box-shadow .18s; }
            .submit-button:hover { background: var(--primary-dark); box-shadow: 0 10px 20px rgba(44,82,130,.28); transform: translateY(-1px); }
            .submit-button:focus-visible { outline: 3px solid rgba(49,130,206,.4); outline-offset: 3px; }
            .auth-switch { margin: 22px 0 0; color: #7b8292; font-size: 13px; text-align: center; }
            .auth-switch a { color: var(--primary); font-weight: 750; text-decoration: none; }
            .auth-switch a:hover { text-decoration: underline; }
            @media (max-width: 800px) { .page { padding: 20px; } .register-card { max-width: 520px; border-radius: 16px; } .form-panel { padding: 40px 28px; } }
            @media (max-width: 430px) { .page { padding: 0; } .register-card { min-height: 100vh; border: 0; border-radius: 0; } .form-panel { padding: 38px 24px; } h1 { font-size: 35px; } }
        </style>
    </head>
    <body>
        <main class="page">
            <section class="register-card" aria-labelledby="register-title">
                <div class="form-panel">
                    <div class="form-content">
                        <p class="eyebrow">Portal internal</p>
                        <h1 id="register-title">Buat akun</h1>
                        <p class="subtitle">Lengkapi data berikut untuk bergabung ke Radarsurat.</p>

                        @if ($errors->any())
                            <div class="alert" role="alert">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('register.store') }}">
                            @csrf
                            <div class="field">
                                <label for="name">Nama lengkap <span class="required" aria-hidden="true">*</span></label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                            </div>
                            <div class="field">
                                <label for="email">Email <span class="required" aria-hidden="true">*</span></label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                            </div>
                            <div class="field">
                                <label for="password">Kata sandi <span class="required" aria-hidden="true">*</span></label>
                                <div class="input-wrap">
                                    <input id="password" name="password" type="password" autocomplete="new-password" required data-password-input aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                                    <button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-label="Tampilkan kata sandi"><svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M2.5 12s3.4-5.5 9.5-5.5 9.5 5.5 9.5 5.5-3.4 5.5-9.5 5.5S2.5 12 2.5 12Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.8"/></svg></button>
                                </div>
                            </div>
                            <div class="field">
                                <label for="password_confirmation">Konfirmasi kata sandi <span class="required" aria-hidden="true">*</span></label>
                                <div class="input-wrap">
                                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required data-password-input>
                                    <button class="password-toggle" type="button" data-password-toggle aria-controls="password_confirmation" aria-label="Tampilkan konfirmasi kata sandi"><svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M2.5 12s3.4-5.5 9.5-5.5 9.5 5.5 9.5 5.5-3.4 5.5-9.5 5.5S2.5 12 2.5 12Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.8"/></svg></button>
                                </div>
                            </div>
                            <button class="submit-button" type="submit">Daftar</button>
                        </form>

                        <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk sekarang</a></p>
                    </div>
                </div>
            </section>
        </main>
        <script>
            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.getAttribute('aria-controls'));
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    button.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                });
            });
        </script>
    </body>
</html>
