<?php
// views/deputi/print_rapor.php
use Core\SecurityHelper;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$isPdfMode = $isPdfMode ?? false;

// Resolusi Base64 Logo jika belum ada
if (empty($logoBase64)) {
    $logoPath = dirname(__DIR__, 2) . '/public/images/logo_alkahfi.png';
    if (file_exists($logoPath)) {
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }
}

// Resolusi Base64 TTD
$resolveSig = function(?string $path) {
    if (!$path) return null;
    $p = dirname(__DIR__, 2) . '/public/' . ltrim($path, '/');
    if (file_exists($p) && is_file($p)) {
        $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
        $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($p));
    }
    return null;
};

$deputiSigBase64 = $resolveSig($deputiUser['signature_path'] ?? ($deputiUser['emp_sig'] ?? null));
$direkturSigBase64 = $resolveSig($direktur['signature_path'] ?? ($direktur['user_sig'] ?? null));
$yayasanSigBase64 = $resolveSig($ketuaYayasan['signature_path'] ?? ($ketuaYayasan['user_sig'] ?? null));

$assignedUnits = $assignedUnits ?? [];
$unitNames = array_column($assignedUnits, 'name');
$taName = $currentTa['name'] ?? '2026/2027';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Kinerja Deputi Divisi Pendidikan - <?= SecurityHelper::escape($deputiUser['full_name'] ?? 'Deputi') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #1e293b;
            background: #ffffff;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .sheet {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
        }

        /* Top Bar Pratinjau (Hanya Layar Web) */
        .no-print-bar {
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        /* Kop Surat Resmi Yayasan Al-Kahfi */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .kop-logo {
            width: 65px;
            vertical-align: middle;
            text-align: center;
            padding-right: 10px;
        }
        .kop-logo img {
            height: 58px;
            width: auto;
        }
        .kop-text {
            vertical-align: middle;
            text-align: center;
        }
        .kop-inst {
            font-size: 13pt;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0;
            line-height: 1.15;
        }
        .kop-dept {
            font-size: 10.5pt;
            font-weight: 700;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .kop-tagline {
            font-size: 7.5pt;
            font-style: italic;
            color: #64748b;
            margin-top: 1px;
        }
        .kop-address {
            font-size: 7.5pt;
            color: #334155;
            margin-top: 2px;
            line-height: 1.25;
        }
        .kop-line {
            border-top: 2.5px solid #1e3a8a;
            border-bottom: 1px solid #1e3a8a;
            height: 2px;
            margin-top: 6px;
            margin-bottom: 10px;
        }

        /* Judul Dokumen */
        .doc-title-box {
            text-align: center;
            margin-bottom: 10px;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }

        /* Metadata Pegawai */
        .meta-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .meta-box td {
            padding: 3px 6px;
            vertical-align: top;
        }

        /* Tabel Penilaian */
        .rapor-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 10px;
        }
        .rapor-tbl th {
            background: #1e3a8a;
            color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            text-align: center;
            font-weight: 700;
            font-size: 7.5pt;
        }
        .rapor-tbl td {
            border: 1px solid #cbd5e1;
            padding: 3.5px 5px;
            vertical-align: top;
        }
        .area-row td {
            background: #f1f5f9;
            color: #1e3a8a;
            font-weight: 700;
            font-size: 8pt;
            border-top: 1.5px solid #94a3b8;
        }
        .total-row td {
            background: #f8fafc;
            font-weight: 800;
            font-size: 8pt;
            border-top: 2px solid #1e3a8a;
        }

        /* Card Ringkasan & Rekomendasi */
        .summary-card {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8pt;
            page-break-inside: avoid;
        }
        .summary-card td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
        }

        /* Blok Tanda Tangan */
        .sig-tbl {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8pt;
            page-break-inside: avoid;
        }
        .sig-tbl td {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
        }
        .sig-img {
            height: 45px;
            width: auto;
            margin: 4px 0;
        }
        .sig-space {
            height: 48px;
        }

        /* Tabel Panduan Rubrik */
        .rubrik-box {
            margin-top: 8px;
            font-size: 7pt;
            color: #64748b;
            border-top: 1px dashed #cbd5e1;
            padding-top: 5px;
            page-break-inside: avoid;
        }
        .rubrik-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-top: 4px;
        }
        .rubrik-tbl th, .rubrik-tbl td {
            border: 1px solid #cbd5e1;
            padding: 2.5px 5px;
        }
        .rubrik-tbl th {
            background: #f1f5f9;
            font-weight: 700;
            color: #1e293b;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .sheet { max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="sheet">

    <?php if (!$isPdfMode): ?>
        <div class="no-print no-print-bar">
            <div>
                <strong style="color: #1e3a8a; font-size: 0.9375rem;">
                    <i class="fa-solid fa-file-invoice"></i> Pratinjau Rapor Resmi Deputi Pendidikan
                </strong>
                <div style="font-size: 0.8125rem; color: #64748b; margin-top: 2px;">
                    Pegawai: <strong><?= SecurityHelper::escape($deputiUser['full_name'] ?? 'Deputi') ?></strong> &bull; Semester <?= SecurityHelper::escape($semester) ?> (<?= SecurityHelper::escape($taName) ?>)
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="<?= $baseUrl ?>/deputi/rapor/pdf?deputi_id=<?= (int)$deputiUserId ?>&semester=<?= SecurityHelper::escape($semester) ?>" target="_blank" style="padding: 6px 14px; background: #dc2626; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-file-pdf"></i> Download File PDF
                </a>
                <button type="button" onclick="window.print()" style="padding: 6px 14px; background: #2563eb; color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.8125rem;">
                    <i class="fa-solid fa-print"></i> Cetak Dokumen
                </button>
                <a href="<?= $baseUrl ?>/deputi/rapor?deputi_id=<?= (int)$deputiUserId ?>&semester=<?= SecurityHelper::escape($semester) ?>" style="padding: 6px 12px; background: #ffffff; color: #475569; border: 1px solid #cbd5e1; text-decoration: none; border-radius: 6px; font-size: 0.8125rem;">
                    Kembali
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- 1. KOP SURAT RESMI YAYASAN ISLAM AL-KAHFI BATAM -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" alt="Logo Al-Kahfi">
                <?php endif; ?>
            </td>
            <td class="kop-text">
                <div class="kop-inst">YAYASAN ISLAM AL-KAHFI BATAM</div>
                <div class="kop-dept">DIREKTORAT PENDIDIKAN</div>
                <div class="kop-tagline">Meniti Jejak Generasi Islam Pertama</div>
                <div class="kop-address">
                    Kompleks Al-Kahfi Islamic School, Jl. Jenderal Sudirman, Ruko Superblock Imperium Blok A40-47B, Taman Baloi, Batam Kota, Batam 29444<br>
                    Telepon: (0778) 7485869 / 0811-700-9257 &bull; Email: pendidikan@alkahfi.sch.id &bull; Website: www.aisbatam.sch.id
                </div>
            </td>
            <td style="width: 65px;"></td>
        </tr>
    </table>
    <div class="kop-line"></div>

    <!-- 2. JUDUL DOKUMEN -->
    <div class="doc-title-box">
        <div class="doc-title">LEMBAR RAPOR EVALUASI KINERJA DEPUTI DIVISI PENDIDIKAN</div>
        <div class="doc-subtitle">Berdasarkan Standar Operasional Prosedur (SOP) Divisi Pendidikan Yayasan Islam Al-Kahfi Batam (SOP 2017 Rev A)</div>
    </div>

    <!-- 3. METADATA PEGAWAI & PENILAI -->
    <table class="meta-box">
        <tr>
            <td style="width: 17%; font-weight: bold; color: #475569;">Nama Pegawai</td>
            <td style="width: 2%;">:</td>
            <td style="width: 36%;"><strong><?= SecurityHelper::escape($deputiUser['full_name'] ?? 'Deputi') ?></strong></td>
            <td style="width: 17%; font-weight: bold; color: #475569;">Periode Penilaian</td>
            <td style="width: 2%;">:</td>
            <td style="width: 26%;">Semester <?= SecurityHelper::escape($semester) ?> TA <?= SecurityHelper::escape($taName) ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #475569;">NIPY / ID</td>
            <td>:</td>
            <td><?= SecurityHelper::escape($deputiUser['nipy'] ?? '-') ?></td>
            <td style="font-weight: bold; color: #475569;">Tanggal Penilaian</td>
            <td>:</td>
            <td><?= !empty($evaluation['evaluation_date']) ? date('d/m/Y', strtotime($evaluation['evaluation_date'])) : date('d/m/Y') ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #475569;">Jabatan</td>
            <td>:</td>
            <td>Deputi Direktur Divisi Pendidikan</td>
            <td style="font-weight: bold; color: #475569;">Pejabat Penilai</td>
            <td>:</td>
            <td><strong>Direktur Pendidikan</strong></td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #475569;">Wilayah Binaan</td>
            <td>:</td>
            <td colspan="4"><strong><?= SecurityHelper::escape(implode(', ', $unitNames) ?: 'Seluruh Unit Binaan Yayasan') ?></strong></td>
        </tr>
    </table>

    <!-- 4. TABEL PENILAIAN PARAMETER KINERJA LENGKAP -->
    <table class="rapor-tbl">
        <thead>
            <tr>
                <th style="width: 22px;">No</th>
                <th style="width: 165px; text-align: left;">Area &amp; Indikator Kinerja Utama (KPI)</th>
                <th style="text-align: left;">Standar Acuan Kerja / Eviden SOP</th>
                <th style="width: 45px;">Bobot</th>
                <th style="width: 40px;">Skala</th>
                <th style="width: 45px;">Capaian</th>
                <th style="width: 55px;">Skor</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $curArea = 0;
            $rowNo = 1;
            $totWeight = 0.0;
            $totWeightedScore = 0.0;

            foreach ($indicators as $ind):
                $iId = $ind['id'];
                $detail = $details[$iId] ?? null;

                $weight = $detail ? (float)$detail['weight'] : (float)$ind['default_weight'];
                $scale = $detail ? (int)$detail['scale_1_to_5'] : 4;
                $achPct = $detail ? (float)$detail['achievement_pct'] : (($scale / 5.0) * 100.0);
                $weighted = $detail ? (float)$detail['weighted_score'] : ($weight * ($achPct / 100.0));

                $totWeight += $weight;
                $totWeightedScore += ($weighted * 100.0);

                if ($ind['area_no'] !== $curArea):
                    $curArea = $ind['area_no'];
            ?>
                <tr class="area-row">
                    <td class="text-center"><?= $curArea ?></td>
                    <td colspan="6">
                        AREA <?= $curArea ?>: <?= SecurityHelper::escape($ind['area_name']) ?>
                    </td>
                </tr>
            <?php endif; ?>
                <tr>
                    <td class="text-center" style="color:#64748b;"><?= $rowNo++ ?></td>
                    <td><strong><?= SecurityHelper::escape($ind['indicator_name']) ?></strong></td>
                    <td style="color:#475569; font-size:7pt;"><?= SecurityHelper::escape($ind['target_standard']) ?></td>
                    <td class="text-right"><?= number_format($weight * 100, 1) ?>%</td>
                    <td class="text-center font-bold" style="color:#1e3a8a;"><?= $scale ?></td>
                    <td class="text-right font-bold"><?= number_format($achPct, 0) ?>%</td>
                    <td class="text-right font-bold" style="color:#0f766e;"><?= number_format($weighted * 100, 2) ?>%</td>
                </tr>
            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="3" class="text-right" style="text-transform:uppercase;">TOTAL BOBOT &amp; SKOR AKHIR KINERJA:</td>
                <td class="text-right font-bold"><?= number_format($totWeight * 100, 1) ?>%</td>
                <td colspan="2"></td>
                <td class="text-right font-bold" style="font-size:9pt; color:#1e3a8a;"><?= number_format($totWeightedScore, 2) ?>%</td>
            </tr>
        </tbody>
    </table>

    <!-- 5. KOTAK KELAYAKAN, PREDIKAT & CATATAN DIREKTUR -->
    <table class="summary-card">
        <tr style="background:#f1f5f9;">
            <td colspan="2" style="font-weight:bold; color:#1e3a8a; font-size:8.5pt;">HASIL EVALUASI &amp; PREDIKAT KINERJA DEPUTI</td>
        </tr>
        <tr>
            <td style="width:28%; font-weight:bold;">Nilai Akhir Rapor Deputi</td>
            <td>
                <span style="font-size:10pt; font-weight:bold; color:#1e3a8a;"><?= number_format($totWeightedScore, 2) ?></span> / 100
            </td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Predikat Mutu Kinerja</td>
            <td>
                <?php
                $pred = $evaluation['predicate'] ?? ($totWeightedScore >= 90 ? 'A - Sangat Baik (Outstanding)' : ($totWeightedScore >= 80 ? 'B - Baik (Good)' : 'C - Cukup (Satisfactory)'));
                ?>
                <strong style="color:#0f766e; font-size:9pt;"><?= SecurityHelper::escape($pred) ?></strong>
            </td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Catatan &amp; Arahan Direktur</td>
            <td style="font-size:7.5pt; color:#334155; line-height:1.4;">
                <?= !empty($evaluation['evaluator_notes']) ? nl2br(SecurityHelper::escape($evaluation['evaluator_notes'])) : 'Terus pertahankan dedikasi kepemimpinan operasional, ritme kerja SOP (Weekly Meeting, Monthly Review, Kajian Pekanan), supervisi berkala 8 Standar Nasional Pendidikan, serta pengawasan timeplan RAPBS unit binaan.' ?>
            </td>
        </tr>
    </table>

    <!-- 6. BLOK TANDA TANGAN 3 PIHAK -->
    <table class="sig-tbl">
        <tr>
            <td>
                Yang Dinilai,<br>
                <strong>Deputi Direktur Pendidikan</strong>
                <div class="sig-space">
                    <?php if (!empty($deputiSigBase64)): ?>
                        <img src="<?= $deputiSigBase64 ?>" class="sig-img" alt="TTD Deputi">
                    <?php endif; ?>
                </div>
                <strong><u>( <?= SecurityHelper::escape($deputiUser['full_name'] ?? 'Deputi Pendidikan') ?> )</u></strong>
            </td>
            <td>
                Pejabat Penilai,<br>
                <strong>Direktur Pendidikan</strong>
                <div class="sig-space">
                    <?php if (!empty($direkturSigBase64)): ?>
                        <img src="<?= $direkturSigBase64 ?>" class="sig-img" alt="TTD Direktur">
                    <?php endif; ?>
                </div>
                <strong><u>( <?= SecurityHelper::escape($direktur['full_name'] ?? 'Direktur Pendidikan') ?> )</u></strong>
            </td>
            <td>
                Menyetujui &amp; Mengesahkan,<br>
                <strong>Ketua Yayasan Islam Al-Kahfi</strong>
                <div class="sig-space">
                    <?php if (!empty($yayasanSigBase64)): ?>
                        <img src="<?= $yayasanSigBase64 ?>" class="sig-img" alt="TTD Ketua Yayasan">
                    <?php endif; ?>
                </div>
                <strong><u>( <?= SecurityHelper::escape($ketuaYayasan['full_name'] ?? 'Pembina / Ketua Yayasan') ?> )</u></strong>
            </td>
        </tr>
    </table>

    <!-- 7. PANDUAN SKALA BOBOT PENILAIAN -->
    <div class="rubrik-box">
        <strong>Panduan Standar Skala Bobot Kinerja (SOP 2017 Rev A):</strong>
        <table class="rubrik-tbl">
            <tr>
                <th style="width:35px;">Skala</th>
                <th style="width:125px;">Predikat</th>
                <th style="width:80px;">Capaian (%)</th>
                <th>Keterangan Kriteria</th>
            </tr>
            <tr>
                <td class="text-center font-bold">5</td>
                <td>Sangat Baik (Outstanding)</td>
                <td class="text-center">90% &ndash; 100%</td>
                <td>Target tercapai melampaui standar SOP, efisien, tepat waktu &amp; eviden terdokumentasi sempurna.</td>
            </tr>
            <tr>
                <td class="text-center font-bold">4</td>
                <td>Baik (Good)</td>
                <td class="text-center">80% &ndash; 89%</td>
                <td>Target tercapai sesuai standar SOP dan timeplan tahunan tanpa kendala berarti.</td>
            </tr>
            <tr>
                <td class="text-center font-bold">3</td>
                <td>Cukup (Satisfactory)</td>
                <td class="text-center">70% &ndash; 79%</td>
                <td>Target tercapai dengan sedikit catatan perbaikan minor dalam pelaksanaan operasional.</td>
            </tr>
            <tr>
                <td class="text-center font-bold">2</td>
                <td>Kurang (Needs Improvement)</td>
                <td class="text-center">60% &ndash; 69%</td>
                <td>Target hanya tercapai sebagian, terdapat keterlambatan atau deviasi dari SOP.</td>
            </tr>
            <tr>
                <td class="text-center font-bold">1</td>
                <td>Sangat Kurang</td>
                <td class="text-center">&lt; 60%</td>
                <td>Target tidak tercapai, pelaksanaan standar SOP terabaikan secara signifikan.</td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>
