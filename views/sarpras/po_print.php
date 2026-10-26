<?php
// views/sarpras/po_print.php
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

$unitLogoData = !empty($po['logo_path']) ? resolveEmbeddedImage($po['logo_path']) : null;

// Tentukan apakah ada foto barang yang dilampirkan
$itemsWithPhotos = [];
foreach ($items as $it) {
    if (!empty($it['photo'])) {
        $photoBase64 = resolveEmbeddedImage($it['photo']);
        if ($photoBase64) {
            $it['photo_data'] = $photoBase64;
            $itemsWithPhotos[] = $it;
        }
    }
}
$hasPhotos = !empty($itemsWithPhotos);
$isContractor = !empty($po['approved_sarpras_dir_id']) || !empty($po['needs_contractor']);
$colWidth = $isContractor ? '25%' : '33.33%';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Permintaan Barang - <?= SecurityHelper::escape($po['po_number']) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 12mm 12mm;
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
            background: #0f766e;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(15, 118, 110, 0.3);
        }
        .btn-print:hover {
            background: #0d9488;
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
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
            }
        }

        /* KOP SURAT */
        .kop-table {
            width: 100%;
            border-bottom: 3px double #0f172a;
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-logo-left {
            width: 78px;
            text-align: left;
        }
        .kop-logo-right {
            width: 78px;
            text-align: right;
        }
        .kop-logo-img {
            max-width: 75px;
            max-height: 75px;
            object-fit: contain;
            display: block;
        }
        .kop-center {
            text-align: center;
            padding: 0 10px;
        }
        .kop-title {
            font-size: 15pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
        }
        .kop-unit {
            font-size: 11pt;
            font-weight: 800;
            color: #0f172a;
            margin: 2px 0 0 0;
            text-transform: uppercase;
        }
        .kop-tagline {
            font-size: 9pt;
            font-style: italic;
            font-weight: 700;
            color: #047857;
            margin: 2px 0;
        }
        .kop-address {
            font-size: 8pt;
            color: #475569;
            margin: 0;
            line-height: 1.3;
        }

        /* HEADER DOKUMEN */
        .doc-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .doc-header-table td {
            vertical-align: top;
        }
        .doc-title-text {
            font-size: 14pt;
            font-weight: 800;
            color: #1e3a8a;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .ref-box {
            text-align: right;
            font-size: 9pt;
            color: #334155;
        }
        .ref-badge {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 800;
            color: #0f172a;
            font-family: monospace;
            font-size: 9.5pt;
        }

        /* META DATA */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 12px;
            background: #fafafa;
            border: 1px solid #cbd5e1;
        }
        .meta-table td {
            padding: 4px 8px;
            vertical-align: top;
            border-bottom: 1px solid #e2e8f0;
        }
        .meta-label {
            font-weight: 700;
            color: #334155;
            background: #f8fafc;
        }

        /* TABEL BARANG */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 10px;
        }
        .table-items th {
            background: #1e3a8a;
            color: #ffffff;
            font-weight: 700;
            padding: 6px 5px;
            border: 1px solid #1e3a8a;
            text-align: center;
            font-size: 8.5pt;
        }
        .table-items td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            vertical-align: middle;
        }
        .sub-header-row td {
            background: #e2e8f0;
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 8px;
            color: #0f172a;
            letter-spacing: 0.5px;
            font-size: 8.5pt;
        }

        /* CATATAN */
        .notes-container {
            border: 1px solid #94a3b8;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }
        .notes-title {
            background: #f1f5f9;
            padding: 4px 8px;
            font-weight: 700;
            color: #334155;
            border-bottom: 1px solid #cbd5e1;
        }
        .notes-body {
            padding: 6px 8px;
            min-height: 28px;
            color: #1e293b;
        }

        /* TANDA TANGAN */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 6px;
            break-inside: avoid;
        }
        .ttd-table td {
            border: 1.5px solid #0f172a;
            padding: 6px 8px;
            vertical-align: top;
            width: <?= $colWidth ?>;
            height: 105px;
        }
        .ttd-header {
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-bottom: 4px;
            text-align: center;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .ttd-sign-status {
            margin-top: 22px;
            text-align: center;
            font-size: 7.5pt;
            font-weight: 800;
        }
        .ttd-name {
            font-weight: 700;
            color: #0f172a;
            border-top: 1px dotted #94a3b8;
            padding-top: 2px;
            margin-top: 4px;
            text-align: center;
        }

        /* LAMPIRAN FOTO */
        .photo-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 10px;
            background: #ffffff;
            break-inside: avoid;
            page-break-inside: avoid;
            margin-bottom: 14px;
        }
        .photo-card-header {
            font-weight: 800;
            font-size: 9.5pt;
            color: #1e3a8a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }
        .photo-container {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 190px;
            max-height: 230px;
        }
        .photo-container img {
            max-width: 100%;
            max-height: 220px;
            object-fit: contain;
            border-radius: 2px;
        }
    </style>
</head>
<body>

<!-- TOP ACTION BAR (Hanya Tampil di Layar) -->
<div class="no-print-bar">
    <div style="display: flex; align-items: center; gap: 12px;">
        <a href="<?= $baseUrl ?>/sarpras/po/detail/<?= $po['id'] ?>" class="btn-action btn-back">
            &larr; Kembali ke Detail PO
        </a>
        <span style="font-size: 13px; color: #64748b; font-weight: 600;">
            Purchase Order: <strong style="color: #0f172a;"><?= SecurityHelper::escape($po['po_number']) ?></strong>
        </span>
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn-action btn-print">
            <svg style="width: 15px; height: 15px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
            Cetak Formulir (Print A4)
        </button>
    </div>
</div>

<!-- PRINT CONTAINER (LEMBAR KERJA A4) -->
<div class="sheet">

    <!-- KOP RESMI ALKAHFI BATAM -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo-left">
                <?php if (!empty($yayasanLogoData)): ?>
                    <img src="<?= $yayasanLogoData ?>" alt="Logo Alkahfi" class="kop-logo-img">
                <?php else: ?>
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #1e3a8a; color: #fff; font-weight: bold; line-height: 60px; font-size: 18px; text-align: center;">AIS</div>
                <?php endif; ?>
            </td>
            <td class="kop-center">
                <h1 class="kop-title"><?= SecurityHelper::escape($po['kop_title'] ?: 'YAYASAN ISLAM AL KAHFI BATAM') ?></h1>
                <?php if (!empty($po['unit_name'])): ?>
                    <div class="kop-unit"><?= SecurityHelper::escape($po['unit_name']) ?></div>
                <?php endif; ?>
                <div class="kop-tagline"><?= SecurityHelper::escape($po['kop_subtitle'] ?: 'Meniti Jejak Generasi Islam Pertama') ?></div>
                <div class="kop-address">
                    <?= SecurityHelper::escape($po['unit_address'] ?: 'Jl. Mayjen RH Sugandhi 2 No.1 MKGR/051, Batu Aji, Batam, Kepri') ?><br>
                    Telepon : <?= SecurityHelper::escape($po['unit_phone'] ?: '08117009257') ?> | Email : yayasan@alkahfi.sch.id
                </div>
            </td>
            <td class="kop-logo-right">
                <?php if (!empty($unitLogoData) && $unitLogoData !== $yayasanLogoData): ?>
                    <img src="<?= $unitLogoData ?>" alt="Logo Unit" class="kop-logo-img" style="margin-left: auto;">
                <?php else: ?>
                    <div style="width: 70px; height: 70px;"></div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- JUDUL DOKUMEN & REFERENSI -->
    <table class="doc-header-table">
        <tr>
            <td>
                <h2 class="doc-title-text">Formulir Permintaan Barang</h2>
                <div style="font-size: 9pt; font-weight: 700; color: #047857; margin-top: 2px;">
                    Satuan Unit : <u><?= SecurityHelper::escape($po['unit_name'] ?: 'Sekolah Alkahfi') ?></u>
                </div>
            </td>
            <td class="ref-box" style="width: 260px;">
                <div>Tanggal Pengajuan : <strong><?= date('d/m/Y', strtotime($po['request_date'])) ?></strong></div>
                <div style="margin-top: 3px;">
                    No. Ref : <span class="ref-badge"><?= SecurityHelper::escape($po['po_number']) ?></span>
                </div>
            </td>
        </tr>
    </table>

    <!-- META DATA PEMBELIAN -->
    <table class="meta-table">
        <tr>
            <td class="meta-label" style="width: 140px;">Alasan Permintaan</td>
            <td style="width: 6px;">:</td>
            <td colspan="4"><?= SecurityHelper::escape($po['reason']) ?></td>
        </tr>
        <tr>
            <td class="meta-label">Pengadaan Melalui</td>
            <td>:</td>
            <td style="width: 220px;"><?= SecurityHelper::escape($po['procurement_division']) ?></td>
            <td class="meta-label" style="width: 120px;">Tgl Delivery :</td>
            <td>:</td>
            <td><strong><?= !empty($po['delivery_date']) ? date('d/m/Y', strtotime($po['delivery_date'])) : '-' ?></strong></td>
        </tr>
        <tr>
            <td class="meta-label">Lokasi Delivery</td>
            <td>:</td>
            <td colspan="4"><strong><?= SecurityHelper::escape($po['delivery_location']) ?></strong></td>
        </tr>
    </table>

    <!-- TABEL ITEM BARANG -->
    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 35px;">Item</th>
                <th>Deskripsi / Merk / Type</th>
                <th style="width: 120px;">Size / Spesifikasi</th>
                <th style="width: 55px;">RAPBS</th>
                <th style="width: 50px;">Satuan</th>
                <th style="width: 60px;">Stok</th>
                <th style="width: 55px;">ORDER</th>
                <th style="width: 100px;">Waktu Pengadaan</th>
                <th style="width: 80px;">Beban Unit</th>
            </tr>
        </thead>
        <tbody>
            <tr class="sub-header-row">
                <td colspan="9">KEBUTUHAN SAPRAS</td>
            </tr>
            <?php $totalOrder = 0; ?>
            <?php foreach ($items as $item): ?>
                <?php $totalOrder += (int)$item['quantity_ordered']; ?>
                <tr>
                    <td style="text-align: center; font-weight: 700;"><?= (int)$item['item_no'] ?></td>
                    <td style="font-weight: 700; color: #0f172a;"><?= SecurityHelper::escape($item['description']) ?></td>
                    <td><?= SecurityHelper::escape($item['specs'] ?: '-') ?></td>
                    <td style="text-align: center; font-weight: 600;"><?= SecurityHelper::escape($item['rapbs']) ?></td>
                    <td style="text-align: center;"><?= SecurityHelper::escape($item['unit_of_measure']) ?></td>
                    <td style="text-align: center;"><?= (int)$item['stock_position'] ?></td>
                    <td style="text-align: center; font-weight: 800; font-size: 9.5pt; color: #0f766e;"><?= (int)$item['quantity_ordered'] ?></td>
                    <td style="text-align: center; font-size: 8pt;"><?= SecurityHelper::escape($item['procurement_time'] ?: '-') ?></td>
                    <td style="text-align: center; font-size: 8pt;"><?= SecurityHelper::escape($item['cost_burden_unit'] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="background: #f8fafc; font-weight: 800;">
                <td colspan="6" style="text-align: right; padding-right: 10px;">TOTAL KUANTITAS ORDER :</td>
                <td style="text-align: center; color: #0f766e; font-size: 10pt;"><?= $totalOrder ?></td>
                <td colspan="2" style="font-size: 8pt; text-align: center; color: #64748b;">(<?= count($items) ?> Item Terdaftar)</td>
            </tr>
        </tbody>
    </table>

    <!-- CATATAN KHUSUS -->
    <div class="notes-container">
        <div class="notes-title">Catatan khusus jika ada :</div>
        <div class="notes-body">
            <?= !empty($po['special_notes']) ? nl2br(SecurityHelper::escape($po['special_notes'])) : '-' ?>
        </div>
    </div>

    <!-- TANDA TANGAN (KOLOM PERSAHAN DOKUMEN) -->
    <table class="ttd-table">
        <tr>
            <!-- Diminta Oleh: Sarpras -->
            <td>
                <div class="ttd-header">Diminta Oleh</div>
                <div style="font-size: 8pt; color: #475569;">Sarpras Unit</div>
                <div style="font-size: 8pt;">Tgl: <?= date('d/m/Y', strtotime($po['requested_at'])) ?></div>
                <div class="ttd-sign-status" style="color: #166534;">
                    &#10003; [TERVERIFIKASI SISTEM]
                </div>
                <div class="ttd-name">
                    <strong><?= SecurityHelper::escape($po['requested_by_name']) ?></strong><br>
                    <span style="font-size: 7.5pt; font-weight: normal; color: #475569;"><?= SecurityHelper::escape($po['requested_by_title']) ?></span>
                </div>
            </td>

            <!-- Disetujui: Kepala Sekolah -->
            <td>
                <div class="ttd-header">Disetujui</div>
                <div style="font-size: 8pt; color: #475569;">Kepala Sekolah</div>
                <div style="font-size: 8pt;">Tgl: <?= !empty($po['approved_kepsek_at']) ? date('d/m/Y', strtotime($po['approved_kepsek_at'])) : '-' ?></div>
                <div class="ttd-sign-status" style="color: <?= !empty($po['approved_kepsek_at']) ? '#166534' : '#94a3b8' ?>;">
                    <?= !empty($po['approved_kepsek_at']) ? '&#10003; [DISETUJUI KEPSEK]' : '(Menunggu Persetujuan)' ?>
                </div>
                <div class="ttd-name">
                    <strong><?= SecurityHelper::escape($po['approved_kepsek_name'] ?: '...........................') ?></strong><br>
                    <span style="font-size: 7.5pt; font-weight: normal; color: #475569;">Kepala Sekolah Unit</span>
                </div>
            </td>

            <!-- Divisi Pengadaan: Direktur Pengadaan -->
            <td>
                <div class="ttd-header">Divisi Pengadaan</div>
                <div style="font-size: 8pt; color: #475569;">Direktur Pengadaan Yayasan</div>
                <div style="font-size: 8pt;">Tgl: <?= !empty($po['approved_pengadaan_at']) ? date('d/m/Y', strtotime($po['approved_pengadaan_at'])) : '-' ?></div>
                <div class="ttd-sign-status" style="color: <?= !empty($po['approved_pengadaan_at']) ? '#166534' : '#94a3b8' ?>;">
                    <?= !empty($po['approved_pengadaan_at']) ? '&#10003; [DISETUJUI PENGADAAN]' : '(Menunggu Approval)' ?>
                </div>
                <div class="ttd-name">
                    <strong><?= SecurityHelper::escape($po['approved_pengadaan_name'] ?: '...........................') ?></strong><br>
                    <span style="font-size: 7.5pt; font-weight: normal; color: #475569;">Direktur Pengadaan</span>
                </div>
            </td>

            <!-- Direktur Sarpras (Jika ada pengerjaan kontraktor) -->
            <?php if ($isContractor): ?>
                <td>
                    <div class="ttd-header">Pengerjaan Kontraktor</div>
                    <div style="font-size: 8pt; color: #475569;">Direktur Sarpras Yayasan</div>
                    <div style="font-size: 8pt;">Tgl: <?= !empty($po['approved_sarpras_dir_at']) ? date('d/m/Y', strtotime($po['approved_sarpras_dir_at'])) : '-' ?></div>
                    <div class="ttd-sign-status" style="color: <?= !empty($po['approved_sarpras_dir_at']) ? '#166534' : '#94a3b8' ?>;">
                        <?= !empty($po['approved_sarpras_dir_at']) ? '&#10003; [DISETUJUI KONTRAKTOR]' : '(Menunggu Approval)' ?>
                    </div>
                    <div class="ttd-name">
                        <strong><?= SecurityHelper::escape($po['approved_sarpras_dir_name'] ?: '...........................') ?></strong><br>
                        <span style="font-size: 7.5pt; font-weight: normal; color: #475569;">Direktur Sarpras</span>
                    </div>
                </td>
            <?php endif; ?>
        </tr>
    </table>

    <div style="font-size: 7.5pt; color: #64748b; margin-top: 10px; display: flex; justify-content: space-between; border-top: 1px dotted #cbd5e1; padding-top: 6px;">
        <span>Dokumen resmi ini diterbitkan secara sah melalui Sistem Informasi AKFIOKE Terpadu Al-Kahfi Batam.</span>
        <span>Dicetak: <?= date('d/m/Y H:i') ?> WIB</span>
    </div>

    <!-- HALAMAN LAMPIRAN FOTO & SPESIFIKASI BARANG -->
    <?php if ($hasPhotos): ?>
        <div class="page-break" style="margin-top: 24px; padding-top: 16px; border-top: 2px dashed #94a3b8;">
            <div style="text-align: center; margin-bottom: 16px;">
                <h3 style="font-size: 12pt; font-weight: 800; color: #1e3a8a; text-transform: uppercase; margin: 0;">
                    Lampiran Dokumentasi Foto & Spesifikasi Barang Pengadaan
                </h3>
                <div style="font-size: 8.5pt; color: #475569; margin-top: 2px;">
                    Dokumen Pendukung Resmi Purchase Order Ref: <strong><?= SecurityHelper::escape($po['po_number']) ?></strong>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;">
                <?php foreach ($itemsWithPhotos as $item): ?>
                    <div class="photo-card">
                        <div class="photo-card-header">
                            Item #<?= (int)$item['item_no'] ?>: <?= SecurityHelper::escape($item['description']) ?>
                        </div>
                        <div style="font-size: 8pt; color: #475569; margin-bottom: 6px;">
                            Size/Spesifikasi: <strong><?= SecurityHelper::escape($item['specs'] ?: '-') ?></strong><br>
                            Kuantitas Diajukan: <strong style="color: #0f766e;"><?= (int)$item['quantity_ordered'] ?> <?= SecurityHelper::escape($item['unit_of_measure']) ?></strong>
                        </div>
                        <div class="photo-container">
                            <img src="<?= $item['photo_data'] ?>" alt="Foto Item #<?= (int)$item['item_no'] ?>">
                        </div>
                        <div style="font-size: 7.5pt; color: #64748b; margin-top: 6px; font-style: italic; text-align: center;">
                            Keterangan: Referensi visual spesifikasi fisik barang yang diajukan untuk pengadaan.
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
