<?php
// views/master/subjects.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$canEdit = Auth::hasRole('operator_sekolah') || Auth::hasRole('operator_yayasan');
$baseUrl = Auth::baseUrl();
$assignments = $assignments ?? [];
$subjects = $subjects ?? [];
$teachers = $teachers ?? [];
$classes = $classes ?? [];

// Hitung metrik
$totalMapel = count($subjects);
$totalPenugasan = count($assignments);
$uniqueTeachersAssigned = count(array_unique(array_column($assignments, 'employee_id')));
$uniqueClassesCovered = count(array_unique(array_column($assignments, 'class_id')));

// Hitung jumlah kelas yang diampu per subject
$subjectUsageCount = [];
foreach ($assignments as $asg) {
    $sId = $asg['subject_id'];
    $subjectUsageCount[$sId] = ($subjectUsageCount[$sId] ?? 0) + 1;
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Mata Pelajaran & Penugasan Guru Pengampu</h1>
        <div class="page-subtitle">Kelola master kurikulum mata pelajaran serta plotting guru yang mengajar mapel di setiap kelas aktif.</div>
    </div>
    <?php if ($canEdit): ?>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-outline" onclick="openAssignModal()">
                <i class="fa-solid fa-user-check"></i> Tugaskan Guru ke Mapel & Kelas
            </button>
            <button type="button" class="btn btn-primary" onclick="openSubjectModal()">
                <i class="fa-solid fa-plus"></i> Tambah Mapel Baru
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Stats Summary Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
            <i class="fa-solid fa-book-open"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalMapel ?></div>
            <div class="stat-label">Total Mata Pelajaran</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalPenugasan ?></div>
            <div class="stat-label">Plotting Kelas Aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
            <i class="fa-solid fa-user-graduate"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $uniqueTeachersAssigned ?></div>
            <div class="stat-label">Guru Terplot Mengajar</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
            <i class="fa-solid fa-door-open"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $uniqueClassesCovered ?> / <?= count($classes) ?></div>
            <div class="stat-label">Kelas Terplot Mapel</div>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div style="display: flex; gap: 8px; margin-bottom: 16px; border-bottom: 2px solid var(--border-color, #e2e8f0); padding-bottom: 8px;">
    <button type="button" id="tabBtnAssign" class="btn btn-primary btn-sm" onclick="switchTab('assign')" style="border-radius: 20px; font-weight: 600;">
        <i class="fa-solid fa-list-check" style="margin-right: 6px;"></i> Daftar Penugasan Guru & Kelas (<?= $totalPenugasan ?>)
    </button>
    <button type="button" id="tabBtnMaster" class="btn btn-outline btn-sm" onclick="switchTab('master')" style="border-radius: 20px; font-weight: 600;">
        <i class="fa-solid fa-book" style="margin-right: 6px;"></i> Master Mata Pelajaran (<?= $totalMapel ?>)
    </button>
</div>

<!-- SECTION 1: TABEL PENUGASAN GURU KE MAPEL & KELAS -->
<div id="sectionAssign">
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1.05rem; margin: 0;">
                    <i class="fa-solid fa-chalkboard-teacher" style="color: #0d9488;"></i> Plotting Guru Mengajar per Mapel & Kelas
                </h3>
                <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); margin-top: 2px;">
                    Daftar lengkap guru yang ditugaskan mengajar mapel pada masing-masing kelas. Anda dapat menambah atau membatalkan penugasan (unassign).
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <input type="text" id="filterAssign" placeholder="Cari guru, mapel, kelas..." class="form-control" style="width: 240px; padding: 6px 12px; font-size: 0.85rem;" onkeyup="filterAssignments()">
                <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openAssignModal()">
                        <i class="fa-solid fa-plus"></i> Plot Guru Baru
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" id="tableAssignments">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Guru Pengampu</th>
                            <th>Mata Pelajaran</th>
                            <th>Kategori</th>
                            <th>Kelas Diajar</th>
                            <th style="width: 140px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignments)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-user-xmark" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada guru yang ditugaskan mengampu mata pelajaran di kelas manapun.
                                    <div style="margin-top: 8px;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="openAssignModal()">
                                            <i class="fa-solid fa-plus"></i> Mulai Tugaskan Guru
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $idx => $asg): 
                                $catClass = match($asg['subject_type']) {
                                    'Diniyah' => 'badge-success',
                                    'Tahfidz' => 'badge-accent',
                                    'Tahsin'  => 'badge-info',
                                    'Baca Latin', 'National Kurikulum' => 'badge-warning',
                                    default   => 'badge-secondary'
                                };
                            ?>
                                <tr class="asg-row">
                                    <td style="text-align: center;"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a);"><?= SecurityHelper::escape($asg['teacher_name']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted, #64748b);">
                                            NIPY: <?= SecurityHelper::escape($asg['teacher_nipy'] ?: '-') ?> &bull; <?= SecurityHelper::escape($asg['teacher_position'] ?: 'Guru') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;"><?= SecurityHelper::escape($asg['subject_name']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted, #64748b);">
                                            Kode: <code><?= SecurityHelper::escape($asg['subject_code']) ?></code>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $catClass ?>"><?= SecurityHelper::escape($asg['subject_type']) ?></span>
                                    </td>
                                    <td>
                                        <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; color: #0f766e; background: rgba(13, 148, 136, 0.08); padding: 4px 10px; border-radius: 6px; border: 1px solid rgba(13, 148, 136, 0.2);">
                                            <i class="fa-solid fa-door-open"></i> <?= SecurityHelper::escape($asg['class_name']) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($canEdit): ?>
                                            <form action="<?= $baseUrl ?>/subjects/unassign-teacher" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan penugasan guru <?= htmlspecialchars(addslashes($asg['teacher_name'])) ?> untuk mapel <?= htmlspecialchars(addslashes($asg['subject_name'])) ?> di <?= htmlspecialchars(addslashes($asg['class_name'])) ?>?');">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="assignment_id" value="<?= $asg['id'] ?>">
                                                <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.3);" title="Hapus Penugasan (Unassign)">
                                                    <i class="fa-solid fa-user-minus"></i> Unassign
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            -
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
</div>

