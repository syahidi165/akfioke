<?php
// views/kurikulum/print_struktur.php
use Core\SecurityHelper;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$unitName = $unit['name'] ?? 'AL KAHFI ISLAMIC SCHOOL';
$unitCode = $unit['code'] ?? 'AIS';
$level = strtoupper($unit['level'] ?? '');
$isKbtk = (in_array($level, ['KB', 'TK'], true) || $unit['id'] == 1 || $unit['id'] == 2);
$isSd   = ($level === 'SD' || $unit['id'] == 6);
$isSmp  = ($level === 'SMP' || $unit['id'] == 7);
$isSma  = ($level === 'SMA' || $unit['id'] == 8);

$taName = $academicYear['name'] ?? '2026-2027';
$docNo = $meta['doc_number'] ?? 'FR – 9023a';
$rev = $meta['revision'] ?? '-';
$ksNama = $meta['kepsek_nama'] ?? 'Kepala Sekolah';
$ksTelp = $meta['kepsek_telp'] ?? '-';
$deputiNama = $meta['deputi_nama'] ?? 'Yona Melinda, B.Eng., M.Ed';
$signDateKs = $meta['sign_date_kepsek'] ?? date('Y-m-d');
$signDateDeputi = $meta['sign_date_deputi'] ?? date('Y-m-d');
$notes = $meta['notes'] ?? '';

// Hitung total JP per kolom
$tot1 = 0; $tot2 = 0; $tot3 = 0; $tot4 = 0; $tot5 = 0; $tot6 = 0;
$tot7 = 0; $tot8 = 0; $tot9 = 0;
$tot10 = 0; $tot11 = 0; $tot12 = 0;

