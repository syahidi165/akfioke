<?php
// views/tahfidz/setoran_siswa.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalSetoran = count($setoranLogs);
$totalLines = array_sum(array_column($setoranLogs, 'lines_count'));
$totalHalaman = round($totalLines / 15, 1);
$tuntasCount = count(array_filter($setoranLogs, fn($l) => !empty($l['is_completed'])));
$indoMonths = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$curMonth = (int)date('n');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Setoran Hafalan Santri</h1>
        <div class="page-subtitle">Kalkulator Mushaf Madinah 15 Baris & Mutaba'ah Capaian Tahfidz Santri.</div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="<?= $baseUrl ?>/tahfidz/ajukan-ujian" class="btn btn-outline">
            <i class="fa-solid fa-graduation-cap"></i> Ajukan Ujian ke Tutor
        </a>
        <a href="<?= $baseUrl ?>/tahfidz/rekap-siswa" class="btn btn-outline">
            <i class="fa-solid fa-chart-pie"></i> Rekapitulasi Capaian
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
            <i class="fa-solid fa-quran"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalSetoran ?></div>
            <div class="stat-label">Total Setoran Dicatat</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
            <i class="fa-solid fa-lines-leaning"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalLines ?></div>
            <div class="stat-label">Akumulasi Baris</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
            <i class="fa-solid fa-book-open"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">&plusmn; <?= $totalHalaman ?></div>
            <div class="stat-label">Halaman Mushaf</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $tuntasCount ?></div>
            <div class="stat-label">Setoran Tuntas Target</div>
        </div>
    </div>
</div>

