<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Berhenti Berlangganan - Apotek Inofarma</title>
    <style>
        body { margin:0; background:#F3F5F6; font-family:Arial,Helvetica,sans-serif; color:#222; }
        main { max-width:460px; margin:64px auto; background:#fff; border:1px solid #eee; padding:32px; text-align:center; }
        h1 { margin:0 0 12px; font-size:22px; color:#0900AA; }
        p { margin:0 0 20px; line-height:1.6; color:#555; font-size:14px; }
        button, a.btn { display:inline-block; border:0; background:#0900AA; color:#fff; padding:12px 28px; font-size:14px; font-weight:bold; text-decoration:none; cursor:pointer; }
        a.plain { color:#005bd3; font-size:13px; }
    </style>
</head>
<body>
    <main>
        @if ($done)
            <h1>Anda sudah berhenti berlangganan</h1>
            <p>{{ $subscriber->email }} tidak akan menerima newsletter Apotek Inofarma lagi.</p>
            <a class="plain" href="{{ url('/') }}">Kembali ke beranda</a>
        @else
            <h1>Berhenti berlangganan?</h1>
            <p>Anda tidak akan lagi menerima newsletter Apotek Inofarma di <strong>{{ $subscriber->email }}</strong>.</p>
            <form method="POST" action="{{ route('newsletter.unsubscribe.store', $subscriber->unsubscribe_token) }}">
                @csrf
                <button type="submit">Ya, berhenti berlangganan</button>
            </form>
            <p style="margin-top:20px;"><a class="plain" href="{{ url('/') }}">Batal, kembali ke beranda</a></p>
        @endif
    </main>
</body>
</html>