foreach ($items as $it) {
    if ($isSd && in_array($it['kelompok'], ['Kelompok C', 'Kelompok D', 'Kelompok C (Kokurikuler P5)', 'Kelompok D (Pengembangan Diri)'], true)) {
        continue;
    }
    if (is_numeric($it['jp_1'])) $tot1 += (int)$it['jp_1'];
    if (is_numeric($it['jp_2'])) $tot2 += (int)$it['jp_2'];
    if (is_numeric($it['jp_3'])) $tot3 += (int)$it['jp_3'];
    if (is_numeric($it['jp_4'])) $tot4 += (int)$it['jp_4'];
    if (is_numeric($it['jp_5'])) $tot5 += (int)$it['jp_5'];
    if (is_numeric($it['jp_6'])) $tot6 += (int)$it['jp_6'];
    if (is_numeric($it['jp_7'])) $tot7 += (int)$it['jp_7'];
    if (is_numeric($it['jp_8'])) $tot8 += (int)$it['jp_8'];
    if (is_numeric($it['jp_9'])) $tot9 += (int)$it['jp_9'];
    if (is_numeric($it['jp_10'])) $tot10 += (int)$it['jp_10'];
    if (is_numeric($it['jp_11'])) $tot11 += (int)$it['jp_11'];
    if (is_numeric($it['jp_12'])) $tot12 += (int)$it['jp_12'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struktur Kurikulum <?= SecurityHelper::escape($unitName) ?> (FR – 9023a)</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/fontawesome.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            background: #f1f5f9;
        }

        /* Toolbar Layar Browser (Hilang saat cetak fisik / PDF) */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #1e293b;
            color: #ffffff;
            padding: 10px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }
        .toolbar-badge {
            background: #0ea5e9;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: opacity 0.2s;
        }
        .btn-action:hover {
            opacity: 0.9;
        }
        .btn-print {
            background: #10b981;
            color: #ffffff;
        }
        .btn-pdf {
            background: #ef4444;
            color: #ffffff;
        }
        .btn-close {
            background: #475569;
            color: #ffffff;
        }

        /* Lembar Dokumen Kertas A4 */
        .a4-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto 40px auto;
            background: #ffffff;
            padding: 15mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            border-radius: 2px;
        }

        .appendix-title {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
            color: #000;
        }

        /* Box Header Dokumen Standar ISO Yayasan */
        .header-box {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-bottom: 0px;
        }
        .header-box td {
            border: 1px solid #000;
            padding: 4px 8px;
            vertical-align: middle;
        }

        /* Box Informasi Satuan & KS */
        .meta-box {
            width: 100%;
            border-collapse: collapse;
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            margin-bottom: 8px;
        }
        .meta-box td {
            padding: 3px 6px;
            font-size: 8.5pt;
            vertical-align: top;
        }

        /* Tabel Data Mata Pelajaran */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-bottom: 8px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 3.5px 5px;
            font-size: 8pt;
        }
        table.data-table th {
            background-color: #f8fafc;
            text-align: center;
            font-weight: bold;
            color: #000;
        }
        table.data-table tr.group-header {
            background-color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            text-align: left;
        }
        table.data-table tr.total-row {
            background-color: #f8fafc;
            font-weight: bold;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }

        /* Catatan Alokasi Waktu */
        .notes-box {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-bottom: 8px;
        }
        .notes-box td {
            padding: 5px 8px;
            font-size: 7.8pt;
        }

        /* Lembar Tanda Tangan */
        .sig-box {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            page-break-inside: avoid;
        }
        .sig-box td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .sig-cell {
            height: 70px;
            position: relative;
            vertical-align: bottom;
        }
        .sig-img {
            max-height: 55px;
            position: absolute;
            left: 20px;
            bottom: 5px;
        }

        /* Format Cetak Fisik / PDF Cetak */
        @media print {
            .print-toolbar {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .a4-sheet {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            th {
                background-color: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<!-- Toolbar Pratinjau Web -->
<div class="print-toolbar">
    <div class="toolbar-info">
        <span class="toolbar-badge"><i class="fa-solid fa-file-contract"></i> FR – 9023a</span>
        <span>Pratinjau Cetak: <strong>Struktur Kurikulum <?= SecurityHelper::escape($unitName) ?></strong></span>
        <span style="opacity: 0.7; font-size: 11px;">(Tahun Ajaran <?= SecurityHelper::escape($taName) ?>)</span>
    </div>
    <div class="toolbar-actions">
        <button onclick="window.print()" class="btn-action btn-print">
            <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
        </button>
        <a href="<?= $baseUrl ?>/kurikulum/struktur/pdf?unit_id=<?= $unit['id'] ?>" target="_blank" class="btn-action btn-pdf">
            <i class="fa-solid fa-file-pdf"></i> Download PDF Asli
        </a>
        <button onclick="window.close()" class="btn-action btn-close">
            <i class="fa-solid fa-xmark"></i> Tutup
        </button>
    </div>
</div>

<!-- Lembar Dokumen Kertas A4 -->
<div class="a4-sheet">

    <div class="appendix-title">
        APPENDIX: STRUKTUR KURIKULUM <?= SecurityHelper::escape($unitCode) ?>
    </div>

    <!-- Header Box Dokumen Resmi ISO Yayasan -->
    <table class="header-box">
        <tr>
            <td style="width: 18%; text-align: center; padding: 6px;">
                <?php if (!empty($logoUrl)): ?>
                    <img src="<?= $logoUrl ?>" style="max-height: 48px; max-width: 90px; object-fit: contain;">
                <?php else: ?>
                    <div style="font-weight: bold; font-size: 11pt;"><?= SecurityHelper::escape($unitCode) ?></div>
                <?php endif; ?>
            </td>
            <td style="width: 54%; padding: 6px 10px;">
                <div style="font-weight: bold; font-size: 9.5pt; margin-bottom: 2px;">FORM</div>
                <div style="font-weight: bold; font-size: 8.5pt;">JUDUL :</div>
                <div style="font-weight: bold; font-size: 9pt; text-transform: uppercase;">PENGAJUAN STRUKTUR KURIKULUM <?= SecurityHelper::escape($taName) ?></div>
            </td>
            <td style="width: 28%; font-size: 8pt; padding: 4px 8px;">
                <div style="margin-bottom: 2px;"><strong>Document Number :</strong><br><span style="font-size: 8.5pt;"><?= SecurityHelper::escape($docNo) ?></span></div>
                <div><strong>Revision :</strong> <?= SecurityHelper::escape($rev) ?></div>
            </td>
        </tr>
    </table>

    <!-- Info Satuan Unit & Kepala Sekolah -->
    <table class="meta-box">
        <tr>
            <td style="width: 65%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 110px; font-weight: bold;">Unit</td>
                        <td>: <?= SecurityHelper::escape($unitName) ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Kepala Sekolah</td>
                        <td>: <?= SecurityHelper::escape($ksNama) ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">No Telp</td>
                        <td>: <?= SecurityHelper::escape($ksTelp) ?></td>
                    </tr>
                </table>
            </td>
            <td style="width: 35%; text-align: right; vertical-align: middle; padding-right: 12px;">
                <span style="font-weight: bold; color: #b91c1c; font-size: 9pt;">Page 1 of 1</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Struktur Kurikulum Sesuai Level Satuan Pendidikan -->
    <table class="data-table">
        <thead>
            <?php if ($isKbtk): ?>
                <tr>
                    <th style="width: 30px;">NO</th>
                    <th>MATA PELAJARAN</th>
                    <th style="width: 45px;">KB</th>
                    <th style="width: 45px;">TK</th>
                    <th style="width: 220px;">1 Jam Pelajaran = 35 Menit (Alokasi Waktu)</th>
                </tr>
            <?php elseif ($isSd): ?>
                <tr>
                    <th rowspan="2" style="width: 30px;">NO</th>
                    <th rowspan="2">MATA PELAJARAN</th>
                    <th colspan="6">Kelas</th>
                    <th rowspan="2" style="width: 110px;">Verifikasi*</th>
                </tr>
                <tr>
                    <th style="width: 24px;">1</th>
                    <th style="width: 24px;">2</th>
                    <th style="width: 24px;">3</th>
                    <th style="width: 24px;">4</th>
                    <th style="width: 24px;">5</th>
                    <th style="width: 24px;">6</th>
                </tr>
            <?php elseif ($isSmp): ?>
                <tr>
                    <th rowspan="2" style="width: 30px;">No</th>
                    <th rowspan="2">Mata Pelajaran</th>
                    <th colspan="3">Kelas</th>
                    <th rowspan="2" style="width: 130px;">Verifikasi*</th>
                </tr>
                <tr>
                    <th style="width: 32px;">7</th>
                    <th style="width: 32px;">8</th>
                    <th style="width: 32px;">9</th>
                </tr>
            <?php else: /* SMA */ ?>
                <tr>
                    <th rowspan="2" style="width: 28px;">No</th>
                    <th rowspan="2">Mata Pelajaran</th>
                    <th style="width: 42px;">Fase E</th>
                    <th colspan="2">Fase F</th>
                    <th rowspan="2" style="width: 120px;">Keterangan / Verifikasi*</th>
                </tr>
                <tr>
                    <th style="width: 36px;">10</th>
                    <th style="width: 36px;">11</th>
                    <th style="width: 36px;">12</th>
                </tr>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php 
                $currentGroup = '';
                foreach ($items as $it): 
                    // Tampilkan sub-header kelompok jika berganti kelompok
                    if ($it['kelompok'] !== $currentGroup && !empty($it['kelompok'])):
                        $currentGroup = $it['kelompok'];
                        $colspan = $isKbtk ? 5 : ($isSd ? 9 : ($isSmp ? 6 : 6));
            ?>
                <tr class="group-header">
                    <td colspan="<?= $colspan ?>" style="padding-left: 6px; font-weight: bold; background: #ffffff;">
                        <?= SecurityHelper::escape($currentGroup) ?>
                    </td>
                </tr>
            <?php endif; ?>

                <tr>
                    <td class="text-center" style="font-size: 8pt;">
                        <?= SecurityHelper::escape($it['item_number']) ?>
                    </td>
                    <td style="<?= !empty($it['is_sub_item']) ? 'padding-left: 18px;' : '' ?>">
                        <?= SecurityHelper::escape($it['nama_mapel']) ?>
                    </td>

                    <?php if ($isKbtk): ?>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_1'] ?: '-') ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_2'] ?: '-') ?></td>
                        <td style="font-size: 7.5pt;"><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                    <?php elseif ($isSd): ?>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_1'] ?: '-') ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_2'] ?: '-') ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_3'] ?: '-') ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_4'] ?: '-') ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_5'] ?: '-') ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_6'] ?: '-') ?></td>
                        <td style="font-size: 7.5pt;"><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                    <?php elseif ($isSmp): ?>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_7'] ?: '-') ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_8'] ?: '-') ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_9'] ?: '-') ?></td>
                        <td style="font-size: 7.5pt;"><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                    <?php else: /* SMA */ ?>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_10'] ?: '-') ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_11'] ?: '-') ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_12'] ?: '-') ?></td>
                        <td style="font-size: 7.5pt;"><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>

            <!-- Baris Total Jam Pelajaran -->
            <?php if ($isSd): ?>
                <tr class="total-row">
                    <td colspan="2" class="bold text-center">TOTAL (tanpa kelompok C dan D)</td>
                    <td class="bold text-center"><?= $tot1 ?></td>
                    <td class="bold text-center"><?= $tot2 ?></td>
                    <td class="bold text-center"><?= $tot3 ?></td>
                    <td class="bold text-center"><?= $tot4 ?></td>
                    <td class="bold text-center"><?= $tot5 ?></td>
                    <td class="bold text-center"><?= $tot6 ?></td>
                    <td></td>
                </tr>
            <?php elseif ($isSmp): ?>
                <tr class="total-row">
                    <td colspan="2" class="bold text-center">TOTAL</td>
                    <td class="bold text-center"><?= $tot7 ?></td>
                    <td class="bold text-center"><?= $tot8 ?></td>
                    <td class="bold text-center"><?= $tot9 ?></td>
                    <td></td>
                </tr>
            <?php elseif ($isSma): ?>
                <tr class="total-row">
                    <td colspan="2" class="bold text-center">TOTAL ALOKASI JP PER MINGGU</td>
                    <td class="bold text-center"><?= $tot10 ?></td>
                    <td class="bold text-center"><?= $tot11 ?></td>
                    <td class="bold text-center"><?= $tot12 ?></td>
                    <td></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Kotak Catatan / Alokasi Waktu -->
    <table class="notes-box">
        <tr>
            <td>
                <?php if ($isKbtk): ?>
                    * Alokasi waktu pembelajaran 1 JP = 35 Menit. Program Khusus dan Pembiasaan Islami terintegrasi dalam sentra.
                <?php elseif ($isSd): ?>
                    * Alokasi waktu tatap muka per JP = 35 menit.<br>
                    * Kelompok C (Kokurikuler P5) dan Kelompok D (Pengembangan Diri/Ekstrakurikuler) dihitung terpisah di luar alokasi intrakurikuler utama.
                <?php elseif ($isSmp): ?>
                    * Alokasi waktu tatap muka per JP = 40 menit.<br>
                    * Dilengkapi dengan program pembiasaan Tahfidz dan Bahasa Arab harian terintegrasi.
                <?php else: /* SMA */ ?>
                    * Alokasi waktu tatap muka per JP = 45 menit.<br>
                    * Fase E (Kelas X) menerapkan kurikulum umum penguatan fondasi; Fase F (Kelas XI &amp; XII) menerapkan mata pelajaran pilihan peminatan siswa.
                <?php endif; ?>

                <?php if (!empty($notes)): ?>
                    <div style="margin-top: 4px; font-weight: bold;">
                        Catatan Khusus: <?= nl2br(SecurityHelper::escape($notes)) ?>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- Lembar Tanda Tangan KS & Deputi Direktur Pendidikan -->
    <table class="sig-box">
        <tr>
            <td style="width: 50%;">
                <div>Diajukan oleh:</div>
                <div class="bold">Kepala Sekolah <?= SecurityHelper::escape($unitCode) ?></div>
                <div style="font-size: 8pt; margin-top: 2px;">
                    Tanggal : <?= date('d F Y', strtotime($signDateKs)) ?>
                </div>
                <div class="sig-cell">
                    <div class="bold" style="text-decoration: underline; margin-bottom: 2px;">
                        <?= SecurityHelper::escape($ksNama) ?>
                    </div>
                    <div style="font-size: 7.5pt;">No Telp: <?= SecurityHelper::escape($ksTelp) ?></div>
                </div>
            </td>
            <td style="width: 50%;">
                <div>Disetujui oleh:</div>
                <div class="bold">Deputi Direktur Pendidikan AIS</div>
                <div style="font-size: 8pt; margin-top: 2px;">
                    Tanggal : <?= date('d F Y', strtotime($signDateDeputi)) ?>
                </div>
                <div class="sig-cell">
                    <?php if (!empty($deputiSigUrl)): ?>
                        <img src="<?= $deputiSigUrl ?>" class="sig-img" alt="TTD Deputi">
                    <?php endif; ?>
                    <div class="bold" style="text-decoration: underline; margin-bottom: 2px;">
                        <?= SecurityHelper::escape($deputiNama) ?>
                    </div>
                    <div style="font-size: 7.5pt;">Deputi Direktur Pendidikan Al Kahfi</div>
                </div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