<!-- Form Card Input Setoran -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
            <i class="fa-solid fa-book-quran" style="color: #0d9488;"></i> Input Mutaba'ah Setoran Santri
        </h3>
        <?php if ($currentHalaqah): ?>
            <span class="badge badge-info" style="font-weight: 600;">
                <i class="fa-solid fa-users" style="margin-right: 4px;"></i> Halaqah: <?= SecurityHelper::escape($currentHalaqah['name']) ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body" style="padding: 20px;">
        <?php if (!$currentHalaqah): ?>
            <div style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; padding: 14px 18px; border-radius: 4px; color: #b45309; font-size: 0.9rem;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i>
                Anda belum memiliki kelompok halaqah yang diampu pada Tahun Ajaran aktif. Silakan hubungi <strong>Koordinator Tahfidz</strong> untuk plotting kelompok halaqah.
            </div>
        <?php else: ?>
            <form action="<?= $baseUrl ?>/tahfidz/setoran-siswa/save" method="POST" id="setoranForm">
                <?= CSRF::field() ?>
                <input type="hidden" name="halaqah_id" value="<?= $currentHalaqah['id'] ?>">

                <!-- Grid Baris 1: Santri, Bulan, Kategori, Kalkulasi -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 18px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Pilih Santri <span style="color: #ef4444;">*</span></label>
                        <select name="student_id" class="form-control" required>
                            <option value="">-- Pilih Santri di Halaqah --</option>
                            <?php foreach ($students as $st): ?>
                                <option value="<?= $st['id'] ?>">
                                    <?= SecurityHelper::escape($st['nama']) ?> (NIS: <?= SecurityHelper::escape($st['nis']) ?> - <?= SecurityHelper::escape($st['class_name'] ?? 'Non-Kelas') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Bulan Setoran</label>
                        <select name="month_num" class="form-control" required>
                            <?php foreach ($indoMonths as $mNum => $mName): ?>
                                <option value="<?= $mNum ?>" <?= $mNum === $curMonth ? 'selected' : '' ?>><?= $mName ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Kategori Santri <span style="color: #ef4444;">*</span></label>
                        <select name="student_type" id="studentType" class="form-control" required>
                            <option value="MHML">MHML (Mudah Hafal Mudah Lupa)</option>
                            <option value="MHSL">MHSL (Mudah Hafal Susah Lupa)</option>
                            <option value="SHSL">SHSL (Susah Hafal Susah Lupa)</option>
                            <option value="SHML">SHML (Susah Hafal Mudah Lupa)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Kalkulasi Baris Mushaf</label>
                        <div style="background: rgba(13, 148, 136, 0.06); border: 1px solid rgba(13, 148, 136, 0.2); border-radius: 6px; padding: 8px 12px; text-align: center;">
                            <span id="lineCalcBadge" style="font-weight: 700; color: #0f766e; font-size: 0.95rem;">0 Baris (0 Halaman)</span>
                            <div id="targetStatus" style="font-size: 0.75rem; color: var(--text-muted, #64748b); margin-top: 2px;">Standar Tuntas: &ge; 150 Baris (&frac12; Juz)</div>
                        </div>
                    </div>
                </div>

                <!-- Grid Baris 2: Surah Mulai, Ayat Mulai, Surah Selesai, Ayat Selesai (Dibatasi Sesuai Jumlah Ayat Surah) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 18px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid var(--border-color, #e2e8f0);">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Surah Mulai</label>
                        <select name="surah_start" id="surahStart" class="form-control" required onchange="handleSurahChange('start', '')">
                            <?php foreach ($surahs as $surah): ?>
                                <option value="<?= $surah['surah_number'] ?>" 
                                        data-verses="<?= (int)$surah['verses_count'] ?>" 
                                        <?= $surah['surah_number'] == 114 ? 'selected' : '' ?>>
                                    <?= $surah['surah_number'] ?>. <?= SecurityHelper::escape($surah['name_id']) ?> (Hal <?= $surah['start_page'] ?> - <?= $surah['verses_count'] ?> Ayat)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin: 0;">Ayat Mulai <span style="color: #ef4444;">*</span></label>
                            <span id="ayahStartHint" style="font-size: 0.72rem; color: #0d9488; font-weight: 600;">(1 - 6)</span>
                        </div>
                        <input type="number" name="ayah_start" id="ayahStart" class="form-control" value="1" min="1" max="6" required oninput="validateAyahInput(this)" onchange="validateAyahInput(this)">
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Surah Selesai</label>
                        <select name="surah_end" id="surahEnd" class="form-control" required onchange="handleSurahChange('end', '')">
                            <?php foreach ($surahs as $surah): ?>
                                <option value="<?= $surah['surah_number'] ?>" 
                                        data-verses="<?= (int)$surah['verses_count'] ?>" 
                                        <?= $surah['surah_number'] == 114 ? 'selected' : '' ?>>
                                    <?= $surah['surah_number'] ?>. <?= SecurityHelper::escape($surah['name_id']) ?> (Hal <?= $surah['end_page'] ?> - <?= $surah['verses_count'] ?> Ayat)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin: 0;">Ayat Selesai <span style="color: #ef4444;">*</span></label>
                            <span id="ayahEndHint" style="font-size: 0.72rem; color: #0d9488; font-weight: 600;">(1 - 6)</span>
                        </div>
                        <input type="number" name="ayah_end" id="ayahEnd" class="form-control" value="6" min="1" max="6" required oninput="validateAyahInput(this)" onchange="validateAyahInput(this)">
                    </div>
                </div>

                <!-- Grid Baris 3: Ujian Tahfidz Checkbox & Catatan Mutaba'ah -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; align-items: start; margin-bottom: 20px;">
                    <div>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; font-size: 0.875rem; color: #0d9488;">
                            <input type="checkbox" name="is_exam_prep" id="isExamPrep" value="1" onchange="toggleExamJuz(this)">
                            <span>Daftarkan Langsung ke Antrean Ujian Tahfidz</span>
                        </label>
                        <div id="examJuzGroup" style="display: none; margin-top: 10px;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Pilih Juz yang Diujikan</label>
                            <select name="exam_juz" class="form-control" style="border-color: #0d9488;">
                                <?php for ($j = 30; $j >= 1; $j--): ?>
                                    <option value="<?= $j ?>">Juz <?= $j ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Mutaba'ah Guru</label>
                        <input type="text" name="tutor_notes" class="form-control" placeholder="Contoh: Kelancaran baik, perbaiki makhraj huruf 'Ain">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Setoran Hafalan
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Card Riwayat Setoran -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
            <i class="fa-solid fa-clock-rotate-left" style="color: #0d9488;"></i> Riwayat Setoran Santri di Halaqah Ini
        </h3>
        <span class="badge badge-secondary"><?= $totalSetoran ?> Catatan</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th>Nama Santri</th>
                        <th>Bulan &amp; Kategori</th>
                        <th>Jangkauan Hafalan</th>
                        <th>Halaman</th>
                        <th style="text-align: center;">Jumlah Baris</th>
                        <th style="text-align: center;">Status</th>
                        <th>Catatan Mutaba'ah</th>
                        <th style="width: 100px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($setoranLogs)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-book-open" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                Belum ada catatan setoran santri di halaqah ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($setoranLogs as $idx => $log): ?>
                            <tr>
                                <td style="text-align: center;"><?= $idx + 1 ?></td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main, #0f172a);"><?= SecurityHelper::escape($log['student_name']) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted, #64748b);">NIS: <?= SecurityHelper::escape($log['nis']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?= SecurityHelper::getNamaBulan((int)$log['month_num']) ?></div>
                                    <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.6875rem; border: 1px solid #cbd5e1; margin-top: 3px;" title="<?= match($log['student_type'] ?? '') {
                                        'MHML' => 'Mudah Hafal Mudah Lupa',
                                        'MHSL' => 'Mudah Hafal Susah Lupa',
                                        'SHSL' => 'Susah Hafal Susah Lupa',
                                        'SHML' => 'Susah Hafal Mudah Lupa',
                                        default => $log['student_type'] ?? '-'
                                    } ?>">
                                        <?= SecurityHelper::escape($log['student_type'] ?? 'MHML') ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight: 500;">
                                        <?= SecurityHelper::escape($log['surah_start_name']) ?>:<?= $log['ayah_start'] ?>
                                        s/d <?= SecurityHelper::escape($log['surah_end_name']) ?>:<?= $log['ayah_end'] ?>
                                    </span>
                                </td>
                                <td>Hal. <?= $log['page_start'] ?> - <?= $log['page_end'] ?></td>
                                <td style="text-align: center;">
                                    <strong style="color: #0f766e;"><?= $log['lines_count'] ?></strong> baris
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($log['is_completed']): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-check" style="margin-right: 4px;"></i> Tuntas</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="background: rgba(245, 158, 11, 0.15); color: #d97706;">
                                            <i class="fa-solid fa-hourglass-half" style="margin-right: 4px;"></i> Berproses
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted, #64748b);">
                                    <?= SecurityHelper::escape($log['tutor_notes'] ?: '-') ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                        <!-- Tombol Edit Riwayat Setoran -->
                                        <button type="button" class="btn btn-outline btn-sm" onclick='openEditSetoranModal(<?= htmlspecialchars(json_encode($log), ENT_QUOTES, "UTF-8") ?>)' style="padding: 4px 8px; font-size: 0.75rem; color: #0284c7; border-color: #0284c7;" title="Edit Riwayat Setoran">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <!-- Form Hapus Riwayat Setoran -->
                                        <form method="POST" action="<?= $baseUrl ?>/tahfidz/setoran-siswa/delete" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan riwayat setoran santri <?= SecurityHelper::escape($log['student_name']) ?> ini? Data yang dihapus tidak dapat dikembalikan.')" style="margin: 0; display: inline;">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$log['id'] ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444; border-color: #ef4444;" title="Hapus Riwayat Setoran">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Riwayat Setoran Santri -->
<div class="modal-overlay" id="editSetoranModal">
    <div class="modal-container" style="max-width: 680px; width: 95%;">
        <form action="<?= $baseUrl ?>/tahfidz/setoran-siswa/update" method="POST" id="editSetoranForm">
            <?= CSRF::field() ?>
            <input type="hidden" name="id" id="edit_id">

            <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                <h3 class="card-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px; color: #0f172a;">
                    <i class="fa-solid fa-pen-to-square" style="color: #0d9488;"></i> Edit Riwayat Setoran Santri
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeEditSetoranModal()">&times;</button>
            </div>

            <div class="modal-body" style="padding: 20px;">
                <!-- Row 1: Santri, Bulan, Kategori -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 16px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Santri <span style="color: #ef4444;">*</span></label>
                        <select name="student_id" id="edit_student_id" class="form-control" required>
                            <?php foreach ($students as $st): ?>
                                <option value="<?= $st['id'] ?>">
                                    <?= SecurityHelper::escape($st['nama']) ?> (NIS: <?= SecurityHelper::escape($st['nis']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Bulan Setoran <span style="color: #ef4444;">*</span></label>
                        <select name="month_num" id="edit_month_num" class="form-control" required>
                            <?php foreach ($indoMonths as $mNum => $mName): ?>
                                <option value="<?= $mNum ?>"><?= $mName ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Kategori Santri <span style="color: #ef4444;">*</span></label>
                        <select name="student_type" id="edit_student_type" class="form-control" required>
                            <option value="MHML">MHML (Mudah Hafal Mudah Lupa)</option>
                            <option value="MHSL">MHSL (Mudah Hafal Susah Lupa)</option>
                            <option value="SHSL">SHSL (Susah Hafal Susah Lupa)</option>
                            <option value="SHML">SHML (Susah Hafal Mudah Lupa)</option>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Surah & Ayat -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-size: 0.8125rem; font-weight: 600; color: #475569; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="fa-solid fa-book-quran" style="color: #0d9488;"></i> Rentang Surah &amp; Batasan Ayat</span>
                        <span id="edit_lineCalcBadge" style="font-weight: 700; color: #0d9488;">0 Baris</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Surah Mulai</label>
                            <select name="surah_start" id="edit_surah_start" class="form-control" required onchange="handleSurahChange('start', 'edit_')">
                                <?php foreach ($surahs as $surah): ?>
                                    <option value="<?= $surah['surah_number'] ?>" data-verses="<?= (int)$surah['verses_count'] ?>">
                                        <?= $surah['surah_number'] ?>. <?= SecurityHelper::escape($surah['name_id']) ?> (<?= $surah['verses_count'] ?> Ayat)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin: 0;">Ayat Mulai</label>
                                <span id="edit_ayahStartHint" style="font-size: 0.7rem; color: #0d9488; font-weight: 600;">(1 - 6)</span>
                            </div>
                            <input type="number" name="ayah_start" id="edit_ayah_start" class="form-control" value="1" min="1" max="6" required oninput="validateAyahInput(this)" onchange="validateAyahInput(this)">
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Surah Selesai</label>
                            <select name="surah_end" id="edit_surah_end" class="form-control" required onchange="handleSurahChange('end', 'edit_')">
                                <?php foreach ($surahs as $surah): ?>
                                    <option value="<?= $surah['surah_number'] ?>" data-verses="<?= (int)$surah['verses_count'] ?>">
                                        <?= $surah['surah_number'] ?>. <?= SecurityHelper::escape($surah['name_id']) ?> (<?= $surah['verses_count'] ?> Ayat)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin: 0;">Ayat Selesai</label>
                                <span id="edit_ayahEndHint" style="font-size: 0.7rem; color: #0d9488; font-weight: 600;">(1 - 6)</span>
                            </div>
                            <input type="number" name="ayah_end" id="edit_ayah_end" class="form-control" value="6" min="1" max="6" required oninput="validateAyahInput(this)" onchange="validateAyahInput(this)">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Mutaba'ah Guru</label>
                    <input type="text" name="tutor_notes" id="edit_tutor_notes" class="form-control" placeholder="Contoh: Kelancaran baik, perbaiki makhraj huruf...">
                </div>
            </div>

            <div class="modal-footer" style="padding: 14px 20px; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline" onclick="closeEditSetoranModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleExamJuz(cb) {
    const group = document.getElementById('examJuzGroup');
    if (group) group.style.display = cb.checked ? 'block' : 'none';
}

function handleSurahChange(type, prefix) {
    prefix = prefix || '';
    const isStart = (type === 'start');
    const selectId = prefix + (isStart ? 'surahStart' : 'surahEnd');
    const selectElem = document.getElementById(selectId) || document.getElementById(prefix + (isStart ? 'surah_start' : 'surah_end'));
    const inputId = prefix + (isStart ? 'ayahStart' : 'ayahEnd');
    const inputElem = document.getElementById(inputId) || document.getElementById(prefix + (isStart ? 'ayah_start' : 'ayah_end'));
    const hintElem = document.getElementById(prefix + (isStart ? 'ayahStartHint' : 'ayahEndHint'));

    if (!selectElem || !inputElem) return;

    const opt = selectElem.options[selectElem.selectedIndex];
    const verses = parseInt(opt ? opt.getAttribute('data-verses') : 1, 10) || 1;

    inputElem.max = verses;
    if (hintElem) {
        hintElem.innerText = '(1 - ' + verses + ')';
    }

    let val = parseInt(inputElem.value, 10);
    if (isNaN(val) || val < 1) {
        inputElem.value = 1;
    } else if (val > verses) {
        inputElem.value = verses;
    }

    calculateLines(prefix);
}

function validateAyahInput(input) {
    const max = parseInt(input.max, 10) || 1;
    const min = parseInt(input.min, 10) || 1;
    let val = parseInt(input.value, 10);
    if (isNaN(val)) return;
    if (val > max) {
        input.value = max;
    } else if (val < min) {
        input.value = min;
    }
}

function calculateLines(prefix) {
    prefix = prefix || '';
    const s1Elem = document.getElementById(prefix + 'surahStart') || document.getElementById(prefix + 'surah_start');
    const s2Elem = document.getElementById(prefix + 'surahEnd') || document.getElementById(prefix + 'surah_end');
    const badge = document.getElementById(prefix + 'lineCalcBadge');
    const targetStatus = document.getElementById(prefix + 'targetStatus');

    if (!s1Elem || !s2Elem || !badge) return;
    const s1 = s1Elem.value;
    const s2 = s2Elem.value;

    fetch('<?= $baseUrl ?>/api/mushaf-calc?surah_start=' + s1 + '&surah_end=' + s2)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                badge.innerText = data.lines + ' Baris (' + data.pages + ' Halaman)';
                if (data.lines >= 150) {
                    badge.style.color = '#10b981';
                    if (targetStatus) targetStatus.innerHTML = '<strong style="color: #10b981;">&#10003; Memenuhi Standar Tuntas (&ge; 150 Baris)</strong>';
                } else {
                    badge.style.color = '#d97706';
                    if (targetStatus) targetStatus.innerHTML = 'Standar Tuntas: &ge; 150 Baris (' + (150 - data.lines) + ' baris lagi)';
                }
            }
        })
        .catch(err => console.error(err));
}

function openEditSetoranModal(log) {
    document.getElementById('edit_id').value = log.id;
    document.getElementById('edit_student_id').value = log.student_id;
    document.getElementById('edit_month_num').value = log.month_num;
    document.getElementById('edit_student_type').value = log.student_type || 'MHML';
    document.getElementById('edit_tutor_notes').value = log.tutor_notes || '';

    const sStart = document.getElementById('edit_surah_start');
    const sEnd = document.getElementById('edit_surah_end');
    if (sStart) sStart.value = log.surah_start;
    if (sEnd) sEnd.value = log.surah_end;

    handleSurahChange('start', 'edit_');
    handleSurahChange('end', 'edit_');

    const aStart = document.getElementById('edit_ayah_start');
    const aEnd = document.getElementById('edit_ayah_end');
    if (aStart) aStart.value = log.ayah_start;
    if (aEnd) aEnd.value = log.ayah_end;

    validateAyahInput(aStart);
    validateAyahInput(aEnd);

    calculateLines('edit_');
    document.getElementById('editSetoranModal').classList.add('active');
}

function closeEditSetoranModal() {
    document.getElementById('editSetoranModal').classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function() {
    handleSurahChange('start', '');
    handleSurahChange('end', '');
    calculateLines('');
});
</script>
