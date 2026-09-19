<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Backup Data {{ $moduleName }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #334155;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;">
        <tr>
            <td style="background: #4f46e5; padding: 20px 24px; color: #ffffff;">
                <h2 style="margin: 0; font-size: 20px; font-weight: 700;">SarabaBisa - File Backup Data</h2>
                <p style="margin: 4px 0 0 0; font-size: 13px; opacity: 0.9;">Arsip Data & Pengurangan Beban Server</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="font-size: 15px; margin-top: 0;">Halo,</p>
                <p style="font-size: 14px; line-height: 1.6;">
                    Berikut terlampir file backup data <strong>.sql</strong> untuk modul <strong>{{ $moduleName }}</strong>.
                    File ini berisi data siap restore jika sewaktu-waktu dibutuhkan kembali.
                </p>

                <table width="100%" style="margin: 20px 0; border-collapse: collapse; font-size: 13px;">
                    <tr style="background-color: #f1f5f9;">
                        <td style="padding: 10px 14px; font-weight: 600; border: 1px solid #e2e8f0; width: 35%;">Modul</td>
                        <td style="padding: 10px 14px; border: 1px solid #e2e8f0;">{{ $moduleName }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 14px; font-weight: 600; border: 1px solid #e2e8f0;">Cabang</td>
                        <td style="padding: 10px 14px; border: 1px solid #e2e8f0;">{{ $cabangName }}</td>
                    </tr>
                    <tr style="background-color: #f1f5f9;">
                        <td style="padding: 10px 14px; font-weight: 600; border: 1px solid #e2e8f0;">Periode Tanggal</td>
                        <td style="padding: 10px 14px; border: 1px solid #e2e8f0;">{{ $startDate }} s/d {{ $endDate }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 14px; font-weight: 600; border: 1px solid #e2e8f0;">Total Record</td>
                        <td style="padding: 10px 14px; border: 1px solid #e2e8f0;">{{ number_format($recordCount, 0, ',', '.') }} baris data</td>
                    </tr>
                    <tr style="background-color: #f1f5f9;">
                        <td style="padding: 10px 14px; font-weight: 600; border: 1px solid #e2e8f0;">Nama File</td>
                        <td style="padding: 10px 14px; border: 1px solid #e2e8f0;"><code>{{ $fileName }}</code></td>
                    </tr>
                </table>

                <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; margin: 20px 0; border-radius: 4px; font-size: 13px; color: #92400e;">
                    <strong>PENTING:</strong> Simpan dan unduh file backup terlampir ke penyimpanan lokal/drive aman Anda sebelum melakukan penghapusan data pada modul terkait.
                </div>

                <p style="font-size: 13px; color: #64748b; margin-bottom: 0;">
                    Email ini dikirim secara otomatis oleh sistem SarabaBisa.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
