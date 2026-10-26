<?php
// views/nilai/index.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$totalSiswa = count($students);
$kkmAktif = (float)($students[0]['kkm'] ?? 75.0);
$sudahDiisi = count(array_filter($students, fn($s) => $s['score'] !== null && $s['score'] !== ''));
$belumDiisi = $totalSiswa - $sudahDiisi;
$tuntasCount = count(array_filter($students, fn($s) => $s['score'] !== null && (float)$s['score'] >= $kkmAktif));
$remedialCount = $sudahDiisi - $tuntasCount;
$totalNilaiSum = array_sum(array_map(fn($s) => (float)$s['score'], array_filter($students, fn($s) => $s['score'] !== null && $s['score'] !== '')));
$rataKelas = $sudahDiisi > 0 ? round($totalNilaiSum / $sudahDiisi, 1) : 0;
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Input Nilai & KKM Bulanan Santri</h1>
        <div class="page-subtitle">Pencatatan capaian akademik bulanan dan penentuan ketuntasan belajar santri per mata pelajaran.</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <?php if (Auth::hasAnyRole(['walas', 'waka_kurikulum', 'kepala_sekolah', 'deputi_pendidikan', 'operator_yayasan']) && $selectedClassId > 0): ?>
            <a href="<?= $baseUrl ?>/nilai/ledger?class_id=<?= $selectedClassId ?>&month_num=<?= $selectedMonth ?>" class="btn btn-outline">
                <i class="fa-solid fa-table-list"></i> Lihat Ledger Kelas
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($classes) || empty($subjects)): ?>
    <div class="alert alert-warning" style="margin-bottom: 24px; display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 8px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e;">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 1.25rem; color: #d97706;"></i>
        <div>
            <strong>Penugasan Mengajar Belum Ditemukan:</strong> Anda belum memiliki penugasan mengajar mata pelajaran pada kelas aktif untuk tahun ajaran ini. Silakan hubungi Waka Kurikulum atau Operator Sekolah untuk penugasan mengajar.
        </div>
    </div>
<?php endif; ?>

<!-- Filter Bar Card -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 14px 20px;">
        <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 0.95rem; margin: 0;">
            <i class="fa-solid fa-filter" style="color: #0d9488;"></i> Filter Kelas, Mata Pelajaran & Bulan Penilaian
        </h3>
    </div>
    <div class="card-body" style="padding: 18px 20px;">
        <form method="GET" action="<?= $baseUrl ?>/nilai/bulanan" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) auto; gap: 16px; align-items: end;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Pilih Kelas</label>
                <select name="class_id" class="form-control" onchange="this.form.submit()" <?= empty($classes) ? 'disabled' : '' ?>>
                    <?php if (empty($classes)): ?>
                        <option value="">(Belum ada kelas yang diajarkan)</option>
                    <?php else: ?>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $selectedClassId ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Mata Pelajaran</label>
                <select name="subject_id" class="form-control" onchange="this.form.submit()" <?= empty($subjects) ? 'disabled' : '' ?>>
                    <?php if (empty($subjects)): ?>
                        <option value="">(Belum ada mata pelajaran)</option>
                    <?php else: ?>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['id'] ?>" <?= $sub['id'] == $selectedSubjectId ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($sub['name']) ?> (<?= SecurityHelper::escape($sub['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Bulan Penilaian</label>
                <select name="month_num" class="form-control" onchange="this.form.submit()">
                    <?php foreach ($months as $num => $nm): ?>
                        <option value="<?= $num ?>" <?= $num == $selectedMonth ? 'selected' : '' ?>><?= $nm ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 18px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 12-Month Timeline / Rekap Status Pengisian KKM & Nilai Bulanan -->
