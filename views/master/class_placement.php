<?php
// views/master/class_placement.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$canEdit = Auth::hasRole('operator_sekolah') || Auth::hasRole('operator_yayasan');
$totalStudents = (int)($stats['total_students'] ?? count($students ?? []));
$assignedStudents = (int)($stats['assigned_students'] ?? 0);
$unassignedStudents = (int)($stats['unassigned_students'] ?? 0);
$totalClasses = count($classes ?? []);
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Operator Sekolah</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tahun Ajaran <?= SecurityHelper::escape(Auth::academicYearName()) ?></span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-users-rectangle" style="color: #0d9488;"></i> Penempatan &amp; Rombongan Belajar Siswa
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Kelola pembagian rombel santri, masukkan santri ke dalam kelas, pindahkan antar-kelas tanpa duplikasi, atau keluarkan santri dari rombel.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="<?= $baseUrl ?>/classes" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-chalkboard-user"></i> Kelola Data Kelas &amp; Walas
            </a>
            <a href="<?= $baseUrl ?>/students" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-user-graduate"></i> Data Induk Siswa
            </a>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Santri Unit</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalStudents ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">
            Aktif terdaftar
            <?php if (!empty($graduatedCount)): ?>
                <span title="<?= $graduatedCount ?> santri tingkat akhir berstatus Lulus dan tidak dimasukkan ke rombel aktif" style="color: #64748b; font-size: 0.75rem;">(<?= $graduatedCount ?> Lulus)</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Sudah Berkelas</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $assignedStudents ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">
            <?= $totalStudents > 0 ? round(($assignedStudents / $totalStudents) * 100, 1) : 0 ?>% masuk rombel
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Belum Masuk Kelas</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b; margin: 6px 0 2px 0;">
            <?= $unassignedStudents ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: #f59e0b; font-weight: 500;">Perlu ditempatkan</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Rombel / Kelas Aktif</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $totalClasses ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Kelas</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tahun ajaran ini</div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <!-- Left: Filter Dropdowns -->
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <label style="font-size: 0.875rem; font-weight: 600; color: var(--text-main, #0f172a); margin: 0;">Filter Kelas:</label>
            <form method="GET" action="<?= $baseUrl ?>/classes/placement" id="filterClassForm" style="margin: 0;">
                <select name="class_id" class="form-control" onchange="this.form.submit()" style="font-size: 0.875rem; padding: 6px 12px; height: 36px; min-width: 220px;">
                    <option value="0" <?= $selectedClassId === 0 ? 'selected' : '' ?>>-- Semua Santri (Semua Kelas) --</option>
                    <option value="-1" <?= $selectedClassId === -1 ? 'selected' : '' ?> style="color: #d97706; font-weight: 600;">⚠️ Khusus Santri Belum Masuk Kelas (<?= $unassignedStudents ?>)</option>
                    <optgroup label="Pilih Berdasarkan Kelas:">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $selectedClassId === (int)$c['id'] ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($c['name']) ?> (<?= (int)$c['student_count'] ?> Santri)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </form>
        </div>

        <!-- Right: Search Box -->
        <div style="display: flex; gap: 8px; align-items: center;">
            <div style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                <input type="text" id="searchPlacement" class="form-control" placeholder="Cari santri / NIS / No. KK / kelas..." style="padding-left: 28px; font-size: 0.8125rem; height: 36px; width: 270px;">
            </div>
        </div>
    </div>

    <!-- Batch Action Toolbar (Centang Massal) -->
    <?php if ($canEdit): ?>
    <form method="POST" action="<?= $baseUrl ?>/classes/batch-placement" id="batchForm">
        <?= CSRF::inputField() ?>
        <input type="hidden" name="action_type" id="batch_action_type" value="assign">
        <input type="hidden" name="redirect_url" value="<?= SecurityHelper::escape($_SERVER['REQUEST_URI']) ?>">

        <div id="batchToolbar" style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; color: #166534; font-weight: 600;">
                <i class="fa-solid fa-square-check"></i>
                <span id="selectedCountText">0 santri dipilih</span>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; gap: 6px; align-items: center;">
                    <select name="target_class_id" id="batchTargetClass" class="form-control" style="font-size: 0.8125rem; height: 32px; padding: 4px 8px; min-width: 180px;">
                        <option value="">-- Pilih Kelas Tujuan --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= SecurityHelper::escape($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-sm btn-primary" onclick="submitBatchAssign()" style="display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; font-size: 0.8125rem;">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Masukkan ke Kelas
                    </button>
                </div>
                <div style="border-left: 1px solid #cbd5e1; height: 24px;"></div>
                <button type="button" class="btn btn-sm btn-outline" onclick="submitBatchRemove()" style="color: #ef4444; border-color: #ef4444; display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; font-size: 0.8125rem;">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluarkan Terpilih
                </button>
            </div>
        </div>
    <?php endif; ?>

    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table" id="placementTable">
                <thead>
                    <tr>
                        <?php if ($canEdit): ?>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllStudents" style="accent-color: #0d9488; cursor: pointer;">
                            </th>
                        <?php endif; ?>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 130px;">NIS / NISN</th>
                        <th>Nama Santri</th>
                        <th style="width: 70px; text-align: center;">L/P</th>
                        <th style="width: 220px;">Kelas Saat Ini</th>
                        <th style="width: 260px; text-align: center;">Aksi Penempatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="<?= $canEdit ? 7 : 6 ?>" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-users-slash" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                Tidak ada santri yang sesuai kriteria filter saat ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $idx => $s): 
                            $hasCurrentClass = !empty($s['class_id']);
                        ?>
                            <tr class="placement-row" data-search="<?= strtolower(SecurityHelper::escape($s['nama'] . ' ' . $s['nis'] . ' ' . ($s['nisn'] ?? '') . ' ' . ($s['no_kk'] ?? '') . ' ' . ($s['class_name'] ?? 'belum masuk kelas'))) ?>">
                                <?php if ($canEdit): ?>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>" class="student-select-cb" style="accent-color: #0d9488; cursor: pointer;">
                                    </td>
                                <?php endif; ?>
                                <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                <td>
                                    <strong style="color: var(--text-main, #0f172a); font-family: monospace; font-size: 0.875rem;">
                                        <?= SecurityHelper::escape($s['nis']) ?>
                                    </strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted, #64748b); font-family: monospace;">
                                        NISN: <?= SecurityHelper::escape($s['nisn'] ?: '-') ?>
                                    </div>
                                    <?php if (!empty($s['no_kk'])): ?>
                                        <div style="font-size: 0.75rem; color: #0d9488; font-family: monospace; margin-top: 1px;">
                                            <i class="fa-solid fa-address-card" style="font-size: 10px;"></i> KK: <?= SecurityHelper::escape($s['no_kk']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                        <?= SecurityHelper::escape($s['nama']) ?>
                                    </strong>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge <?= $s['gender'] === 'L' ? 'badge-primary' : 'badge-secondary' ?>" style="font-weight: 600;">
                                        <?= $s['gender'] === 'L' ? 'L' : 'P' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($hasCurrentClass): ?>
                                        <span class="badge badge-success" style="font-size: 0.8125rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-chalkboard"></i> <?= SecurityHelper::escape($s['class_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="font-size: 0.8125rem; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-circle-exclamation"></i> Belum Masuk Kelas
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($canEdit): ?>
                                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                            <!-- Tombol Pindah / Masukkan Kelas -->
                                            <button type="button" class="btn btn-sm btn-primary" onclick="openMoveModal(<?= $s['id'] ?>, '<?= SecurityHelper::escape($s['nama']) ?>', <?= (int)($s['class_id'] ?? 0) ?>)" style="padding: 4px 10px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-arrows-turn-to-dots"></i> <?= $hasCurrentClass ? 'Pindah Kelas' : 'Pilih Kelas' ?>
                                            </button>

                                            <?php if ($hasCurrentClass): ?>
                                                <!-- Tombol Keluarkan dari Kelas -->
                                                <button type="button" class="btn btn-sm btn-outline" onclick="confirmRemoveStudent(<?= $s['id'] ?>, '<?= SecurityHelper::escape($s['nama']) ?>', '<?= SecurityHelper::escape($s['class_name']) ?>')" style="color: #ef4444; border-color: #ef4444; padding: 4px 8px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;" title="Keluarkan dari kelas">
                                                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluarkan
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted, #64748b); font-size: 0.85rem;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($canEdit): ?>
    </form>
    <?php endif; ?>
</div>

<!-- Modal Pindah / Masukkan Siswa ke Kelas (Single) -->
<div class="modal-overlay" id="modalMoveStudent">
    <div class="modal-container" style="max-width: 460px;">
        <form method="POST" action="<?= $baseUrl ?>/classes/assign-student">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="student_id" id="move_student_id">
            <input type="hidden" name="redirect_url" value="<?= SecurityHelper::escape($_SERVER['REQUEST_URI']) ?>">

            <div class="modal-header">
                <h3 class="card-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-chalkboard-user" style="color: #0d9488;"></i> Penempatan Kelas Santri
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeMoveModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Nama Santri</label>
                    <input type="text" id="move_student_name" class="form-control" style="background: #f1f5f9; font-weight: 600;" readonly>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Pilih Kelas Tujuan <span style="color: #ef4444;">*</span></label>
                    <select name="class_id" id="move_target_class_id" class="form-control" required style="font-size: 0.9375rem;">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>">
                                <?= SecurityHelper::escape($c['name']) ?> (Tingkat: <?= SecurityHelper::escape($c['level']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-muted, #64748b); font-size: 0.75rem; display: block; margin-top: 4px;">
                        * Memindahkan kelas otomatis memperbarui data tanpa menimbulkan duplikasi.
                    </small>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeMoveModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Penempatan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Form for Single Remove -->
<form method="POST" action="<?= $baseUrl ?>/classes/remove-student" id="formRemoveSingle">
    <?= CSRF::inputField() ?>
    <input type="hidden" name="student_id" id="remove_single_student_id">
    <input type="hidden" name="redirect_url" value="<?= SecurityHelper::escape($_SERVER['REQUEST_URI']) ?>">
</form>

<script>
function openMoveModal(studentId, studentName, currentClassId) {
    document.getElementById('move_student_id').value = studentId;
    document.getElementById('move_student_name').value = studentName;
    const select = document.getElementById('move_target_class_id');
    if (select) {
        select.value = currentClassId > 0 ? currentClassId : '';
    }
    document.getElementById('modalMoveStudent').classList.add('active');
}

function closeMoveModal() {
    document.getElementById('modalMoveStudent').classList.remove('active');
}

function confirmRemoveStudent(studentId, studentName, className) {
    if (confirm('Keluarkan ' + studentName + ' dari ' + className + '?\n\nSantri akan berstatus Belum Masuk Kelas dan tidak dihapus dari sistem.')) {
        document.getElementById('remove_single_student_id').value = studentId;
        document.getElementById('formRemoveSingle').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchPlacement');
    const rows = document.querySelectorAll('.placement-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            rows.forEach(function(row) {
                const text = row.getAttribute('data-search') || '';
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    // Select All Checkbox
    const selectAll = document.getElementById('selectAllStudents');
    const checkboxes = document.querySelectorAll('.student-select-cb');
    const countText = document.getElementById('selectedCountText');

    function updateCount() {
        let count = 0;
        checkboxes.forEach(function(cb) {
            if (cb.checked) count++;
        });
        if (countText) {
            countText.innerText = count + ' santri dipilih';
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const isChecked = this.checked;
            checkboxes.forEach(function(cb) {
                // Hanya check yang terlihat di pencarian
                const row = cb.closest('tr');
                if (row && row.style.display !== 'none') {
                    cb.checked = isChecked;
                }
            });
            updateCount();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateCount);
    });
});

function submitBatchAssign() {
    const checked = document.querySelectorAll('.student-select-cb:checked');
    if (checked.length === 0) {
        alert('Pilih minimal satu santri yang ingin dimasukkan ke kelas.');
        return;
    }
    const targetClass = document.getElementById('batchTargetClass').value;
    if (!targetClass) {
        alert('Harap pilih kelas tujuan terlebih dahulu pada dropdown di atas.');
        document.getElementById('batchTargetClass').focus();
        return;
    }
    document.getElementById('batch_action_type').value = 'assign';
    if (confirm('Masukkan ' + checked.length + ' santri terpilih ke kelas tujuan?')) {
        document.getElementById('batchForm').submit();
    }
}

function submitBatchRemove() {
    const checked = document.querySelectorAll('.student-select-cb:checked');
    if (checked.length === 0) {
        alert('Pilih minimal satu santri yang ingin dikeluarkan dari kelas.');
        return;
    }
    document.getElementById('batch_action_type').value = 'remove';
    if (confirm('Apakah Anda yakin ingin mengeluarkan ' + checked.length + ' santri terpilih dari kelasnya masing-masing?\n\nSantri akan berstatus Belum Masuk Kelas dan tidak dihapus dari sistem.')) {
        document.getElementById('batchForm').submit();
    }
}
</script>
