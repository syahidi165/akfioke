<?php
// views/jadwal/index.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$isTahfidzOnly = $isTahfidzOnly ?? false;
$returnRoute = $returnRoute ?? ($isTahfidzOnly ? 'tahfidz' : 'kelas');
$baseScheduleUrl = $baseUrl . '/' . ($isTahfidzOnly ? 'jadwal/tahfidz' : 'jadwal/kelas');

$totalKelas = count($classes);
$totalMapel = count($subjects);
$totalGuru = count($teachers);
$totalBentrok = count($conflicts);
$totalInval = count($substitutions);

// Hitung total jam terjadwal
$totalSesi = 0;
foreach ($scheduleMatrix as $d => $jGroup) {
    foreach ($jGroup as $j => $cGroup) {
        foreach ($cGroup as $cId => $s) {
            if ($s['session_type'] === 'pelajaran' && (!empty($s['teacher_id']) || !empty($s['substitute_teacher_id']) || !empty($s['halaqahs']))) {
                $totalSesi++;
            }
        }
    }
}

$tahfidzInvalCount = count(array_filter($substitutions, fn($sb) => !empty($sb['halaqah_number'])));
$tahfidzConflictCount = count($tahfidzConflicts ?? []);
$activeConflicts = $isTahfidzOnly ? ($tahfidzConflicts ?? []) : $conflicts;
?>

<div class="page-header" style="margin-bottom: 20px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
            <?php if ($isTahfidzOnly): ?>
                <h1 class="page-title" style="margin: 0; color: #065f46; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-quran" style="color: #059669;"></i> Tabel Jadwal Halaqah Guru Tahfidz
                </h1>
                <span class="badge" style="background: #d1fae5; color: #065f46; font-size: 0.8rem; font-weight: 700; border: 1px solid #6ee7b7;">Mode Fokus Tahfidz</span>
            <?php else: ?>
                <h1 class="page-title" style="margin: 0;">Tabel Jadwal Pelajaran Kelas</h1>
            <?php endif; ?>
            <span class="badge badge-primary" style="font-size: 0.8rem;"><?= SecurityHelper::escape($unit['name'] ?? 'Unit') ?></span>
            <span class="badge badge-info" style="font-size: 0.8rem;">TA: <?= SecurityHelper::escape($ta['name'] ?? '') ?> (<?= SecurityHelper::escape($ta['semester'] ?? '') ?>)</span>
        </div>
        <div class="page-subtitle">
            <?php if ($isTahfidzOnly): ?>
                Pengaturan & pemantauan jadwal guru tahfidz berbasis halaqah (hingga 3 halaqah per kelas), ketersediaan guru tahfidz, deteksi bentrok jadwal, dan penugasan inval halaqah.
            <?php else: ?>
                Penyusunan jadwal KBM interaktif, pemantauan guru mengajar vs jam free, deteksi bentrok jadwal, dan penugasan guru pengganti (inval).
            <?php endif; ?>
        </div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <?php if ($isTahfidzOnly): ?>
            <a href="<?= $baseUrl ?>/jadwal/kelas" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px; border-color: #64748b; color: #475569;">
                <i class="fa-solid fa-table-cells"></i> Buka Jadwal Seluruh Mapel
            </a>
        <?php else: ?>
            <a href="<?= $baseUrl ?>/jadwal/tahfidz" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px; border-color: #059669; color: #047857; background: #f0fdf4;">
                <i class="fa-solid fa-quran"></i> Khusus Halaqah Tahfidz
            </a>
        <?php endif; ?>

        <?php if ($canManage): ?>
        <button type="button" class="btn btn-outline" onclick="openAllSlotsModal()" style="display: inline-flex; align-items: center; gap: 6px; border-color: #6366f1; color: #4f46e5;">
            <i class="fa-solid fa-clock"></i> Atur Jam Pelajaran
        </button>
        <?php endif; ?>

        <button type="button" class="btn btn-outline" onclick="openRadarModal(<?= $isTahfidzOnly ? 'true' : 'false' ?>)" style="display: inline-flex; align-items: center; gap: 6px; border-color: #0d9488; color: #0d9488; background: #f0fdfa;">
            <i class="fa-solid fa-radar fa-spin" style="--fa-animation-duration: 3s;"></i> Radar Guru <?= $isTahfidzOnly ? 'Tahfidz' : '' ?> Free vs Mengajar
        </button>

        <a href="<?= $baseUrl ?>/jadwal/print<?= $viewMode === 'harian' ? '?day=' . urlencode($selectedDay) : '' ?>" target="_blank" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-print"></i> Cetak Format Blanko
        </a>
    </div>
</div>

<!-- KPI Stat Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 20px;">
    <?php if ($isTahfidzOnly): ?>
        <!-- KPI Cards Khusus Koordinator Tahfidz -->
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #059669;">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $totalKelas ?></div>
                <div class="stat-label">Total Kelas Terdaftar</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(13, 148, 136, 0.15); color: #0d9488;">
                <i class="fa-solid fa-quran"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $totalHalaqahCount ?></div>
                <div class="stat-label">Total Halaqah Terjadwal</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #2563eb;">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $activeTahfidzTeacherCount ?></div>
                <div class="stat-label">Guru Tahfidz Aktif</div>
            </div>
        </div>
        <div class="stat-card" style="<?= $tahfidzConflictCount > 0 ? 'border: 1px solid #ef4444; background: #fff5f5;' : '' ?>">
            <div class="stat-icon" style="background: <?= $tahfidzConflictCount > 0 ? 'rgba(239, 68, 68, 0.15)' : 'rgba(16, 185, 129, 0.12)' ?>; color: <?= $tahfidzConflictCount > 0 ? '#ef4444' : '#10b981' ?>;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="<?= $tahfidzConflictCount > 0 ? 'color: #dc2626; font-weight: bold;' : '' ?>">
                    <?= $tahfidzConflictCount ?>
                </div>
                <div class="stat-label"><?= $tahfidzConflictCount > 0 ? 'Bentrok Guru Tahfidz!' : 'Bentrok Tahfidz (Aman)' ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #d97706;">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $tahfidzInvalCount ?></div>
                <div class="stat-label">Inval Halaqah Aktif</div>
            </div>
        </div>
    <?php else: ?>
        <!-- KPI Cards Reguler Waka Kurikulum -->
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $totalKelas ?></div>
                <div class="stat-label">Total Kelas Terdaftar</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6;">
                <i class="fa-solid fa-book-open-reader"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $totalSesi ?></div>
                <div class="stat-label">Sesi Pelajaran Terjadwal</div>
            </div>
        </div>
        <div class="stat-card" style="<?= $totalBentrok > 0 ? 'border: 1px solid #ef4444; background: #fff5f5;' : '' ?>">
            <div class="stat-icon" style="background: <?= $totalBentrok > 0 ? 'rgba(239, 68, 68, 0.15)' : 'rgba(16, 185, 129, 0.12)' ?>; color: <?= $totalBentrok > 0 ? '#ef4444' : '#10b981' ?>;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="<?= $totalBentrok > 0 ? 'color: #dc2626; font-weight: bold;' : '' ?>">
                    <?= $totalBentrok ?>
                </div>
                <div class="stat-label"><?= $totalBentrok > 0 ? 'Jadwal Bentrok (Segera Atur!)' : 'Jadwal Bentrok (Aman)' ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $totalInval ?></div>
                <div class="stat-label">Guru Pengganti (Inval)</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Alert Banner jika Ada Jadwal Bentrok -->
