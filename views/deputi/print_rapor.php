<?php
// views/deputi/print_rapor.php
use Core\SecurityHelper;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Kinerja Deputi Divisi Pendidikan - <?= SecurityHelper::escape($deputiUser['full_name'] ?? 'Deputi') ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header-section {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }
        .header-title-main {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header-title-sub {
            font-size: 13px;
            font-weight: bold;
            margin-top: 3px;
        }
        .header-sop {
            font-size: 10px;
            font-style: italic;
            margin-top: 2px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 11px;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .rapor-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .rapor-table th, .rapor-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .rapor-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .area-header {
            background-color: #e6e6e6;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .summary-box {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 15px;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .summary-box td {
            border: 1px solid #000;
            padding: 4px 8px;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            width: 50%;
            font-size: 11px;
        }
        .guidance-box {
            margin-top: 25px;
            page-break-before: auto;
            font-size: 9px;
            color: #333;
            border-top: 1px dashed #666;
            padding-top: 10px;
        }
        .guidance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 4px;
        }
        .guidance-table th, .guidance-table td {
            border: 1px solid #999;
            padding: 3px 5px;
        }
        .guidance-table th { background: #f0f0f0; }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
    <div><strong>Pratinjau Cetak Lembar Rapor Kinerja Deputi Pendidikan</strong></div>
    <div>
        <button onclick="window.print()" style="padding: 6px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Cetak Dokumen (Print / PDF)
        </button>
    </div>
</div>

<div class="header-section">
    <div class="header-title-main">YAYASAN ISLAM AL-KAHFI KOTA BATAM</div>
    <div class="header-title-sub">FORM PENILAIAN KINERJA DEPUTI DIVISI PENDIDIKAN</div>
    <div class="header-sop">Berdasarkan SOP Deputi Divisi Pendidikan (SOP 2017 Rev A)</div>
</div>

<table class="meta-table">
    <tr>
        <td style="width: 15%;">Nama Pegawai</td>
        <td style="width: 2%;">:</td>
        <td style="width: 43%; font-weight: bold;"><?= SecurityHelper::escape($deputiUser['full_name'] ?? '-') ?></td>
        <td style="width: 15%;">Periode Penilaian</td>
        <td style="width: 2%;">:</td>
        <td style="width: 23%;">Semester <?= SecurityHelper::escape($semester) ?> (Tahun Ajaran <?= SecurityHelper::escape($currentTa['name'] ?? '-') ?>)</td>
    </tr>
    <tr>
        <td>Jabatan</td>
        <td>:</td>
        <td>Deputi Divisi Pendidikan</td>
        <td>Tanggal Evaluasi</td>
        <td>:</td>
        <td><?= !empty($evaluation['evaluation_date']) ? date('d F Y', strtotime($evaluation['evaluation_date'])) : date('d F Y') ?></td>
    </tr>
    <tr>
        <td>Unit Kerja</td>
        <td>:</td>
        <td>Yayasan Islam Al-Kahfi Kota Batam</td>
        <td>Penilai</td>
        <td>:</td>
        <td>Direktur Pendidikan</td>
    </tr>
</table>

<table class="rapor-table">
    <thead>
        <tr>
            <th style="width: 25px;">No</th>
            <th>Area Kinerja Utama (Tupoksi SOP)</th>
            <th>Indikator Kinerja Utama (KPI) / Parameter Evaluasi</th>
            <th>Target / Standar Kerja</th>
            <th style="width: 50px;">Bobot (%)</th>
            <th style="width: 45px;">Skala (1-5)</th>
            <th style="width: 55px;">Capaian (%)</th>
            <th style="width: 65px;">Skor Tertimbang</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $curArea = 0;
        $rowNo = 1;
        $totalWeight = 0.0;
        $totalWeightedScore = 0.0;

        foreach ($indicators as $ind):
            $iId = $ind['id'];
            $detail = $details[$iId] ?? null;

            $weight = $detail ? (float)$detail['weight'] : (float)$ind['default_weight'];
            $scale = $detail ? (int)$detail['scale_1_to_5'] : 4;
            $achPct = $detail ? (float)$detail['achievement_pct'] : (($scale / 5.0) * 100.0);
            $weighted = $detail ? (float)$detail['weighted_score'] : ($weight * ($achPct / 100.0));

            $totalWeight += $weight;
            $totalWeightedScore += ($weighted * 100.0);
        ?>
            <?php if ($ind['area_no'] !== $curArea): ?>
                <?php $curArea = $ind['area_no']; ?>
                <tr class="area-header">
                    <td class="text-center"><?= $curArea ?></td>
                    <td colspan="7"><?= SecurityHelper::escape($ind['area_name']) ?></td>
                </tr>
            <?php endif; ?>

            <tr>
                <td class="text-center"><?= $rowNo++ ?></td>
                <td style="color: #444;"><?= SecurityHelper::escape($ind['area_name']) ?></td>
                <td><strong><?= SecurityHelper::escape($ind['indicator_name']) ?></strong></td>
                <td><?= SecurityHelper::escape($ind['target_standard']) ?></td>
                <td class="text-right"><?= number_format($weight * 100, 1) ?>%</td>
                <td class="text-center"><strong><?= $scale ?></strong></td>
                <td class="text-right"><?= number_format($achPct, 0) ?>%</td>
                <td class="text-right font-bold"><?= number_format($weighted * 100, 2) ?>%</td>
            </tr>
        <?php endforeach; ?>

        <tr class="total-row">
            <td colspan="4" class="text-right">TOTAL BOBOT &amp; SKOR FINAL</td>
            <td class="text-right"><?= number_format($totalWeight * 100, 1) ?>%</td>
            <td colspan="2"></td>
            <td class="text-right" style="font-size: 11px; font-weight: bold;"><?= number_format($totalWeightedScore, 2) ?>%</td>
        </tr>
    </tbody>
</table>

<table class="summary-box">
    <tr style="background: #f2f2f2; font-weight: bold;">
        <td colspan="2">HASIL EVALUASI &amp; PREDIKAT KINERJA</td>
    </tr>
    <tr>
        <td style="width: 35%;">Nilai Akhir (Skor Akhir Tertimbang)</td>
        <td><strong><?= number_format($totalWeightedScore, 2) ?></strong> (Skala 100)</td>
    </tr>
    <tr>
        <td>Predikat Kinerja</td>
        <td><strong><?= SecurityHelper::escape($evaluation['predicate'] ?? 'Baik (B)') ?></strong></td>
    </tr>
    <tr>
        <td>Rekomendasi / Catatan Evaluator</td>
        <td><?= !empty($evaluation['evaluator_notes']) ? nl2br(SecurityHelper::escape($evaluation['evaluator_notes'])) : 'Terus pertahankan dedikasi, kepemimpinan operasional, dan tindak lanjut komitmen rapat mutu unit pendidikan.' ?></td>
    </tr>
</table>

<table class="signature-table">
    <tr>
        <td>
            Disetujui Oleh:<br>
            <strong>Ketua Yayasan Islam Al-Kahfi</strong>
            <div style="height: 65px; margin-top: 5px;">
                <?php if (!empty($ketuaYayasan['signature_path'])): ?>
                    <img src="<?= $baseUrl ?>/<?= ltrim($ketuaYayasan['signature_path'], '/') ?>" style="max-height: 55px;" alt="TTD Ketua Yayasan">
                <?php endif; ?>
            </div>
            <strong>( <?= SecurityHelper::escape($ketuaYayasan['full_name'] ?? 'H. Pembina / Ketua Yayasan') ?> )</strong>
        </td>
        <td>
            Dibuat Oleh:<br>
            <strong>Direktur Pendidikan</strong>
            <div style="height: 65px; margin-top: 5px;">
                <?php if (!empty($direktur['signature_path'])): ?>
                    <img src="<?= $baseUrl ?>/<?= ltrim($direktur['signature_path'], '/') ?>" style="max-height: 55px;" alt="TTD Direktur Pendidikan">
                <?php endif; ?>
            </div>
            <strong>( <?= SecurityHelper::escape($direktur['full_name'] ?? 'Direktur Pendidikan') ?> )</strong>
        </td>
    </tr>
</table>

<div class="guidance-box">
    <strong>PANDUAN PENILAIAN &amp; SKALA BOBOT KINERJA DEPUTI DIVISI PENDIDIKAN</strong>
    <table class="guidance-table">
        <tr>
            <th style="width: 50px;">Skala</th>
            <th style="width: 140px;">Kategori</th>
            <th style="width: 110px;">Persentase Capaian</th>
            <th>Deskripsi Kriteria</th>
        </tr>
        <tr>
            <td class="text-center"><strong>5</strong></td>
            <td>Sangat Baik (Outstanding)</td>
            <td class="text-center">90% - 100%</td>
            <td>Target tercapai melampaui standar SOP, efisien, tepat waktu &amp; dokumentasi sempurna.</td>
        </tr>
        <tr>
            <td class="text-center"><strong>4</strong></td>
            <td>Baik (Good)</td>
            <td class="text-center">80% - 89%</td>
            <td>Target tercapai sesuai standar SOP dan timeplan yang ditetapkan tanpa kendala berarti.</td>
        </tr>
        <tr>
            <td class="text-center"><strong>3</strong></td>
            <td>Cukup (Satisfactory)</td>
            <td class="text-center">70% - 79%</td>
            <td>Target tercapai dengan sedikit keterlambatan atau perbaikan minor dalam pelaksanaan.</td>
        </tr>
        <tr>
            <td class="text-center"><strong>2</strong></td>
            <td>Kurang (Needs Improvement)</td>
            <td class="text-center">60% - 69%</td>
            <td>Target hanya tercapai sebagian, sering ada keterlambatan atau ketidaksesuaian SOP.</td>
        </tr>
        <tr>
            <td class="text-center"><strong>1</strong></td>
            <td>Sangat Kurang (Unsatisfactory)</td>
            <td class="text-center">&lt; 60%</td>
            <td>Target tidak tercapai, pelaksanaan SOP terabaikan secara signifikan.</td>
        </tr>
    </table>
</div>

</body>
</html>