<?php if (!empty($classes) && !empty($subjects) && $selectedClassId > 0 && $selectedSubjectId > 0): ?>
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-calendar-check" style="color: #0d9488;"></i>
            <h4 style="margin: 0; font-size: 0.875rem; font-weight: 600; color: var(--text-main, #0f172a);">
                Status Pengisian Nilai &amp; KKM per Bulan (Tahun Ajaran Berjalan)
            </h4>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">
            Klik pada kotak bulan untuk berpindah langsung ke bulan tersebut
        </div>
    </div>
    <div class="card-body" style="padding: 14px 16px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(85px, 1fr)); gap: 8px;">
            <?php 
            $shortMonths = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];
            foreach ($shortMonths as $num => $sNm): 
                $st = $monthStatus[$num] ?? ['is_filled' => false, 'count' => 0, 'avg' => 0, 'kkm' => 75];
                $isSelected = ($num == $selectedMonth);
                $isFilled = $st['is_filled'];
            ?>
                <a href="<?= $baseUrl ?>/nilai/bulanan?class_id=<?= $selectedClassId ?>&subject_id=<?= $selectedSubjectId ?>&month_num=<?= $num ?>" 
                   style="text-decoration: none; border-radius: 8px; padding: 10px 6px; text-align: center; transition: all 0.2s ease; display: flex; flex-direction: column; justify-content: space-between; min-height: 78px;
                          <?= $isSelected ? 'outline: 2px solid #0d9488; outline-offset: 1px; box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.2);' : 'border: 1px solid var(--border-color, #e2e8f0);' ?>
                          <?= $isFilled ? 'background: #f0fdf4; border-color: #bbf7d0;' : 'background: #f8fafc;' ?>">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 2px;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: <?= $isSelected ? '#0d9488' : '#334155' ?>;">
                            <?= $sNm ?>
                        </span>
                        <?php if ($isSelected): ?>
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #0d9488;"></span>
                        <?php endif; ?>
                    </div>

                    <div style="margin: 4px 0;">
                        <?php if ($isFilled): ?>
                            <span style="display: inline-flex; align-items: center; gap: 3px; font-size: 0.7rem; font-weight: 700; color: #16a34a;">
                                <i class="fa-solid fa-circle-check"></i> Terisi
                            </span>
                            <div style="font-size: 0.68rem; color: #64748b; font-family: monospace; margin-top: 2px;">
                                R: <?= $st['avg'] ?>
                            </div>
                        <?php else: ?>
                            <span style="display: inline-flex; align-items: center; gap: 3px; font-size: 0.7rem; color: #94a3b8;">
                                <i class="fa-regular fa-circle"></i> Kosong
                            </span>
                            <div style="font-size: 0.68rem; color: #cbd5e1; margin-top: 2px;">
                                -
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="font-size: 0.65rem; color: #94a3b8; border-top: 1px dashed rgba(0,0,0,0.06); padding-top: 3px;">
                        KKM: <?= $st['kkm'] ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stats Ringkasan Penilaian -->
