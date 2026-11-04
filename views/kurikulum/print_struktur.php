<?php
// views/kurikulum/print_struktur.php
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
$tot1 = 0; $tot2 = 0; $tot3 = 0; $tot4 = 0; $tot5 = 0; $tot6 = 0;
$tot7 = 0; $tot8 = 0; $tot9 = 0;
$tot10 = 0; $tot11 = 0; $tot12 = 0;

foreach ($items as $it) {
    if ($isSd && in_array($it['kelompok'], ['Kelompok C', 'Kelompok D'], true)) {
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
    <title>Cetak Struktur Kurikulum <?= SecurityHelper::escape($unitName) ?></title>
    <style>
        @page { size: A4 portrait; margin: 12mm 15mm; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #111; line-height: 1.35; margin: 0; padding: 20px; background: #fff; }
        .no-print { margin-bottom: 20px; padding: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .btn-print { background: #059669; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-close { background: #64748b; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 11px; }
        
        .header-box { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f766e; padding-bottom: 8px; margin-bottom: 12px; }
        .header-left { display: flex; align-items: center; gap: 12px; }
        .logo-img { height: 46px; }
        .doc-title { font-size: 13px; font-weight: 700; color: #047857; text-transform: uppercase; margin: 0; }
        .doc-sub { font-size: 10px; color: #475569; margin: 0; }
        .doc-meta-table { font-size: 10px; border-collapse: collapse; }
        .doc-meta-table td { padding: 2px 6px; border: 1px solid #cbd5e1; }
        .doc-meta-table td.label { font-weight: 600; background: #f8fafc; }

        .main-heading { text-align: center; font-size: 12px; font-weight: 700; text-transform: uppercase; margin: 10px 0 14px 0; letter-spacing: 0.5px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data-table th, table.data-table td { border: 1px solid #334155; padding: 4px 6px; font-size: 10px; }
        table.data-table th { background: #f1f5f9; text-align: center; font-weight: 700; color: #0f172a; }
        table.data-table tr.kelompok-header { background: #e2e8f0; font-weight: 700; }
        table.data-table tr.total-row { background: #f8fafc; font-weight: 700; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: 700; }

        .notes-box { font-size: 9.5px; margin: 10px 0 18px 0; border: 1px dashed #94a3b8; padding: 8px 12px; border-radius: 4px; background: #fafafa; }
        .notes-box p { margin: 2px 0; }

        .signatures-table { width: 100%; margin-top: 15px; border-collapse: collapse; page-break-inside: avoid; }
        .signatures-table td { width: 50%; vertical-align: top; border: none; padding: 4px 10px; }
        .sig-box { text-align: center; min-height: 110px; }
        .sig-title { font-weight: 600; font-size: 10px; margin-bottom: 4px; }
        .sig-name { font-weight: 700; font-size: 10.5px; text-decoration: underline; margin-top: 55px; }
        .sig-meta { font-size: 9px; color: #475569; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div>
        <strong>Mode Cetak Dokumen Resmi FR – 9023a</strong>
        <span style="color:#64748b; font-size:11px; margin-left:10px;">Gunakan kertas A4 Portrait untuk hasil presisi.</span>
    </div>
    <div style="display:flex; gap:8px;">
        <button onclick="window.print()" class="btn-print">
            <svg style="width:14px;height:14px;fill:currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"></path></svg>
            Cetak / Simpan PDF
        </button>
        <a href="javascript:window.close()" class="btn-close">Tutup</a>
    </div>
</div>

<div class="header-box">
    <div class="header-left">
        <div>
            <div class="doc-title"><?= SecurityHelper::escape($unitName) ?></div>
            <div class="doc-sub">YAYASAN PENDIDIKAN ISLAM AL KAHFI BATAM</div>
        </div>
    </div>
    <div>
        <table class="doc-meta-table">
            <tr>
                <td class="label">No. Dokumen</td>
                <td><?= SecurityHelper::escape($docNo) ?></td>
            </tr>
            <tr>
                <td class="label">Revisi</td>
                <td><?= SecurityHelper::escape($rev) ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="main-heading">
    PENGAJUAN STRUKTUR KURIKULUM <?= SecurityHelper::escape($unitCode) ?><br>
    TAHUN AJARAN <?= SecurityHelper::escape($taName) ?>
</div>

<?php if ($isKbtk): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 35px;">NO</th>
                <th rowspan="2">STRUKTUR PROGRAM</th>
                <th colspan="2">ALOKASI WAKTU</th>
                <th rowspan="2" style="width: 140px;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 70px;">KB</th>
                <th style="width: 70px;">TK</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $currKel = '';
            foreach ($items as $it): 
                if ($it['kelompok'] !== $currKel):
                    $currKel = $it['kelompok'];
            ?>
                <tr class="kelompok-header">
                    <td class="text-center fw-bold"><?= SecurityHelper::escape(substr($currKel, -1)) ?></td>
                    <td colspan="4" class="fw-bold"><?= SecurityHelper::escape($currKel) ?></td>
                </tr>
            <?php endif; ?>
                <tr>
                    <td class="text-center"><?= SecurityHelper::escape($it['item_number']) ?></td>
                    <td><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_1'] ?: '-') ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_2'] ?: '-') ?></td>
                    <td><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($isSd): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">NO</th>
                <th rowspan="2">MATA PELAJARAN</th>
                <th colspan="6">ALOKASI WAKTU PER MINGGU</th>
                <th rowspan="2" style="width: 120px;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 32px;">I</th>
                <th style="width: 32px;">II</th>
                <th style="width: 32px;">III</th>
                <th style="width: 32px;">IV</th>
                <th style="width: 32px;">V</th>
                <th style="width: 32px;">VI</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $currKel = '';
            foreach ($items as $it): 
                if ($it['kelompok'] !== $currKel):
                    $currKel = $it['kelompok'];
            ?>
                <tr class="kelompok-header">
                    <td colspan="9" class="fw-bold"><?= SecurityHelper::escape($currKel) ?></td>
                </tr>
            <?php endif; ?>
                <tr>
                    <td class="text-center"><?= SecurityHelper::escape($it['item_number']) ?></td>
                    <td><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_1'] ?: '-') ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_2'] ?: '-') ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_3'] ?: '-') ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_4'] ?: '-') ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_5'] ?: '-') ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_6'] ?: '-') ?></td>
                    <td><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="2" class="text-center fw-bold">TOTAL (tanpa kelompok C dan D)</td>
                <td class="text-center fw-bold"><?= $tot1 ?></td>
                <td class="text-center fw-bold"><?= $tot2 ?></td>
                <td class="text-center fw-bold"><?= $tot3 ?></td>
                <td class="text-center fw-bold"><?= $tot4 ?></td>
                <td class="text-center fw-bold"><?= $tot5 ?></td>
                <td class="text-center fw-bold"><?= $tot6 ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>

<?php elseif ($isSmp): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">NO</th>
                <th rowspan="2">MATA PELAJARAN</th>
                <th colspan="3">ALOKASI WAKTU PER MINGGU</th>
                <th rowspan="2" style="width: 130px;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 45px;">VII</th>
                <th style="width: 45px;">VIII</th>
                <th style="width: 45px;">IX</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $currKel = '';
            foreach ($items as $it): 
                if ($it['kelompok'] !== $currKel):
                    $currKel = $it['kelompok'];
            ?>
                <tr class="kelompok-header">
                    <td colspan="6" class="fw-bold"><?= SecurityHelper::escape($currKel) ?></td>
                </tr>
            <?php endif; ?>
                <tr>
                    <td class="text-center"><?= SecurityHelper::escape($it['item_number']) ?></td>
                    <td><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_7'] ?: '-') ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_8'] ?: '-') ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_9'] ?: '-') ?></td>
                    <td><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="2" class="text-center fw-bold">TOTAL ALOKASI JP PER MINGGU</td>
                <td class="text-center fw-bold"><?= $tot7 ?></td>
                <td class="text-center fw-bold"><?= $tot8 ?></td>
                <td class="text-center fw-bold"><?= $tot9 ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>

<?php elseif ($isSma): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">NO</th>
                <th rowspan="2">MATA PELAJARAN</th>
                <th colspan="3">ALOKASI WAKTU PER MINGGU</th>
                <th rowspan="2" style="width: 130px;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 45px;">X</th>
                <th style="width: 45px;">XI</th>
                <th style="width: 45px;">XII</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $currKel = '';
            foreach ($items as $it): 
                if ($it['kelompok'] !== $currKel):
                    $currKel = $it['kelompok'];
            ?>
                <tr class="kelompok-header">
                    <td colspan="6" class="fw-bold"><?= SecurityHelper::escape($currKel) ?></td>
                </tr>
            <?php endif; ?>
                <tr>
                    <td class="text-center"><?= SecurityHelper::escape($it['item_number']) ?></td>
                    <td><?= SecurityHelper::escape($it['nama_mapel']) ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_10'] ?: '-') ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_11'] ?: '-') ?></td>
                    <td class="text-center"><?= SecurityHelper::escape($it['jp_12'] ?: '-') ?></td>
                    <td><?= SecurityHelper::escape($it['keterangan'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="2" class="text-center fw-bold">TOTAL ALOKASI JP PER MINGGU</td>
                <td class="text-center fw-bold"><?= $tot10 ?></td>
                <td class="text-center fw-bold"><?= $tot11 ?></td>
                <td class="text-center fw-bold"><?= $tot12 ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>

<?php if (!empty($notes)): ?>
    <div class="notes-box">
        <strong>Catatan / Keterangan:</strong><br>
        <?= nl2br(SecurityHelper::escape($notes)) ?>
    </div>
<?php endif; ?>

<table class="signatures-table">
    <tr>
        <td>
            <div class="sig-box">
                <div class="sig-title">Diajukan oleh:</div>
                <div class="sig-meta">Kepala Sekolah <?= SecurityHelper::escape($unitCode) ?></div>
                <div class="sig-meta">Tanggal: <?= date('d F Y', strtotime($meta['sign_date_kepsek'] ?? date('Y-m-d'))) ?></div>
                <div class="sig-name"><?= SecurityHelper::escape($ksNama) ?></div>
                <div class="sig-meta">No. Telp / WA: <?= SecurityHelper::escape($ksTelp) ?></div>
            </div>
        </td>
        <td>
            <div class="sig-box">
                <div class="sig-title">Disetujui oleh:</div>
                <div class="sig-meta">Deputi Direktur Pendidikan AIS</div>
                <div class="sig-meta">Tanggal: <?= date('d F Y', strtotime($meta['sign_date_deputi'] ?? date('Y-m-d'))) ?></div>
                <div class="sig-name"><?= SecurityHelper::escape($deputiNama) ?></div>
                <div class="sig-meta">Deputi Direktur Pendidikan Al Kahfi</div>
            </div>
        </td>
    </tr>
</table>

</body>
</html>

