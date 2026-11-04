<?php
// views/kurikulum/pdf_struktur.php
use Core\SecurityHelper;

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
$notes = $meta['notes'] ?? '';

// Hitung total JP per kolom
$totKb = 0; $totTk = 0;
$tot1 = 0; $tot2 = 0; $tot3 = 0; $tot4 = 0; $tot5 = 0; $tot6 = 0;
$tot7 = 0; $tot8 = 0; $tot9 = 0;
$tot10 = 0; $tot11 = 0; $tot12 = 0;

foreach ($items as $it) {
    if ($isSd && in_array($it['kelompok'], ['Kelompok C', 'Kelompok D'], true)) {
        // SD approved note: TOTAL (tanpa kelompok C dan D)
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
<html>
<head>
    <meta charset="utf-8">
    <title>Struktur Kurikulum <?= SecurityHelper::escape($unitName) ?></title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: A4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .appendix-title {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .header-box {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-bottom: 0px;
        }
        .header-box td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
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
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-bottom: 8px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 8pt;
        }
        table.data-table th {
            background-color: #f8fafc;
            text-align: center;
            font-weight: bold;
        }
        .group-header {
            background-color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            text-align: left;
            padding-left: 6px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        
        .notes-box {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-bottom: 8px;
        }
        .notes-box td {
            padding: 6px 8px;
            font-size: 8pt;
        }

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
            height: 75px;
            position: relative;
            vertical-align: bottom;
        }
    </style>
</head>
<body>

    <div class="appendix-title">
        APPENDIX: STRUKTUR KURIKULUM <?= SecurityHelper::escape($unitCode) ?>
    </div>

    <!-- Header Box Dokumen Resmi ISO/Yayasan -->
    <table class="header-box">
        <tr>
            <td style="width: 18%; text-align: center; padding: 6px;">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" style="max-height: 52px; max-width: 90px; object-fit: contain;">
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
                    <th>10</th>
                    <th style="width: 32px;">11</th>
                    <th style="width: 32px;">12</th>
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
                <tr>
                    <td colspan="<?= $colspan ?>" class="group-header">
                        <?= SecurityHelper::escape($currentGroup) ?>
                    </td>
                </tr>
            <?php endif; ?>

                <tr>
                    <td class="text-center" style="font-size: 8pt;">
                        <?= SecurityHelper::escape($it['item_number']) ?>
                    </td>
                    <td style="<?= $it['is_sub_item'] ? 'padding-left: 18px;' : '' ?>">
                        <?= SecurityHelper::escape($it['nama_mapel']) ?>
                    </td>

                    <?php if ($isKbtk): ?>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_1']) ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_2']) ?></td>
                        <td style="font-size: 7.5pt;"><?= SecurityHelper::escape($it['keterangan']) ?></td>
                    <?php elseif ($isSd): ?>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_1']) ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_2']) ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_3']) ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_4']) ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_5']) ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_6']) ?></td>
                        <td style="font-size: 7pt;"><?= SecurityHelper::escape($it['keterangan']) ?></td>
                    <?php elseif ($isSmp): ?>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_7']) ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_8']) ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_9']) ?></td>
                        <td style="font-size: 7.5pt;"><?= SecurityHelper::escape($it['keterangan']) ?></td>
                    <?php else: /* SMA */ ?>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_10']) ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_11']) ?></td>
                        <td class="text-center"><?= SecurityHelper::escape($it['jp_12']) ?></td>
                        <td style="font-size: 7.5pt;"><?= SecurityHelper::escape($it['keterangan']) ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>

            <!-- Baris Total JP -->
            <tr style="font-weight: bold; background-color: #f8fafc;">
                <td colspan="2" class="text-right" style="padding-right: 10px;">
                    <?php if ($isSd): ?>
                        TOTAL (tanpa kelompok C dan D)
                    <?php else: ?>
                        TOTAL
                    <?php endif; ?>
                </td>
                <?php if ($isKbtk): ?>
                    <td class="text-center"><?= $tot1 ?></td>
                    <td class="text-center"><?= $tot2 ?></td>
                    <td></td>
                <?php elseif ($isSd): ?>
                    <td class="text-center"><?= $tot1 ?></td>
                    <td class="text-center"><?= $tot2 ?></td>
                    <td class="text-center"><?= $tot3 ?></td>
                    <td class="text-center"><?= $tot4 ?></td>
                    <td class="text-center"><?= $tot5 ?></td>
                    <td class="text-center"><?= $tot6 ?></td>
                    <td></td>
                <?php elseif ($isSmp): ?>
                    <td class="text-center"><?= $tot7 ?></td>
                    <td class="text-center"><?= $tot8 ?></td>
                    <td class="text-center"><?= $tot9 ?></td>
                    <td></td>
                <?php else: /* SMA */ ?>
                    <td class="text-center"><?= $tot10 ?></td>
                    <td class="text-center"><?= $tot11 ?></td>
                    <td class="text-center"><?= $tot12 ?></td>
                    <td></td>
                <?php endif; ?>
            </tr>
        </tbody>
    </table>

    <!-- Catatan Notes -->
    <table class="notes-box">
        <tr>
            <td>
                <strong>Notes:</strong><br>
                <?= nl2br(SecurityHelper::escape($notes)) ?>
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan Resmi Pengajuan & Persetujuan -->
    <table class="sig-box">
        <tr>
            <td style="width: 50%;">
                <div style="font-weight: bold; margin-bottom: 6px;">Yang Menyerahkan</div>
                <div>Nama : <strong><?= SecurityHelper::escape($ksNama) ?></strong></div>
                <div>Tanggal : <?= date('d F Y', strtotime($meta['sign_date_kepsek'] ?? date('Y-m-d'))) ?></div>
                <div style="margin-top: 4px;">Tanda Tangan :</div>
                
                <div class="sig-cell">
                    <!-- Cap & Tanda Tangan Kepala Sekolah -->
                    <div style="padding-top: 35px;">
                        <span style="border-bottom: 1px solid #000; padding-bottom: 1px; font-weight: bold;">
                            <?= SecurityHelper::escape($ksNama) ?>
                        </span>
                    </div>
                </div>
                <div style="font-weight: bold; text-align: center; margin-top: 4px;">Kepala Sekolah</div>
            </td>
            <td style="width: 50%;">
                <div style="font-weight: bold; margin-bottom: 6px;">Yang Menyetujui</div>
                <div>Nama : <strong><?= SecurityHelper::escape($deputiNama) ?></strong></div>
                <div>Tanggal : <?= date('d F Y', strtotime($meta['sign_date_deputi'] ?? date('Y-m-d'))) ?></div>
                <div style="margin-top: 4px;">Tanda Tangan :</div>

                <div class="sig-cell">
                    <!-- TTD & Cap Resmi Deputi AIS -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <?php if (!empty($deputiSigBase64)): ?>
                            <img src="<?= $deputiSigBase64 ?>" style="height: 48px; object-fit: contain;">
                        <?php endif; ?>
                        <!-- Stempel Deputi SVG/HTML -->
                        <div style="display: inline-block; border: 2px solid #1e3a8a; border-radius: 50%; width: 52px; height: 52px; text-align: center; color: #1e3a8a; font-size: 6.5pt; font-weight: bold; line-height: 1; padding-top: 8px;">
                            EDUCATION<br>DEPT<br><span style="font-size: 8pt; color: #0284c7;">-AIS-</span><br>BATAM
                        </div>
                    </div>
                    <div style="margin-top: 4px;">
                        <span style="border-bottom: 1px solid #000; padding-bottom: 1px; font-weight: bold;">
                            <?= SecurityHelper::escape($deputiNama) ?>
                        </span>
                    </div>
                </div>
                <div style="font-weight: bold; text-align: center; margin-top: 4px;">Deputi AIS</div>
            </td>
        </tr>
    </table>

</body>
</html>
