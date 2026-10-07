<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
    @unless ($isCustom)
        <style>
            .nl p { margin:0 0 16px; line-height:1.6; }
            .nl h2 { margin:24px 0 12px; font-size:22px; line-height:1.3; color:#0900AA; }
            .nl h3 { margin:20px 0 10px; font-size:18px; line-height:1.3; color:#0900AA; }
            .nl a { color:#005bd3; }
            .nl table a { color:#ffffff !important; }
            .nl img { max-width:100%; height:auto; }
            .nl ul, .nl ol { margin:0 0 16px; padding-left:24px; line-height:1.6; }
        </style>
    @endunless
    {{-- CSS from a pasted HTML body: kept in the head, where mail apps read it. --}}
    {!! $styles !!}
</head>
<body style="margin:0;padding:0;background:#F3F5F6;">
    @if ($previewText)
        {{-- The grey snippet mail apps show next to the subject. Hidden in the body itself. --}}
        <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#F3F5F6;font-size:1px;line-height:1px;">{{ $previewText }}</div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F3F5F6;">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;">
                    <tr>
                        <td style="background:#0900AA;padding:20px 32px;font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:bold;color:#ffffff;">
                            Apotek Inofarma
                        </td>
                    </tr>

                    @if ($isTest)
                        <tr>
                            <td style="background:#FFF8E1;padding:10px 32px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#B35400;">
                                Ini adalah email uji coba. Belum dikirim ke pelanggan.
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:32px;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#222222;">
                            <div class="{{ $isCustom ? 'nl-custom' : 'nl' }}">{!! $body !!}</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="border-top:1px solid #eeeeee;padding:20px 32px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#666666;">
                            Anda menerima email ini karena berlangganan newsletter Apotek Inofarma.<br>
                            <a href="{{ $unsubscribeUrl }}" style="color:#005bd3;">Berhenti berlangganan</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
