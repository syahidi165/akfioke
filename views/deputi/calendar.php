<?php
// views/deputi/calendar.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;
use Core\Database;

$baseUrl = Auth::baseUrl();
$stakeholders = $stakeholders ?? [];
$academicStartYear = $academicStartYear ?? 2026;
$taName = $taName ?? ($academicStartYear . '/' . ($academicStartYear + 1));

$categoryColors = [
    'Weekly Meeting'          => ['bg' => '#3b82f6', 'text' => '#ffffff'],
    'Monthly Review'          => ['bg' => '#8b5cf6', 'text' => '#ffffff'],
    'Management Review'       => ['bg' => '#ec4899', 'text' => '#ffffff'],
    'Kajian Pekanan'          => ['bg' => '#10b981', 'text' => '#ffffff'],
    'Pelatihan SDM'           => ['bg' => '#f59e0b', 'text' => '#ffffff'],
    'Sosialisasi Visi Misi'   => ['bg' => '#0d9488', 'text' => '#ffffff'],
    'Timeplan SPMB/PGB/RAPBS' => ['bg' => '#475569', 'text' => '#ffffff'],
    'Lainnya'                 => ['bg' => '#64748b', 'text' => '#ffffff'],
];

// Helper render kartu bulan mini server-side (Juli s.d Juni)
$renderMiniMonth = function($mNum, $mName, $year, $evList, $catColors, $isKepsek = false) {
    $firstDay = (int)date('w', strtotime(sprintf('%04d-%02d-01', $year, $mNum)));
    $daysInMonth = (int)date('t', strtotime(sprintf('%04d-%02d-01', $year, $mNum)));
    $prefix = sprintf('%04d-%02d-', $year, $mNum);

    $monthEvents = [];
    foreach ($evList as $e) {
        if (str_starts_with($e['start_date'], $prefix)) {
            $monthEvents[$e['start_date']][] = $e;
        }
    }

    $monthNotes = [];
    ?>
    <div class="mini-month-card">
        <div class="mini-month-header" style="cursor: pointer;" onclick="jumpToMonth(<?= $year ?>, <?= $mNum - 1 ?>)" title="Klik untuk melihat kalender detail <?= $mName ?> <?= $year ?>">
            <span><?= $mName ?></span>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: normal;"><?= $year ?></span>
        </div>
        <table class="mini-table">
            <thead>
                <tr>
                    <th class="sun-th">M</th>
                    <th>S</th><th>S</th><th>R</th><th>K</th><th>J</th><th>S</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dateNum = 1;
                for ($row = 0; $row < 6; $row++) {
                    $hasCells = false;
                    $rowHtml = '<tr>';
                    for ($col = 0; $col < 7; $col++) {
                        $isSun = ($col === 0);
                        $sunClass = $isSun ? ' class="sun-td"' : '';
                        if ($row === 0 && $col < $firstDay) {
                            $rowHtml .= "<td{$sunClass}></td>";
                        } elseif ($dateNum > $daysInMonth) {
                            $rowHtml .= "<td{$sunClass}></td>";
                        } else {
                            $hasCells = true;
                            $dateStr = sprintf('%04d-%02d-%02d', $year, $mNum, $dateNum);
                            $dayEvs = $monthEvents[$dateStr] ?? [];

                            if (!empty($dayEvs)) {
                                $dotColor = $catColors[$dayEvs[0]['category']]['bg'] ?? '#2563eb';
                                $tdClick = 'onclick="handleDateCellClick(\'' . $dateStr . '\')" style="cursor: pointer;"';
                                $rowHtml .= '<td class="has-event' . ($isSun ? ' sun-td' : '') . '" ' . $tdClick . '>' . $dateNum . '<span class="mini-event-dot" style="background: ' . $dotColor . ';"></span></td>';
                                foreach ($dayEvs as $ev) {
                                    $monthNotes[] = ['date' => $dateNum, 'title' => $ev['title'], 'id' => $ev['id'], 'cat' => $ev['category']];
                                }
                            } else {
                                $tdAdd = !empty($isKepsek) ? '' : ' onclick="handleDateCellClick(\'' . $dateStr . '\')" style="cursor: pointer;"';
                                $rowHtml .= '<td' . $sunClass . $tdAdd . '>' . $dateNum . '</td>';
                            }
                            $dateNum++;
                        }
                    }
                    $rowHtml .= '</tr>';
                    if ($hasCells) {
                        echo $rowHtml;
                    }
                }
                ?>
            </tbody>
        </table>
        <div class="mini-month-notes">
            <?php if (!empty($monthNotes)): ?>
                <?php foreach (array_slice($monthNotes, 0, 3) as $n): 
                    $dotC = $catColors[$n['cat']]['bg'] ?? '#2563eb';
                ?>
                    <div class="mini-note-item" onclick="openMomModal(<?= $n['id'] ?>)">
                        <span style="color: <?= $dotC ?>; font-weight: 800;"><?= $n['date'] ?>:</span>
                        <span><?= SecurityHelper::escape(mb_strimwidth($n['title'], 0, 28, '...')) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (count($monthNotes) > 3): ?>
                    <div style="font-size: 10px; color: #2563eb; cursor: pointer;">
                        + <?= count($monthNotes) - 3 ?> agenda lainnya
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <span style="color: #94a3b8; font-style: italic;">Belum ada agenda</span>
            <?php endif; ?>
        </div>
    </div>
    <?php
};

// Data event di-encode ke JSON untuk kalender interaktif JS
$eventsJson = json_encode($events, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>

<style>
/* Styling Khusus Kalender Visual Interaktif */
.cal-view-tab {
    padding: 8px 18px;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.cal-view-tab.active {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
}

/* 12-Month Yearly Grid (Persis Contoh Lampiran Kalender 2022) */
.year-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}
@media (max-width: 1024px) {
    .year-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .year-grid { grid-template-columns: 1fr; }
}
.mini-month-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
}
.mini-month-header {
    font-weight: 800;
    font-size: 1.0625rem;
    color: #1e3a8a;
    border-bottom: 2px solid #eab308;
    padding-bottom: 4px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.mini-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.75rem;
    text-align: center;
}
.mini-table th {
    color: #1e293b;
    font-weight: 700;
    padding: 4px 0;
}
.mini-table th.sun-th, .mini-table td.sun-td {
    color: #dc2626; /* Minggu berwarna merah */
    font-weight: 700;
}
.mini-table td {
    padding: 4px 2px;
    height: 26px;
    vertical-align: middle;
    position: relative;
    cursor: default;
    border-radius: 4px;
}
.mini-table td.has-event {
    cursor: pointer;
    font-weight: 800;
    background: #eff6ff;
    color: #1d4ed8;
}
.mini-table td.has-event:hover {
    background: #dbeafe;
    transform: scale(1.1);
}
.mini-event-dot {
    position: absolute;
    bottom: 2px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 4px;
    border-radius: 50%;
}
.mini-month-notes {
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px dashed #e2e8f0;
    font-size: 0.6875rem;
    color: #475569;
    min-height: 40px;
}
.mini-note-item {
    margin-bottom: 2px;
    display: flex;
    align-items: baseline;
    gap: 4px;
    cursor: pointer;
}
.mini-note-item:hover {
    color: #2563eb;
    font-weight: 600;
}