<!-- SECTION 2: TABEL MASTER MATA PELAJARAN -->
<div id="sectionMaster" style="display: none;">
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1.05rem; margin: 0;">
                    <i class="fa-solid fa-book-bookmark" style="color: #0d9488;"></i> Master Kurikulum Mata Pelajaran
                </h3>
                <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); margin-top: 2px;">
                    Daftar mata pelajaran yang diajarkan pada unit sekolah ini.
                </div>
            </div>
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="openSubjectModal()">
                    <i class="fa-solid fa-plus"></i> Tambah Mapel Baru
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Kode Mapel</th>
                            <th>Nama Mata Pelajaran</th>
                            <th>Kategori / Jenis</th>
                            <th style="text-align: center;">Kelas Diampu</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subjects)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    Belum ada mata pelajaran yang didaftarkan pada unit ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subjects as $sub): 
                                $badgeClass = match($sub['type']) {
                                    'Diniyah' => 'badge-success',
                                    'Tahfidz' => 'badge-accent',
                                    'Tahsin'  => 'badge-info',
                                    'Baca Latin', 'National Kurikulum' => 'badge-warning',
                                    default   => 'badge-secondary'
                                };
                                $usage = $subjectUsageCount[$sub['id']] ?? 0;
                            ?>
                                <tr>
                                    <td><code><?= SecurityHelper::escape($sub['code']) ?></code></td>
                                    <td><strong style="color: var(--text-main, #0f172a);"><?= SecurityHelper::escape($sub['name']) ?></strong></td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?>"><?= SecurityHelper::escape($sub['type']) ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($usage > 0): ?>
                                            <span class="badge badge-info" style="font-weight: 600;"><?= $usage ?> Kelas</span>
                                        <?php else: ?>
                                            <span style="font-size: 0.8rem; color: var(--text-muted, #94a3b8);">Belum ada kelas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($canEdit): ?>
                                            <button type="button" class="btn btn-outline btn-sm" onclick='editSubject(<?= json_encode($sub) ?>)' title="Edit Mapel">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                        <?php else: ?>
                                            -
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
</div>

