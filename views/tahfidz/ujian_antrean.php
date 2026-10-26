<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$totalExams = count($exams ?? []);
$waitingCount = 0;
$passedCount = 0;
$failedCount = 0;

foreach ($exams as $ex) {
    $st = $ex['status'] ?? '';
    if ($st === 'Lulus') $passedCount++;
    elseif ($st === 'Tidak Lulus') $failedCount++;
    else $waitingCount++;
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Tutor Siswa</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Penguji Tasmi' &amp; Tahfidz Santri</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-clipboard-check" style="color: #0d9488;"></i> Antrean &amp; Penilaian Ujian Tahfidz Siswa
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Verifikasi kelayakan ujian tasmi' hafalan santri, input penilaian tajwid &amp; makhraj, serta penerbitan nomor syahadah resmi.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Pendaftar</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalExams ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tahun ajaran aktif</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Menunggu Ujian</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b; margin: 6px 0 2px 0;">
            <?= $waitingCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: #f59e0b; font-weight: 500;">Perlu dijadwalkan / dinilai</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Lulus (Syahadah)</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $passedCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">
            <?= $totalExams > 0 ? round(($passedCount / $totalExams) * 100, 1) : 0 ?>% tingkat kelulusan
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #ef4444;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Remedial / Belum Lulus</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #ef4444; margin: 6px 0 2px 0;">
            <?= $failedCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Perlu pengulangan tasmi'</div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-list-check" style="color: #0d9488;"></i>
            <h3 class="card-title" style="margin: 0; font-size: 1rem;">Daftar Antrean &amp; Hasil Ujian Tahfidz</h3>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <!-- Filter Status -->
            <div style="display: flex; gap: 4px;">
                <button type="button" class="btn btn-sm btn-filter btn-primary" data-status="all">Semua</button>
                <button type="button" class="btn btn-sm btn-filter btn-secondary" data-status="menunggu">Menunggu</button>
                <button type="button" class="btn btn-sm btn-filter btn-secondary" data-status="lulus">Lulus</button>
                <button type="button" class="btn btn-sm btn-filter btn-secondary" data-status="tidak lulus">Remedial</button>
            </div>

            <!-- Search -->
            <div style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                <input type="text" id="searchExam" class="form-control" placeholder="Cari santri / NIS / pengusul..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 220px;">
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Santri</th>
                        <th style="width: 140px;">Unit</th>
                        <th style="width: 110px; text-align: center;">Juz Ujian</th>
                        <th>Diusulkan Oleh</th>
                        <th style="width: 130px; text-align: center;">Status</th>
                        <th style="width: 80px; text-align: center;">Nilai</th>
                        <th style="width: 150px; text-align: center;">No. Syahadah</th>
                        <th style="width: 140px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($exams)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-clipboard-user" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                Belum ada antrean santri yang diajukan untuk ujian tahfidz.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($exams as $idx => $ex): 
                            $status = $ex['status'] ?? 'Menunggu';
                            $statusLower = strtolower($status);
                        ?>
                            <tr class="exam-row" data-status="<?= $statusLower ?>" data-search="<?= strtolower(SecurityHelper::escape($ex['student_name'] . ' ' . $ex['nis'] . ' ' . $ex['proposer_name'] . ' ' . $ex['unit_name'] . ' juz ' . $ex['juz_number'])) ?>">
                                <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                        <?= SecurityHelper::escape($ex['student_name']) ?>
                                    </div>
                                    <small style="color: var(--text-muted, #64748b); font-family: monospace;">NIS: <?= SecurityHelper::escape($ex['nis']) ?></small>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem; color: var(--text-muted, #64748b);"><?= SecurityHelper::escape($ex['unit_name']) ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-primary" style="font-weight: 600;">
                                        Juz <?= (int)$ex['juz_number'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
                                        <i class="fa-solid fa-chalkboard-user" style="color: #0d9488;"></i>
                                        <span><?= SecurityHelper::escape($ex['proposer_name']) ?></span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($status === 'Lulus'): ?>
                                        <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-circle-check"></i> Lulus
                                        </span>
                                    <?php elseif ($status === 'Tidak Lulus'): ?>
                                        <span class="badge badge-danger" style="display: inline-flex; align-items: center; gap: 4px; background: #fee2e2; color: #ef4444;">
                                            <i class="fa-solid fa-circle-xmark"></i> Tidak Lulus
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-hourglass-half"></i> Menunggu
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; font-weight: 700; color: var(--text-main, #0f172a);">
                                    <?= $ex['score'] !== null ? number_format($ex['score'], 1) : '-' ?>
                                </td>
                                <td style="text-align: center; font-family: monospace; font-size: 0.8rem; color: #475569;">
                                    <?= SecurityHelper::escape($ex['certificate_number'] ?: '-') ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <button type="button" class="btn btn-sm btn-primary" onclick="openGradeModal(<?= htmlspecialchars(json_encode($ex), ENT_QUOTES, 'UTF-8') ?>)" style="padding: 4px 10px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-pen-to-square"></i> Nilai
                                        </button>
                                        <?php if ($status === 'Lulus'): ?>
                                            <a href="<?= Auth::baseUrl() ?>/tahfidz/ujian/sertifikat/<?= $ex['id'] ?>" target="_blank" class="btn btn-sm btn-secondary" style="padding: 4px 8px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;" title="Cetak Syahadah">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                        <?php endif; ?>
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

<!-- Native Custom Modal Penilaian (No Bootstrap JS dependency) -->
<div id="gradeModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border: 1px solid var(--border-color, #e2e8f0); overflow: hidden; animation: modalPop 0.2s ease-out;">
        <form action="<?= Auth::baseUrl() ?>/tahfidz/ujian/grade" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="id" id="modalExamId">

            <!-- Modal Header -->
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <h3 style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px; color: var(--text-main, #0f172a);">
                    <i class="fa-solid fa-award" style="color: #0d9488;"></i> Penilaian Ujian Tasmi' Tahfidz
                </h3>
                <button type="button" onclick="closeGradeModal()" style="border: none; background: transparent; cursor: pointer; color: #94a3b8; font-size: 1.25rem; line-height: 1; padding: 4px;">
                    &times;
                </button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Nama Santri</label>
                    <input type="text" id="modalStudentName" class="form-control" style="background: #f1f5f9; font-weight: 600;" readonly>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Juz Yang Diuji</label>
                        <input type="text" id="modalJuz" class="form-control" style="background: #f1f5f9; font-weight: 600; text-align: center;" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Tanggal Ujian <span style="color: #ef4444;">*</span></label>
                        <input type="date" name="exam_date" id="modalExamDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Nilai Angka (0-100) <span style="color: #ef4444;">*</span></label>
                        <input type="number" step="0.1" min="0" max="100" name="score" id="modalScore" class="form-control" placeholder="Contoh: 88.5" style="text-align: center; font-size: 1.1rem; font-weight: 700; color: #0d9488;" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Keputusan Hasil <span style="color: #ef4444;">*</span></label>
                        <select name="status" id="modalStatus" class="form-control" required>
                            <option value="Lulus">Lulus (Terbitkan Syahadah)</option>
                            <option value="Tidak Lulus">Tidak Lulus (Remedial)</option>
                            <option value="Menunggu">Menunggu</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Catatan Penguji / Evaluasi Tajwid</label>
                    <textarea name="notes" id="modalNotes" rows="3" class="form-control" placeholder="Catatan fashahah, kelancaran, makharijul huruf, dan hukum mad..."></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 14px 20px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 10px; background: #f8fafc;">
                <button type="button" onclick="closeGradeModal()" class="btn btn-secondary" style="padding: 8px 16px;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 18px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Hasil Ujian
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<script>
function openGradeModal(ex) {
    document.getElementById('modalExamId').value = ex.id;
    document.getElementById('modalStudentName').value = ex.student_name + ' (NIS: ' + ex.nis + ')';
    document.getElementById('modalJuz').value = 'Juz ' + ex.juz_number;
    document.getElementById('modalExamDate').value = ex.exam_date ? ex.exam_date : '<?= date('Y-m-d') ?>';
    document.getElementById('modalScore').value = ex.score !== null ? ex.score : '';
    document.getElementById('modalStatus').value = ex.status;
    document.getElementById('modalNotes').value = ex.notes || '';

    const overlay = document.getElementById('gradeModalOverlay');
    overlay.style.display = 'flex';
}

function closeGradeModal() {
    const overlay = document.getElementById('gradeModalOverlay');
    overlay.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Tutup modal jika klik di luar area dialog
    const overlay = document.getElementById('gradeModalOverlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeGradeModal();
            }
        });
    }

    // Filter Buttons
    const filterBtns = document.querySelectorAll('.btn-filter');
    const rows = document.querySelectorAll('.exam-row');
    const searchInput = document.getElementById('searchExam');

    let activeFilter = 'all';

    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            filterBtns.forEach(function(b) {
                b.classList.remove('btn-primary');
                b.classList.add('btn-secondary');
            });
            this.classList.remove('btn-secondary');
            this.classList.add('btn-primary');

            activeFilter = this.getAttribute('data-status');
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            applyFilters();
        });
    }

    function applyFilters() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        rows.forEach(function(row) {
            const status = row.getAttribute('data-status') || '';
            const search = row.getAttribute('data-search') || '';

            const matchesStatus = (activeFilter === 'all') || (status === activeFilter);
            const matchesSearch = (!query) || (search.includes(query));

            row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
        });
    }
});
</script>