<?php if (!empty($activeConflicts)): ?>
<div style="background: #fef2f2; border: 1px solid #f87171; border-left: 5px solid #dc2626; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <i class="fa-solid fa-circle-exclamation" style="color: #dc2626; font-size: 1.4rem; margin-top: 2px;"></i>
            <div>
                <h4 style="margin: 0 0 6px 0; color: #991b1b; font-size: 0.95rem; font-weight: 700;">
                    Terdeteksi <?= count($activeConflicts) ?> Jadwal Guru yang Bentrok (Double-Booked)!
                </h4>
                <div style="font-size: 0.85rem; color: #7f1d1d; line-height: 1.5;">
                    Satu guru dijadwalkan mengajar di beberapa kelas atau halaqah pada hari dan jam yang sama. Silakan sesuaikan atau ganti guru pengajar:
                </div>
                <ul style="margin: 6px 0 0 16px; padding: 0; font-size: 0.825rem; color: #991b1b;">
                    <?php foreach ($activeConflicts as $conf): ?>
                        <li style="margin-bottom: 3px;">
                            <strong><?= SecurityHelper::escape($conf['teacher_name']) ?>:</strong> <?= SecurityHelper::escape($conf['description']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline" onclick="openRadarModal(<?= $isTahfidzOnly ? 'true' : 'false' ?>)" style="border-color: #dc2626; color: #dc2626; background: #fff; white-space: nowrap;">
            <i class="fa-solid fa-magnifying-glass"></i> Cari Guru Pengganti
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Control Bar: Mode View (Mingguan vs Harian), Hari Filter & Teacher Filter -->
<div class="card" style="margin-bottom: 20px; padding: 14px 18px; background: #ffffff;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <!-- Switcher Mode Tampilan -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-weight: 600; font-size: 0.85rem; color: #475569;">Mode Tampilan:</span>
            <div class="btn-group" style="display: inline-flex; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden;">
                <a href="<?= $baseScheduleUrl ?>?view_mode=mingguan<?= $filterTeacherId ? '&teacher_id='.$filterTeacherId : '' ?>" 
                   class="btn btn-sm <?= $viewMode === 'mingguan' ? 'btn-primary' : 'btn-light' ?>" 
                   style="border-radius: 0; font-weight: 600; padding: 6px 14px; text-decoration: none;">
                    <i class="fa-solid fa-table-cells"></i> Format Mingguan (Sesuai Lampiran)
                </a>
                <a href="<?= $baseScheduleUrl ?>?view_mode=harian&day=<?= urlencode($selectedDay) ?><?= $filterTeacherId ? '&teacher_id='.$filterTeacherId : '' ?>" 
                   class="btn btn-sm <?= $viewMode === 'harian' ? 'btn-primary' : 'btn-light' ?>" 
                   style="border-radius: 0; font-weight: 600; padding: 6px 14px; text-decoration: none;">
                    <i class="fa-solid fa-calendar-day"></i> Format Harian (Fokus)
                </a>
            </div>
        </div>

        <!-- Filter Hari (jika mode harian) -->
        <?php if ($viewMode === 'harian'): ?>
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-weight: 600; font-size: 0.85rem; color: #475569;">Pilih Hari:</span>
            <?php foreach ($daysList as $d): ?>
                <a href="<?= $baseScheduleUrl ?>?view_mode=harian&day=<?= urlencode($d) ?><?= $filterTeacherId ? '&teacher_id='.$filterTeacherId : '' ?>" 
                   class="btn btn-sm <?= $selectedDay === $d ? 'btn-primary' : 'btn-outline' ?>" 
                   style="font-weight: 600; padding: 4px 12px; text-decoration: none;">
                    <?= $d ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Filter Sorot Guru Tertentu -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <label for="filterTeacher" style="font-weight: 600; font-size: 0.85rem; color: #475569; margin: 0; white-space: nowrap;">
                <i class="fa-solid fa-filter" style="color: <?= $isTahfidzOnly ? '#059669' : '#0d9488' ?>;"></i> <?= $isTahfidzOnly ? 'Sorot Guru Tahfidz:' : 'Sorot Guru:' ?>
            </label>
            <select id="filterTeacher" class="form-control" style="width: 220px; padding: 4px 8px; font-size: 0.85rem;" onchange="handleTeacherFilter(this.value)">
                <option value="">-- Tampilkan Semua Guru --</option>
                <?php 
                    $filterList = $isTahfidzOnly ? $tahfidzTeachers : $teachers;
                    foreach ($filterList as $t): 
                ?>
                    <option value="<?= $t['id'] ?>" <?= $filterTeacherId === (int)$t['id'] ? 'selected' : '' ?>>
                        <?= SecurityHelper::escape($t['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($filterTeacherId): ?>
                <a href="<?= $baseScheduleUrl ?>?view_mode=<?= urlencode($viewMode) ?>&day=<?= urlencode($selectedDay) ?>" class="btn btn-sm btn-outline" title="Reset Filter Guru">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Petunjuk Visual Legenda -->
<div style="display: flex; gap: 16px; align-items: center; margin-bottom: 14px; font-size: 0.8rem; color: #64748b; flex-wrap: wrap;">
    <span style="font-weight: 600; color: #334155;">Keterangan / Legenda:</span>
    <span style="display: inline-flex; align-items: center; gap: 5px;">
        <span style="display: inline-block; width: 14px; height: 14px; background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 3px;"></span> Mapel Umum
    </span>
    <span style="display: inline-flex; align-items: center; gap: 5px;">
        <span style="display: inline-block; width: 14px; height: 14px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 3px;"></span> Mapel Diniyah
    </span>
    <span style="display: inline-flex; align-items: center; gap: 5px;">
        <span style="display: inline-block; width: 14px; height: 14px; background: #dcfce7; border: 1px solid #86efac; border-radius: 3px;"></span> Tahfidz / Tahsin
    </span>
    <span style="display: inline-flex; align-items: center; gap: 5px;">
        <span style="display: inline-block; width: 14px; height: 14px; background: #fee2e2; border: 1px solid #f87171; border-radius: 3px;"></span> Jadwal Bentrok
    </span>
    <span style="display: inline-flex; align-items: center; gap: 5px;">
        <span style="display: inline-block; width: 14px; height: 14px; background: #ffedd5; border: 1px solid #fdba74; border-radius: 3px;"></span> Guru Pengganti (Inval)
    </span>
    <span style="display: inline-flex; align-items: center; gap: 5px;">
        <span style="display: inline-block; width: 14px; height: 14px; background: #fce7f3; border: 1px solid #fbcfe8; border-radius: 3px;"></span> Istirahat / Sholat
    </span>
</div>

<!-- ============================================================ -->
<!-- MAIN TABLE JADWAL PELAJARAN (DESAIN IDENTIK DENGAN LAMPIRAN) -->
<!-- ============================================================ -->
<div class="card" style="padding: 0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05); border: 1px solid #cbd5e1;">
    <div style="overflow-x: auto; width: 100%;">
        <table class="jadwal-table" id="jadwalTable" style="width: 100%; border-collapse: collapse; min-width: 900px; font-size: 0.825rem; text-align: center;">
            <thead>
                <!-- Baris Header 1: HARI, WAKTU, JAM KE, dan TINGKAT KELAS (Colspan per Tingkat) -->
                <tr style="background: #fed7aa; color: #431407; font-weight: 700; border-bottom: 1px solid #fdba74;">
                    <th rowspan="2" style="width: 90px; border: 1px solid #ea580c; padding: 10px 6px; vertical-align: middle; text-transform: uppercase;">HARI</th>
                    <th rowspan="2" style="width: 105px; border: 1px solid #ea580c; padding: 10px 6px; vertical-align: middle; text-transform: uppercase;">WAKTU</th>
                    <th rowspan="2" style="width: 55px; border: 1px solid #ea580c; padding: 10px 4px; vertical-align: middle; text-transform: uppercase;">JAM KE</th>
                    <?php foreach ($classesByLevel as $levelName => $lvlClasses): ?>
                        <th colspan="<?= count($lvlClasses) ?>" style="border: 1px solid #ea580c; padding: 8px 6px; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">
                            <?= SecurityHelper::escape($levelName) ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
                <!-- Baris Header 2: Nama Sub-Kelas (A, B, C / 1A, 1B, dst) -->
                <tr style="background: #ffedd5; color: #431407; font-weight: 700;">
                    <?php foreach ($classesByLevel as $levelName => $lvlClasses): ?>
                        <?php foreach ($lvlClasses as $c): ?>
                            <?php 
                                // Format nama sub-kelas ringkas (misal: "1A", "1B", "A", dsb)
                                $shortClassName = preg_replace('/^Kelas\s+/i', '', $c['name']);
                            ?>
                            <th style="border: 1px solid #ea580c; padding: 6px 4px; min-width: 100px; max-width: 140px;" title="<?= SecurityHelper::escape($c['name']) ?> (Walas: <?= SecurityHelper::escape($c['walas_name'] ?? '-') ?>)">
                                <div style="font-weight: 700; font-size: 0.85rem;"><?= SecurityHelper::escape($shortClassName) ?></div>
                            </th>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $daysToRender = ($viewMode === 'harian') ? [$selectedDay] : $daysList;
                foreach ($daysToRender as $day):
                    $slots = $timeSlotsByDay[$day] ?? [];
                    $totalSlots = count($slots);
                    if ($totalSlots === 0) continue;

                    foreach ($slots as $slotIndex => $slot):
                        $jam = (int)$slot['jam_ke'];
                        $isBreak = (bool)$slot['is_break'];
                        $breakType = $slot['break_type'] ?? 'none';

                        // Styling baris khusus istirahat / apel seperti di lampiran
                        $rowBg = '#ffffff';
                        if ($breakType === 'upacara' || $jam === 0) {
                            $rowBg = '#fff7ed'; // Peach muda
                        } elseif ($breakType === 'istirahat') {
                            $rowBg = '#ffedd5'; // Peach istirahat
                        } elseif ($breakType === 'sholat') {
                            $rowBg = '#e0f2fe'; // Biru muda sholat dzuhur
                        }
                ?>
                <tr style="background: <?= $rowBg ?>; transition: background 0.15s ease;" class="jadwal-row">
                    <!-- Kolom HARI (Rowspan untuk seluruh jam pada hari tersebut) -->
                    <?php if ($slotIndex === 0): ?>
                        <td rowspan="<?= $totalSlots ?>" style="border: 1px solid #ea580c; background: #fed7aa; color: #431407; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; vertical-align: middle; padding: 12px 6px; letter-spacing: 1px;">
                            <?= $day ?>
                        </td>
                    <?php endif; ?>

                    <!-- Kolom WAKTU (Bisa Diklik untuk Edit Jam jika Waka Kurikulum) -->
                    <td class="waktu-cell" style="border: 1px solid #cbd5e1; padding: 6px 4px; font-weight: 600; font-size: 0.775rem; color: #334155; white-space: nowrap; <?= $isBreak ? 'font-style: italic;' : '' ?> <?= $canManage ? 'cursor: pointer;' : '' ?>"
                        <?= $canManage ? "onclick=\"openEditSlotModal('{$day}', {$jam}, '" . htmlspecialchars($slot['time_start']) . "', '" . htmlspecialchars($slot['time_end']) . "', '" . addslashes(htmlspecialchars($slot['label'] ?? '')) . "', " . ($isBreak ? 1 : 0) . ", '" . htmlspecialchars($breakType) . "')\" title='Klik untuk mengubah jam pelajaran ini'" : '' ?>>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 4px;">
                            <span><?= SecurityHelper::escape($slot['time_start']) ?> - <?= SecurityHelper::escape($slot['time_end']) ?></span>
                            <?php if ($canManage): ?>
                                <i class="fa-solid fa-pen waktu-edit-icon" style="font-size: 0.65rem; color: #64748b; opacity: 0.5;"></i>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Kolom JAM KE -->
                    <td style="border: 1px solid #cbd5e1; padding: 6px 2px; font-weight: 700; color: #1e293b; font-size: 0.85rem;">
                        <?php if ($jam === 0): ?>
                            <span style="font-size: 0.75rem; color: #64748b;">-</span>
                        <?php else: ?>
                            <?= $jam ?>
                        <?php endif; ?>
                    </td>

                    <!-- Sel Jadwal Tiap Kelas -->
                    <?php if ($isBreak): ?>
                        <!-- Jika Baris Istirahat / Sholat / Upacara (Menjangkau seluruh kelas seperti lampiran) -->
                        <td colspan="<?= $totalKelas ?>" style="border: 1px solid #cbd5e1; padding: 8px 12px; font-weight: 700; font-size: 0.825rem; color: <?= $breakType === 'sholat' ? '#0369a1' : '#c2410c' ?>; letter-spacing: 0.5px; <?= $canManage ? 'cursor: pointer;' : '' ?>"
                            <?= $canManage ? "onclick=\"openEditSlotModal('{$day}', {$jam}, '" . htmlspecialchars($slot['time_start']) . "', '" . htmlspecialchars($slot['time_end']) . "', '" . addslashes(htmlspecialchars($slot['label'] ?? '')) . "', " . ($isBreak ? 1 : 0) . ", '" . htmlspecialchars($breakType) . "')\" title='Klik untuk mengubah jam atau label rutinitas ini'" : '' ?>>
                            <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <i class="<?= $breakType === 'sholat' ? 'fa-solid fa-mosque' : ($breakType === 'upacara' ? 'fa-solid fa-flag' : 'fa-solid fa-mug-hot') ?>"></i>
                                <span><?= SecurityHelper::escape($slot['label'] ?: 'Istirahat') ?></span>
                                <?php if ($canManage): ?>
                                    <span style="font-size: 0.7rem; font-weight: normal; color: #64748b; margin-left: 6px;"><i class="fa-solid fa-pen"></i> Ubah Jam</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    <?php else: ?>
                        <!-- Sel Pelajaran untuk masing-masing kelas -->
                        <?php foreach ($classesByLevel as $levelName => $lvlClasses): ?>
                            <?php foreach ($lvlClasses as $c): ?>
                                <?php 
                                    $cId = (int)$c['id'];
                                    $cellData = $scheduleMatrix[$day][$jam][$cId] ?? null;
                                    $cellKey = "{$day}_{$jam}_{$cId}";
                                    $isConflict = isset($conflictCells[$cellKey]);
                                    $conflictInfo = $conflictCells[$cellKey] ?? null;

                                    $hasSchedule = !empty($cellData) && !empty($cellData['subject_id']);
                                    $hasTeacher = !empty($cellData['teacher_id']);
                                    $hasSubstitute = !empty($cellData['substitute_teacher_id']);

                                    // Filter sorot guru
                                    $isTeacherMatch = false;
                                    $hasHalaqahs = !empty($cellData['halaqahs']);
                                    $subType = $cellData['subject_type'] ?? 'Umum';
                                    $isTahfidz = ($subType === 'Tahfidz' || $subType === 'Tahsin' || $hasHalaqahs || stripos($cellData['subject_name'] ?? '', 'Tahfidz') !== false || stripos($cellData['subject_name'] ?? '', 'Tahsin') !== false);

                                    if ($filterTeacherId) {
                                        if ($hasHalaqahs) {
                                            foreach ($cellData['halaqahs'] as $h) {
                                                $actId = !empty($h['substitute_teacher_id']) ? (int)$h['substitute_teacher_id'] : (int)$h['teacher_id'];
                                                if ($actId === $filterTeacherId) {
                                                    $isTeacherMatch = true;
                                                    break;
                                                }
                                            }
                                        } else {
                                            $activeTId = $hasSubstitute ? (int)$cellData['substitute_teacher_id'] : ($hasTeacher ? (int)$cellData['teacher_id'] : 0);
                                            $isTeacherMatch = ($activeTId === $filterTeacherId);
                                        }
                                    }

                                    // Subject styling badge & background
                                    $cellBg = '#ffffff';
                                    $borderCol = '#cbd5e1';
                                    $hasAnyInval = $hasSubstitute || ($hasHalaqahs && !empty(array_filter($cellData['halaqahs'], fn($h) => !empty($h['substitute_teacher_id']))));

                                    if ($isConflict) {
                                        $cellBg = '#fef2f2';
                                        $borderCol = '#dc2626';
                                    } elseif ($hasAnyInval) {
                                        $cellBg = '#fffbeb';
                                        $borderCol = '#f59e0b';
                                    } elseif ($filterTeacherId && $isTeacherMatch) {
                                        $cellBg = '#ecfdf5';
                                        $borderCol = '#10b981';
                                    } elseif ($hasSchedule) {
                                        if ($isTahfidz) {
                                            $cellBg = '#f0fdf4';
                                            $borderCol = '#86efac';
                                        } elseif ($subType === 'Diniyah') {
                                            $cellBg = '#fffdf5';
                                        } else {
                                            $cellBg = '#f8fafc';
                                        }
                                    }
                                ?>
                                <td class="jadwal-cell <?= $isConflict ? 'cell-conflict' : '' ?> <?= ($filterTeacherId && !$isTeacherMatch && $hasSchedule) ? 'cell-dimmed' : '' ?>"
                                    style="border: 1px solid <?= $borderCol ?>; background: <?= $cellBg ?>; padding: 6px 4px; vertical-align: top; min-width: 105px; max-width: 155px; position: relative;"
                                    data-day="<?= $day ?>"
                                    data-jam="<?= $jam ?>"
                                    data-time-start="<?= SecurityHelper::escape($slot['time_start']) ?>"
                                    data-time-end="<?= SecurityHelper::escape($slot['time_end']) ?>"
                                    data-class-id="<?= $cId ?>"
                                    data-class-name="<?= SecurityHelper::escape($c['name']) ?>"
                                    data-schedule-id="<?= $cellData['id'] ?? '' ?>"
                                    data-subject-id="<?= $cellData['subject_id'] ?? '' ?>"
                                    data-subject-name="<?= SecurityHelper::escape($cellData['subject_name'] ?? '') ?>"
                                    data-subject-type="<?= SecurityHelper::escape($subType) ?>"
                                    data-has-halaqah="<?= $hasHalaqahs ? '1' : '0' ?>"
                                    data-halaqahs="<?= $hasHalaqahs ? htmlspecialchars(json_encode($cellData['halaqahs']), ENT_QUOTES, 'UTF-8') : '[]' ?>"
                                    data-teacher-id="<?= $cellData['teacher_id'] ?? '' ?>"
                                    data-teacher-name="<?= SecurityHelper::escape($cellData['teacher_name'] ?? '') ?>"
                                    data-substitute-id="<?= $cellData['substitute_teacher_id'] ?? '' ?>"
                                    data-substitute-name="<?= SecurityHelper::escape($cellData['substitute_teacher_name'] ?? '') ?>"
                                    data-substitute-reason="<?= SecurityHelper::escape($cellData['substitute_reason'] ?? '') ?>"
                                    data-substitute-date="<?= SecurityHelper::escape($cellData['substitute_date'] ?? '') ?>"
                                    data-substitute-note="<?= SecurityHelper::escape($cellData['substitute_note'] ?? '') ?>"
                                    data-is-conflict="<?= $isConflict ? '1' : '0' ?>">

                                    <?php if ($hasHalaqahs): ?>
                                        <!-- Kartu Sesi Tahfidz dengan Struktur Halaqah (Hingga 3 Halaqah) -->
                                        <div class="cell-content" style="text-align: left; display: flex; flex-direction: column; gap: 3px;">
                                            <?php if ($isConflict): ?>
                                                <div style="background: #dc2626; color: #ffffff; font-size: 0.65rem; font-weight: 800; border-radius: 3px; padding: 2px 4px; text-align: center; margin-bottom: 2px; animation: pulse 1.5s infinite;" title="Bentrok! Salah satu guru halaqah ini dijadwalkan di tempat lain pada jam yang sama!">
                                                    <i class="fa-solid fa-triangle-exclamation"></i> BENTROK HALAQAH!
                                                </div>
                                            <?php endif; ?>

                                            <!-- Header Mapel Tahfidz & Badge Jumlah Halaqah -->
                                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                                                <div style="font-weight: 700; font-size: 0.775rem; color: #065f46; line-height: 1.2; display: flex; align-items: center; gap: 3px;">
                                                    <i class="fa-solid fa-quran" style="font-size: 0.7rem; color: #059669;"></i>
                                                    <?= SecurityHelper::escape($cellData['subject_name']) ?>
                                                </div>
                                                <span class="badge" style="background: #d1fae5; color: #047857; font-size: 0.6rem; font-weight: 700; padding: 1px 4px; border-radius: 3px;">
                                                    <?= count($cellData['halaqahs']) ?> Hal.
                                                </span>
                                            </div>

                                            <!-- Rincian Tiap Halaqah (Halaqah 1, 2, 3) -->
                                            <div style="display: flex; flex-direction: column; gap: 2px; margin-top: 2px;">
                                                <?php foreach ($cellData['halaqahs'] as $h): 
                                                    $hasHInval = !empty($h['substitute_teacher_id']);
                                                    $hTeacherName = $hasHInval ? $h['substitute_teacher_name'] : $h['teacher_name'];
                                                    $isHMatch = ($filterTeacherId && ($h['teacher_id'] == $filterTeacherId || $h['substitute_teacher_id'] == $filterTeacherId));
                                                ?>
                                                    <div style="background: <?= $hasHInval ? '#fef3c7' : ($isHMatch ? '#ecfdf5' : '#ffffff') ?>; border: 1px solid <?= $hasHInval ? '#f59e0b' : ($isHMatch ? '#10b981' : '#bbf7d0') ?>; border-radius: 3px; padding: 2px 4px; font-size: 0.685rem; line-height: 1.2;">
                                                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 2px;">
                                                            <span style="font-weight: 700; color: #0f766e; font-size: 0.65rem;">
                                                                H<?= (int)$h['halaqah_number'] ?>:
                                                            </span>
                                                            <?php if ($hasHInval): ?>
                                                                <span class="badge" style="background: #f59e0b; color: #fff; font-size: 0.55rem; padding: 0 3px;">INVAL</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div style="font-weight: 600; color: <?= $hasHInval ? '#92400e' : '#1e293b' ?>; font-size: 0.7rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= SecurityHelper::escape($h['halaqah_name']) ?>: <?= SecurityHelper::escape($hTeacherName) ?><?= $hasHInval ? ' (Asal: ' . SecurityHelper::escape($h['teacher_name']) . ')' : '' ?>">
                                                            <?= SecurityHelper::escape($hTeacherName ?: 'Belum diplot') ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <!-- Tombol Aksi jika canManage -->
                                            <?php if ($canManage): ?>
                                                <div class="cell-actions" style="margin-top: 3px; display: flex; gap: 3px; justify-content: flex-end;">
                                                    <button type="button" class="btn btn-xs btn-outline" style="padding: 1px 4px; font-size: 0.65rem; border-color: #059669; color: #047857;" onclick="openEditModal(this.closest('.jadwal-cell'))" title="Atur Guru Halaqah">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-outline" style="padding: 1px 4px; font-size: 0.65rem; color: #d97706; border-color: #f59e0b;" onclick="openSubstituteModal(this.closest('.jadwal-cell'))" title="Tugaskan Guru Inval Halaqah">
                                                        <i class="fa-solid fa-user-plus"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                    <?php elseif ($hasSchedule): ?>
                                        <!-- Kartu Mapel & Guru Reguler (1 Guru) -->
                                        <div class="cell-content" style="text-align: left; display: flex; flex-direction: column; gap: 3px;">
                                            
                                            <!-- Conflict Indicator Badge -->
                                            <?php if ($isConflict): ?>
                                                <div style="background: #dc2626; color: #ffffff; font-size: 0.675rem; font-weight: 800; border-radius: 3px; padding: 2px 4px; text-align: center; margin-bottom: 2px; animation: pulse 1.5s infinite;" title="Bentrok! Guru ini juga dijadwalkan di kelas lain pada jam yang sama!">
                                                    <i class="fa-solid fa-triangle-exclamation"></i> BENTROK!
                                                </div>
                                            <?php endif; ?>

                                            <!-- Nama Mata Pelajaran -->
                                            <div style="font-weight: 700; font-size: 0.8rem; color: #1e293b; line-height: 1.2;">
                                                <?= SecurityHelper::escape($cellData['subject_name']) ?>
                                            </div>

                                            <!-- Nama Guru Utama -->
                                            <div style="font-size: 0.75rem; color: <?= $hasSubstitute ? '#94a3b8; text-decoration: line-through;' : '#0f766e' ?>; font-weight: 600; line-height: 1.2;">
                                                <i class="fa-solid fa-user-tie" style="font-size: 0.7rem; margin-right: 2px;"></i>
                                                <?= SecurityHelper::escape($cellData['teacher_name'] ?: 'Belum diplot') ?>
                                            </div>

                                            <!-- Status Guru Pengganti / Inval -->
                                            <?php if ($hasSubstitute): ?>
                                                <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 4px; padding: 2px 4px; margin-top: 2px;">
                                                    <div style="font-size: 0.675rem; font-weight: 800; color: #b45309; display: flex; align-items: center; gap: 3px;">
                                                        <i class="fa-solid fa-person-chalkboard"></i> INVAL (<?= SecurityHelper::escape($cellData['substitute_reason'] ?: 'Izin') ?>)
                                                    </div>
                                                    <div style="font-size: 0.725rem; font-weight: 700; color: #92400e; line-height: 1.1;">
                                                        <?= SecurityHelper::escape($cellData['substitute_teacher_name']) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Tombol Aksi Cepat jika canManage -->
                                            <?php if ($canManage): ?>
                                                <div class="cell-actions" style="margin-top: 4px; display: flex; gap: 4px; justify-content: flex-end;">
                                                    <button type="button" class="btn btn-xs btn-outline" style="padding: 1px 5px; font-size: 0.675rem;" onclick="openEditModal(this.closest('.jadwal-cell'))" title="Atur / Ubah Jadwal">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-outline" style="padding: 1px 5px; font-size: 0.675rem; color: #d97706; border-color: #f59e0b;" onclick="openSubstituteModal(this.closest('.jadwal-cell'))" title="Tugaskan Guru Pengganti (Inval)">
                                                        <i class="fa-solid fa-user-plus"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Sel Kosong -->
                                        <div style="padding: 8px 2px; text-align: center; color: #cbd5e1;">
                                            <?php if ($canManage): ?>
                                                <button type="button" class="btn btn-xs btn-light" style="width: 100%; border: 1px dashed #cbd5e1; color: #64748b; font-size: 0.7rem; padding: 4px 2px;" onclick="openEditModal(this.closest('.jadwal-cell'))">
                                                    <i class="fa-solid fa-plus"></i> Isi
                                                </button>
                                            <?php else: ?>
                                                <span style="font-size: 0.75rem; color: #94a3b8;">-</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
                <?php 
                    endforeach; // end slots
                endforeach; // end days
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 1: ATUR / EDIT JADWAL PELAJARAN (MAPEL & GURU)         -->
<!-- ============================================================ -->
<div class="modal-overlay" id="editModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="card" style="width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; margin: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding: 14px 18px;">
            <h3 class="card-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-calendar-check" style="color: #0d9488;"></i> Atur Jadwal Pelajaran
            </h3>
            <button type="button" class="btn btn-sm btn-light" onclick="closeModal('editModal')">&times;</button>
        </div>
        <div class="card-body" style="padding: 18px;">
            <form action="<?= $baseUrl ?>/jadwal/save" method="POST" id="formSaveJadwal">
                <?= CSRF::field() ?>
                <input type="hidden" name="return_route" value="<?= SecurityHelper::escape($returnRoute) ?>">
                <input type="hidden" name="return_view_mode" value="<?= SecurityHelper::escape($viewMode) ?>">
                <input type="hidden" name="class_id" id="editClassId">
                <input type="hidden" name="day" id="editDay">
                <input type="hidden" name="jam_ke" id="editJamKe">
                <input type="hidden" name="time_start" id="editTimeStart">
                <input type="hidden" name="time_end" id="editTimeEnd">
                <input type="hidden" name="is_halaqah" id="isHalaqahInput" value="0">

                <!-- Info Header Slot -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px;">
                    <div style="font-size: 0.825rem; color: #64748b;">Target Slot:</div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: #1e293b;" id="editSlotInfo">
                        Senin - Jam Ke-1 (Kelas 1A)
                    </div>
                </div>

                <!-- Dropdown Mata Pelajaran -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Mata Pelajaran <span style="color: #ef4444;">*</span></label>
                    <select name="subject_id" id="editSubjectSelect" class="form-control" required onchange="handleSubjectChange(this.value)">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php foreach ($subjects as $sb): ?>
                            <option value="<?= $sb['id'] ?>" data-name="<?= SecurityHelper::escape($sb['name']) ?>" data-type="<?= SecurityHelper::escape($sb['type']) ?>">
                                <?= SecurityHelper::escape($sb['name']) ?> (<?= SecurityHelper::escape($sb['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ======================================================== -->
                <!-- SECTION KHUSUS: STRUKTUR HALAQAH TAHFIDZ (1 - 3 HALAQAH) -->
                <!-- ======================================================== -->
                <div id="tahfidzHalaqahSection" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                        <div style="font-weight: 700; color: #166534; font-size: 0.875rem; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-quran" style="color: #059669; font-size: 1rem;"></i>
                            <span>Struktur Halaqah Tahfidz Kelas</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 0.775rem; color: #15803d; font-weight: 600;">Jumlah Halaqah:</span>
                            <select name="halaqah_count" id="halaqahCountSelect" class="form-control" style="width: auto; padding: 2px 8px; font-size: 0.8rem; font-weight: 700; height: auto;" onchange="updateHalaqahRows(this.value)">
                                <option value="1">1 Halaqah</option>
                                <option value="2">2 Halaqah</option>
                                <option value="3" selected>3 Halaqah</option>
                            </select>
                        </div>
                    </div>
                    <div style="font-size: 0.75rem; color: #166534; margin-bottom: 10px; line-height: 1.4;">
                        <i class="fa-solid fa-circle-info"></i> Sesuai format pondok/sekolah, tiap kelas Tahfidz dibagi menjadi beberapa halaqah (maksimal 3 halaqah) dengan guru masing-masing.
                    </div>

                    <!-- Row Halaqah 1 -->
                    <div class="halaqah-row-card" id="rowHalaqah1" style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span class="badge" style="background: #059669; color: #fff; font-size: 0.725rem; font-weight: 700;">Halaqah 1</span>
                            <input type="text" name="halaqah_names[1]" id="halaqahName1" class="form-control" value="Halaqah 1" style="width: 140px; height: 26px; font-size: 0.75rem; padding: 2px 6px;" placeholder="Label Halaqah">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 2px; display: block;">Guru Tahfidz Halaqah 1 <span style="color: #ef4444;">*</span></label>
                            <select name="halaqah_teachers[1]" id="halaqahTeacher1" class="form-control halaqah-teacher-select" style="font-size: 0.825rem;" onchange="checkTeacherConflict(this.value)">
                                <option value="">-- Pilih Guru Tahfidz --</option>
                                <?php foreach ($tahfidzTeachers as $tc): ?>
                                    <option value="<?= $tc['id'] ?>"><?= SecurityHelper::escape($tc['nama']) ?> (<?= SecurityHelper::escape($tc['level_posisi']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Row Halaqah 2 -->
                    <div class="halaqah-row-card" id="rowHalaqah2" style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span class="badge" style="background: #0d9488; color: #fff; font-size: 0.725rem; font-weight: 700;">Halaqah 2</span>
                            <input type="text" name="halaqah_names[2]" id="halaqahName2" class="form-control" value="Halaqah 2" style="width: 140px; height: 26px; font-size: 0.75rem; padding: 2px 6px;" placeholder="Label Halaqah">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 2px; display: block;">Guru Tahfidz Halaqah 2</label>
                            <select name="halaqah_teachers[2]" id="halaqahTeacher2" class="form-control halaqah-teacher-select" style="font-size: 0.825rem;" onchange="checkTeacherConflict(this.value)">
                                <option value="">-- Pilih Guru Tahfidz --</option>
                                <?php foreach ($tahfidzTeachers as $tc): ?>
                                    <option value="<?= $tc['id'] ?>"><?= SecurityHelper::escape($tc['nama']) ?> (<?= SecurityHelper::escape($tc['level_posisi']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Row Halaqah 3 -->
                    <div class="halaqah-row-card" id="rowHalaqah3" style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px; margin-bottom: 4px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span class="badge" style="background: #0284c7; color: #fff; font-size: 0.725rem; font-weight: 700;">Halaqah 3</span>
                            <input type="text" name="halaqah_names[3]" id="halaqahName3" class="form-control" value="Halaqah 3" style="width: 140px; height: 26px; font-size: 0.75rem; padding: 2px 6px;" placeholder="Label Halaqah">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 2px; display: block;">Guru Tahfidz Halaqah 3</label>
                            <select name="halaqah_teachers[3]" id="halaqahTeacher3" class="form-control halaqah-teacher-select" style="font-size: 0.825rem;" onchange="checkTeacherConflict(this.value)">
                                <option value="">-- Pilih Guru Tahfidz --</option>
                                <?php foreach ($tahfidzTeachers as $tc): ?>
                                    <option value="<?= $tc['id'] ?>"><?= SecurityHelper::escape($tc['nama']) ?> (<?= SecurityHelper::escape($tc['level_posisi']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Dropdown Guru Pengajar Tunggal (Untuk Mapel Umum & Diniyah Reguler) -->
                <div class="form-group" id="singleTeacherSection" style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin: 0;">Guru Pengajar <span style="color: #ef4444;">*</span></label>
                        <span id="conflictWarningBadge" style="display: none; font-size: 0.75rem; color: #dc2626; font-weight: 700;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Terdeteksi Bentrok!
                        </span>
                    </div>
                    <select name="teacher_id" id="editTeacherSelect" class="form-control" onchange="checkTeacherConflict(this.value)">
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($teachers as $tc): ?>
                            <option value="<?= $tc['id'] ?>" data-name="<?= SecurityHelper::escape($tc['nama']) ?>">
                                <?= SecurityHelper::escape($tc['nama']) ?> (<?= SecurityHelper::escape($tc['level_posisi']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="conflictAlertBox" style="display: none; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 4px; padding: 6px 10px; margin-top: 6px; font-size: 0.775rem; color: #991b1b;"></div>
                </div>

                <!-- Catatan Tambahan (Opsional) -->
                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Jadwal (Opsional)</label>
                    <input type="text" name="notes" id="editNotes" class="form-control" placeholder="Contoh: Pertemuan di Lab Komputer / Lapangan / Masjid">
                </div>

                <!-- Tombol Aksi Simpan & Hapus -->
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                    <button type="button" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; display: none;" id="btnDeleteSchedule" onclick="submitDeleteSchedule()">
                        <i class="fa-solid fa-trash-can"></i> Kosongkan Sel
                    </button>
                    <div style="display: flex; gap: 8px; margin-left: auto;">
                        <button type="button" class="btn btn-light" onclick="closeModal('editModal')">Batal</button>
                        <button type="submit" class="btn btn-primary" style="background: <?= $isTahfidzOnly ? '#059669' : '#0d9488' ?>; border-color: <?= $isTahfidzOnly ? '#047857' : '#0f766e' ?>;">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Jadwal
                        </button>
                    </div>
                </div>
            </form>

            <!-- Hidden Form untuk Hapus Jadwal -->
            <form action="<?= $baseUrl ?>/jadwal/delete" method="POST" id="formDeleteJadwal" style="display: none;">
                <?= CSRF::field() ?>
                <input type="hidden" name="id" id="deleteScheduleId">
                <input type="hidden" name="return_route" value="<?= SecurityHelper::escape($returnRoute) ?>">
                <input type="hidden" name="day" value="<?= SecurityHelper::escape($selectedDay) ?>">
                <input type="hidden" name="view_mode" value="<?= SecurityHelper::escape($viewMode) ?>">
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 2: TUGASKAN GURU PENGGANTI (INVAL / GURU BADAL)        -->
<!-- ============================================================ -->
<div class="modal-overlay" id="substituteModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="card" style="width: 100%; max-width: 540px; max-height: 90vh; overflow-y: auto; margin: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding: 14px 18px; background: #fffbeb;">
            <h3 class="card-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px; color: #b45309;">
                <i class="fa-solid fa-user-plus"></i> Penugasan Guru Pengganti (Inval / Badal)
            </h3>
            <button type="button" class="btn btn-sm btn-light" onclick="closeModal('substituteModal')">&times;</button>
        </div>
        <div class="card-body" style="padding: 18px;">
            <form action="<?= $baseUrl ?>/jadwal/substitute" method="POST" id="formSubstitute">
                <?= CSRF::field() ?>
                <input type="hidden" name="return_route" value="<?= SecurityHelper::escape($returnRoute) ?>">
                <input type="hidden" name="view_mode" value="<?= SecurityHelper::escape($viewMode) ?>">
                <input type="hidden" name="day" value="<?= SecurityHelper::escape($selectedDay) ?>">
                <input type="hidden" name="schedule_id" id="subScheduleId">
                <input type="hidden" name="halaqah_number" id="subHalaqahNumber" value="0">

                <!-- Pilihan Halaqah (Muncul jika Sesi ini Memiliki Halaqah Tahfidz) -->
                <div class="form-group" id="subHalaqahChooserGroup" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #166534; margin-bottom: 4px;">
                        <i class="fa-solid fa-quran"></i> Pilih Halaqah yang Membutuhkan Inval:
                    </label>
                    <select id="subHalaqahSelect" class="form-control" style="font-weight: 600;" onchange="onSelectSubHalaqah(this.value)">
                        <!-- Diisi secara dinamis dari JS -->
                    </select>
                </div>

                <!-- Rincian Guru Asli yang Tidak Masuk -->
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 12px 14px; margin-bottom: 16px;">
                    <div style="font-size: 0.775rem; color: #991b1b; font-weight: 700; text-transform: uppercase;">Guru Asli Berhalangan:</div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: #7f1d1d;" id="subOriginalTeacherName">-</div>
                    <div style="font-size: 0.8rem; color: #991b1b; margin-top: 2px;" id="subSlotDetails">-</div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Alasan Ketidakhadiran Guru Asli <span style="color: #ef4444;">*</span></label>
                    <select name="substitute_reason" id="substituteReason" class="form-control" required>
                        <option value="Sakit">Sakit</option>
                        <option value="Izin Pribadi">Izin Pribadi / Keperluan Mendesak</option>
                        <option value="Dinas Luar">Dinas Luar / Pelatihan Yayasan</option>
                        <option value="Cuti">Cuti Resmi</option>
                        <option value="Tugas Sekolah">Penugasan Khusus Sekolah</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tanggal Penggantian <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="substitute_date" id="substituteDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <!-- Dropdown Guru Pengganti (Direkomendasikan Guru yang Free di Jam ini) -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin: 0;">Pilih Guru Pengganti (Inval) <span style="color: #ef4444;">*</span></label>
                        <span id="subTeacherLoading" style="display: none; font-size: 0.75rem; color: #0d9488;">
                            <i class="fa-solid fa-spinner fa-spin"></i> Memeriksa ketersediaan guru...
                        </span>
                    </div>
                    <select name="substitute_teacher_id" id="substituteTeacherSelect" class="form-control" required>
                        <option value="">-- Pilih Guru Pengganti --</option>
                        <?php foreach ($teachers as $tc): ?>
                            <option value="<?= $tc['id'] ?>">
                                <?= SecurityHelper::escape($tc['nama']) ?> (<?= SecurityHelper::escape($tc['level_posisi']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                        <i class="fa-solid fa-circle-info"></i> Guru yang sedang FREE di jam ini akan diprioritaskan di posisi atas dengan label hijau.
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Penggantian / Materi yang Diajarkan</label>
                    <textarea name="substitute_note" id="substituteNote" class="form-control" rows="2" placeholder="Contoh: Menyimak setoran juz 30 / melanjutkan tilawah tahsin."></textarea>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                    <button type="button" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; display: none;" id="btnRemoveSubstitute" onclick="submitRemoveSubstitute()">
                        <i class="fa-solid fa-user-xmark"></i> Batalkan Inval (Kembalikan Guru Asal)
                    </button>
                    <div style="display: flex; gap: 8px; margin-left: auto;">
                        <button type="button" class="btn btn-light" onclick="closeModal('substituteModal')">Batal</button>
                        <button type="submit" class="btn btn-primary" style="background: #d97706; border-color: #b45309;">
                            <i class="fa-solid fa-check"></i> Tetapkan Pengganti
                        </button>
                    </div>
                </div>
            </form>

            <!-- Hidden Form untuk Batal Inval -->
            <form action="<?= $baseUrl ?>/jadwal/remove-substitute" method="POST" id="formRemoveSubstitute" style="display: none;">
                <?= CSRF::field() ?>
                <input type="hidden" name="return_route" value="<?= SecurityHelper::escape($returnRoute) ?>">
                <input type="hidden" name="schedule_id" id="removeSubScheduleId">
                <input type="hidden" name="halaqah_number" id="removeSubHalaqahNumber" value="0">
                <input type="hidden" name="day" value="<?= SecurityHelper::escape($selectedDay) ?>">
                <input type="hidden" name="view_mode" value="<?= SecurityHelper::escape($viewMode) ?>">
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 3: RADAR GURU FREE VS MENGAJAR (LIVE CHECKER)          -->
<!-- ============================================================ -->
<div class="modal-overlay" id="radarModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="card" style="width: 100%; max-width: 820px; max-height: 90vh; overflow-y: auto; margin: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding: 14px 20px; background: #f0fdfa;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; color: #0f766e;">
                    <i class="fa-solid fa-radar fa-spin" style="--fa-animation-duration: 4s;"></i> Radar Ketersediaan Guru (Free vs Mengajar)
                </h3>
                <div style="font-size: 0.8rem; color: #115e59; margin-top: 2px;">
                    Cari guru yang sedang jam kosong (free) di hari dan jam tertentu untuk penugasan inval / pengganti secara instan.
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-light" onclick="closeModal('radarModal')">&times;</button>
        </div>
        <div class="card-body" style="padding: 20px;">
            <!-- Filter Hari, Jam Ke & Tahfidz Switcher -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 0.825rem;">Hari Pembelajaran:</label>
                    <select id="radarDaySelect" class="form-control" onchange="fetchRadarStatus()">
                        <?php foreach ($daysList as $d): ?>
                            <option value="<?= $d ?>" <?= $d === $selectedDay ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight: 600; font-size: 0.825rem;">Jam Pelajaran Ke:</label>
                    <select id="radarJamSelect" class="form-control" onchange="fetchRadarStatus()">
                        <option value="1">Jam Ke-1 (07.45 - 08.20)</option>
                        <option value="2">Jam Ke-2 (08.20 - 08.55)</option>
                        <option value="3">Jam Ke-3 (08.55 - 09.30)</option>
                        <option value="5">Jam Ke-4 (10.00 - 10.35)</option>
                        <option value="6">Jam Ke-5 (10.35 - 11.10)</option>
                        <option value="7">Jam Ke-6 (11.10 - 11.45)</option>
                        <option value="9">Jam Ke-7 (12.45 - 13.20)</option>
                        <option value="10">Jam Ke-8 (13.20 - 13.55)</option>
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; justify-content: flex-end;">
                    <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.825rem; font-weight: 600; color: #047857; margin-bottom: 8px; cursor: pointer;">
                        <input type="checkbox" id="radarTahfidzOnlyCheck" <?= $isTahfidzOnly ? 'checked' : '' ?> onchange="fetchRadarStatus()" style="accent-color: #059669; width: 16px; height: 16px;">
                        Khusus Guru Tahfidz
                    </label>
                </div>
            </div>

            <!-- Hasil Perbandingan 2 Kolom -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                
                <!-- Kolom Kiri: Guru Free (Jam Kosong) -->
                <div style="border: 1px solid #bbf7d0; border-radius: 8px; overflow: hidden; background: #ffffff;">
                    <div style="background: #dcfce7; padding: 10px 14px; border-bottom: 1px solid #bbf7d0; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 700; color: #166534; font-size: 0.9rem; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-circle-check" style="color: #16a34a;"></i> Guru FREE / Jam Kosong
                        </span>
                        <span class="badge badge-success" id="radarFreeCount" style="background: #16a34a; font-weight: 700;">0 Guru</span>
                    </div>
                    <div id="radarFreeList" style="padding: 12px; max-height: 380px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                        <div style="text-align: center; color: #94a3b8; padding: 20px;">Memuat data...</div>
                    </div>
                </div>

                <!-- Kolom Kanan: Guru Sedang Mengajar -->
                <div style="border: 1px solid #fecaca; border-radius: 8px; overflow: hidden; background: #ffffff;">
                    <div style="background: #fee2e2; padding: 10px 14px; border-bottom: 1px solid #fecaca; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 700; color: #991b1b; font-size: 0.9rem; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-chalkboard-user" style="color: #dc2626;"></i> Guru Sedang Mengajar
                        </span>
                        <span class="badge badge-danger" id="radarBusyCount" style="background: #dc2626; font-weight: 700;">0 Guru</span>
                    </div>
                    <div id="radarBusyList" style="padding: 12px; max-height: 380px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                        <div style="text-align: center; color: #94a3b8; padding: 20px;">Memuat data...</div>
                    </div>
                </div>

            </div>
        </div>
        <div class="card-footer" style="padding: 12px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('radarModal')">Tutup</button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 4: EDIT JAM PELAJARAN (SLOT WAKTU TUNGGAL)             -->
<!-- ============================================================ -->
<div class="modal-overlay" id="editSlotModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="card" style="width: 100%; max-width: 490px; max-height: 90vh; overflow-y: auto; margin: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding: 14px 18px; background: #e0e7ff;">
            <h3 class="card-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px; color: #3730a3;">
                <i class="fa-solid fa-clock"></i> Edit Jam Pelajaran (Slot Waktu)
            </h3>
            <button type="button" class="btn btn-sm btn-light" onclick="closeModal('editSlotModal')">&times;</button>
        </div>
        <div class="card-body" style="padding: 18px;">
            <form action="<?= $baseUrl ?>/jadwal/slots/save" method="POST" id="formSaveSlot">
                <?= CSRF::field() ?>
                <input type="hidden" name="view_mode" value="<?= SecurityHelper::escape($viewMode) ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Hari Pembelajaran</label>
                        <select name="day" id="slotDayInput" class="form-control" required>
                            <?php foreach ($daysList as $d): ?>
                                <option value="<?= $d ?>"><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Jam Ke-</label>
                        <input type="number" name="jam_ke" id="slotJamKeInput" class="form-control" min="0" max="15" required placeholder="0 = Apel, 1, 2..">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Waktu Mulai <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="time_start" id="slotTimeStartInput" class="form-control" placeholder="Contoh: 07.45" required>
                        <small style="color: #64748b; font-size: 0.725rem;">Format: 07.45 atau 07:45</small>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Waktu Selesai <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="time_end" id="slotTimeEndInput" class="form-control" placeholder="Contoh: 08.20" required>
                        <small style="color: #64748b; font-size: 0.725rem;">Format: 08.20 atau 08:20</small>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Label / Keterangan Sesi</label>
                    <input type="text" name="label" id="slotLabelInput" class="form-control" placeholder="Contoh: Jam Ke-1, Istirahat I & Dhuha, Upacara">
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tipe Jam / Sesi</label>
                    <select name="break_type" id="slotBreakTypeInput" class="form-control" onchange="handleBreakTypeChange(this.value)">
                        <option value="none">Pelajaran KBM Normal</option>
                        <option value="istirahat">Istirahat & Snack</option>
                        <option value="sholat">Sholat (Dhuha / Dzuhur / Jum'at)</option>
                        <option value="upacara">Upacara / Ikrar / Pembiasaan Pagi</option>
                        <option value="kegiatan">Kegiatan / Ekskul Khusus</option>
                    </select>
                    <input type="hidden" name="is_break" id="slotIsBreakHidden" value="0">
                </div>

                <!-- Checkbox Terapkan ke Semua Hari -->
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; margin-bottom: 18px;">
                    <label style="display: flex; align-items: center; gap: 8px; margin: 0; cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #1e293b;">
                        <input type="checkbox" name="apply_all_days" id="slotApplyAllDays" value="1" checked style="width: 16px; height: 16px; accent-color: #4f46e5;">
                        <span>Terapkan jam ini ke semua hari (Senin s/d Jum'at)</span>
                    </label>
                    <div style="font-size: 0.75rem; color: #64748b; margin-left: 24px; margin-top: 2px;">
                        Centang ini agar rentang jam yang diatur otomatis seragam di seluruh hari sekolah.
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                    <button type="button" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5; display: none;" id="btnDeleteSlot" onclick="submitDeleteSlot()">
                        <i class="fa-solid fa-trash-can"></i> Hapus Jam Ini
                    </button>
                    <div style="display: flex; gap: 8px; margin-left: auto;">
                        <button type="button" class="btn btn-light" onclick="closeModal('editSlotModal')">Batal</button>
                        <button type="submit" class="btn btn-primary" style="background: #4f46e5; border-color: #4338ca;">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Jam
                        </button>
                    </div>
                </div>
            </form>

            <form action="<?= $baseUrl ?>/jadwal/slots/delete" method="POST" id="formDeleteSlot" style="display: none;">
                <?= CSRF::field() ?>
                <input type="hidden" name="day" id="deleteSlotDay">
                <input type="hidden" name="jam_ke" id="deleteSlotJamKe">
                <input type="hidden" name="view_mode" value="<?= SecurityHelper::escape($viewMode) ?>">
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 5: KELOLA SEMUA JAM PELAJARAN (SLOT MANAGER)          -->
<!-- ============================================================ -->
<div class="modal-overlay" id="allSlotsModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="card" style="width: 100%; max-width: 860px; max-height: 90vh; overflow-y: auto; margin: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding: 14px 20px; background: #e0e7ff;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; color: #3730a3;">
                    <i class="fa-solid fa-clock"></i> Pengaturan Jam Pelajaran (Slot Waktu Sekolah)
                </h3>
                <div style="font-size: 0.8rem; color: #4338ca; margin-top: 2px;">
                    Kelola jam mulai, jam selesai, waktu istirahat, dan sholat untuk setiap hari pembelajaran.
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-light" onclick="closeModal('allSlotsModal')">&times;</button>
        </div>
        <div class="card-body" style="padding: 20px;">
            
            <!-- Tab Hari -->
            <div style="display: flex; gap: 8px; border-bottom: 2px solid #e2e8f0; margin-bottom: 16px; padding-bottom: 2px;">
                <?php foreach ($daysList as $dIdx => $d): ?>
                    <button type="button" class="btn btn-sm slot-day-tab-btn <?= $dIdx === 0 ? 'btn-primary' : 'btn-light' ?>" 
                            id="tabBtn_<?= $d ?>" onclick="switchSlotDayTab('<?= $d ?>')" style="font-weight: 700; padding: 6px 16px;">
                        <?= $d ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Content per Hari -->
            <?php foreach ($daysList as $dIdx => $d): 
                $daySlots = $timeSlotsByDay[$d] ?? [];
            ?>
            <div class="slot-day-content" id="slotContent_<?= $d ?>" style="<?= $dIdx === 0 ? 'display: block;' : 'display: none;' ?>">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-weight: 700; color: #334155; font-size: 0.9rem;">
                        Daftar Jam Pembelajaran - Hari <?= $d ?> (<?= count($daySlots) ?> Slot)
                    </span>
                    <button type="button" class="btn btn-sm btn-outline" onclick="openNewSlotModalForDay('<?= $d ?>')" style="display: inline-flex; align-items: center; gap: 5px; color: #4f46e5; border-color: #6366f1;">
                        <i class="fa-solid fa-plus"></i> Tambah Jam Baru
                    </button>
                </div>

                <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 6px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem; text-align: left;">
                        <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 700;">
                            <tr>
                                <th style="padding: 8px 10px; width: 60px; text-align: center;">Jam Ke</th>
                                <th style="padding: 8px 10px; width: 140px;">Rentang Waktu</th>
                                <th style="padding: 8px 10px;">Label / Keterangan</th>
                                <th style="padding: 8px 10px; width: 130px;">Tipe Sesi</th>
                                <th style="padding: 8px 10px; width: 100px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daySlots)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px; color: #94a3b8;">Belum ada slot waktu untuk hari ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($daySlots as $sl): 
                                    $jamNum = (int)$sl['jam_ke'];
                                    $isBrk = (bool)$sl['is_break'];
                                    $brkType = $sl['break_type'] ?? 'none';
                                ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; background: <?= $isBrk ? ($brkType === 'sholat' ? '#f0f9ff' : '#fff7ed') : '#ffffff' ?>;">
                                    <td style="padding: 8px 10px; text-align: center; font-weight: 700;">
                                        <?= $jamNum === 0 ? '-' : $jamNum ?>
                                    </td>
                                    <td style="padding: 8px 10px; font-weight: 600; color: #1e293b; white-space: nowrap;">
                                        <i class="fa-regular fa-clock" style="color: #64748b; margin-right: 4px;"></i>
                                        <?= SecurityHelper::escape($sl['time_start']) ?> - <?= SecurityHelper::escape($sl['time_end']) ?>
                                    </td>
                                    <td style="padding: 8px 10px; color: #334155;">
                                        <?= SecurityHelper::escape($sl['label'] ?: ($isBrk ? 'Istirahat' : "Jam Ke-{$jamNum}")) ?>
                                    </td>
                                    <td style="padding: 8px 10px;">
                                        <?php if ($brkType === 'sholat'): ?>
                                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.725rem;">Sholat</span>
                                        <?php elseif ($brkType === 'istirahat'): ?>
                                            <span class="badge" style="background: #ffedd5; color: #c2410c; font-size: 0.725rem;">Istirahat</span>
                                        <?php elseif ($brkType === 'upacara'): ?>
                                            <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 0.725rem;">Upacara/Apel</span>
                                        <?php else: ?>
                                            <span class="badge badge-light" style="font-size: 0.725rem;">KBM Normal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 8px 10px; text-align: center;">
                                        <div style="display: flex; gap: 4px; justify-content: center;">
                                            <button type="button" class="btn btn-xs btn-outline" 
                                                    onclick="openEditSlotModal('<?= $d ?>', <?= $jamNum ?>, '<?= SecurityHelper::escape($sl['time_start']) ?>', '<?= SecurityHelper::escape($sl['time_end']) ?>', '<?= addslashes(SecurityHelper::escape($sl['label'] ?? '')) ?>', <?= $isBrk ? 1 : 0 ?>, '<?= SecurityHelper::escape($brkType) ?>')" 
                                                    title="Edit Jam">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline" style="color: #ef4444; border-color: #fca5a5;" 
                                                    onclick="triggerDirectDeleteSlot('<?= $d ?>', <?= $jamNum ?>)" 
                                                    title="Hapus Jam">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Fitur Salin Jam ke Hari Lain -->
                <div style="margin-top: 18px; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                    <form action="<?= $baseUrl ?>/jadwal/slots/copy" method="POST" onsubmit="return confirm('Salin seluruh susunan jam hari <?= $d ?> ke hari yang dipilih?')">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="source_day" value="<?= $d ?>">
                        <input type="hidden" name="view_mode" value="<?= SecurityHelper::escape($viewMode) ?>">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span style="font-weight: 600; font-size: 0.825rem; color: #475569;">
                                    <i class="fa-solid fa-copy" style="color: #6366f1;"></i> Salin Jam Hari <strong><?= $d ?></strong> ke:
                                </span>
                                <?php foreach ($daysList as $targetD): ?>
                                    <?php if ($targetD !== $d): ?>
                                        <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.8rem; margin: 0; cursor: pointer;">
                                            <input type="checkbox" name="target_days[]" value="<?= $targetD ?>" checked style="accent-color: #4f46e5;">
                                            <?= $targetD ?>
                                        </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline" style="color: #4f46e5; border-color: #6366f1;">
                                <i class="fa-solid fa-clone"></i> Terapkan Salinan Jam
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
        <div class="card-footer" style="padding: 12px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <form action="<?= $baseUrl ?>/jadwal/slots/reset" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan susunan jam ke pengaturan standar sekolah?')">
                <?= CSRF::field() ?>
                <input type="hidden" name="view_mode" value="<?= SecurityHelper::escape($viewMode) ?>">
                <input type="hidden" name="day" value="<?= SecurityHelper::escape($selectedDay) ?>">
                <button type="submit" class="btn btn-sm btn-outline" style="color: #64748b;">
                    <i class="fa-solid fa-rotate-left"></i> Reset ke Jam Standar
                </button>
            </form>
            <button type="button" class="btn btn-secondary" onclick="closeModal('allSlotsModal')">Tutup</button>
        </div>
    </div>
</div>

<style>
/* CSS Styling Khusus Tabel Jadwal Format Lampiran */
.jadwal-table th, .jadwal-table td {
    vertical-align: middle;
}
.jadwal-table tbody tr:hover td {
    background-color: rgba(241, 245, 249, 0.6) !important;
}
.cell-conflict {
    box-shadow: inset 0 0 0 2px #dc2626 !important;
}
.cell-dimmed {
    opacity: 0.35;
}
.cell-actions {
    opacity: 0.85;
    transition: opacity 0.2s ease;
}
.jadwal-cell:hover .cell-actions {
    opacity: 1;
}
.btn-xs {
    padding: 2px 6px;
    font-size: 0.7rem;
    line-height: 1.2;
    border-radius: 4px;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
</style>

<script>
// Data Subject-Teacher Map dari PHP
const subjectTeachersMap = <?= json_encode($subjectTeachersMap) ?>;
const allTeachers = <?= json_encode($teachers) ?>;
const tahfidzTeachers = <?= json_encode($tahfidzTeachers ?? []) ?>;
const isTahfidzOnly = <?= $isTahfidzOnly ? 'true' : 'false' ?>;
const baseUrl = "<?= $baseUrl ?>";
let currentModalHalaqahs = [];

// Filter Sorot Guru
function handleTeacherFilter(teacherId) {
    const url = new URL(window.location.href);
    if (teacherId) {
        url.searchParams.set('teacher_id', teacherId);
    } else {
        url.searchParams.delete('teacher_id');
    }
    window.location.href = url.toString();
}

// Update Jumlah Tampilan Baris Halaqah (1, 2, atau 3)
function updateHalaqahRows(count) {
    count = parseInt(count) || 1;
    document.getElementById('rowHalaqah1').style.display = count >= 1 ? 'block' : 'none';
    document.getElementById('rowHalaqah2').style.display = count >= 2 ? 'block' : 'none';
    document.getElementById('rowHalaqah3').style.display = count >= 3 ? 'block' : 'none';
}

// Open Modal Atur Jadwal
function openEditModal(cell) {
    const day = cell.getAttribute('data-day');
    const jam = cell.getAttribute('data-jam');
    const timeStart = cell.getAttribute('data-time-start');
    const timeEnd = cell.getAttribute('data-time-end');
    const classId = cell.getAttribute('data-class-id');
    const className = cell.getAttribute('data-class-name');
    const scheduleId = cell.getAttribute('data-schedule-id');
    const subjectId = cell.getAttribute('data-subject-id');
    const teacherId = cell.getAttribute('data-teacher-id');
    const hasHalaqah = cell.getAttribute('data-has-halaqah') === '1';
    const halaqahsJson = cell.getAttribute('data-halaqahs');

    document.getElementById('editClassId').value = classId;
    document.getElementById('editDay').value = day;
    document.getElementById('editJamKe').value = jam;
    document.getElementById('editTimeStart').value = timeStart;
    document.getElementById('editTimeEnd').value = timeEnd;
    document.getElementById('editSlotInfo').innerHTML = `Hari <strong>${day}</strong>, Jam Ke-<strong>${jam}</strong> (${timeStart} - ${timeEnd}) &bull; <strong>${className}</strong>`;

    document.getElementById('editSubjectSelect').value = subjectId || '';

    let parsedHalaqahs = [];
    try {
        parsedHalaqahs = halaqahsJson ? JSON.parse(halaqahsJson) : [];
    } catch (e) {
        parsedHalaqahs = [];
    }

    // Periksa apakah mapel Tahfidz atau memiliki halaqah
    const subjOption = document.querySelector(`#editSubjectSelect option[value="${subjectId}"]`);
    const subjType = subjOption ? subjOption.getAttribute('data-type') : '';
    const subjName = subjOption ? subjOption.getAttribute('data-name') : '';
    const isTahfidzSubj = (hasHalaqah || subjType === 'Tahfidz' || subjType === 'Tahsin' || (subjName && (subjName.toLowerCase().includes('tahfidz') || subjName.toLowerCase().includes('tahsin'))));

    if (isTahfidzSubj) {
        document.getElementById('tahfidzHalaqahSection').style.display = 'block';
        document.getElementById('singleTeacherSection').style.display = 'none';
        document.getElementById('isHalaqahInput').value = '1';
        document.getElementById('editTeacherSelect').removeAttribute('required');

        const count = parsedHalaqahs.length > 0 ? parsedHalaqahs.length : 3;
        document.getElementById('halaqahCountSelect').value = count;
        updateHalaqahRows(count);

        // Reset & Isi Data Halaqah
        for (let i = 1; i <= 3; i++) {
            const h = parsedHalaqahs.find(item => item.halaqah_number == i);
            document.getElementById(`halaqahName${i}`).value = h ? h.halaqah_name : `Halaqah ${i}`;
            document.getElementById(`halaqahTeacher${i}`).value = h ? h.teacher_id : '';
        }
    } else {
        document.getElementById('tahfidzHalaqahSection').style.display = 'none';
        document.getElementById('singleTeacherSection').style.display = 'block';
        document.getElementById('isHalaqahInput').value = '0';
        document.getElementById('editTeacherSelect').setAttribute('required', 'required');
        populateTeacherOptions(subjectId, teacherId);
    }

    // Tombol delete
    const btnDelete = document.getElementById('btnDeleteSchedule');
    const deleteIdInput = document.getElementById('deleteScheduleId');
    if (scheduleId) {
        btnDelete.style.display = 'inline-flex';
        deleteIdInput.value = scheduleId;
    } else {
        btnDelete.style.display = 'none';
        deleteIdInput.value = '';
    }

    document.getElementById('editModal').style.display = 'flex';
}

// Populate Guru saat Mapel Berubah
function handleSubjectChange(subjectId) {
    const subjOption = document.querySelector(`#editSubjectSelect option[value="${subjectId}"]`);
    const subjType = subjOption ? subjOption.getAttribute('data-type') : '';
    const subjName = subjOption ? subjOption.getAttribute('data-name') : '';
    const isTahfidz = (subjType === 'Tahfidz' || subjType === 'Tahsin' || (subjName && (subjName.toLowerCase().includes('tahfidz') || subjName.toLowerCase().includes('tahsin'))));

    if (isTahfidz) {
        document.getElementById('tahfidzHalaqahSection').style.display = 'block';
        document.getElementById('singleTeacherSection').style.display = 'none';
        document.getElementById('isHalaqahInput').value = '1';
        document.getElementById('editTeacherSelect').removeAttribute('required');
        document.getElementById('halaqahCountSelect').value = '3';
        updateHalaqahRows(3);
    } else {
        document.getElementById('tahfidzHalaqahSection').style.display = 'none';
        document.getElementById('singleTeacherSection').style.display = 'block';
        document.getElementById('isHalaqahInput').value = '0';
        document.getElementById('editTeacherSelect').setAttribute('required', 'required');
        populateTeacherOptions(subjectId, '');
    }
}

function populateTeacherOptions(subjectId, selectedTeacherId) {
    const select = document.getElementById('editTeacherSelect');
    select.innerHTML = '<option value="">-- Pilih Guru --</option>';

    // Guru yang di-assign mapel ini oleh operator sekolah
    const mapped = (subjectId && subjectTeachersMap[subjectId]) ? subjectTeachersMap[subjectId] : {};
    const mappedIds = Object.keys(mapped);

    if (mappedIds.length > 0) {
        const groupMapped = document.createElement('optgroup');
        groupMapped.label = "⭐ Guru Spesialis Mapel Ini (Input Operator)";
        for (const id in mapped) {
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = mapped[id].nama + " (Guru Mapel Terdaftar)";
            if (id == selectedTeacherId) opt.selected = true;
            groupMapped.appendChild(opt);
        }
        select.appendChild(groupMapped);
    }

    // Semua guru lain di unit
    const groupOther = document.createElement('optgroup');
    groupOther.label = mappedIds.length > 0 ? "Guru Lainnya di Sekolah" : "Semua Guru";
    allTeachers.forEach(t => {
        if (!mapped[t.id]) {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.nama + " (" + (t.level_posisi || 'Guru') + ")";
            if (t.id == selectedTeacherId) opt.selected = true;
            groupOther.appendChild(opt);
        }
    });
    select.appendChild(groupOther);
}

// Cek Bentrok Real-Time via API
function checkTeacherConflict(teacherId) {
    const day = document.getElementById('editDay').value;
    const jam = document.getElementById('editJamKe').value;
    const classId = document.getElementById('editClassId').value;
    const alertBox = document.getElementById('conflictAlertBox');
    const badge = document.getElementById('conflictWarningBadge');

    if (!teacherId) {
        alertBox.style.display = 'none';
        badge.style.display = 'none';
        return;
    }

    fetch(`${baseUrl}/api/jadwal/teacher-status?day=${encodeURIComponent(day)}&jam_ke=${jam}`)
        .then(r => r.json())
        .then(data => {
            const busyList = data.busy_teachers || [];
            const foundBusy = busyList.find(b => b.id == teacherId);

            if (foundBusy && foundBusy.assignments) {
                // Filter excluding current class
                const otherClasses = foundBusy.assignments.filter(a => a.class_id != classId);
                if (otherClasses.length > 0) {
                    badge.style.display = 'inline-block';
                    alertBox.style.display = 'block';
                    alertBox.innerHTML = `<strong>Peringatan Bentrok:</strong> Guru ini sudah terjadwal mengajar di <strong>${otherClasses.map(c => c.class_name).join(', ')}</strong> pada jam yang sama! Anda tetap dapat menyimpan jika bermaksud penugasan ganda.`;
                    return;
                }
            }
            alertBox.style.display = 'none';
            badge.style.display = 'none';
        })
        .catch(e => console.error(e));
}

function submitDeleteSchedule() {
    if (confirm('Apakah Anda yakin ingin mengosongkan jadwal pada sel ini?')) {
        document.getElementById('formDeleteJadwal').submit();
    }
}

// Open Modal Tugaskan Inval / Pengganti (Mendukung Pemilihan Halaqah 1, 2, atau 3)
function openSubstituteModal(cell) {
    const scheduleId = cell.getAttribute('data-schedule-id');
    const teacherName = cell.getAttribute('data-teacher-name');
    const subjectName = cell.getAttribute('data-subject-name');
    const className = cell.getAttribute('data-class-name');
    const day = cell.getAttribute('data-day');
    const jam = cell.getAttribute('data-jam');
    const timeStart = cell.getAttribute('data-time-start');
    const timeEnd = cell.getAttribute('data-time-end');
    const hasHalaqah = cell.getAttribute('data-has-halaqah') === '1';
    const halaqahsJson = cell.getAttribute('data-halaqahs');

    document.getElementById('subScheduleId').value = scheduleId;
    document.getElementById('removeSubScheduleId').value = scheduleId;
    document.getElementById('subSlotDetails').textContent = `${subjectName} &bull; ${className} &bull; ${day}, Jam Ke-${jam} (${timeStart} - ${timeEnd})`;

    try {
        currentModalHalaqahs = halaqahsJson ? JSON.parse(halaqahsJson) : [];
    } catch (e) {
        currentModalHalaqahs = [];
    }

    const halaqahGroup = document.getElementById('subHalaqahChooserGroup');
    const halaqahSelect = document.getElementById('subHalaqahSelect');

    if (hasHalaqah && currentModalHalaqahs.length > 0) {
        halaqahGroup.style.display = 'block';
        halaqahSelect.innerHTML = '';

        currentModalHalaqahs.forEach(h => {
            const opt = document.createElement('option');
            opt.value = h.halaqah_number;
            const hasInv = !!h.substitute_teacher_id;
            opt.textContent = `${h.halaqah_name} &mdash; Guru Asal: ${h.teacher_name || 'Belum diplot'}${hasInv ? ' [SUDAH DI-INVAL]' : ''}`;
            halaqahSelect.appendChild(opt);
        });

        // Trigger pemilihan halaqah pertama secara default
        onSelectSubHalaqah(currentModalHalaqahs[0].halaqah_number);
    } else {
        halaqahGroup.style.display = 'none';
        document.getElementById('subHalaqahNumber').value = '0';
        document.getElementById('removeSubHalaqahNumber').value = '0';

        const substituteId = cell.getAttribute('data-substitute-id');
        const substituteReason = cell.getAttribute('data-substitute-reason');
        const substituteDate = cell.getAttribute('data-substitute-date');
        const substituteNote = cell.getAttribute('data-substitute-note');

        document.getElementById('subOriginalTeacherName').textContent = teacherName || 'Belum diplot';
        if (substituteReason) document.getElementById('substituteReason').value = substituteReason;
        if (substituteDate) document.getElementById('substituteDate').value = substituteDate;
        if (substituteNote) document.getElementById('substituteNote').value = substituteNote;

        const btnRemove = document.getElementById('btnRemoveSubstitute');
        if (substituteId) {
            btnRemove.style.display = 'inline-flex';
        } else {
            btnRemove.style.display = 'none';
        }

        loadSubstituteTeacherOptions(day, jam, substituteId);
    }

    document.getElementById('substituteModal').style.display = 'flex';
}

// Handler Saat Pengguna Memilih Halaqah yang Digantikan
function onSelectSubHalaqah(halaqahNumber) {
    document.getElementById('subHalaqahNumber').value = halaqahNumber;
    document.getElementById('removeSubHalaqahNumber').value = halaqahNumber;

    const cellDay = document.getElementById('radarDaySelect').value;
    const cellJam = document.getElementById('radarJamSelect').value;
    const day = cellDay || 'Senin';
    const jam = cellJam || 1;

    const h = currentModalHalaqahs.find(item => item.halaqah_number == halaqahNumber);
    if (!h) return;

    document.getElementById('subOriginalTeacherName').textContent = `${h.halaqah_name}: ${h.teacher_name || 'Belum diplot'}`;

    const btnRemove = document.getElementById('btnRemoveSubstitute');
    if (h.substitute_teacher_id) {
        btnRemove.style.display = 'inline-flex';
        document.getElementById('substituteReason').value = h.substitute_reason || 'Sakit';
        document.getElementById('substituteDate').value = h.substitute_date || '<?= date('Y-m-d') ?>';
        document.getElementById('substituteNote').value = h.substitute_note || '';
        loadSubstituteTeacherOptions(day, jam, h.substitute_teacher_id, true);
    } else {
        btnRemove.style.display = 'none';
        document.getElementById('substituteReason').value = 'Sakit';
        document.getElementById('substituteDate').value = '<?= date('Y-m-d') ?>';
        document.getElementById('substituteNote').value = '';
        loadSubstituteTeacherOptions(day, jam, '', true);
    }
}

function loadSubstituteTeacherOptions(day, jam, selectedSubstituteId, isTahfidzContext = false) {
    const loading = document.getElementById('subTeacherLoading');
    const select = document.getElementById('substituteTeacherSelect');
    loading.style.display = 'inline-block';

    const tahfidzParam = (isTahfidzOnly || isTahfidzContext) ? '&is_tahfidz_only=1' : '';

    fetch(`${baseUrl}/api/jadwal/teacher-status?day=${encodeURIComponent(day)}&jam_ke=${jam}${tahfidzParam}`)
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            select.innerHTML = '<option value="">-- Pilih Guru Pengganti --</option>';

            const freeList = data.free_teachers || [];
            const busyList = data.busy_teachers || [];

            // Group 1: Guru FREE (Rekomendasi Utama)
            if (freeList.length > 0) {
                const grpFree = document.createElement('optgroup');
                grpFree.label = `🟢 Guru FREE / Jam Kosong (${freeList.length} Guru Tersedia)`;
                freeList.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = `[FREE] ${t.nama} (${t.position || 'Guru'})`;
                    if (t.id == selectedSubstituteId) opt.selected = true;
                    grpFree.appendChild(opt);
                });
                select.appendChild(grpFree);
            }

            // Group 2: Guru yang sedang mengajar
            if (busyList.length > 0) {
                const grpBusy = document.createElement('optgroup');
                grpBusy.label = `🔴 Sedang Mengajar (${busyList.length} Guru)`;
                busyList.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    const cl = (t.assignments && t.assignments[0]) ? t.assignments[0].class_name : 'Kelas Lain';
                    opt.textContent = `[MENGAJAR di ${cl}] ${t.nama}`;
                    if (t.id == selectedSubstituteId) opt.selected = true;
                    grpBusy.appendChild(opt);
                });
                select.appendChild(grpBusy);
            }
        })
        .catch(e => {
            loading.style.display = 'none';
            console.error(e);
        });
}

function submitRemoveSubstitute() {
    const hNum = document.getElementById('subHalaqahNumber').value;
    const msg = hNum > 0 
        ? `Batalkan status inval pada Halaqah Ke-${hNum} dan kembalikan ke guru asal?`
        : 'Batalkan status guru pengganti dan kembalikan penugasan ke guru asal?';

    if (confirm(msg)) {
        document.getElementById('formRemoveSubstitute').submit();
    }
}

// Radar Modal Logic
function openRadarModal(forceTahfidz = false) {
    const checkEl = document.getElementById('radarTahfidzOnlyCheck');
    if (checkEl) {
        checkEl.checked = forceTahfidz || isTahfidzOnly;
    }
    document.getElementById('radarModal').style.display = 'flex';
    fetchRadarStatus();
}

function fetchRadarStatus() {
    const day = document.getElementById('radarDaySelect').value;
    const jam = document.getElementById('radarJamSelect').value;
    const isTahfidz = document.getElementById('radarTahfidzOnlyCheck') && document.getElementById('radarTahfidzOnlyCheck').checked ? 1 : 0;

    const freeListEl = document.getElementById('radarFreeList');
    const busyListEl = document.getElementById('radarBusyList');
    const freeCountEl = document.getElementById('radarFreeCount');
    const busyCountEl = document.getElementById('radarBusyCount');

    freeListEl.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 20px;"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data...</div>';
    busyListEl.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 20px;"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data...</div>';

    fetch(`${baseUrl}/api/jadwal/teacher-status?day=${encodeURIComponent(day)}&jam_ke=${jam}&is_tahfidz_only=${isTahfidz}`)
        .then(r => r.json())
        .then(data => {
            const free = data.free_teachers || [];
            const busy = data.busy_teachers || [];

            freeCountEl.textContent = `${free.length} Guru Free`;
            busyCountEl.textContent = `${busy.length} Guru Mengajar`;

            if (free.length === 0) {
                freeListEl.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 20px;">Tidak ada guru yang free pada jam ini.</div>';
            } else {
                freeListEl.innerHTML = free.map(t => `
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 8px 12px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 700; color: #15803d; font-size: 0.85rem;">${t.nama}</div>
                            <div style="font-size: 0.75rem; color: #166534;">${t.position || 'Guru'} &bull; NIPY: ${t.nipy || '-'}</div>
                        </div>
                        <span class="badge badge-success" style="background: #16a34a; font-size: 0.7rem;">Siap Menggantikan</span>
                    </div>
                `).join('');
            }

            if (busy.length === 0) {
                busyListEl.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada guru yang terjadwal mengajar di jam ini.</div>';
            } else {
                busyListEl.innerHTML = busy.map(t => {
                    const assignText = (t.assignments || []).map(a => `${a.class_name} (${a.subject_name})`).join(', ');
                    return `
                        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 8px 12px;">
                            <div style="font-weight: 700; color: #b91c1c; font-size: 0.85rem;">${t.nama}</div>
                            <div style="font-size: 0.775rem; color: #991b1b; margin-top: 2px;">
                                <i class="fa-solid fa-chalkboard-user"></i> Mengajar di: <strong>${assignText}</strong>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        })
        .catch(e => {
            freeListEl.innerHTML = '<div style="color: #ef4444; text-align: center; padding: 10px;">Gagal memuat status guru.</div>';
            busyListEl.innerHTML = '<div style="color: #ef4444; text-align: center; padding: 10px;">Gagal memuat status guru.</div>';
        });
}


// Open Modal Edit Slot Waktu
function openEditSlotModal(day, jam, timeStart, timeEnd, label, isBreak, breakType) {
    document.getElementById('slotDayInput').value = day;
    document.getElementById('slotJamKeInput').value = jam;
    document.getElementById('slotTimeStartInput').value = timeStart;
    document.getElementById('slotTimeEndInput').value = timeEnd;
    document.getElementById('slotLabelInput').value = label || '';
    document.getElementById('slotBreakTypeInput').value = breakType || 'none';
    document.getElementById('slotIsBreakHidden').value = isBreak ? 1 : 0;
    
    // Setup delete button
    const btnDel = document.getElementById('btnDeleteSlot');
    const delDay = document.getElementById('deleteSlotDay');
    const delJam = document.getElementById('deleteSlotJamKe');
    if (jam !== '' && jam !== null && jam >= 0) {
        btnDel.style.display = 'inline-flex';
        delDay.value = day;
        delJam.value = jam;
    } else {
        btnDel.style.display = 'none';
    }

    document.getElementById('editSlotModal').style.display = 'flex';
}

function handleBreakTypeChange(val) {
    const isBreakInput = document.getElementById('slotIsBreakHidden');
    const labelInput = document.getElementById('slotLabelInput');
    if (val !== 'none') {
        isBreakInput.value = '1';
        if (!labelInput.value || labelInput.value.startsWith('Jam Ke')) {
            if (val === 'istirahat') labelInput.value = 'Istirahat & Snack';
            if (val === 'sholat') labelInput.value = 'Sholat Dzuhur & Makan Siang';
            if (val === 'upacara') labelInput.value = 'Upacara Bendera / Ikrar Pagi';
        }
    } else {
        isBreakInput.value = '0';
        const jam = document.getElementById('slotJamKeInput').value;
        if (jam && (!labelInput.value || labelInput.value.includes('Istirahat') || labelInput.value.includes('Sholat') || labelInput.value.includes('Upacara'))) {
            labelInput.value = 'Jam Ke-' + jam;
        }
    }
}

function submitDeleteSlot() {
    if (confirm('Hapus slot jam ini? Jadwal pelajaran yang berada di jam ini juga akan terhapus.')) {
        document.getElementById('formDeleteSlot').submit();
    }
}

function triggerDirectDeleteSlot(day, jam) {
    if (confirm(`Hapus Jam Ke-${jam} pada hari ${day}? Jadwal pada jam ini akan ikut terhapus.`)) {
        document.getElementById('deleteSlotDay').value = day;
        document.getElementById('deleteSlotJamKe').value = jam;
        document.getElementById('formDeleteSlot').submit();
    }
}

// Open Modal All Slots Manager
function openAllSlotsModal() {
    document.getElementById('allSlotsModal').style.display = 'flex';
}

function switchSlotDayTab(day) {
    document.querySelectorAll('.slot-day-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.slot-day-tab-btn').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-light');
    });

    const activeContent = document.getElementById('slotContent_' + day);
    const activeBtn = document.getElementById('tabBtn_' + day);
    if (activeContent) activeContent.style.display = 'block';
    if (activeBtn) {
        activeBtn.classList.remove('btn-light');
        activeBtn.classList.add('btn-primary');
    }
}

function openNewSlotModalForDay(day) {
    const slots = <?= json_encode($timeSlotsByDay) ?>;
    const daySlots = slots[day] || [];
    let nextJam = 1;
    let nextStart = '14.00';
    let nextEnd = '14.35';

    if (daySlots.length > 0) {
        const lastSlot = daySlots[daySlots.length - 1];
        nextJam = parseInt(lastSlot.jam_ke) + 1;
        nextStart = lastSlot.time_end || '14.00';
    }

    openEditSlotModal(day, nextJam, nextStart, nextEnd, 'Jam Ke-' + nextJam, 0, 'none');
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal on click outside
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});
</script>
