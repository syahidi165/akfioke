<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$totalLogs = count($logs ?? []);
$tuntasCount = 0;
$totalLines = 0;

foreach ($logs as $lg) {
    if (!empty($lg['is_completed'])) {
        $tuntasCount++;
    }
    $totalLines += (int)($lg['lines_count'] ?? 0);
}

$avgLines = $totalLogs > 0 ? round($totalLines / $totalLogs) : 0;
$indoMonths = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$curMonth = (int)date('n');
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Tutor Siswa &amp; Tutor Sebaya</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tahun Ajaran <?= SecurityHelper::escape(Auth::academicYearName()) ?></span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-calculator" style="color: #0d9488;"></i> Mutaba'ah &amp; Nilai Hafalan Guru Bulanan
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pencatatan setoran hafalan guru &amp; staf bulanan berpedoman pada standar Mushaf Madinah 15 baris per halaman.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Mutaba'ah</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalLogs ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Setoran</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Riwayat guru terdata</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Tuntas Target</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $tuntasCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Setoran</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">
            <?= $totalLogs > 0 ? round(($tuntasCount / $totalLogs) * 100, 1) : 0 ?>% dari total setoran
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Rata-rata Baris</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $avgLines ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Baris</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Setara <?= round($avgLines / 15, 1) ?> halaman / guru</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Akumulasi</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #8b5cf6; margin: 6px 0 2px 0;">
            <?= number_format($totalLines, 0, ',', '.') ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Baris</span>
        </div>
        <div style="font-size: 0.8125rem; color: #8b5cf6; font-weight: 500;">Setara <?= round($totalLines / 300, 1) ?> Juz Al-Qur'an</div>
    </div>
</div>

