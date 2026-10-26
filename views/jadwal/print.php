<?php
// views/jadwal/print.php
use Core\SecurityHelper;

$totalKelas = count($classes);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran - <?= SecurityHelper::escape($unit['name'] ?? 'Sekolah') ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }
        .header h1 {
            font-size: 14pt;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 11pt;
            margin: 0 0 2px 0;
            font-weight: normal;
        }
        .header p {
            font-size: 9pt;
            margin: 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 2px;
            font-size: 8.5pt;
        }
        th {
            background-color: #fbd38d !important;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .th-sub {
            background-color: #feebc8 !important;
        }
        .col-hari {
            background-color: #fbd38d !important;
            font-weight: bold;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            width: 55px;
        }
        .break-row {
            background-color: #feebc8 !important;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .break-sholat {
            background-color: #bee3f8 !important;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .cell-subject {
            font-weight: bold;
            font-size: 8pt;
            line-height: 1.1;
        }
        .cell-teacher {
            font-size: 7.5pt;
            color: #2d3748;
            margin-top: 1px;
            line-height: 1.1;
        }
        .cell-inval {
            font-size: 7pt;
            font-weight: bold;
            color: #c05621;
        }
        .footer-sign {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .sign-box {
            text-align: center;
            width: 220px;
        }
        .sign-space {
            height: 50px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; padding: 10px; background: #edf2f7; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong>Mode Cetak Format Jadwal Pelajaran</strong> &bull; Ukuran rekomendasi: A4 Landscape
        </div>
        <div>
            <button onclick="window.print()" style="padding: 6px 16px; background: #0d9488; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                Cetak Sekarang (Ctrl + P)
            </button>
            <button onclick="window.close()" style="padding: 6px 12px; background: #e2e8f0; color: #333; border: none; border-radius: 4px; cursor: pointer; margin-left: 6px;">
                Tutup
            </button>
        </div>
    </div>

    <!-- Header Dokumen Cetak -->
    <div class="header">
        <h1>JADWAL PELAJARAN <?= SecurityHelper::escape($unit['name'] ?? 'SEKOLAH') ?></h1>
        <h2>TAHUN AJARAN <?= SecurityHelper::escape($ta['name'] ?? '') ?> - SEMESTER <?= SecurityHelper::escape($ta['semester'] ?? '') ?></h2>
        <p><?= SecurityHelper::escape($unit['address'] ?? '') ?></p>
    </div>

    <!-- Tabel Jadwal -->
    <table>
        <thead>
            <tr>
                <th rowspan="2" class="col-hari">HARI</th>
                <th rowspan="2" style="width: 75px;">WAKTU</th>
                <th rowspan="2" style="width: 35px;">JAM KE</th>
                <?php foreach ($classesByLevel as $lvl => $lvlClasses): ?>
                    <th colspan="<?= count($lvlClasses) ?>">
                        <?= SecurityHelper::escape($lvl) ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            <tr class="th-sub">
                <?php foreach ($classesByLevel as $lvl => $lvlClasses): ?>
                    <?php foreach ($lvlClasses as $c): ?>
                        <?php $short = preg_replace('/^Kelas\s+/i', '', $c['name']); ?>
                        <th><?= SecurityHelper::escape($short) ?></th>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daysList as $day): ?>
                <?php 
                    $slots = $timeSlotsByDay[$day] ?? [];
                    $totalSlots = count($slots);
                    if ($totalSlots === 0) continue;

                    foreach ($slots as $idx => $slot):
                        $jam = (int)$slot['jam_ke'];
                        $isBreak = (bool)$slot['is_break'];
                        $breakType = $slot['break_type'] ?? 'none';
                        $rowClass = ($breakType === 'sholat') ? 'break-sholat' : ($isBreak ? 'break-row' : '');
                ?>
                <tr class="<?= $rowClass ?>">
                    <?php if ($idx === 0): ?>
                        <td rowspan="<?= $totalSlots ?>" class="col-hari">
                            <?= $day ?>
                        </td>
                    <?php endif; ?>

                    <td><?= SecurityHelper::escape($slot['time_start']) ?> - <?= SecurityHelper::escape($slot['time_end']) ?></td>
                    <td><?= $jam === 0 ? '-' : $jam ?></td>

                    <?php if ($isBreak): ?>
                        <td colspan="<?= $totalKelas ?>">
                            <?= SecurityHelper::escape($slot['label'] ?: 'Istirahat') ?>
                        </td>
                    <?php else: ?>
                        <?php foreach ($classesByLevel as $lvl => $lvlClasses): ?>
                            <?php foreach ($lvlClasses as $c): ?>
                                <?php 
                                    $cId = (int)$c['id'];
                                    $cell = $scheduleMatrix[$day][$jam][$cId] ?? null;
                                ?>
                                <td>
                                    <?php if ($cell && !empty($cell['subject_name'])): ?>
                                        <div class="cell-subject"><?= SecurityHelper::escape($cell['subject_name']) ?></div>
                                        <div class="cell-teacher">
                                            <?php if (!empty($cell['substitute_teacher_id'])): ?>
                                                <span class="cell-inval">[Inval] <?= SecurityHelper::escape($cell['substitute_teacher_name']) ?></span>
                                            <?php else: ?>
                                                <?= SecurityHelper::escape($cell['teacher_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tanda Tangan Pengesahan -->
    <div class="footer-sign">
        <div class="sign-box">
            Mengetahui,<br>
            <strong>Kepala Sekolah</strong>
            <div class="sign-space"></div>
            <strong><u>( .................................................. )</u></strong><br>
            NIPY. .....................................
        </div>
        <div class="sign-box">
            Batam, <?= date('d F Y') ?><br>
            <strong>Waka Kurikulum</strong>
            <div class="sign-space"></div>
            <strong><u>( .................................................. )</u></strong><br>
            NIPY. .....................................
        </div>
    </div>

</body>
</html>