<!-- Modal Form Tambah/Edit Mapel -->
<?php if ($canEdit): ?>
<div class="modal-overlay" id="subjectModal">
    <div class="modal-container" style="max-width: 480px;">
        <form method="POST" action="<?= $baseUrl ?>/subjects/save">
            <?= CSRF::field() ?>
            <input type="hidden" name="id" id="sub_id" value="0">
            
            <div class="modal-header">
                <h3 class="card-title" id="modalSubTitle">Tambah Mata Pelajaran</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeSubjectModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">Kode Mapel <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="code" id="sub_code" class="form-control" required placeholder="Contoh: SD-MAT">
                </div>
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">Nama Mata Pelajaran <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="name" id="sub_name" class="form-control" required placeholder="Contoh: Matematika">
                </div>
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600;">Kategori / Jenis Mapel <span style="color: #ef4444;">*</span></label>
                    <select name="type" id="sub_type" class="form-control" required>
                        <option value="Umum">Umum</option>
                        <option value="Diniyah">Diniyah</option>
                        <option value="Tahfidz">Tahfidz</option>
                        <option value="Tahsin">Tahsin</option>
                        <option value="Baca Latin">Baca Latin (Khusus TK)</option>
                        <option value="National Kurikulum">National Kurikulum (Khusus TK)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeSubjectModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Mapel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Penugasan Guru ke Mapel & Kelas -->
<div class="modal-overlay" id="assignModal">
    <div class="modal-container" style="max-width: 520px;">
        <form method="POST" action="<?= $baseUrl ?>/subjects/assign-teacher">
            <?= CSRF::field() ?>
            <div class="modal-header">
                <h3 class="card-title">Tugaskan Guru ke Mapel & Kelas</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeAssignModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600;">Pilih Guru Pengampu <span style="color: #ef4444;">*</span></label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">-- Pilih Guru Pengampu --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= SecurityHelper::escape($t['nama']) ?> (<?= SecurityHelper::escape($t['position']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600;">Mata Pelajaran <span style="color: #ef4444;">*</span></label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= SecurityHelper::escape($s['name']) ?> (<?= SecurityHelper::escape($s['code']) ?> - <?= SecurityHelper::escape($s['type']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600;">Kelas yang Diajar <span style="color: #ef4444;">*</span></label>
                    <select name="class_id" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= SecurityHelper::escape($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="background: rgba(13, 148, 136, 0.08); border-left: 3px solid #0d9488; padding: 10px 14px; border-radius: 4px; font-size: 0.8rem; color: #0f766e;">
                    <i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i>
                    Jika mapel pada kelas yang dipilih sudah diampu oleh guru lain, penugasan akan otomatis diperbarui ke guru yang baru dipilih.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAssignModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab) {
    const secAssign = document.getElementById('sectionAssign');
    const secMaster = document.getElementById('sectionMaster');
    const btnAssign = document.getElementById('tabBtnAssign');
    const btnMaster = document.getElementById('tabBtnMaster');

    if (tab === 'assign') {
        secAssign.style.display = 'block';
        secMaster.style.display = 'none';
        btnAssign.className = 'btn btn-primary btn-sm';
        btnMaster.className = 'btn btn-outline btn-sm';
    } else {
        secAssign.style.display = 'none';
        secMaster.style.display = 'block';
        btnAssign.className = 'btn btn-outline btn-sm';
        btnMaster.className = 'btn btn-primary btn-sm';
    }
}

function filterAssignments() {
    const query = document.getElementById('filterAssign').value.toLowerCase();
    const rows = document.querySelectorAll('#tableAssignments .asg-row');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

function openAssignModal() {
    document.getElementById('assignModal').classList.add('active');
}
function closeAssignModal() {
    document.getElementById('assignModal').classList.remove('active');
}

function openSubjectModal() {
    document.getElementById('sub_id').value = '0';
    document.getElementById('modalSubTitle').innerText = 'Tambah Mata Pelajaran';
    document.getElementById('sub_code').value = '';
    document.getElementById('sub_name').value = '';
    document.getElementById('sub_type').value = 'Umum';
    document.getElementById('subjectModal').classList.add('active');
}
function editSubject(s) {
    document.getElementById('sub_id').value = s.id;
    document.getElementById('modalSubTitle').innerText = 'Edit Mapel: ' + s.name;
    document.getElementById('sub_code').value = s.code;
    document.getElementById('sub_name').value = s.name;
    document.getElementById('sub_type').value = s.type;
    document.getElementById('subjectModal').classList.add('active');
}
function closeSubjectModal() {
    document.getElementById('subjectModal').classList.remove('active');
}
</script>
<?php endif; ?>