<!-- Input Form Card -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-book-open-reader" style="color: #0d9488;"></i> Form Input Setoran Mutaba'ah Guru
        </h3>
        <span class="badge badge-secondary">Target: 1 Juz (SD/SMP/SMA) | 0.5 Juz (TK/KB)</span>
    </div>

    <div class="card-body" style="padding: 24px;">
        <form action="<?= Auth::baseUrl() ?>/tahfidz/guru-nilai/save" method="POST" id="guruNilaiForm">
            <?= CSRF::field() ?>

            <!-- Row 1: Guru, Bulan, Tipe, Kalkulasi -->
            <div style="display: grid; grid-template-columns: 2fr 1fr 1.5fr 1.5fr; gap: 16px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Pilih Guru / Pegawai <span style="color: #ef4444;">*</span></label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">-- Pilih Guru / Tenaga Pendidik --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>">
                                <?= SecurityHelper::escape($t['nama']) ?> (NIPY: <?= SecurityHelper::escape($t['nipy'] ?? '-') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Bulan Setoran <span style="color: #ef4444;">*</span></label>
                    <select name="month_num" class="form-control" required>
                        <?php foreach ($indoMonths as $mNum => $mName): ?>
                            <option value="<?= $mNum ?>" <?= $mNum === $curMonth ? 'selected' : '' ?>><?= $mName ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Kategori Santri / Pembinaan <span style="color: #ef4444;">*</span></label>
                    <select name="teacher_type" id="teacherType" class="form-control" required>
                        <option value="MHML">MHML (Mudah Hafal Mudah Lupa)</option>
                        <option value="MHSL">MHSL (Mudah Hafal Susah Lupa)</option>
                        <option value="SHSL">SHSL (Susah Hafal Susah Lupa)</option>
                        <option value="SHML">SHML (Susah Hafal Mudah Lupa)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Estimasi Capaian</label>
                    <div style="height: 38px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px; padding: 0 10px;">
                        <span id="lineCalcBadge" class="badge badge-secondary" style="font-size: 0.8125rem; font-weight: 600;">
                            0 Baris (Menghitung...)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Row 2: Range Surah & Ayat (Dibatasi Sesuai Jumlah Ayat Surah) -->
            <div style="background: #f8fafc; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                <div style="font-size: 0.8125rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-arrows-left-right-to-line" style="color: #0d9488;"></i> Rentang Ayat &amp; Surah (Mushaf Madinah)
                </div>
                <div style="display: grid; grid-template-columns: 2fr 1fr 2fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Surah Awal</label>
                        <select name="surah_start" id="surahStart" class="form-control" required onchange="handleSurahChange('start')">
                            <?php foreach ($surahs as $surah): ?>
                                <option value="<?= $surah['surah_number'] ?>" 
                                        data-verses="<?= (int)$surah['verses_count'] ?>" 
                                        <?= $surah['surah_number'] == 114 ? 'selected' : '' ?>>
                                    <?= $surah['surah_number'] ?>. <?= SecurityHelper::escape($surah['name_id']) ?> (Hal <?= $surah['start_page'] ?> - <?= $surah['verses_count'] ?> Ayat)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin: 0;">Ayat Awal <span style="color: #ef4444;">*</span></label>
                            <span id="ayahStartHint" style="font-size: 0.72rem; color: #0d9488; font-weight: 600;">(1 - 6)</span>
                        </div>
                        <input type="number" name="ayah_start" id="ayahStart" class="form-control" value="1" min="1" max="6" style="text-align: center;" required oninput="validateAyahInput(this)" onchange="validateAyahInput(this)">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Surah Akhir</label>
                        <select name="surah_end" id="surahEnd" class="form-control" required onchange="handleSurahChange('end')">
                            <?php foreach ($surahs as $surah): ?>
                                <option value="<?= $surah['surah_number'] ?>" 
                                        data-verses="<?= (int)$surah['verses_count'] ?>" 
                                        <?= $surah['surah_number'] == 114 ? 'selected' : '' ?>>
                                    <?= $surah['surah_number'] ?>. <?= SecurityHelper::escape($surah['name_id']) ?> (Hal <?= $surah['end_page'] ?> - <?= $surah['verses_count'] ?> Ayat)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin: 0;">Ayat Akhir <span style="color: #ef4444;">*</span></label>
                            <span id="ayahEndHint" style="font-size: 0.72rem; color: #0d9488; font-weight: 600;">(1 - 6)</span>
                        </div>
                        <input type="number" name="ayah_end" id="ayahEnd" class="form-control" value="6" min="1" max="6" style="text-align: center;" required oninput="validateAyahInput(this)" onchange="validateAyahInput(this)">
                    </div>
                </div>
            </div>

            <!-- Row 3: Notes & Submit -->
            <div style="display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: end;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Catatan Evaluasi / Makharijul Huruf / Tajwid</label>
                    <input type="text" name="tutor_notes" class="form-control" placeholder="Catatan khusus kelancaran tilawah, hukum tajwid mad lazim, fashahah...">
                </div>

                <div>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Mutaba'ah Guru
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- History Card Table -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-clock-rotate-left" style="color: #0d9488;"></i>
            <h3 class="card-title" style="margin: 0; font-size: 1rem;">Rekap Mutaba'ah Tahfidz Guru</h3>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <div style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                <input type="text" id="searchGuruNilai" class="form-control" placeholder="Cari guru / surah..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 220px;">
            </div>
            <span class="badge badge-secondary"><?= $totalLogs ?> Riwayat</span>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Nama Guru / Pegawai</th>
                        <th style="width: 130px;">Unit</th>
                        <th style="width: 100px; text-align: center;">Bulan</th>
                        <th>Jangkauan Hafalan</th>
                        <th style="width: 130px; text-align: center;">Halaman</th>
                        <th style="width: 110px; text-align: center;">Jumlah Baris</th>
                        <th style="width: 140px; text-align: center;">Status</th>
                        <th>Catatan Evaluasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-book-journal-whills" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                Belum ada riwayat mutaba'ah tahfidz guru untuk periode ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $idx => $lg): 
                            $bName = SecurityHelper::getNamaBulan((int)$lg['month_num']);
                        ?>
                            <tr class="log-row" data-search="<?= strtolower(SecurityHelper::escape($lg['teacher_name'] . ' ' . $lg['nipy'] . ' ' . $lg['unit_name'] . ' ' . $bName . ' ' . ($lg['surah_start_name'] ?? '') . ' ' . ($lg['surah_end_name'] ?? ''))) ?>">
                                <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                        <?= SecurityHelper::escape($lg['teacher_name']) ?>
                                    </div>
                                    <small style="color: var(--text-muted, #64748b); font-family: monospace;">NIPY: <?= SecurityHelper::escape($lg['nipy'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem; color: var(--text-muted, #64748b);"><?= SecurityHelper::escape($lg['unit_name']) ?></span>
                                </td>
                                <td style="text-align: center; font-weight: 500;">
                                    <?= SecurityHelper::escape($bName) ?>
                                </td>
                                <td>
                                    <strong style="color: #0d9488;">
                                        <?= SecurityHelper::escape($lg['surah_start_name'] ?? '') ?>:<?= $lg['ayah_start'] ?>
                                    </strong>
                                    <span style="color: var(--text-muted, #64748b); margin: 0 4px;">s.d</span>
                                    <strong style="color: #0d9488;">
                                        <?= SecurityHelper::escape($lg['surah_end_name'] ?? '') ?>:<?= $lg['ayah_end'] ?>
                                    </strong>
                                </td>
                                <td style="text-align: center; font-family: monospace; font-size: 0.85rem;">
                                    Hal. <?= $lg['page_start'] ?> - <?= $lg['page_end'] ?>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-weight: 700; color: var(--text-main, #0f172a);">
                                        <?= (int)$lg['lines_count'] ?>
                                    </span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted, #64748b);">Baris</span>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($lg['is_completed'])): ?>
                                        <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-circle-check"></i> Tuntas Target
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-clock"></i> Belum Tuntas
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted, #64748b);">
                                    <?= SecurityHelper::escape($lg['tutor_notes'] ?: '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function handleSurahChange(type) {
    const isStart = (type === 'start');
    const selectElem = document.getElementById(isStart ? 'surahStart' : 'surahEnd');
    const inputElem = document.getElementById(isStart ? 'ayahStart' : 'ayahEnd');
    const hintElem = document.getElementById(isStart ? 'ayahStartHint' : 'ayahEndHint');

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

    calculateLines();
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

function calculateLines() {
    const s1 = document.getElementById('surahStart');
    const s2 = document.getElementById('surahEnd');
    const badge = document.getElementById('lineCalcBadge');
    if (!s1 || !s2 || !badge) return;
    const v1 = s1.value;
    const v2 = s2.value;

    fetch('<?= Auth::baseUrl() ?>/api/mushaf-calc?surah_start=' + v1 + '&surah_end=' + v2)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                badge.textContent = data.lines + ' Baris (' + data.pages + ' Hal: ' + data.page_start + '-' + data.page_end + ')';
                if (data.lines >= 150) {
                    badge.className = 'badge badge-success';
                } else {
                    badge.className = 'badge badge-warning';
                }
            }
        })
        .catch(err => console.error(err));
}

document.addEventListener('DOMContentLoaded', function() {
    handleSurahChange('start');
    handleSurahChange('end');

    const s1 = document.getElementById('surahStart');
    const s2 = document.getElementById('surahEnd');

    if (s1 && s2) {
        s1.addEventListener('change', function() { handleSurahChange('start'); });
        s2.addEventListener('change', function() { handleSurahChange('end'); });
    }

    const searchInput = document.getElementById('searchGuruNilai');
    const rows = document.querySelectorAll('.log-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            rows.forEach(function(row) {
                const text = row.getAttribute('data-search') || '';
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }
});
</script>
