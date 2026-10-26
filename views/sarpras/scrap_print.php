<?php
// views/sarpras/scrap_print.php
use Core\SecurityHelper;

$items = $items ?? [];

// Helper untuk embed gambar sebagai data URI agar rendering instan & anti-stuck loading
if (!function_exists('resolveEmbeddedImage')) {
    function resolveEmbeddedImage(?string $path): ?string {
        if (empty($path)) return null;
        $clean = ltrim(str_replace(['..', '\\'], ['', '/'], $path), '/');
        $rootDir = dirname(__DIR__, 2);
        
        // Daftar kemungkinan path di server
        $candidates = [
            $rootDir . '/public/' . $clean,
            $rootDir . '/storage/uploads/' . $clean,
            $rootDir . '/public/uploads/' . preg_replace('#^(public/|uploads/|storage/uploads/)#', '', $clean),
            $rootDir . '/storage/uploads/' . preg_replace('#^(public/|uploads/|storage/uploads/)#', '', $clean),
            $rootDir . '/' . $clean,
        ];

        foreach ($candidates as $cand) {
            if (!empty($cand) && file_exists($cand) && is_file($cand)) {
                $ext = strtolower(pathinfo($cand, PATHINFO_EXTENSION));
                $mime = match($ext) {
                    'png'  => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'webp' => 'image/webp',
                    'svg'  => 'image/svg+xml',
                    default => 'image/png'
                };
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($cand));
            }
        }
        return null;
    }
}

// Logo Yayasan & Logo Unit
$yayasanLogoData = resolveEmbeddedImage('public/images/logo_alkahfi.png') 
                ?? resolveEmbeddedImage('uploads/logos/app_logo_1790375160.png');

