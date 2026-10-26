<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$totalAssignments = count($assignments ?? []);
$osnCount = 0;
$o2snCount = 0;
$fls2nCount = 0;
$aktifCount = 0;
$selesaiCount = 0;

foreach ($assignments as $a) {
    $evt = $a['event_type'] ?? '';
    if ($evt === 'OSN') $osnCount++;
    elseif ($evt === 'O2SN') $o2snCount++;
    elseif ($evt === 'FLS2N') $fls2nCount++;

    if (($a['status'] ?? 'Aktif') === 'Selesai') {
        $selesaiCount++;
    } else {
        $aktifCount++;
    }
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Dokumen No. 8</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Administrasi Kepala Sekolah</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-user-tag" style="color: #0d9488;"></i> SK Penugasan Pembina & Peserta Lomba Siswa
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Penetapan resmi surat keputusan (SK) guru pembina ajang talenta nasional (OSN, O2SN, FLS2N), pemilihan peserta didik utusan lomba, dan cetak SK berformat PDF.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Penugasan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalAssignments ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">SK</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0d9488; font-weight: 500;">
            <i class="fa-solid fa-circle-check"></i> <?= $aktifCount ?> Aktif | <?= $selesaiCount ?> Selesai
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Pembina OSN</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $osnCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Guru</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Olimpiade Sains Nasional</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Pembina O2SN</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $o2snCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Guru</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">Olimpiade Olahraga Siswa</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Pembina FLS2N</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b; margin: 6px 0 2px 0;">
            <?= $fls2nCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Guru</span>
        </div>
        <div style="font-size: 0.8125rem; color: #f59e0b; font-weight: 500;">Festival Seni Siswa</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">
    <!-- Form Card -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-signature" style="color: #0d9488;"></i> Form SK Penugasan Pembina
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= Auth::baseUrl() ?>/panitia/penugasan/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Pilih Guru Pembina <span style="color: #ef4444;">*</span></label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= SecurityHelper::escape($t['nama']) ?> (<?= SecurityHelper::escape($t['position']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Cabang Ajang Lomba Resmi <span style="color: #ef4444;">*</span></label>
                    <select name="event_type" class="form-control" required>
                        <option value="OSN">OSN (Olimpiade Sains Nasional)</option>
                        <option value="O2SN">O2SN (Olimpiade Olahraga Siswa Nasional)</option>
                        <option value="FLS2N">FLS2N (Festival Lomba Seni Siswa Nasional)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nomor SK (Opsional)</label>
                    <input type="text" name="sk_number" class="form-control" placeholder="Contoh: 421.1/SK-LOMBA/<?= date('Y') ?>/001">
                    <small style="color: var(--text-muted, #64748b); font-size: 0.75rem;">Biarkan kosong untuk penomoran otomatis sistem.</small>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem; margin: 0;">Pilih Santri Peserta Didik (Opsional)</label>
                        <span style="font-size: 0.75rem; color: #0d9488; cursor: pointer;" onclick="document.getElementById('newSantriSearch').focus();">Cari...</span>
                    </div>
                    <input type="text" id="newSantriSearch" class="form-control" placeholder="Ketik nama santri..." style="height: 32px; font-size: 0.8125rem; margin-bottom: 8px;">
                    <div style="max-height: 180px; overflow-y: auto; border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px; padding: 8px 12px; background: #fafafa;" id="newSantriBox">
                        <?php if (empty($students)): ?>
                            <div style="font-size: 0.8125rem; color: #94a3b8; text-align: center; padding: 12px;">Tidak ada data santri aktif.</div>
                        <?php else: ?>
                            <?php foreach ($students as $st): ?>
                                <label class="student-item-chk" data-name="<?= strtolower(SecurityHelper::escape($st['nama'] . ' ' . $st['nis'] . ' ' . ($st['class_name'] ?? ''))) ?>" style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; padding: 4px 0; cursor: pointer; border-bottom: 1px dashed #f1f5f9;">
                                    <input type="checkbox" name="student_ids[]" value="<?= $st['id'] ?>">
                                    <span style="flex: 1;">
                                        <strong><?= SecurityHelper::escape($st['nama']) ?></strong>
                                        <span style="color: #64748b; font-size: 0.75rem;">(<?= SecurityHelper::escape($st['class_name'] ?? 'Tanpa Rombel') ?>)</span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan &amp; Terbitkan SK
                </button>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-users-gear" style="color: #0d9488;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Daftar Guru Pembina &amp; Peserta Lomba</h3>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                    <input type="text" id="searchPembina" class="form-control" placeholder="Cari guru / cabang..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 220px;">
                </div>
                <span class="badge badge-secondary"><?= $totalAssignments ?> Pembina</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th>Guru Pembina &amp; No. SK</th>
                            <th style="width: 90px; text-align: center;">Cabang</th>
                            <th>Peserta Didik Binaan</th>
                            <th style="width: 120px; text-align: center;">Status Lomba</th>
                            <th style="width: 170px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignments)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-user-slash" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada penugasan guru pembina lomba untuk tahun ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $idx => $a): 
                                $evt = $a['event_type'] ?? '';
                                $badgeColor = 'badge-primary';
                                if ($evt === 'O2SN') $badgeColor = 'badge-success';
                                elseif ($evt === 'FLS2N') $badgeColor = 'badge-warning';

                                $assignedStudentIds = json_decode($a['student_ids'] ?? '[]', true) ?: [];
                                $studentCount = count($assignedStudentIds);
                                $isSelesai = (($a['status'] ?? 'Aktif') === 'Selesai');
                            ?>
                                <tr class="pembina-row" data-search="<?= strtolower(SecurityHelper::escape($a['teacher_name'] . ' ' . $a['nipy'] . ' ' . $evt . ' ' . ($a['sk_number'] ?? ''))) ?>">
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                            <?= SecurityHelper::escape($a['teacher_name']) ?>
                                        </div>
                                        <div style="display: flex; gap: 8px; align-items: center; margin-top: 2px;">
                                            <span style="font-family: monospace; font-size: 0.75rem; color: #64748b;">NIPY: <?= SecurityHelper::escape($a['nipy'] ?? '-') ?></span>
                                            <?php if (!empty($a['sk_number'])): ?>
                                                <span class="badge badge-secondary" style="font-size: 0.7rem; font-family: monospace;"><?= SecurityHelper::escape($a['sk_number']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $badgeColor ?>" style="font-weight: 600;">
                                            <?= SecurityHelper::escape($evt) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($studentCount > 0): ?>
                                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <span class="badge badge-info" style="font-size: 0.8125rem;">
                                                    <i class="fa-solid fa-user-graduate" style="margin-right: 4px;"></i> <?= $studentCount ?> Santri
                                                </span>
                                                <button type="button" class="btn btn-outline btn-sm btn-open-students" data-id="<?= $a['id'] ?>" style="padding: 2px 8px; font-size: 0.75rem;">
                                                    <i class="fa-solid fa-pen-to-square"></i> Ubah
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <span style="color: #94a3b8; font-size: 0.8125rem;">Belum ada peserta</span>
                                                <button type="button" class="btn btn-outline btn-sm btn-open-students" data-id="<?= $a['id'] ?>" style="padding: 2px 8px; font-size: 0.75rem;">
                                                    <i class="fa-solid fa-plus"></i> Pilih Santri
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($isSelesai): ?>
                                            <div>
                                                <span class="badge badge-secondary" style="display: inline-flex; align-items: center; gap: 4px; background: #e2e8f0; color: #475569; padding: 4px 8px; font-size: 0.8125rem;">
                                                    <i class="fa-solid fa-flag-checkered"></i> Lomba Selesai
                                                </span>
                                            </div>
                                            <form action="<?= Auth::baseUrl() ?>/panitia/penugasan/toggle-status" method="POST" style="margin-top: 6px;" onsubmit="return confirm('Kembalikan status lomba menjadi Aktif?');">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                                                <button type="submit" class="btn btn-outline btn-sm" style="font-size: 0.7rem; padding: 2px 8px; color: #0284c7;">
                                                    <i class="fa-solid fa-arrow-rotate-left"></i> Buka Kembali
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <div>
                                                <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; font-size: 0.8125rem;">
                                                    <i class="fa-solid fa-circle-check"></i> Aktif Bertugas
                                                </span>
                                            </div>
                                            <form action="<?= Auth::baseUrl() ?>/panitia/penugasan/toggle-status" method="POST" style="margin-top: 6px;" onsubmit="return confirm('Tandai bahwa kegiatan lomba ini telah SELESAI?');">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                                                <button type="submit" class="btn btn-sm" style="background: #0d9488; color: white; border: none; font-size: 0.72rem; padding: 3px 8px; font-weight: 600; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                                    <i class="fa-solid fa-flag-checkered"></i> Tandai Lomba Selesai
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: inline-flex; gap: 6px; align-items: center; flex-wrap: wrap; justify-content: center;">
                                            <!-- Cetak SK Button -->
                                            <a href="<?= Auth::baseUrl() ?>/panitia/penugasan/cetak-sk/<?= $a['id'] ?>" target="_blank" class="btn btn-primary btn-sm" style="padding: 4px 10px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="Cetak Surat Keputusan (PDF)">
                                                <i class="fa-solid fa-file-pdf"></i> Cetak SK
                                            </a>

                                            <!-- Kelola Santri & Detail SK -->
                                            <button type="button" class="btn btn-outline btn-sm btn-open-students" data-id="<?= $a['id'] ?>" style="padding: 4px 8px; font-size: 0.75rem;" title="Ubah Santri Binaan, No SK & Status">
                                                <i class="fa-solid fa-pen-to-square"></i> Kelola
                                            </button>
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
</div>

<!-- JSON Data Assignments -->
<script id="assignmentsJson" type="application/json">
<?= json_encode($assignments, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>

<!-- Modal Pilih Santri Peserta & Ubah No SK -->
<div class="modal-overlay" id="studentsModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="modal-container" style="background: #fff; width: 100%; max-width: 580px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden;">
        <form action="<?= Auth::baseUrl() ?>/panitia/penugasan/update-students" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="assignment_id" id="modalAssignmentId">

            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <div>
                    <h3 class="modal-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-user-graduate" style="color: #0d9488;"></i> Pilih Santri Peserta &amp; Nomor SK
                    </h3>
                    <div id="modalTeacherName" style="font-size: 0.8125rem; color: #64748b; margin-top: 2px;"></div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" style="border: none; font-size: 1.25rem; line-height: 1; padding: 4px 8px;" onclick="closeStudentsModal()">&times;</button>
            </div>

            <div class="modal-body" style="padding: 20px; max-height: 60vh; overflow-y: auto;">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nomor SK Penugasan</label>
                    <input type="text" name="sk_number" id="modalSkNumber" class="form-control" placeholder="Contoh: 421.1/SK-LOMBA/<?= date('Y') ?>/001">
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Status Kegiatan Lomba</label>
                    <select name="status" id="modalStatus" class="form-control">
                        <option value="Aktif">Aktif Bertugas (Lomba Berjalan)</option>
                        <option value="Selesai">Lomba Selesai</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem; margin: 0;">Centang Santri yang Didampingi</label>
                        <span id="selectedCounterBadge" class="badge badge-info" style="font-size: 0.75rem;">0 Santri Terpilih</span>
                    </div>
                    <div style="position: relative; margin-bottom: 8px;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                        <input type="text" id="modalFilterSantri" class="form-control" placeholder="Cari nama santri atau kelas..." style="padding-left: 28px; height: 34px; font-size: 0.8125rem;">
                    </div>
                    <div style="max-height: 240px; overflow-y: auto; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 8px 12px; background: #fafafa;" id="modalSantriList">
                        <?php foreach ($students as $st): ?>
                            <label class="modal-st-row" data-search="<?= strtolower(SecurityHelper::escape($st['nama'] . ' ' . $st['nis'] . ' ' . ($st['class_name'] ?? ''))) ?>" style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; padding: 6px 0; cursor: pointer; border-bottom: 1px dashed #e2e8f0;">
                                <input type="checkbox" name="student_ids[]" value="<?= $st['id'] ?>" class="modal-st-chk" onchange="updateSelectedCount()">
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: #0f172a;"><?= SecurityHelper::escape($st['nama']) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b;">
                                        NIS: <?= SecurityHelper::escape($st['nis']) ?> | Rombel: <?= SecurityHelper::escape($st['class_name'] ?? 'Tanpa Rombel') ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="padding: 14px 20px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 8px; background: #f8fafc;">
                <button type="button" class="btn btn-outline" onclick="closeStudentsModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Perubahan SK
                </button>
            </div>
        </form>
    </div>
</div>

<style>
#studentsModal.active,
#studentsModal[style*="display: flex"],
#studentsModal[style*="display:flex"] {
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    z-index: 99999 !important;
}
</style>

<script>
let assignmentsMap = {};
try {
    const rawAssignments = document.getElementById('assignmentsJson');
    if (rawAssignments) {
        const arr = JSON.parse(rawAssignments.textContent);
        if (Array.isArray(arr)) {
            arr.forEach(function(a) {
                assignmentsMap[a.id] = a;
            });
        }
    }
} catch (e) {
    console.error('Error parsing assignments JSON:', e);
}

document.addEventListener('DOMContentLoaded', function() {
    // Search table pembina
    const search = document.getElementById('searchPembina');
    const rows = document.querySelectorAll('.pembina-row');
    if (search) {
        search.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            rows.forEach(function(row) {
                const text = row.getAttribute('data-search') || '';
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    // Filter santri in create form
    const newSantriSearch = document.getElementById('newSantriSearch');
    const newItems = document.querySelectorAll('#newSantriBox .student-item-chk');
    if (newSantriSearch) {
        newSantriSearch.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            newItems.forEach(function(item) {
                const text = item.getAttribute('data-name') || '';
                item.style.display = text.includes(q) ? 'flex' : 'none';
            });
        });
    }

    // Filter santri in modal
    const modalFilter = document.getElementById('modalFilterSantri');
    const modalRows = document.querySelectorAll('#modalSantriList .modal-st-row');
    if (modalFilter) {
        modalFilter.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            modalRows.forEach(function(item) {
                const text = item.getAttribute('data-search') || '';
                item.style.display = text.includes(q) ? 'flex' : 'none';
            });
        });
    }

    // Event listener for opening students modal
    document.querySelectorAll('.btn-open-students').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const a = assignmentsMap[id];
            if (a) {
                openStudentsModal(a);
            }
        });
    });

    const modal = document.getElementById('studentsModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStudentsModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStudentsModal();
        }
    });
});