<?php if (!empty($students)): ?>
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalSiswa ?></div>
            <div class="stat-label">Total Santri</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $sudahDiisi ?> / <?= $totalSiswa ?></div>
            <div class="stat-label">Nilai Terisi</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
            <i class="fa-solid fa-chart-simple"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $rataKelas > 0 ? $rataKelas : '-' ?></div>
            <div class="stat-label">Rata-rata Kelas</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $tuntasCount ?></div>
            <div class="stat-label">Tuntas KKM</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $remedialCount ?></div>
            <div class="stat-label">Remedial</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Card Form Input Nilai Siswa -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
            <i class="fa-solid fa-file-pen" style="color: #0d9488;"></i> Form Penilaian Akademik Santri
        </h3>
        <span class="badge badge-secondary"><?= $totalSiswa ?> Santri Terdaftar</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($students)): ?>
            <div style="text-align: center; padding: 48px 20px; color: var(--text-muted, #64748b);">
                <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; margin-bottom: 12px; display: block; opacity: 0.5;"></i>
                Belum ada data siswa aktif pada kelas yang dipilih. Silakan pilih kelas lain atau periksa data penempatan santri.
            </div>
        <?php else: ?>
            <form action="<?= $baseUrl ?>/nilai/bulanan/save" method="POST">
                <?= CSRF::field() ?>
                <input type="hidden" name="class_id" value="<?= $selectedClassId ?>">
                <input type="hidden" name="subject_id" value="<?= $selectedSubjectId ?>">
                <input type="hidden" name="month_num" value="<?= $selectedMonth ?>">

                <!-- KKM Bar -->
                <div style="background: rgba(13, 148, 136, 0.05); border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-weight: 600; font-size: 0.9rem; color: var(--text-main, #0f172a);">
                            <i class="fa-solid fa-sliders" style="color: #0d9488; margin-right: 4px;"></i> Standar KKM Mata Pelajaran:
                        </span>
                        <input type="number" step="0.1" min="0" max="100" name="kkm" id="kkmInput" 
                               class="form-control" style="width: 100px; font-weight: 700; text-align: center; border-color: #0d9488;" 
                               value="<?= $kkmAktif ?>" required onchange="updateAllStatuses()">
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted, #64748b);">
                        <i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> Santri dengan nilai &ge; KKM dinyatakan <strong style="color: #10b981;">Tuntas</strong>, sedangkan di bawah KKM <strong style="color: #ef4444;">Remedial</strong>.
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="width: 130px;">NIS</th>
                                <th>Nama Lengkap Santri</th>
                                <th style="width: 60px; text-align: center;">L/P</th>
                                <th style="width: 200px; text-align: center;">Nilai Angka (0 - 100)</th>
                                <th style="width: 150px; text-align: center;">Status Ketercapaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $idx => $st): 
                                $sc = $st['score'] !== null ? (float)$st['score'] : '';
                                $isFilled = ($sc !== '');
                                $isPass = ($isFilled && $sc >= $kkmAktif);
                            ?>
                                <tr>
                                    <td style="text-align: center;"><?= $idx + 1 ?></td>
                                    <td><code style="font-size: 0.85rem;"><?= SecurityHelper::escape($st['nis']) ?></code></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a);"><?= SecurityHelper::escape($st['nama']) ?></div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-secondary" style="font-size: 0.75rem;"><?= $st['gender'] ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="max-width: 130px; margin: 0 auto;">
                                            <input type="number" step="0.1" min="0" max="100" 
                                                   name="score[<?= $st['student_id'] ?>]" 
                                                   id="score_<?= $st['student_id'] ?>"
                                                   class="form-control score-input" 
                                                   style="text-align: center; font-weight: 600; font-family: monospace;" 
                                                   value="<?= $sc ?>" 
                                                   placeholder="0 - 100"
                                                   oninput="checkSingleStatus(<?= $st['student_id'] ?>)">
                                        </div>
                                    </td>
                                    <td style="text-align: center;" id="status_<?= $st['student_id'] ?>">
                                        <?php if ($isFilled): ?>
                                            <?php if ($isPass): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-check" style="margin-right: 4px;"></i> Tuntas</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation" style="margin-right: 4px;"></i> Remedial</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="font-size: 0.8rem; color: var(--text-muted, #94a3b8);">Belum diisi</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="padding: 16px 20px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; align-items: center; background: #fafafa;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Nilai Bulanan
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function checkSingleStatus(studentId) {
    const kkm = parseFloat(document.getElementById('kkmInput').value) || 75.0;
    const input = document.getElementById('score_' + studentId);
    const container = document.getElementById('status_' + studentId);
    const val = input.value.trim();

    if (val === '') {
        container.innerHTML = '<span style="font-size: 0.8rem; color: var(--text-muted, #94a3b8);">Belum diisi</span>';
        return;
    }

    const num = parseFloat(val);
    if (num >= kkm) {
        container.innerHTML = '<span class="badge badge-success"><i class="fa-solid fa-check" style="margin-right: 4px;"></i> Tuntas</span>';
    } else {
        container.innerHTML = '<span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation" style="margin-right: 4px;"></i> Remedial</span>';
    }
}

function updateAllStatuses() {
    const inputs = document.querySelectorAll('.score-input');
    inputs.forEach(input => {
        const id = input.id.replace('score_', '');
        checkSingleStatus(id);
    });
}
</script>
