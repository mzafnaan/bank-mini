<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Bank Mini</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Main Card -->
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="max-width: 480px; width: 100%; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 28px 32px; text-align: center;">
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                <tr>
                                    <td style="width: 36px; height: 36px; background-color: rgba(255,255,255,0.2); border-radius: 8px; text-align: center; vertical-align: middle;">
                                        <span style="font-size: 18px; color: #ffffff; font-weight: 700;">B</span>
                                    </td>
                                    <td style="padding-left: 10px;">
                                        <span style="font-size: 18px; font-weight: 700; color: #ffffff; letter-spacing: -0.3px;">Bank Mini</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px 32px 16px;">
                            <p style="margin: 0 0 6px; font-size: 18px; font-weight: 700; color: #0f172a;">
                                Verifikasi Email Anda
                            </p>
                            <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.5;">
                                Halo <strong style="color: #334155;">{{ $customerName }}</strong>, konfirmasi alamat email ini untuk mengaktifkannya sebagai email pemulihan akun Anda.
                            </p>
                        </td>
                    </tr>

                    <!-- Button -->
                    <tr>
                        <td style="padding: 16px 32px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $verificationUrl }}" target="_blank"
                                           style="display: inline-block; padding: 12px 36px; background-color: #2563eb; color: #ffffff; font-size: 13px; font-weight: 600; text-decoration: none; border-radius: 8px; letter-spacing: 0.2px;">
                                            Verifikasi Email Saya
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 0 32px;">
                            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 0;">
                        </td>
                    </tr>

                    <!-- Info -->
                    <tr>
                        <td style="padding: 20px 32px 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="width: 6px; height: 6px; background-color: #cbd5e1; border-radius: 50%; vertical-align: top; padding-top: 5px;"></td>
                                    <td style="padding-left: 10px; font-size: 12px; color: #94a3b8; line-height: 1.6;">
                                        Tautan ini berlaku selama <strong style="color: #64748b;">60 menit</strong>.
                                    </td>
                                </tr>
                                <tr><td colspan="2" style="height: 6px;"></td></tr>
                                <tr>
                                    <td style="width: 6px; height: 6px; background-color: #cbd5e1; border-radius: 50%; vertical-align: top; padding-top: 5px;"></td>
                                    <td style="padding-left: 10px; font-size: 12px; color: #94a3b8; line-height: 1.6;">
                                        Jika Anda tidak merasa meminta ini, abaikan email ini.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Footer -->
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="max-width: 480px; width: 100%;">
                    <tr>
                        <td style="padding: 20px 32px; text-align: center;">
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                &copy; {{ date('Y') }} Bank Mini Sekolah &middot; Email ini dikirim secara otomatis
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