/* Full Month Grid */
.full-month-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.full-month-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
}
.full-grid-head {
    background: #f8fafc;
    padding: 10px;
    text-align: center;
    font-weight: 700;
    font-size: 0.8125rem;
    color: #334155;
    border-bottom: 1px solid #cbd5e1;
    border-right: 1px solid #e2e8f0;
}
.full-grid-head.sun {
    color: #dc2626;
    background: #fef2f2;
}
.full-grid-cell {
    min-height: 100px;
    padding: 6px;
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
    transition: background 0.15s;
    cursor: pointer;
}
.full-grid-cell:hover {
    background: #f8fafc;
}
.full-grid-cell.sun {
    background: #fffafa;
}
.full-grid-cell.other-month {
    background: #f1f5f9;
    opacity: 0.5;
    cursor: default;
}
.full-cell-num {
    font-size: 0.875rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 4px;
    display: inline-block;
}
.full-grid-cell.sun .full-cell-num {
    color: #dc2626;
}
.full-cell-num.today {
    background: #2563eb;
    color: #ffffff !important;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}
.full-event-chip {
    font-size: 0.6875rem;
    padding: 2px 6px;
    border-radius: 4px;
    margin-bottom: 3px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.full-event-chip:hover {
    opacity: 0.85;
}
</style>

<div class="page-header" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge badge-primary">Deputi Pendidikan</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Ritme Kerja SOP: WM, MM, MR &amp; Kajian Pekanan</span>
            </div>
            <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading, #1e293b); margin: 0;">
                <i class="fa-solid fa-calendar-days" style="color: #2563eb; margin-right: 8px;"></i>
                Kalender Kerja Visual &amp; Meeting Manager (MoM)
            </h1>
            <p style="margin: 4px 0 0 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Deputi: <strong><?= SecurityHelper::escape($deputiUser['full_name'] ?? 'Deputi') ?></strong> &bull; Kalender visual interaktif, agenda notula rapat, komitmen tindak lanjut, dan undangan email.
            </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <?php if (count($deputiList) > 1 && Auth::hasAnyRole(['direktur_pendidikan', 'operator_yayasan'])): ?>
                <form method="GET" action="<?= $baseUrl ?>/deputi/calendar" style="display: flex; align-items: center; gap: 6px;">
                    <select name="deputi_id" class="form-control" style="font-size: 0.875rem;" onchange="this.form.submit()">
                        <?php foreach ($deputiList as $dl): ?>
                            <option value="<?= $dl['id'] ?>" <?= $dl['id'] == $deputiUserId ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($dl['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
            <a href="<?= $baseUrl ?>/deputi/calendar/pdf?deputi_id=<?= (int)$deputiUserId ?>" target="_blank" class="btn btn-outline" style="font-weight: 600; color: #dc2626; border-color: #fca5a5; background: #fff5f5;" title="Unduh Kalender Resmi Lengkap Beserta Keterangan Agenda (PDF)">
                <i class="fa-solid fa-file-pdf"></i> Download PDF Kalender
            </a>
            <?php if (!empty($isKepsek)): ?>
                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.8125rem; padding: 8px 14px; border: 1px solid #cbd5e1;">
                    <i class="fa-solid fa-eye"></i> Mode Lihat (Kepala Sekolah)
                </span>
            <?php else: ?>
                <button class="btn btn-primary" onclick="openAddEventModal()" style="font-weight: 600;">
                    <i class="fa-solid fa-plus"></i> Tambah Agenda Rapat
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Mode Switcher & Category Filter Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <!-- Switcher Buttons -->
    <div style="display: flex; gap: 8px;">
        <button type="button" class="cal-view-tab active" id="tabYearly" onclick="switchView('yearly')">
            <i class="fa-solid fa-calendar"></i> Kalender Tahunan (12 Bulan)
        </button>
        <button type="button" class="cal-view-tab" id="tabMonthly" onclick="switchView('monthly')">
            <i class="fa-solid fa-calendar-week"></i> Kalender Bulanan Interaktif
        </button>
        <button type="button" class="cal-view-tab" id="tabTable" onclick="switchView('table')">
            <i class="fa-solid fa-list-check"></i> Daftar Notula MoM &amp; Aksi
        </button>
    </div>

    <!-- Category Filter -->
    <div style="display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 0.8125rem; color: #64748b; font-weight: 600;">Filter Kategori:</span>
        <select id="categoryFilter" class="form-control" style="font-size: 0.8125rem; width: auto;" onchange="applyFilters()">
            <option value="ALL">-- Semua Kategori --</option>
            <option value="Weekly Meeting">Weekly Meeting (WM)</option>
            <option value="Monthly Review">Monthly Review (MM)</option>
            <option value="Management Review">Management Review (MR)</option>
            <option value="Kajian Pekanan">Kajian Pekanan Guru</option>
            <option value="Pelatihan SDM">Pelatihan SDM</option>
            <option value="Sosialisasi Visi Misi">Sosialisasi Visi Misi</option>
            <option value="Timeplan SPMB/PGB/RAPBS">Timeplan SPMB/PGB/RAPBS</option>
        </select>
    </div>
</div>

<!-- Quick Stat Badges -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 10px; margin-bottom: 20px;">
    <?php
    $catCounts = [
        'Weekly Meeting'   => 0,
        'Monthly Review'   => 0,
        'Management Review'=> 0,
        'Kajian Pekanan'   => 0,
        'Pelatihan SDM'    => 0,
    ];
    foreach ($events as $ev) {
        if (isset($catCounts[$ev['category']])) $catCounts[$ev['category']]++;
    }
    ?>
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 14px;">
        <div style="font-size: 11px; color: #1e40af; font-weight: 600;">Weekly Meeting (WM)</div>
        <div style="font-size: 1.25rem; font-weight: 800; color: #1d4ed8;"><?= $catCounts['Weekly Meeting'] ?> Agenda</div>
    </div>
    <div style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 8px; padding: 10px 14px;">
        <div style="font-size: 11px; color: #5b21b6; font-weight: 600;">Monthly Review (MM)</div>
        <div style="font-size: 1.25rem; font-weight: 800; color: #6d28d9;"><?= $catCounts['Monthly Review'] ?> Agenda</div>
    </div>
    <div style="background: #fdf2f8; border: 1px solid #fbcfe8; border-radius: 8px; padding: 10px 14px;">
        <div style="font-size: 11px; color: #9d174d; font-weight: 600;">Management Review (MR)</div>
        <div style="font-size: 1.25rem; font-weight: 800; color: #be185d;"><?= $catCounts['Management Review'] ?> Agenda</div>
    </div>
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 10px 14px;">
        <div style="font-size: 11px; color: #065f46; font-weight: 600;">Kajian Pekanan Guru</div>
        <div style="font-size: 1.25rem; font-weight: 800; color: #047857;"><?= $catCounts['Kajian Pekanan'] ?> Sesi</div>
    </div>
    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px;">
        <div style="font-size: 11px; color: #92400e; font-weight: 600;">Pelatihan SDM</div>
        <div style="font-size: 1.25rem; font-weight: 800; color: #b45309;"><?= $catCounts['Pelatihan SDM'] ?> Program</div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- VIEW 1: KALENDER TAHUNAN 12 BULAN (PERSIS FORMAT LAMPIRAN KALENDER 2022)   -->
<!-- ========================================================================= -->
<div id="viewYearlyContainer">
    <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <h2 style="margin: 0; font-size: 1.375rem; font-weight: 800; color: #1e3a8a;">
                    <i class="fa-solid fa-calendar-check" style="color: #2563eb;"></i> Kalender Pendidikan Yayasan (Juli - Juni)
                </h2>
                <div style="display: flex; gap: 6px; align-items: center;">
                    <button type="button" class="btn btn-outline" onclick="changeYear(-1)" style="padding: 3px 10px; font-size: 12px; font-weight: 700;">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <span id="yearlyYearLabel" style="font-weight: 800; font-size: 1.0625rem; color: #1e3a8a; padding: 2px 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px;">
                        <?= SecurityHelper::escape($academicStartYear . ' / ' . ($academicStartYear + 1)) ?>
                    </span>
                    <button type="button" class="btn btn-outline" onclick="changeYear(1)" style="padding: 3px 10px; font-size: 12px; font-weight: 700;">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div style="font-size: 0.8125rem; color: #64748b;">
                <span style="color: #dc2626; font-weight: 700;">M = Minggu</span> &bull; 
                <span style="color: #2563eb; font-weight: 700;">Dot Biru/Warna = Ada Agenda Rapat/Kajian</span>
            </div>
        </div>

        <div id="yearlyCalendarGrid">
            <!-- SEMESTER 1 (GANJIL): JULI - DESEMBER -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding: 10px 16px; background: #f0fdf4; border-left: 5px solid #16a34a; border-radius: 6px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-calendar-check" style="color: #16a34a; font-size: 1.125rem;"></i>
                    <span style="font-weight: 800; color: #15803d; font-size: 1rem; letter-spacing: 0.5px;">
                        SEMESTER 1 (GANJIL) &bull; TAHUN <?= $academicStartYear ?> (Juli &ndash; Desember)
                    </span>
                </div>
                <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 11px; font-weight: 700; padding: 4px 10px;">
                    Awal Tahun Ajaran
                </span>
            </div>

            <div class="year-grid" style="margin-bottom: 28px;">
                <?php 
                $sem1Months = [
                    [7, 'Juli', $academicStartYear],
                    [8, 'Agustus', $academicStartYear],
                    [9, 'September', $academicStartYear],
                    [10, 'Oktober', $academicStartYear],
                    [11, 'November', $academicStartYear],
                    [12, 'Desember', $academicStartYear],
                ];
                foreach ($sem1Months as $sm) {
                    $renderMiniMonth($sm[0], $sm[1], $sm[2], $events, $categoryColors, !empty($isKepsek));
                }
                ?>
            </div>

            <!-- SEMESTER 2 (GENAP): JANUARI - JUNI -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding: 10px 16px; background: #eff6ff; border-left: 5px solid #2563eb; border-radius: 6px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-calendar-days" style="color: #2563eb; font-size: 1.125rem;"></i>
                    <span style="font-weight: 800; color: #1d4ed8; font-size: 1rem; letter-spacing: 0.5px;">
                        SEMESTER 2 (GENAP) &bull; TAHUN <?= $academicStartYear + 1 ?> (Januari &ndash; Juni)
                    </span>
                </div>
                <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 700; padding: 4px 10px;">
                    Akhir Tahun Ajaran
                </span>
            </div>

            <div class="year-grid">
                <?php 
                $sem2Months = [
                    [1, 'Januari', $academicStartYear + 1],
                    [2, 'Februari', $academicStartYear + 1],
                    [3, 'Maret', $academicStartYear + 1],
                    [4, 'April', $academicStartYear + 1],
                    [5, 'Mei', $academicStartYear + 1],
                    [6, 'Juni', $academicStartYear + 1],
                ];
                foreach ($sem2Months as $sm) {
                    $renderMiniMonth($sm[0], $sm[1], $sm[2], $events, $categoryColors, !empty($isKepsek));
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- VIEW 2: KALENDER BULANAN INTERAKTIF PENUH (FULL MONTH GRID)               -->
<!-- ========================================================================= -->
<div id="viewMonthlyContainer" style="display: none;">
    <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
        <div class="full-month-header">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <button type="button" class="btn btn-outline" onclick="changeMonth(-1)">
                    <i class="fa-solid fa-chevron-left"></i> Bulan Lalu
                </button>
                <h2 id="currentMonthYearLabel" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #1e3a8a; min-width: 180px; text-align: center;">
                    Bulan 2026
                </h2>
                <button type="button" class="btn btn-outline" onclick="changeMonth(1)">
                    Bulan Depan <i class="fa-solid fa-chevron-right"></i>
                </button>
                <button type="button" class="btn btn-outline" onclick="goToToday()" style="font-size: 0.8125rem;">
                    Hari Ini
                </button>
                <select id="jumpMonthSelect" class="form-control" style="width: auto; font-size: 0.8125rem; font-weight: 600;" onchange="handleMonthJump(this.value)">
                    <option value="">-- Pilih Bulan Akademik --</option>
                    <optgroup label="Semester 1 (Ganjil)">
                        <option value="6-0">Juli (Sem 1)</option>
                        <option value="7-0">Agustus (Sem 1)</option>
                        <option value="8-0">September (Sem 1)</option>
                        <option value="9-0">Oktober (Sem 1)</option>
                        <option value="10-0">November (Sem 1)</option>
                        <option value="11-0">Desember (Sem 1)</option>
                    </optgroup>
                    <optgroup label="Semester 2 (Genap)">
                        <option value="0-1">Januari (Sem 2)</option>
                        <option value="1-1">Februari (Sem 2)</option>
                        <option value="2-1">Maret (Sem 2)</option>
                        <option value="3-1">April (Sem 2)</option>
                        <option value="4-1">Mei (Sem 2)</option>
                        <option value="5-1">Juni (Sem 2)</option>
                    </optgroup>
                </select>
            </div>
            <div style="font-size: 0.8125rem; color: #64748b;">
                Klik pada tanggal untuk menambah agenda &bull; Klik pada badge agenda untuk membuka notula MoM
            </div>
        </div>

        <!-- 7-column header: M S S R K J S -->
        <div class="full-month-grid">
            <div class="full-grid-head sun">Minggu</div>
            <div class="full-grid-head">Senin</div>
            <div class="full-grid-head">Selasa</div>
            <div class="full-grid-head">Rabu</div>
            <div class="full-grid-head">Kamis</div>
            <div class="full-grid-head">Jumat</div>
            <div class="full-grid-head">Sabtu</div>
        </div>
        <div class="full-month-grid" id="fullMonthDaysGrid" style="border-top: none;">
            <!-- Diisi secara dinamis oleh JS -->
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- VIEW 3: DAFTAR TABEL TIMELINE & REKAP MoM                                 -->
<!-- ========================================================================= -->
<div id="viewTableContainer" style="display: none;">
    <div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: #1e293b;">
                <i class="fa-solid fa-list-check" style="color: #4f46e5; margin-right: 6px;"></i> Daftar Agenda Kerja &amp; Rekap Realisasi MoM
            </h3>
        </div>

        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #64748b;">
                        <th style="padding: 12px 16px;">Waktu &amp; Tanggal</th>
                        <th style="padding: 12px 16px;">Kategori</th>
                        <th style="padding: 12px 16px;">Agenda / Judul Kegiatan</th>
                        <th style="padding: 12px 16px;">Unit / Lokasi</th>
                        <th style="padding: 12px 16px; text-align: center;">Undangan</th>
                        <th style="padding: 12px 16px; text-align: center;">Notula &amp; Realisasi</th>
                        <th style="padding: 12px 16px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableEventBody">
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="7" style="padding: 30px; text-align: center; color: var(--text-muted);">
                                Belum ada agenda rapat atau kegiatan yang dijadwalkan. Klik "Tambah Agenda" untuk memulai.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $ev): ?>
                            <?php
                                $catColors = [
                                    'Weekly Meeting'   => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
                                    'Monthly Review'   => ['bg' => '#f5f3ff', 'text' => '#6d28d9'],
                                    'Management Review'=> ['bg' => '#fdf2f8', 'text' => '#be185d'],
                                    'Kajian Pekanan'   => ['bg' => '#ecfdf5', 'text' => '#047857'],
                                    'Pelatihan SDM'    => ['bg' => '#fffbeb', 'text' => '#b45309'],
                                    'Sosialisasi Visi Misi' => ['bg' => '#f0fdfa', 'text' => '#0f766e'],
                                    'Timeplan SPMB/PGB/RAPBS' => ['bg' => '#f1f5f9', 'text' => '#334155'],
                                    'Lainnya'          => ['bg' => '#f1f5f9', 'text' => '#475569'],
                                ];
                                $cColor = $catColors[$ev['category']] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                            ?>
                            <tr class="event-table-row" data-category="<?= SecurityHelper::escape($ev['category']) ?>" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 16px; white-space: nowrap;">
                                    <div style="font-weight: 700; color: #1e293b; font-size: 0.875rem;">
                                        <?= date('d M Y', strtotime($ev['start_date'])) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b;">
                                        <?= !empty($ev['start_time']) ? date('H:i', strtotime($ev['start_time'])) . ' WIB' : 'Penuh' ?>
                                    </div>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <span class="badge" style="background: <?= $cColor['bg'] ?>; color: <?= $cColor['text'] ?>; font-size: 11px; padding: 4px 8px;">
                                        <?= SecurityHelper::escape($ev['category']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <div style="font-weight: 600; color: #1e293b; font-size: 0.875rem;">
                                        <?= SecurityHelper::escape($ev['title']) ?>
                                    </div>
                                    <?php if (!empty($ev['description'])): ?>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                            <?= SecurityHelper::escape(mb_strimwidth($ev['description'], 0, 70, '...')) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 16px; font-size: 0.8125rem; color: #475569;">
                                    <div><i class="fa-solid fa-school" style="font-size: 10px; color: #94a3b8;"></i> <?= SecurityHelper::escape($ev['unit_name'] ?: 'Semua Unit') ?></div>
                                    <?php if (!empty($ev['location'])): ?>
                                        <div style="font-size: 0.75rem; color: #64748b;"><i class="fa-solid fa-location-dot" style="font-size: 10px; color: #94a3b8;"></i> <?= SecurityHelper::escape($ev['location']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <?php if (!empty($ev['invitation_sent_at'])): ?>
                                        <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 10px;">
                                            <i class="fa-solid fa-check"></i> Terkirim (<?= $ev['invitation_h_days'] >= 1 ? "H-{$ev['invitation_h_days']}" : "Hari H" ?>)
                                        </span>
                                    <?php elseif (!empty($isKepsek)): ?>
                                        <span style="color: #94a3b8; font-size: 11px;">-</span>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline" onclick="openInviteModal(<?= $ev['id'] ?>, '<?= SecurityHelper::escape($ev['title']) ?>')" style="padding: 2px 8px; font-size: 11px;">
                                            <i class="fa-solid fa-envelope"></i> Kirim Undangan
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <?php if (!empty($ev['mom_id'])): ?>
                                        <div>
                                            <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 700;">
                                                MoM: <?= number_format((float)$ev['realization_pct'], 0) ?>% Tuntas
                                            </span>
                                        </div>
                                        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                            <?= (int)$ev['completed_actions'] ?>/<?= (int)$ev['total_actions'] ?> komitmen
                                        </div>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #94a3b8; font-size: 10px;">Belum Diisi</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 16px; text-align: right; white-space: nowrap;">
                                    <?php if (!empty($isKepsek)): ?>
                                        <button class="btn btn-outline" onclick="openMomModal(<?= $ev['id'] ?>)" style="padding: 4px 10px; font-size: 0.8125rem;">
                                            <i class="fa-solid fa-eye"></i> Detail MoM
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-primary" onclick="openMomModal(<?= $ev['id'] ?>)" style="padding: 4px 10px; font-size: 0.8125rem;">
                                            <i class="fa-solid fa-file-pen"></i> MoM &amp; Komitmen
                                        </button>
                                        <form action="<?= $baseUrl ?>/deputi/calendar/delete-event" method="POST" style="display: inline-block;" onsubmit="return confirm('Hapus agenda kegiatan ini?')">
                                            <?= CSRF::inputField() ?>
                                            <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                            <input type="hidden" name="deputi_id" value="<?= $deputiUserId ?>">
                                            <button type="submit" class="btn btn-outline" style="padding: 4px 8px; font-size: 0.8125rem; color: #dc2626; border-color: #fecaca;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 0: DAFTAR AGENDA PADA TANGGAL (MULTI-AGENDA SUPPORT)               -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="dayEventsModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9998; align-items: center; justify-content: center; padding: 20px;">
    <div class="modal-container" style="max-width: 650px; max-height: 85vh; overflow-y: auto; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: flex-start; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <div>
                <h3 class="card-title" id="dayEventsModalTitle" style="margin: 0; font-size: 1.125rem; color: #1e293b; font-weight: 700;">
                    <i class="fa-solid fa-calendar-day" style="color: #2563eb;"></i> Agenda Tanggal
                </h3>
                <div id="dayEventsModalSubtitle" style="font-size: 0.8125rem; color: #64748b; margin-top: 2px;"></div>
            </div>
            <button type="button" class="btn btn-outline" onclick="closeDayEventsModal()" style="padding: 4px 10px; font-size: 1rem; line-height: 1; border-color: #cbd5e1;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 18px 20px;">
            <div id="dayEventsList" style="display: flex; flex-direction: column; gap: 10px;"></div>
            
            <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <span style="font-size: 0.8125rem; color: #64748b; font-weight: 500;">
                    <i class="fa-solid fa-circle-question" style="color: #3b82f6;"></i> Ingin menjadwalkan rapat unit lain di tanggal ini?
                </span>
                <button type="button" class="btn btn-primary" id="btnAddNewEventOnDate" style="font-size: 0.8125rem; font-weight: 600; padding: 6px 14px;">
                    <i class="fa-solid fa-plus-circle"></i> + Tambah Agenda Baru
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: TAMBAH / EDIT AGENDA                                              -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="addEventModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="modal-container" style="max-width: 600px; border-radius: 12px;">
        <form action="<?= $baseUrl ?>/deputi/calendar/save-event" method="POST">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="event_modal_id" value="0">
            <input type="hidden" name="deputi_user_id" value="<?= $deputiUserId ?>">

            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                <h3 class="card-title" id="event_modal_header_title" style="margin: 0; font-size: 1.125rem; font-weight: 700;">
                    <i class="fa-solid fa-calendar-plus" style="color: #2563eb;"></i> Tambah Agenda Rapat &amp; Kegiatan
                </h3>
            </div>
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px; padding: 18px 20px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Judul Agenda / Rapat *</label>
                    <input type="text" name="title" id="event_modal_title" class="form-control" placeholder="Contoh: Weekly Meeting WM 1 - Pembahasan Kesiapan KBM" required>
                </div>
                <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Kategori Agenda *</label>
                        <select name="category" id="event_modal_category" class="form-control" required>
                            <option value="Weekly Meeting">Weekly Meeting (WM)</option>
                            <option value="Monthly Review">Monthly Review (MM)</option>
                            <option value="Management Review">Management Review (MR)</option>
                            <option value="Kajian Pekanan">Kajian Pekanan Guru</option>
                            <option value="Sosialisasi Visi Misi">Sosialisasi Visi Misi &amp; QO</option>
                            <option value="Pelatihan SDM">Pelatihan &amp; Kompetensi SDM</option>
                            <option value="Timeplan SPMB/PGB/RAPBS">Timeplan SPMB/PGB/RAPBS</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Unit Terkait</label>
                        <select name="unit_id" id="event_modal_unit_id" class="form-control">
                            <option value="">-- Semua Unit / Lintas Yayasan --</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= SecurityHelper::escape($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tanggal Pelaksanaan *</label>
                        <input type="date" name="start_date" id="event_modal_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Waktu Mulai</label>
                        <input type="time" name="start_time" id="event_modal_start_time" class="form-control" value="08:30">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Lokasi / Tautan Rapat</label>
                    <input type="text" name="location" id="event_modal_location" class="form-control" placeholder="Contoh: Ruang Rapat Yayasan / Google Meet">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Deskripsi &amp; Pokok Bahasan</label>
                    <textarea name="description" id="event_modal_description" class="form-control" rows="2" placeholder="Tuliskan pokok agenda yang akan dibahas..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px; padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" class="btn btn-outline" onclick="closeAddEventModal()">Batal</button>
                <button type="submit" id="event_modal_submit_btn" class="btn btn-primary"><i class="fa-solid fa-save"></i> Jadwalkan Agenda</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: POP-UP NOTULA MoM & DAFTAR KOMITMEN TINDAK LANJUT                -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="momModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="modal-container" style="max-width: 950px; width: 95vw; max-height: 92vh; overflow-y: auto; border-radius: 12px;">
        <form action="<?= $baseUrl ?>/deputi/calendar/save-mom" method="POST" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="event_id" id="mom_event_id" value="0">
            <input type="hidden" name="deputi_id" value="<?= $deputiUserId ?>">

            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                <div>
                    <h3 class="card-title" id="momModalTitle" style="margin: 0; font-size: 1.125rem; font-weight: 700;">
                        <i class="fa-solid fa-file-signature" style="color: #0d9488;"></i> Notula Rapat (MoM) &amp; Komitmen Tindak Lanjut
                    </h3>
                    <div id="momEventSubtitle" style="font-size: 0.8125rem; color: #64748b; margin-top: 2px;"></div>
                </div>
            </div>

            <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px; padding: 18px 20px;">
                <!-- Rekap Komitmen Belum Tuntas Otomatis -->
                <div id="prevUnfinishedBox" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 14px;">
                    <div style="font-weight: 700; color: #1e40af; font-size: 0.8125rem; margin-bottom: 4px;">
                        <i class="fa-solid fa-clock-rotate-left"></i> Komitmen Terbawa Otomatis dari Rapat Sejenis Sebelumnya:
                    </div>
                    <div id="prevUnfinishedInfo" style="font-size: 0.8125rem; color: #1e3a8a;"></div>
                </div>

                <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tanggal Notula / Pelaksanaan *</label>
                        <input type="date" name="meeting_date" id="mom_meeting_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Upload Lampiran Berkas MoM (PDF/Doc/Foto)</label>
                        <input type="file" name="mom_file" class="form-control">
                        <div id="momCurrentFile" style="font-size: 11px; color: #0369a1; margin-top: 3px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Isi Notula Rapat &amp; Keputusan Musyawarah *</label>
                    <textarea name="mom_content" id="mom_content" class="form-control" rows="3" placeholder="Tuliskan ringkasan jalannya rapat, hasil kesepakatan, dan keputusan rapat..." required></textarea>
                </div>

                <!-- Action Items Section -->
                <div style="background: #ffffff; border-radius: 8px; padding: 16px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-weight: 700; font-size: 0.9375rem; color: #1e293b;">
                                <i class="fa-solid fa-list-check" style="color: #4f46e5;"></i> Butir Komitmen / Action Items (Tindak Lanjut)
                            </span>
                            <span id="momAvgRealizationWrapper" style="font-size: 0.75rem; font-weight: 600; color: #475569;">
                                Realisasi Rata-rata: <span id="momAvgScoreBadge" style="display: inline-block; padding: 2px 8px; border-radius: 9999px; background: #e0f2fe; color: #0369a1; font-weight: 800;">0%</span>
                            </span>
                        </div>
                        <button type="button" class="btn btn-outline" id="btnMomAddAction" onclick="addActionItemRow()" style="padding: 4px 10px; font-size: 0.8125rem; font-weight: 600; color: #2563eb; border-color: #93c5fd;">
                            <i class="fa-solid fa-plus"></i> Tambah Butir
                        </button>
                    </div>

                    <!-- Unified Actions Table -->
                    <div id="momActionsWrapper" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <table id="momActionsTable" style="width: 100%; font-size: 0.8125rem; border-collapse: collapse; min-width: 750px;">
                            <thead>
                                <tr style="background: #f8fafc; color: #475569; border-bottom: 2px solid #e2e8f0;">
                                    <th style="padding: 8px 6px; text-align: center; width: 36px;">No</th>
                                    <th style="padding: 8px 8px;">Topik Komitmen &amp; Sumber</th>
                                    <th style="padding: 8px 8px; width: 140px;">PIC</th>
                                    <th style="padding: 8px 8px; width: 130px;">Tenggat</th>
                                    <th style="padding: 8px 8px; width: 220px; text-align: center;">Progres Capaian (%)</th>
                                    <th style="padding: 8px 8px; width: 100px; text-align: center;">Status</th>
                                    <th style="padding: 8px 6px; width: 44px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="momActionsTbody"></tbody>
                        </table>
                        <div id="momActionsEmpty" style="padding: 20px; text-align: center; color: #94a3b8; font-style: italic; font-size: 0.8125rem; display: none;">
                            Belum ada komitmen untuk agenda ini. Klik <strong>+ Tambah Butir</strong> di atas untuk menambahkan.
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px; padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" class="btn btn-outline" onclick="closeMomModal()">Tutup</button>
                <button type="submit" id="btnSubmitMom" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Notula &amp; Komitmen</button>
            </div>
        </form>
    </div>
</div>
<!-- MODAL 3: KIRIM UNDANGAN RAPAT                                             -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="inviteModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="modal-container" style="max-width: 500px;">
        <form action="<?= $baseUrl ?>/deputi/calendar/send-invitation" method="POST">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="event_id" id="invite_event_id" value="0">
            <input type="hidden" name="deputi_id" value="<?= $deputiUserId ?>">

            <div class="modal-header">
                <h3 class="card-title" style="margin: 0; font-size: 1.125rem;">
                    <i class="fa-solid fa-paper-plane" style="color: #2563eb;"></i> Broadcast Undangan Rapat
                </h3>
            </div>
            <div class="modal-body">
                <p style="font-size: 0.875rem; color: #334155; margin-bottom: 12px;">
                    Kirim notifikasi &amp; email undangan untuk agenda: <strong id="inviteEventTitle"></strong>
                </p>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Pilih Stakeholder / Peserta Rapat:</label>
                    <div style="display: flex; flex-direction: column; gap: 6px; max-height: 180px; overflow-y: auto; background: #f8fafc; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <?php 
                        foreach ($stakeholders as $sh):
                        ?>
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; cursor: pointer;">
                                <input type="checkbox" name="recipients[]" value="<?= $sh['id'] ?>" checked style="accent-color: #2563eb;">
                                <span><?= SecurityHelper::escape($sh['full_name']) ?> <span style="color: #64748b; font-size: 11px;">(<?= SecurityHelper::escape($sh['display_name']) ?>)</span></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeInviteModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Kirim Sekarang</button>
            </div>
        </form>
    </div>
</div>

<script>
// Seluruh Events dari Database
var allEvents = <?= $eventsJson ?>;
var academicStartYear = <?= (int)($academicStartYear ?? 2026) ?>;
var isKepsek = <?= !empty($isKepsek) ? 'true' : 'false' ?>;
var canDeleteMomFile = <?= in_array(Auth::activeRoleName(), ['deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan'], true) ? 'true' : 'false' ?>;
var currentYear = <?= (int)date('Y') ?>;
var currentMonth = <?= (int)date('n') - 1 ?>; // Bulan berjalan saat ini (0-11)
var selectedCategory = 'ALL';

var monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

// 12 Bulan Kalender Pendidikan: Juli s.d Juni
var academicMonths = [
    { mIndex: 6,  name: 'Juli',      yearOffset: 0 },
    { mIndex: 7,  name: 'Agustus',   yearOffset: 0 },
    { mIndex: 8,  name: 'September', yearOffset: 0 },
    { mIndex: 9,  name: 'Oktober',   yearOffset: 0 },
    { mIndex: 10, name: 'November',  yearOffset: 0 },
    { mIndex: 11, name: 'Desember',  yearOffset: 0 },
    { mIndex: 0,  name: 'Januari',   yearOffset: 1 },
    { mIndex: 1,  name: 'Februari',  yearOffset: 1 },
    { mIndex: 2,  name: 'Maret',     yearOffset: 1 },
    { mIndex: 3,  name: 'April',     yearOffset: 1 },
    { mIndex: 4,  name: 'Mei',       yearOffset: 1 },
    { mIndex: 5,  name: 'Juni',      yearOffset: 1 }
];

var categoryColors = {
    'Weekly Meeting':    { bg: '#3b82f6', text: '#ffffff' },
    'Monthly Review':    { bg: '#8b5cf6', text: '#ffffff' },
    'Management Review': { bg: '#ec4899', text: '#ffffff' },
    'Kajian Pekanan':    { bg: '#10b981', text: '#ffffff' },
    'Pelatihan SDM':     { bg: '#f59e0b', text: '#ffffff' },
    'Sosialisasi Visi Misi': { bg: '#0d9488', text: '#ffffff' },
    'Timeplan SPMB/PGB/RAPBS': { bg: '#475569', text: '#ffffff' },
    'Lainnya':           { bg: '#64748b', text: '#ffffff' }
};

// Switch Antara Tab Tampilan
function switchView(mode) {
    document.querySelectorAll('.cal-view-tab').forEach(function(b) { b.classList.remove('active'); });
    document.getElementById('viewYearlyContainer').style.display = 'none';
    document.getElementById('viewMonthlyContainer').style.display = 'none';
    document.getElementById('viewTableContainer').style.display = 'none';

    if (mode === 'yearly') {
        document.getElementById('tabYearly').classList.add('active');
        document.getElementById('viewYearlyContainer').style.display = 'block';
        renderYearlyGrid();
    } else if (mode === 'monthly') {
        document.getElementById('tabMonthly').classList.add('active');
        document.getElementById('viewMonthlyContainer').style.display = 'block';
        renderMonthlyGrid();
    } else if (mode === 'table') {
        document.getElementById('tabTable').classList.add('active');
        document.getElementById('viewTableContainer').style.display = 'block';
    }
}

function applyFilters() {
    selectedCategory = document.getElementById('categoryFilter').value;
    renderYearlyGrid();
    renderMonthlyGrid();

    // Filter baris tabel
    document.querySelectorAll('.event-table-row').forEach(function(row) {
        var cat = row.getAttribute('data-category');
        if (selectedCategory === 'ALL' || cat === selectedCategory) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function getFilteredEvents() {
    if (selectedCategory === 'ALL') return allEvents;
    return allEvents.filter(function(e) { return e.category === selectedCategory; });
}

// -----------------------------------------------------------------------------
// RENDER VIEW 1: KALENDER TAHUNAN 12 BULAN (PERSIS LAMPIRAN USER)
// -----------------------------------------------------------------------------
function changeYear(delta) {
    academicStartYear += delta;
    var lbl = document.getElementById('yearlyYearLabel');
    if (lbl) {
        lbl.innerText = academicStartYear + ' / ' + (academicStartYear + 1);
    }
    renderYearlyGrid();
}

function jumpToMonth(year, monthIdx) {
    currentYear = year;
    currentMonth = monthIdx;
    switchView('monthly');
}

function handleMonthJump(val) {
    if (!val) return;
    var parts = val.split('-');
    var mIdx = parseInt(parts[0], 10);
    var yOffset = parseInt(parts[1], 10);
    currentYear = academicStartYear + yOffset;
    currentMonth = mIdx;
    renderMonthlyGrid();
}

function buildMiniMonthCard(mObj, baseYear, dateMap) {
    var card = document.createElement('div');
    card.className = 'mini-month-card';

    var monthYear = baseYear + mObj.yearOffset;

    // Header bulan
    var header = document.createElement('div');
    header.className = 'mini-month-header';
    header.style.cursor = 'pointer';
    header.title = 'Klik untuk melihat kalender detail bulan ' + mObj.name + ' ' + monthYear;
    header.innerHTML = '<span>' + mObj.name + '</span><span style="font-size:0.75rem; color:#64748b; font-weight:normal;">' + monthYear + '</span>';
    (function(tYear, tMonth) {
        header.onclick = function() {
            jumpToMonth(tYear, tMonth);
        };
    })(monthYear, mObj.mIndex);
    card.appendChild(header);

    // Table M S S R K J S
    var table = document.createElement('table');
    table.className = 'mini-table';
    table.innerHTML = '<thead><tr>' +
                      '<th class="sun-th">M</th>' +
                      '<th>S</th><th>S</th><th>R</th><th>K</th><th>J</th><th>S</th>' +
                      '</tr></thead>';
    var tbody = document.createElement('tbody');

    var firstDay = new Date(monthYear, mObj.mIndex, 1).getDay(); // 0 is Sunday
    var daysInMonth = new Date(monthYear, mObj.mIndex + 1, 0).getDate();

    var dateNum = 1;
    var monthNotes = [];

    for (var row = 0; row < 6; row++) {
        var tr = document.createElement('tr');
        var hasCells = false;

        for (var col = 0; col < 7; col++) {
            var td = document.createElement('td');
            if (col === 0) td.className = 'sun-td';

            if (row === 0 && col < firstDay) {
                td.innerHTML = '';
            } else if (dateNum > daysInMonth) {
                td.innerHTML = '';
            } else {
                hasCells = true;
                var dateStr = monthYear + '-' + String(mObj.mIndex + 1).padStart(2, '0') + '-' + String(dateNum).padStart(2, '0');
                var dayEvents = dateMap[dateStr] || [];

                td.innerText = dateNum;

                if (dayEvents.length > 0) {
                    td.classList.add('has-event');
                    var dotColor = categoryColors[dayEvents[0].category] ? categoryColors[dayEvents[0].category].bg : '#2563eb';
                    td.innerHTML = dateNum + '<span class="mini-event-dot" style="background:' + dotColor + ';"></span>';

                    (function(clickedDate) {
                        td.onclick = function() {
                            handleDateCellClick(clickedDate);
                        };
                    })(dateStr);

                    // Kumpulkan catatan agenda bulan ini
                    dayEvents.forEach(function(ev) {
                        monthNotes.push({ date: dateNum, title: ev.title, id: ev.id, cat: ev.category });
                    });
                } else {
                    // Klik tanggal kosong -> tambah agenda di tanggal tersebut
                    (function(clickedDate) {
                        td.onclick = function() {
                            handleDateCellClick(clickedDate);
                        };
                    })(dateStr);
                }
                dateNum++;
            }
            tr.appendChild(td);
        }
        if (hasCells) {
            tbody.appendChild(tr);
        }
    }
    table.appendChild(tbody);
    card.appendChild(table);

    // Notes section di bawah tabel bulan (seperti lampiran)
    var notesDiv = document.createElement('div');
    notesDiv.className = 'mini-month-notes';
    if (monthNotes.length > 0) {
        monthNotes.slice(0, 3).forEach(function(n) {
            var dotC = categoryColors[n.cat] ? categoryColors[n.cat].bg : '#2563eb';
            var nItem = document.createElement('div');
            nItem.className = 'mini-note-item';
            nItem.innerHTML = '<span style="color:' + dotC + '; font-weight:800;">' + n.date + ':</span> ' +
                              '<span>' + n.title.substring(0, 28) + (n.title.length > 28 ? '...' : '') + '</span>';
            nItem.onclick = function() { openMomModal(n.id); };
            notesDiv.appendChild(nItem);
        });
        if (monthNotes.length > 3) {
            var more = document.createElement('div');
            more.style.fontSize = '10px';
            more.style.color = '#2563eb';
            more.style.cursor = 'pointer';
            more.innerText = '+ ' + (monthNotes.length - 3) + ' agenda lainnya';
            notesDiv.appendChild(more);
        }
    } else {
        notesDiv.innerHTML = '<span style="color:#94a3b8; font-style:italic;">Belum ada agenda</span>';
    }
    card.appendChild(notesDiv);

    return card;
}

function renderYearlyGrid() {
    var container = document.getElementById('yearlyCalendarGrid');
    if (!container) return;
    container.innerHTML = '';
    var events = getFilteredEvents();

    // Map events by date YYYY-MM-DD
    var dateMap = {};
    events.forEach(function(ev) {
        var d = ev.start_date;
        if (!dateMap[d]) dateMap[d] = [];
        dateMap[d].push(ev);
    });

    // 1. SEMESTER 1 (GANJIL): JULI - DESEMBER
    var sem1Header = document.createElement('div');
    sem1Header.style.cssText = 'display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding: 10px 16px; background: #f0fdf4; border-left: 5px solid #16a34a; border-radius: 6px;';
    sem1Header.innerHTML = '<div style="display: flex; align-items: center; gap: 10px;">' +
                           '<i class="fa-solid fa-calendar-check" style="color: #16a34a; font-size: 1.125rem;"></i>' +
                           '<span style="font-weight: 800; color: #15803d; font-size: 1rem; letter-spacing: 0.5px;">' +
                           'SEMESTER 1 (GANJIL) &bull; TAHUN ' + academicStartYear + ' (Juli &ndash; Desember)' +
                           '</span></div>' +
                           '<span class="badge" style="background: #dcfce7; color: #15803d; font-size: 11px; font-weight: 700; padding: 4px 10px;">Awal Tahun Ajaran</span>';
    container.appendChild(sem1Header);

    var sem1Grid = document.createElement('div');
    sem1Grid.className = 'year-grid';
    sem1Grid.style.marginBottom = '28px';

    academicMonths.slice(0, 6).forEach(function(mObj) {
        sem1Grid.appendChild(buildMiniMonthCard(mObj, academicStartYear, dateMap));
    });
    container.appendChild(sem1Grid);

    // 2. SEMESTER 2 (GENAP): JANUARI - JUNI
    var sem2Header = document.createElement('div');
    sem2Header.style.cssText = 'display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding: 10px 16px; background: #eff6ff; border-left: 5px solid #2563eb; border-radius: 6px;';
    sem2Header.innerHTML = '<div style="display: flex; align-items: center; gap: 10px;">' +
                           '<i class="fa-solid fa-calendar-days" style="color: #2563eb; font-size: 1.125rem;"></i>' +
                           '<span style="font-weight: 800; color: #1d4ed8; font-size: 1rem; letter-spacing: 0.5px;">' +
                           'SEMESTER 2 (GENAP) &bull; TAHUN ' + (academicStartYear + 1) + ' (Januari &ndash; Juni)' +
                           '</span></div>' +
                           '<span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 700; padding: 4px 10px;">Akhir Tahun Ajaran</span>';
    container.appendChild(sem2Header);

    var sem2Grid = document.createElement('div');
    sem2Grid.className = 'year-grid';

    academicMonths.slice(6, 12).forEach(function(mObj) {
        sem2Grid.appendChild(buildMiniMonthCard(mObj, academicStartYear, dateMap));
    });
    container.appendChild(sem2Grid);
}

// -----------------------------------------------------------------------------
// RENDER VIEW 2: KALENDER BULANAN PENUH INTERAKTIF
// -----------------------------------------------------------------------------
function changeMonth(delta) {
    currentMonth += delta;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    } else if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    renderMonthlyGrid();
}

function goToToday() {
    var today = new Date();
    currentYear = today.getFullYear();
    currentMonth = today.getMonth();
    renderMonthlyGrid();
}

function renderMonthlyGrid() {
    document.getElementById('currentMonthYearLabel').innerText = monthNames[currentMonth] + ' ' + currentYear;
    var grid = document.getElementById('fullMonthDaysGrid');
    grid.innerHTML = '';

    var events = getFilteredEvents();
    var dateMap = {};
    events.forEach(function(ev) {
        var d = ev.start_date;
        if (!dateMap[d]) dateMap[d] = [];
        dateMap[d].push(ev);
    });

    var firstDay = new Date(currentYear, currentMonth, 1).getDay();
    var daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    var prevMonthDays = new Date(currentYear, currentMonth, 0).getDate();

    var todayStr = new Date().toISOString().slice(0, 10);

    // Days from prev month
    for (var i = firstDay - 1; i >= 0; i--) {
        var cell = document.createElement('div');
        cell.className = 'full-grid-cell other-month';
        cell.innerHTML = '<span class="full-cell-num">' + (prevMonthDays - i) + '</span>';
        grid.appendChild(cell);
    }

    // Days of current month
    for (var d = 1; d <= daysInMonth; d++) {
        var cell = document.createElement('div');
        var dayOfWeek = new Date(currentYear, currentMonth, d).getDay();
        var isSun = dayOfWeek === 0;
        cell.className = 'full-grid-cell' + (isSun ? ' sun' : '');

        var dateStr = currentYear + '-' + String(currentMonth + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
        var isToday = dateStr === todayStr;

        var numSpan = '<span class="full-cell-num' + (isToday ? ' today' : '') + '">' + d + '</span>';
        cell.innerHTML = numSpan;

        // Onclick cell -> buka agenda/tambah agenda di tanggal ini
        (function(clickedDate) {
            cell.onclick = function(e) {
                if (e.target.closest('.full-event-chip')) return;
                handleDateCellClick(clickedDate);
            };
        })(dateStr);

        var dayEvents = dateMap[dateStr] || [];
        dayEvents.forEach(function(ev) {
            var chip = document.createElement('div');
            chip.className = 'full-event-chip';
            var cColor = categoryColors[ev.category] || { bg: '#2563eb', text: '#ffffff' };
            chip.style.background = cColor.bg;
            chip.style.color = cColor.text;
            chip.innerHTML = '<i class="fa-solid fa-circle-dot" style="font-size:8px;"></i> ' + ev.title;
            chip.title = ev.title + ' (' + ev.category + ')';
            chip.onclick = function(e) {
                e.stopPropagation();
                openMomModal(ev.id);
            };
            cell.appendChild(chip);
        });

        grid.appendChild(cell);
    }

    // Trailing days from next month to fill grid
    var totalCells = firstDay + daysInMonth;
    var nextDays = (totalCells % 7 === 0) ? 0 : (7 - (totalCells % 7));
    for (var n = 1; n <= nextDays; n++) {
        var cell = document.createElement('div');
        cell.className = 'full-grid-cell other-month';
        cell.innerHTML = '<span class="full-cell-num">' + n + '</span>';
        grid.appendChild(cell);
    }
}

// -----------------------------------------------------------------------------
// MODAL FUNCTIONS & MULTI-AGENDA + ACTION COMMITMENT WITH PROGRESS
// -----------------------------------------------------------------------------
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatDateIndonesian(dateStr) {
    if (!dateStr) return '';
    var parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    var d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    var dayName = days[d.getDay()] || '';
    var mName = monthNames[d.getMonth()] || '';
    return dayName + ', ' + parts[2] + ' ' + mName + ' ' + parts[0];
}

function getEventsForDate(dateStr) {
    var evs = getFilteredEvents();
    return evs.filter(function(e) { return e.start_date === dateStr; });
}

function getProgressColor(pct) {
    pct = parseInt(pct, 10) || 0;
    if (pct >= 100) return '#16a34a'; // Hijau Selesai
    if (pct >= 70) return '#0284c7';  // Biru Maju
    if (pct >= 30) return '#d97706';  // Kuning Proses
    if (pct > 0) return '#ea580c';   // Orange Awal
    return '#dc2626';                // Merah Pending
}

function getStatusBadgeStyle(pct) {
    pct = parseInt(pct, 10) || 0;
    if (pct >= 100) return 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-weight: 700;';
    if (pct > 0) return 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 700;';
    return 'background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; font-weight: 700;';
}

function getStatusBadgeText(pct) {
    pct = parseInt(pct, 10) || 0;
    if (pct >= 100) return '<i class="fa-solid fa-check-circle"></i> Selesai (100%)';
    if (pct > 0) return '<i class="fa-solid fa-spinner"></i> Proses (' + pct + '%)';
    return '<i class="fa-solid fa-hourglass-start"></i> Pending (0%)';
}

// Handler klik cell tanggal (Yearly Mini Calendar & Monthly Full Calendar)
function handleDateCellClick(dateStr) {
    var dayEvs = getEventsForDate(dateStr);
    if (dayEvs.length > 0) {
        openDayEventsModal(dateStr, dayEvs);
    } else {
        if (!isKepsek) {
            openAddEventModalWithDate(dateStr);
        }
    }
}

// -----------------------------------------------------------------------------
// MODAL 0: DAY EVENTS LIST (MULTI-AGENDA SUPPORT)
// -----------------------------------------------------------------------------
function openDayEventsModal(dateStr, dayEvs) {
    var m = document.getElementById('dayEventsModal');
    if (!m) return;

    var formattedDate = formatDateIndonesian(dateStr);
    document.getElementById('dayEventsModalTitle').innerHTML = '<i class="fa-solid fa-calendar-day" style="color: #2563eb;"></i> Agenda: ' + formattedDate;
    document.getElementById('dayEventsModalSubtitle').innerText = 'Terdapat ' + dayEvs.length + ' agenda terdaftar pada tanggal ini';

    var container = document.getElementById('dayEventsList');
    container.innerHTML = '';

    dayEvs.forEach(function(ev, idx) {
        var cColor = categoryColors[ev.category] || { bg: '#2563eb', text: '#ffffff' };
        var unitBadge = ev.unit_name 
            ? '<span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:600;"><i class="fa-solid fa-school"></i> ' + escapeHtml(ev.unit_name) + '</span>'
            : '<span class="badge" style="background:#f3e8ff; color:#6b21a8; font-weight:600;"><i class="fa-solid fa-layer-group"></i> Semua Unit / Lintas Yayasan</span>';

        var timeText = (ev.start_time ? ev.start_time.slice(0, 5) : '08:30') + (ev.end_time ? ' - ' + ev.end_time.slice(0, 5) : '');
        var momTuntas = ev.mom_id 
            ? '<span class="badge" style="background:#dcfce7; color:#15803d; font-weight:700;"><i class="fa-solid fa-check-circle"></i> Notula MoM (' + Math.round(ev.realization_pct || 0) + '%)</span>'
            : '<span class="badge" style="background:#fef3c7; color:#b45309;"><i class="fa-solid fa-clock"></i> Belum ada Notula</span>';

        var card = document.createElement('div');
        card.style.cssText = 'border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 8px; border-left: 4px solid ' + cColor.bg + ';';

        var topHtml = '<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">' +
                      '  <div>' +
                      '    <div style="font-weight: 700; font-size: 0.9375rem; color: #1e293b;">' + (idx + 1) + '. ' + escapeHtml(ev.title) + '</div>' +
                      '    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 5px;">' +
                      '      <span class="badge" style="background:' + cColor.bg + '; color:' + cColor.text + '; font-size: 11px;">' + escapeHtml(ev.category) + '</span>' +
                      '      ' + unitBadge +
                      '      ' + momTuntas +
                      '    </div>' +
                      '  </div>' +
                      '  <div style="font-size: 0.8125rem; font-weight: 600; color: #475569; white-space: nowrap;"><i class="fa-regular fa-clock"></i> ' + timeText + '</div>' +
                      '</div>';

        if (ev.location) {
            topHtml += '<div style="font-size: 0.75rem; color: #64748b;"><i class="fa-solid fa-location-dot"></i> ' + escapeHtml(ev.location) + '</div>';
        }

        var btnHtml = '<div style="display: flex; gap: 6px; justify-content: flex-end; margin-top: 6px; padding-top: 8px; border-top: 1px dashed #f1f5f9; flex-wrap: wrap;">' +
                      '  <button type="button" class="btn btn-primary" style="padding: 4px 12px; font-size: 0.8125rem;" onclick="closeDayEventsModal(); openMomModal(' + ev.id + ');">' +
                      '    <i class="fa-solid fa-file-signature"></i> Notula &amp; Komitmen (MoM)' +
                      '  </button>';

        if (!isKepsek) {
            btnHtml += '  <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 0.8125rem;" onclick="closeDayEventsModal(); openEditEventModal(' + ev.id + ');">' +
                       '    <i class="fa-solid fa-pen-to-square"></i> Edit' +
                       '  </button>' +
                       '  <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 0.8125rem;" onclick="closeDayEventsModal(); openInviteModal(' + ev.id + ', \'' + escapeHtml(ev.title).replace(/'/g, "\\'") + '\');">' +
                       '    <i class="fa-solid fa-paper-plane"></i> Undangan' +
                       '  </button>';
        }

        btnHtml += '</div>';

        card.innerHTML = topHtml + btnHtml;
        container.appendChild(card);
    });

    var btnAdd = document.getElementById('btnAddNewEventOnDate');
    if (btnAdd) {
        if (isKepsek) {
            btnAdd.style.display = 'none';
        } else {
            btnAdd.style.display = 'inline-flex';
            btnAdd.onclick = function() {
                closeDayEventsModal();
                openAddEventModalWithDate(dateStr);
            };
        }
    }

    m.style.display = 'flex';
    m.classList.add('active');
}

function closeDayEventsModal() {
    var m = document.getElementById('dayEventsModal');
    if (m) {
        m.style.display = 'none';
        m.classList.remove('active');
    }
}

// -----------------------------------------------------------------------------
// MODAL 1: TAMBAH / EDIT AGENDA
// -----------------------------------------------------------------------------
function openAddEventModal() {
    if (isKepsek) return;
    document.getElementById('event_modal_id').value = '0';
    document.getElementById('event_modal_title').value = '';
    document.getElementById('event_modal_category').value = 'Weekly Meeting';
    document.getElementById('event_modal_unit_id').value = '';
    document.getElementById('event_modal_date').value = new Date().toISOString().slice(0, 10);
    document.getElementById('event_modal_start_time').value = '08:30';
    document.getElementById('event_modal_location').value = '';
    document.getElementById('event_modal_description').value = '';
    document.getElementById('event_modal_header_title').innerHTML = '<i class="fa-solid fa-calendar-plus" style="color: #2563eb;"></i> Tambah Agenda Rapat &amp; Kegiatan';
    document.getElementById('event_modal_submit_btn').innerHTML = '<i class="fa-solid fa-save"></i> Jadwalkan Agenda';

    var m = document.getElementById('addEventModal');
    m.style.display = 'flex';
    m.classList.add('active');
}

function openAddEventModalWithDate(dateStr) {
    if (isKepsek) return;
    openAddEventModal();
    document.getElementById('event_modal_date').value = dateStr;
}

function openEditEventModal(eventId) {
    if (isKepsek) return;
    var ev = allEvents.find(function(e) { return parseInt(e.id, 10) === parseInt(eventId, 10); });
    if (!ev) return;

    document.getElementById('event_modal_id').value = ev.id;
    document.getElementById('event_modal_title').value = ev.title;
    document.getElementById('event_modal_category').value = ev.category;
    document.getElementById('event_modal_unit_id').value = ev.unit_id || '';
    document.getElementById('event_modal_date').value = ev.start_date;
    document.getElementById('event_modal_start_time').value = ev.start_time ? ev.start_time.slice(0, 5) : '08:30';
    document.getElementById('event_modal_location').value = ev.location || '';
    document.getElementById('event_modal_description').value = ev.description || '';

    document.getElementById('event_modal_header_title').innerHTML = '<i class="fa-solid fa-pen-to-square" style="color: #2563eb;"></i> Edit Agenda Rapat &amp; Kegiatan';
    document.getElementById('event_modal_submit_btn').innerHTML = '<i class="fa-solid fa-save"></i> Perbarui Agenda';

    var m = document.getElementById('addEventModal');
    m.style.display = 'flex';
    m.classList.add('active');
}

function closeAddEventModal() {
    var m = document.getElementById('addEventModal');
    if (m) {
        m.style.display = 'none';
        m.classList.remove('active');
    }
}

// -----------------------------------------------------------------------------
// MODAL 3: KIRIM UNDANGAN RAPAT
// -----------------------------------------------------------------------------
function openInviteModal(eventId, title) {
    document.getElementById('invite_event_id').value = eventId;
    document.getElementById('inviteEventTitle').innerText = title;
    var m = document.getElementById('inviteModal');
    m.style.display = 'flex';
    m.classList.add('active');
}

function closeInviteModal() {
    var m = document.getElementById('inviteModal');
    if (m) {
        m.style.display = 'none';
        m.classList.remove('active');
    }
}

// -----------------------------------------------------------------------------
// MODAL 2: NOTULA MoM & BUTIR KOMITMEN TINDAK LANJUT
// -----------------------------------------------------------------------------
function openMomModal(eventId) {
    document.getElementById('mom_event_id').value = eventId;
    var tbody = document.getElementById('momActionsTbody');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 20px;"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data notula &amp; komitmen...</td></tr>';

    fetch('<?= $baseUrl ?>/deputi/calendar/get-event-details?id=' + eventId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data.success) {
                alert('Gagal memuat detail agenda.');
                return;
            }
            var readOnly = isKepsek;
            document.getElementById('momModalTitle').innerHTML = '<i class="fa-solid fa-file-signature" style="color: #0d9488;"></i> ' + (readOnly ? 'Detail Notula: ' : 'Notula: ') + escapeHtml(data.event.title);
            document.getElementById('momEventSubtitle').innerText = (data.event.unit_name || 'Semua Unit / Lintas Yayasan') + ' • ' + data.event.start_date + ' ' + (data.event.start_time ? data.event.start_time.slice(0, 5) : '08:30');
            document.getElementById('mom_meeting_date').value = (data.mom && data.mom.meeting_date) ? data.mom.meeting_date : data.event.start_date;
            document.getElementById('mom_content').value = (data.mom && data.mom.mom_content) ? data.mom.mom_content : '';

            // Read-only controls untuk Kepala Sekolah
            document.getElementById('mom_meeting_date').readOnly = readOnly;
            document.getElementById('mom_content').readOnly = readOnly;
            var fileGroup = document.querySelector('input[name="mom_file"]')?.closest('.form-group');
            if (fileGroup) fileGroup.style.display = readOnly ? 'none' : '';
            var btnAddAction = document.getElementById('btnMomAddAction');
            if (btnAddAction) btnAddAction.style.display = readOnly ? 'none' : 'inline-flex';
            var btnSubmitMom = document.getElementById('btnSubmitMom');
            if (btnSubmitMom) btnSubmitMom.style.display = readOnly ? 'none' : 'inline-flex';

            var fileSpan = document.getElementById('momCurrentFile');
            if (data.mom && data.mom.file_path) {
                var deleteBtn = '';
                if (canDeleteMomFile && !readOnly) {
                    deleteBtn = '<button type="button" class="btn btn-outline btn-sm" onclick="deleteMomFile(' + data.mom.id + ')" style="padding: 2px 8px; font-size: 11px; color: #dc2626; border-color: #fca5a5; display: inline-flex; align-items: center; gap: 4px;" title="Hapus Berkas Lampiran Ini"><i class="fa-solid fa-trash"></i> Hapus Berkas</button>';
                }
                fileSpan.innerHTML = '<div style="display:flex; align-items:center; justify-content:space-between; margin-top:5px; padding:6px 10px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:6px; flex-wrap:wrap; gap:8px;">' +
                    '<span><i class="fa-solid fa-paperclip" style="color:#0284c7;"></i> Dokumen terlampir: <a href="<?= $baseUrl ?>/deputi/calendar/download-mom/' + data.mom.id + '" target="_blank" style="font-weight:600; text-decoration:underline; color:#0284c7;">Buka File Lampiran</a></span>' +
                    deleteBtn +
                    '</div>';
            } else {
                fileSpan.innerHTML = '';
            }

            // Bersihkan baris tabel aksi
            tbody.innerHTML = '';

            var combinedActions = [];

            // 1. Aksi yang sudah tersimpan di MoM ini
            if (data.actions && data.actions.length > 0) {
                data.actions.forEach(function(a) {
                    combinedActions.push({
                        id: a.id,
                        source_action_id: a.source_action_id || '',
                        action_topic: a.action_topic,
                        pic_name: a.pic_name,
                        due_date: a.due_date,
                        progress_pct: (a.progress_pct !== null && a.progress_pct !== undefined) ? parseInt(a.progress_pct, 10) : (a.status === 'Completed' ? 100 : 0),
                        status: a.status,
                        src_event_title: a.src_event_title,
                        src_event_date: a.src_event_date,
                        is_carried: !!a.source_action_id
                    });
                });
            }

            // 2. Aksi belum tuntas dari rapat sejenis sebelumnya yang BELUM ada di MoM ini (Auto Carry-Over)
            var autoCarriedCount = 0;
            if (data.unfinishedPrevious && data.unfinishedPrevious.length > 0) {
                data.unfinishedPrevious.forEach(function(item) {
                    var alreadyIn = combinedActions.some(function(ca) {
                        return ca.source_action_id && parseInt(ca.source_action_id, 10) === parseInt(item.id, 10);
                    });
                    if (!alreadyIn) {
                        combinedActions.push({
                            id: '', // belum tersimpan pada MoM ini
                            source_action_id: item.id,
                            action_topic: item.action_topic,
                            pic_name: item.pic_name,
                            due_date: item.due_date,
                            progress_pct: parseInt(item.progress_pct || 0, 10),
                            status: item.status || 'Pending',
                            src_event_title: item.prev_event_title,
                            src_event_date: item.prev_date,
                            is_carried: true
                        });
                        autoCarriedCount++;
                    }
                });
            }

            // Banner info carry-over
            var prevBox = document.getElementById('prevUnfinishedBox');
            var prevInfo = document.getElementById('prevUnfinishedInfo');
            if (autoCarriedCount > 0) {
                prevInfo.innerHTML = '✨ <strong>' + autoCarriedCount + ' butir komitmen</strong> dari rapat <em>' + escapeHtml(data.event.category) + ' (' + escapeHtml(data.event.unit_name || 'Semua Unit') + ')</em> sebelumnya <strong>otomatis dimuat</strong> ke tabel di bawah untuk ditindaklanjuti progresnya.';
                prevBox.style.display = 'block';
            } else if (combinedActions.some(function(a) { return a.is_carried; })) {
                prevInfo.innerHTML = '<i class="fa-solid fa-circle-check" style="color: #16a34a;"></i> Komitmen lanjutan dari rapat sejenis sebelumnya aktif terpantau di tabel di bawah.';
                prevBox.style.display = 'block';
            } else {
                prevBox.style.display = 'none';
            }

            // Render baris ke tabel
            combinedActions.forEach(function(item) {
                addActionItemRow(item, readOnly);
            });

            renumberActionRows();
            recalculateModalAvgScore();

            var m = document.getElementById('momModal');
            m.style.display = 'flex';
            m.classList.add('active');
        });
}

function closeMomModal() {
    var m = document.getElementById('momModal');
    if (m) {
        m.style.display = 'none';
        m.classList.remove('active');
    }
}

function deleteMomFile(momId) {
    if (!canDeleteMomFile) {
        alert('Akses Ditolak: Hanya Deputi Pendidikan yang berwenang menghapus berkas lampiran MoM.');
        return;
    }
    if (!confirm('Apakah Anda yakin ingin menghapus berkas lampiran MoM ini? Berkas yang dihapus tidak dapat dipulihkan.')) {
        return;
    }

    var formData = new FormData();
    formData.append('mom_id', momId);
    formData.append('csrf_token', '<?= CSRF::token() ?>');

    fetch('<?= $baseUrl ?>/deputi/calendar/delete-mom-file', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?= CSRF::token() ?>'
        },
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(res) {
        if (res.success) {
            document.getElementById('momCurrentFile').innerHTML = '<div style="margin-top:5px; padding:6px 10px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; color:#166534; font-size:11.5px; font-weight:600;"><i class="fa-solid fa-circle-check"></i> Berkas lampiran berhasil dihapus.</div>';
            var fileInput = document.querySelector('input[name="mom_file"]');
            if (fileInput) fileInput.value = '';
        } else {
            alert('Gagal menghapus berkas: ' + (res.message || 'Terjadi kesalahan sistem.'));
        }
    })
    .catch(function(err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan.');
    });
}

// Tambah Baris Butir Komitmen ke Tabel
function addActionItemRow(item, readOnly) {
    if (readOnly === undefined) readOnly = isKepsek;
    item = item || {
        id: '',
        source_action_id: '',
        action_topic: '',
        pic_name: '',
        due_date: '',
        progress_pct: 0,
        status: 'Pending',
        is_carried: false
    };

    var tbody = document.getElementById('momActionsTbody');
    var tr = document.createElement('tr');
    tr.style.borderBottom = '1px solid #e2e8f0';

    var progress = parseInt(item.progress_pct || 0, 10);
    if (progress < 0) progress = 0;
    if (progress > 100) progress = 100;

    var sourceBadge = '';
    if (item.is_carried) {
        var srcText = (item.src_event_title ? item.src_event_title : 'Rapat Lalu') + (item.src_event_date ? ' (' + item.src_event_date + ')' : '');
        sourceBadge = '<div style="margin-top: 4px;"><span class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; font-weight:600; padding: 2px 6px;"><i class="fa-solid fa-clock-rotate-left"></i> Lanjutan dari ' + escapeHtml(srcText) + '</span></div>';
    } else {
        sourceBadge = '<div style="margin-top: 4px;"><span class="badge" style="background:#e0e7ff; color:#3730a3; font-size:10px; font-weight:600; padding: 2px 6px;"><i class="fa-solid fa-asterisk"></i> Baru</span></div>';
    }

    var html = '<td class="action-row-num" style="padding: 8px 6px; text-align: center; font-weight: 700; color: #64748b; vertical-align: middle;">-</td>' +
               '<td style="padding: 8px 8px; vertical-align: top;">' +
               '  <input type="hidden" name="action_ids[]" value="' + (item.id || '') + '">' +
               '  <input type="hidden" name="action_source_ids[]" value="' + (item.source_action_id || '') + '">' +
               '  <input type="text" name="action_topics[]" class="form-control" style="font-size: 0.8125rem; padding: 5px 8px; font-weight: 500;" value="' + escapeHtml(item.action_topic) + '" placeholder="Topik komitmen / butir kesepakatan..." required ' + (readOnly ? 'readonly' : '') + '>' +
               sourceBadge +
               '</td>' +
               '<td style="padding: 8px 8px; vertical-align: top;">' +
               '  <input type="text" name="action_pics[]" class="form-control" style="font-size: 0.8125rem; padding: 5px 8px;" value="' + escapeHtml(item.pic_name || '') + '" placeholder="Nama PIC" ' + (readOnly ? 'readonly' : '') + '>' +
               '</td>' +
               '<td style="padding: 8px 8px; vertical-align: top;">' +
               '  <input type="date" name="action_due_dates[]" class="form-control" style="font-size: 0.8125rem; padding: 5px 6px;" value="' + (item.due_date || '') + '" ' + (readOnly ? 'readonly' : '') + '>' +
               '</td>' +
               '<td style="padding: 8px 8px; vertical-align: top;">' +
               '  <div style="display: flex; flex-direction: column; gap: 4px;">' +
               '    <div style="display: flex; align-items: center; gap: 8px;">' +
               '      <input type="range" min="0" max="100" step="5" class="action-progress-range" value="' + progress + '" style="flex: 1; accent-color: #2563eb; cursor: pointer;" oninput="syncActionProgress(this, \'range\')" ' + (readOnly ? 'disabled' : '') + '>' +
               '      <div style="display: flex; align-items: center; width: 62px;">' +
               '        <input type="number" min="0" max="100" step="1" name="action_progress[]" class="form-control action-progress-num" value="' + progress + '" style="font-size: 0.8125rem; padding: 2px 4px; text-align: right; font-weight: 700;" oninput="syncActionProgress(this, \'num\')" ' + (readOnly ? 'readonly' : '') + '>' +
               '        <span style="font-size: 11px; margin-left: 2px; font-weight: bold; color: #475569;">%</span>' +
               '      </div>' +
               '    </div>' +
               '    <div style="height: 5px; background: #e2e8f0; border-radius: 9999px; overflow: hidden;">' +
               '      <div class="action-progress-bar" style="width: ' + progress + '%; height: 100%; background: ' + getProgressColor(progress) + '; transition: width 0.2s, background-color 0.2s;"></div>' +
               '    </div>';

    if (!readOnly) {
        html += '    <div style="display: flex; gap: 2px; justify-content: space-between; margin-top: 2px;">' +
                '      <button type="button" class="btn" style="padding: 1px 5px; font-size: 10px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 4px;" onclick="setActionProgressPreset(this, 0)">0%</button>' +
                '      <button type="button" class="btn" style="padding: 1px 5px; font-size: 10px; background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; border-radius: 4px;" onclick="setActionProgressPreset(this, 25)">25%</button>' +
                '      <button type="button" class="btn" style="padding: 1px 5px; font-size: 10px; background: #fefce8; color: #a16207; border: 1px solid #fef08a; border-radius: 4px;" onclick="setActionProgressPreset(this, 50)">50%</button>' +
                '      <button type="button" class="btn" style="padding: 1px 5px; font-size: 10px; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; border-radius: 4px;" onclick="setActionProgressPreset(this, 75)">75%</button>' +
                '      <button type="button" class="btn" style="padding: 1px 5px; font-size: 10px; background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; border-radius: 4px; font-weight: bold;" onclick="setActionProgressPreset(this, 100)">100%</button>' +
                '    </div>';
    }

    html += '  </div>' +
            '</td>' +
            '<td style="padding: 8px 8px; text-align: center; vertical-align: middle;">' +
            '  <span class="action-status-badge badge" style="font-size: 10px; padding: 4px 6px; ' + getStatusBadgeStyle(progress) + '">' +
            getStatusBadgeText(progress) +
            '  </span>' +
            '</td>' +
            '<td style="padding: 8px 6px; text-align: center; vertical-align: middle;">';

    if (!readOnly) {
        html += '<button type="button" class="btn btn-outline" style="padding: 4px 8px; font-size: 11px; color: #dc2626; border-color: #fca5a5;" onclick="removeActionRow(this)" title="Hapus Butir Komitmen"><i class="fa-solid fa-trash"></i></button>';
    } else {
        html += '<span style="color:#94a3b8;">-</span>';
    }

    html += '</td>';

    tr.innerHTML = html;
    tbody.appendChild(tr);

    renumberActionRows();
    recalculateModalAvgScore();
}

function removeActionRow(btn) {
    var tr = btn.closest('tr');
    if (tr) {
        tr.remove();
        renumberActionRows();
        recalculateModalAvgScore();
    }
}

function renumberActionRows() {
    var rows = document.querySelectorAll('#momActionsTbody tr');
    rows.forEach(function(r, idx) {
        var numCell = r.querySelector('.action-row-num');
        if (numCell) numCell.innerText = (idx + 1);
    });
}

function syncActionProgress(el, source) {
    var tr = el.closest('tr');
    if (!tr) return;

    var rangeInput = tr.querySelector('.action-progress-range');
    var numInput = tr.querySelector('.action-progress-num');
    var bar = tr.querySelector('.action-progress-bar');
    var badge = tr.querySelector('.action-status-badge');

    var val = (source === 'range') ? parseInt(rangeInput.value, 10) : parseInt(numInput.value, 10);
    if (isNaN(val)) val = 0;
    if (val < 0) val = 0;
    if (val > 100) val = 100;

    rangeInput.value = val;
    numInput.value = val;
    if (bar) {
        bar.style.width = val + '%';
        bar.style.background = getProgressColor(val);
    }
    if (badge) {
        badge.style.cssText = 'font-size: 10px; padding: 4px 6px; ' + getStatusBadgeStyle(val);
        badge.innerHTML = getStatusBadgeText(val);
    }
    recalculateModalAvgScore();
}

function setActionProgressPreset(btn, presetVal) {
    var tr = btn.closest('tr');
    if (!tr) return;
    var rangeInput = tr.querySelector('.action-progress-range');
    if (rangeInput) {
        rangeInput.value = presetVal;
        syncActionProgress(rangeInput, 'range');
    }
}

function recalculateModalAvgScore() {
    var numInputs = document.querySelectorAll('#momActionsTbody .action-progress-num');
    var badge = document.getElementById('momAvgScoreBadge');
    var emptyDiv = document.getElementById('momActionsEmpty');
    var table = document.getElementById('momActionsTable');

    if (numInputs.length === 0) {
        if (badge) {
            badge.innerText = '0%';
            badge.style.background = '#e2e8f0';
            badge.style.color = '#64748b';
        }
        if (emptyDiv) emptyDiv.style.display = 'block';
        if (table) table.style.display = 'none';
        return;
    }

    if (emptyDiv) emptyDiv.style.display = 'none';
    if (table) table.style.display = 'table';

    var total = 0;
    numInputs.forEach(function(inp) {
        total += parseInt(inp.value, 10) || 0;
    });
    var avg = Math.round(total / numInputs.length);
    if (badge) {
        badge.innerText = avg + '%';
        if (avg >= 100) {
            badge.style.background = '#dcfce7';
            badge.style.color = '#15803d';
        } else if (avg >= 70) {
            badge.style.background = '#e0f2fe';
            badge.style.color = '#0369a1';
        } else if (avg >= 40) {
            badge.style.background = '#fef3c7';
            badge.style.color = '#b45309';
        } else {
            badge.style.background = '#fee2e2';
            badge.style.color = '#b91c1c';
        }
    }
}

function toggleAction(actionId, status) {
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = '<?= $baseUrl ?>/deputi/calendar/toggle-action';
    f.innerHTML = '<?= CSRF::inputField() ?>' +
                  '<input type="hidden" name="action_id" value="' + actionId + '">' +
                  '<input type="hidden" name="status" value="' + status + '">' +
                  '<input type="hidden" name="deputi_id" value="<?= $deputiUserId ?>">';
    document.body.appendChild(f);
    f.submit();
}

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    renderYearlyGrid();
    renderMonthlyGrid();
});
</script>