$unitLogoData = !empty($report['logo_path']) ? resolveEmbeddedImage($report['logo_path']) : null;
$primaryLogoData = $unitLogoData ?: $yayasanLogoData;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Pernyataan Barang Scrap - <?= SecurityHelper::escape($report['report_number']) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: Arial, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, sans-serif;
            color: #0f172a;
            background: #e2e8f0;
            margin: 0;
            padding: 24px 12px;
            font-size: 10pt;
            line-height: 1.35;
        }
        .no-print-bar {
            max-width: 820px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }
        .btn-back {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .btn-print {
            background: #dc2626;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
        }
        .btn-print:hover {
            background: #b91c1c;
        }
        .sheet {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px 36px;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #cbd5e1;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }

        /* HEADER KOP RESMI SESUAI LAMPIRAN 2 */
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-logo {
            width: 78px;
            text-align: left;
        }
        .header-logo-img {
            max-width: 75px;
            max-height: 75px;
            object-fit: contain;
            display: block;
        }
        .header-unit {
            font-size: 13pt;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .header-sub {
            font-size: 9.5pt;
            color: #047857;
            font-weight: 700;
            margin: 2px 0 3px 0;
        }
        .header-address {
            font-size: 8pt;
            color: #475569;
            margin: 0;
            line-height: 1.25;
        }
        .form-no-box {
            width: 180px;
            text-align: right;
            vertical-align: middle;
        }
        .form-no-badge {
            border: 1.5px solid #0f172a;
            padding: 5px 10px;
            display: inline-block;
            background: #f8fafc;
            font-size: 9.5pt;
            font-weight: 800;
            color: #0f172a;
        }
        .ref-text {
            font-size: 8pt;
            color: #475569;
            margin-top: 3px;
        }

        /* JUDUL DOKUMEN */
        .doc-title-box {
            text-align: center;
            margin: 12px 0 14px 0;
        }
        .doc-title {
            font-size: 14pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            color: #0f172a;
            text-decoration: underline;
        }
        .doc-subtitle {
            font-size: 8.5pt;
            color: #475569;
            font-weight: 700;
            margin-top: 3px;
        }

        /* TEKS PENGANTAR */
        .intro-box {
            font-size: 9pt;
            line-height: 1.45;
            margin-bottom: 12px;
            color: #1e293b;
        }
        .intro-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 9pt;
        }
        .intro-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        /* TABEL BARANG SCRAP */
        .table-scrap {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 12px;
        }
        .table-scrap th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            border: 1.5px solid #0f172a;
            padding: 6px 6px;
            text-align: center;
            font-size: 8.5pt;
        }
        .table-scrap td {
            border: 1.5px solid #0f172a;
            padding: 6px 6px;
            vertical-align: middle;
        }

        /* SEBAB DI SCRAP */
        .reason-box {
            border: 1.5px solid #0f172a;
            margin-bottom: 12px;
            break-inside: avoid;
        }
        .reason-title {
            font-size: 8.5pt;
            font-weight: 800;
            border-bottom: 1.5px solid #0f172a;
            padding: 4px 8px;
            background: #f8fafc;
            color: #0f172a;
        }
        .reason-content {
            padding: 8px 10px;
            font-size: 8.5pt;
            min-height: 42px;
            line-height: 1.4;
            color: #1e293b;
        }

        /* TANDA TANGAN 4 KOLOM */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 8px;
            break-inside: avoid;
        }
        .sign-table td {
            border: 1.5px solid #0f172a;
            padding: 6px 6px;
            vertical-align: top;
            width: 25%;
            height: 90px;
        }
        .sign-title {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .sign-footer {
            margin-top: 38px;
            font-weight: 700;
            border-top: 1px dotted #94a3b8;
            padding-top: 2px;
        }
    </style>
</head>
<body>

<!-- TOP ACTION BAR (Hanya Tampil di Layar) -->
<div class="no-print-bar">
    <div style="display: flex; align-items: center; gap: 12px;">
        <a href="<?= $baseUrl ?>/sarpras/scrap/detail/<?= $report['id'] ?>" class="btn-action btn-back">
            &larr; Kembali ke Detail Berita Acara
        </a>
        <span style="font-size: 13px; color: #64748b; font-weight: 600;">
            Berita Acara Scrap: <strong style="color: #0f172a;"><?= SecurityHelper::escape($report['report_number']) ?></strong>
        </span>
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn-action btn-print">
            <svg style="width: 15px; height: 15px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
            Cetak Berita Acara (Print A4)
        </button>
    </div>
</div>

<!-- PRINT CONTAINER (LEMBAR KERJA A4) -->
<div class="sheet">

    <!-- HEADER RESMI SESUAI LAMPIRAN 2 FORM F30 -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <?php if (!empty($primaryLogoData)): ?>
                    <img src="<?= $primaryLogoData ?>" alt="AIS Batam" class="header-logo-img">
                <?php else: ?>
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #1e3a8a; color: #fff; font-weight: bold; line-height: 60px; font-size: 18px; text-align: center;">AIS</div>
                <?php endif; ?>
            </td>
            <td style="padding-left: 10px;">
                <h1 class="header-unit">
                    <?= SecurityHelper::escape($report['unit_name'] ?? 'SEKOLAH ALKAHFI ISLAMIC SCHOOL') ?>
                </h1>
                <div class="header-sub">
                    Yayasan Islam Alkahfi Batam - Divisi Sarana & Prasarana
                </div>
                <div class="header-address">
                    Alamat: <?= SecurityHelper::escape($report['unit_address'] ?: 'Jl. Mayjen RH Sugandhi 2 No. 1 MKGR/051 Batu Aji Batam, Kepri') ?>
                </div>
            </td>
            <td class="form-no-box">
                <div class="form-no-badge">
                    NO FORM : <strong><?= SecurityHelper::escape($report['form_code'] ?: 'F30') ?></strong>
                </div>
                <div class="ref-text">
                    Ref: <strong><?= SecurityHelper::escape($report['report_number']) ?></strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title-box">
        <h2 class="doc-title">Berita Acara Pernyataan Barang Scrap</h2>
        <div class="doc-subtitle">(FORMULIR F30 - PENYATAAN BARANG AFKIR/SCRAP RESMI)</div>
    </div>

    <!-- PENGANTAR SESUAI LAMPIRAN RESMI -->
    <div class="intro-box">
        <table class="intro-table">
            <tr>
                <td style="width: 80px; font-weight: 700;">Pada hari ini</td>
                <td style="width: 6px;">:</td>
                <td style="font-weight: 700; width: 140px; color: #0f172a;"><?= SecurityHelper::escape($report['day_name']) ?></td>
                <td style="width: 65px; font-weight: 700;">Tanggal :</td>
                <td><u><?= date('d/m/Y', strtotime($report['report_date'])) ?></u></td>
            </tr>
        </table>
        Yang bertanda tangan di bawah ini, berdasarkan Daftar Status Inventaris & Daftar Target Scrap, telah melaksanakan <strong>PERNYATAAN barang Scrap</strong> sebagaimana tercantum di dalam tabel berikut :
    </div>

    <!-- TABEL BARANG SCRAP -->
    <table class="table-scrap">
        <thead>
            <tr>
                <th style="width: 35px;">No.</th>
                <th>Nama Item / Rincian Aset</th>
                <th style="width: 130px;">Jumlah</th>
                <th style="width: 150px;">Penanggung Jawab</th>
                <th style="width: 170px;">Foto Fisik Barang</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 18px; color: #64748b;">Tidak ada item tercatat dalam Berita Acara Scrap ini.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($items as $item): ?>
                    <?php 
                        $itemPhotoData = !empty($item['photo']) ? resolveEmbeddedImage($item['photo']) : null;
                    ?>
                    <tr>
                        <td style="text-align: center; font-weight: 700;"><?= $no++ ?></td>
                        <td>
                            <div style="font-weight: 800; font-size: 9pt; color: #0f172a;">
                                <?= SecurityHelper::escape($item['item_name']) ?>
                            </div>
                            <?php if (!empty($item['asset_code'])): ?>
                                <div style="font-size: 7.5pt; color: #475569; font-family: monospace; margin-top: 2px;">
                                    No Aset: <strong><?= SecurityHelper::escape($item['asset_code']) ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($item['notes'])): ?>
                                <div style="font-size: 7.5pt; color: #dc2626; margin-top: 2px; font-style: italic;">
                                    Ket: <?= SecurityHelper::escape($item['notes']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center; font-weight: 800; font-size: 9.5pt; color: #dc2626;">
                            <?= SecurityHelper::escape($item['quantity_text'] ?: ($item['item_name'] . ' : ' . (int)$item['quantity'])) ?>
                        </td>
                        <td style="font-size: 8.5pt;">
                            <strong><?= SecurityHelper::escape($item['responsible_person']) ?></strong>
                        </td>
                        <td style="text-align: center; padding: 4px;">
                            <?php if (!empty($itemPhotoData)): ?>
                                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px; display: inline-block;">
                                    <img src="<?= $itemPhotoData ?>" alt="Foto Scrap" style="max-width: 150px; max-height: 95px; object-fit: contain; display: block; margin: 0 auto; border-radius: 2px;">
                                </div>
                            <?php else: ?>
                                <div style="color: #64748b; font-size: 7.5pt; font-style: italic; background: #f8fafc; border: 1px dashed #cbd5e1; padding: 6px; border-radius: 3px;">
                                    [Dokumentasi Fisik Terlampir]
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- KOTAK SEBAB DI SCRAP -->
    <div class="reason-box">
        <div class="reason-title">Sebab di scrap :</div>
        <div class="reason-content">
            <?= nl2br(SecurityHelper::escape($report['reason'])) ?>
        </div>
    </div>

    <!-- KOTAK TANDA TANGAN 4 KOLOM SESUAI LAMPIRAN F30 -->
    <table class="sign-table">
        <tr>
            <!-- Diambil Oleh (Kolom 1) -->
            <td>
                <div class="sign-title">Diambil Oleh :</div>
                <div style="font-size: 7.5pt; color: #64748b;">(Pihak Pengambil/Pembuang)</div>
                <div class="sign-footer">
                    Nama : <?= SecurityHelper::escape($report['taken_by_name'] ?: '...........................') ?><br>
                    Tgl &nbsp; : <?= !empty($report['taken_by_date']) ? date('d/m/Y', strtotime($report['taken_by_date'])) : '...........................' ?>
                </div>
            </td>

            <!-- Diambil Oleh Saksi / Petugas (Kolom 2) -->
            <td>
                <div class="sign-title">Diambil Oleh (Petugas) :</div>
                <div style="font-size: 7.5pt; color: #64748b;">(Saksi Lapangan)</div>
                <div class="sign-footer">
                    Nama : ...........................<br>
                    Tgl &nbsp; : ...........................
                </div>
            </td>

            <!-- Disiapkan Oleh (Sarpras) -->
            <td>
                <div class="sign-title">Disiapkan Oleh :</div>
                <div style="font-size: 7.5pt; color: #64748b;"><?= SecurityHelper::escape($report['prepared_by_title']) ?></div>
                <div class="sign-footer">
                    <strong><?= SecurityHelper::escape($report['prepared_by_name']) ?></strong><br>
                    Tgl &nbsp; : <?= date('d/m/Y', strtotime($report['report_date'])) ?>
                </div>
            </td>

            <!-- Disetujui Oleh (Deputi Direktur AIS / KS) -->
            <td>
                <div class="sign-title">Disetujui Oleh :</div>
                <div style="font-size: 7.5pt; color: #64748b;"><?= SecurityHelper::escape($report['approved_by_title']) ?></div>
                <div class="sign-footer">
                    <strong><?= SecurityHelper::escape($report['approved_by_name']) ?></strong><br>
                    Tgl &nbsp; : <?= date('d/m/Y', strtotime($report['report_date'])) ?>
                </div>
            </td>
        </tr>
    </table>

    <div style="font-size: 7.5pt; color: #64748b; margin-top: 10px; display: flex; justify-content: space-between; border-top: 1px dotted #cbd5e1; padding-top: 6px;">
        <span>Barang-barang di atas telah dikeluarkan secara sah & dihapus dari daftar stok aktif inventaris sistem.</span>
        <span>Dicetak: <?= date('d/m/Y H:i') ?> WIB</span>
    </div>

</div>

</body>
</html>
