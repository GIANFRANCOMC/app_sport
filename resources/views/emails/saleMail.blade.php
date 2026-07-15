<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mail->subject }}</title>
</head>
<body style="margin:0;padding:0;background:#f7f8fa;font-family:Arial,Helvetica,sans-serif;color:#1a1a35;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f7f8fa;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="height:4px;background:#2899e5;"></td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px 10px;">
                            <p style="margin:0 0 6px;font-size:12px;font-weight:700;text-transform:uppercase;color:#2899e5;letter-spacing:.4px;">
                                {{ $mail->company_name ?? config("app.name") }}
                            </p>
                            <h1 style="margin:0;font-size:20px;line-height:1.3;color:#1a1a35;">
                                {{ $mail->subject }}
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 28px 24px;font-size:15px;line-height:1.6;color:#334155;">
                            @if(!empty($mail->customer_name))
                                <p style="margin:0 0 12px;color:#1a1a35;font-weight:700;">
                                    Hola, {{ $mail->customer_name }}.
                                </p>
                            @endif
                            @if(!empty($mail->branch_name))
                                <p style="margin:0 0 12px;color:#64748b;font-size:13px;">
                                    Sucursal: <strong style="color:#1a1a35;">{{ $mail->branch_name }}</strong>
                                </p>
                            @endif
                            {!! nl2br(e($mail->message)) !!}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 28px;background:#f7f8fa;color:#64748b;font-size:12px;">
                            Enviado por {{ $mail->company_name ?? config("app.name") }} con {{ $mail->owner_app->commercial_name ?? "BLAPOS" }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
