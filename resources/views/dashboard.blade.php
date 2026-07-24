<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard · Radarsurat</title>
        <style>
            body { margin: 0; color: #252b3a; background: #f6f7fb; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
            main { display: grid; min-height: 100vh; padding: 28px; place-items: center; }
            section { width: min(100%, 680px); padding: 46px; border: 1px solid #e2e5ed; border-radius: 18px; background: #fff; box-shadow: 0 18px 45px rgba(49, 60, 95, .08); }
            p { color: #747c8e; line-height: 1.65; }
            h1 { margin: 0; font-size: 32px; letter-spacing: -.04em; }
            form { margin-top: 28px; }
            button { padding: 11px 15px; border: 1px solid #c8d9e8; border-radius: 9px; color: #2C5282; background: #fff; cursor: pointer; font: inherit; font-weight: 700; }
            button:hover { color: #fff; background: #3182CE; }
        </style>
    </head>
    <body>
        <main>
            <section>
                <h1>Selamat datang, {{ auth()->user()->name }}.</h1>
                <p>Anda berhasil masuk ke Radarsurat. Dashboard operasional dapat dikembangkan dari halaman ini.</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            </section>
        </main>
    </body>
</html>