function openStudentsModal(assignment) {
    const modal = document.getElementById('studentsModal');
    if (!modal) return;
    document.getElementById('modalAssignmentId').value = assignment.id;
    document.getElementById('modalTeacherName').innerText = 'Pembina: ' + (assignment.teacher_name || '') + ' (' + (assignment.event_type || '') + ')';
    document.getElementById('modalSkNumber').value = assignment.sk_number || '';
    
    const statusSelect = document.getElementById('modalStatus');
    if (statusSelect) {
        statusSelect.value = assignment.status || 'Aktif';
    }

    let selectedIds = [];
    try {
        selectedIds = JSON.parse(assignment.student_ids || '[]');
        if (!Array.isArray(selectedIds)) selectedIds = [];
    } catch(e) {
        selectedIds = [];
    }

    const chks = document.querySelectorAll('.modal-st-chk');
    chks.forEach(function(chk) {
        chk.checked = selectedIds.includes(parseInt(chk.value, 10));
    });

    updateSelectedCount();
    modal.classList.add('active');
    modal.style.display = 'flex';
    modal.style.opacity = '1';
    modal.style.visibility = 'visible';
}

function closeStudentsModal() {
    const modal = document.getElementById('studentsModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        modal.style.opacity = '0';
        modal.style.visibility = 'hidden';
    }
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.modal-st-chk:checked').length;
    const badge = document.getElementById('selectedCounterBadge');
    if (badge) {
        badge.innerText = checked + ' Santri Terpilih';
    }
}
</script>
