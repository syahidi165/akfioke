<?php
// views/master/classes.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$canEdit = Auth::hasRole('operator_sekolah');
$isYayasan = Auth::hasRole('operator_yayasan');

// Helper penentuan warna badge tingkat
function getLevelBadgeStyle(string $lvl): array {
    $lvlUpper = strtoupper(trim($lvl));
    if (str_contains($lvlUpper, 'TK') || str_contains($lvlUpper, 'KB')) {
        return ['bg' => '#ecfdf5', 'color' => '#059669', 'border' => '#a7f3d0', 'icon' => 'fa-shapes'];
    }
    if (str_contains($lvlUpper, '1') || str_contains($lvlUpper, '2')) {
        return ['bg' => '#eff6ff', 'color' => '#2563eb', 'border' => '#bfdbfe', 'icon' => 'fa-book-open'];
    }
    if (str_contains($lvlUpper, '3') || str_contains($lvlUpper, '4')) {
        return ['bg' => '#f0fdfa', 'color' => '#0d9488', 'border' => '#99f6e4', 'icon' => 'fa-bookmark'];
    }
    if (str_contains($lvlUpper, '5') || str_contains($lvlUpper, '6')) {
        return ['bg' => '#eef2ff', 'color' => '#4f46e5', 'border' => '#c7d2fe', 'icon' => 'fa-graduation-cap'];
    }
    if (str_contains($lvlUpper, '7') || str_contains($lvlUpper, '8') || str_contains($lvlUpper, '9')) {
        return ['bg' => '#faf5ff', 'color' => '#7e22ce', 'border' => '#e9d5ff', 'icon' => 'fa-user-graduate'];
    }
    return ['bg' => '#fff7ed', 'color' => '#ea580c', 'border' => '#fed7aa', 'icon' => 'fa-award'];
}
?>

<div class="page-header" style="margin-bottom: 20px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-chalkboard-user" style="color: var(--primary);"></i>
            Kelola Rombongan Belajar (Kelas) & Wali Kelas
        </h1>
        <div class="page-subtitle">
            Manajemen rombel siswa, penugasan wali kelas, serta otomasi kenaikan kelas peserta didik antar tahun ajaran.
        </div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php if ($canEdit || $isYayasan): ?>
            <!-- Tombol Tambah Kelas -->
            <button type="button" class="btn btn-primary" onclick="openClassModal()">
                <i class="fa-solid fa-plus"></i> Tambah Kelas Baru
            </button>
        <?php endif; ?>

        <!-- Tombol Penempatan Siswa -->
        <a href="<?= $baseUrl ?>/classes/placement" class="btn btn-outline" style="font-weight: 600;">
            <i class="fa-solid fa-users-gear"></i> Penempatan Siswa
        </a>

        <!-- Switcher Tampilan (Kartu / Tabel) -->
        <div class="btn-group" style="display: inline-flex; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
            <button type="button" id="btnViewCard" class="btn btn-sm" onclick="switchView('card')" style="background: var(--primary); color: white; border: none; border-radius: 0; padding: 7px 12px;" title="Tampilan Kartu">
                <i class="fa-solid fa-table-cells-large"></i>
            </button>
            <button type="button" id="btnViewTable" class="btn btn-sm" onclick="switchView('table')" style="background: #ffffff; color: var(--text-muted); border: none; border-radius: 0; padding: 7px 12px;" title="Tampilan Tabel">
                <i class="fa-solid fa-table-list"></i>
            </button>
        </div>
    </div>
</div>

<!-- Baris Statistik Ringkas -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; background: #ffffff;">
        <div style="width: 46px; height: 46px; border-radius: 12px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-chalkboard"></i>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Total Rombel</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--text-main); line-height: 1.2;">
                <?= count($classes) ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">Kelas</span>
            </div>
        </div>
    </div>

    <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; background: #ffffff;">
        <div style="width: 46px; height: 46px; border-radius: 12px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-user-graduate"></i>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Siswa di Rombel</div>
            <div style="font-size: 20px; font-weight: 800; color: #059669; line-height: 1.2;">
                <?= (int)$totalStudentsInClasses ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">Siswa Aktif</span>
            </div>
        </div>
    </div>

    <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; background: #ffffff;">
        <div style="width: 46px; height: 46px; border-radius: 12px; background: #faf5ff; color: #7e22ce; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Wali Kelas Aktif</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--text-main); line-height: 1.2;">
                <?= (int)$assignedWalasCount ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">dari <?= count($classes) ?> Kelas</span>
            </div>
        </div>
    </div>

    <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; background: #ffffff;">
        <div style="width: 46px; height: 46px; border-radius: 12px; background: #fff7ed; color: #ea580c; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-regular fa-calendar-check"></i>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Tahun Ajaran Aktif</div>
            <div style="font-size: 13px; font-weight: 800; color: #ea580c; line-height: 1.2; margin-top: 2px;">
                <?= SecurityHelper::escape($currentTa['name'] ?? '-') ?> - <?= SecurityHelper::escape($currentTa['semester'] ?? '-') ?>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 1. TAMPILAN KARTU KELAS (CARD VIEW)        -->
<!-- ========================================== -->
<div id="classesCardView">
    <?php if (empty($classes)): ?>
        <div class="card" style="padding: 48px; text-align: center; color: var(--text-muted);">
            <i class="fa-solid fa-chalkboard" style="font-size: 48px; margin-bottom: 12px; opacity: 0.4;"></i>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Belum Ada Rombongan Belajar</h3>
            <p style="font-size: 13px; max-width: 420px; margin: 0 auto 16px;">
                Tambahkan kelas baru atau gunakan fungsi Kenaikan Kelas dari tahun ajaran sebelumnya.
            </p>
            <?php if ($canEdit || $isYayasan): ?>
                <button type="button" class="btn btn-primary" onclick="openClassModal()">
                    <i class="fa-solid fa-plus"></i> Tambah Kelas Pertama
                </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            <?php foreach ($classes as $c): 
                $badgeStyle = getLevelBadgeStyle($c['level']);
                $hasWalas = !empty($c['walas_name']);
                $initials = '';
                if ($hasWalas) {
                    $parts = explode(' ', trim($c['walas_name']));
                    $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
                }
            ?>
                <div class="card" style="border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
                    <!-- Header Kartu -->
                    <div style="padding: 16px 18px; border-bottom: 1px solid var(--border-color); background: #ffffff; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; background: <?= $badgeStyle['bg'] ?>; color: <?= $badgeStyle['color'] ?>; border: 1px solid <?= $badgeStyle['border'] ?>; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="fa-solid <?= $badgeStyle['icon'] ?>"></i>
                            <?= SecurityHelper::escape($c['level']) ?>
                        </span>
                        
                        <?php if ($canEdit || $isYayasan): ?>
                            <button type="button" class="btn btn-outline btn-sm" onclick='editClass(<?= json_encode($c) ?>)' title="Edit Nama / Wali Kelas" style="padding: 3px 8px; font-size: 11px;">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Isi Kartu -->
                    <div style="padding: 18px; flex: 1; display: flex; flex-direction: column; gap: 14px;">
                        <!-- Nama Kelas -->
                        <div>
                            <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3;">
                                <?= SecurityHelper::escape($c['name']) ?>
                            </h3>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 3px;">
                                <i class="fa-solid fa-school" style="margin-right: 4px; font-size: 11px;"></i>
                                Rombel <?= SecurityHelper::escape($unit['name'] ?? 'Unit') ?>
                            </div>
                        </div>

                        <!-- Info Wali Kelas -->
                        <div style="padding: 12px; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; display: flex; align-items: center; gap: 12px;">
                            <?php if ($hasWalas): ?>
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 4px rgba(37,99,235,0.2);">
                                    <?= $initials ?>
                                </div>
                                <div style="min-width: 0; flex: 1;">
                                    <div style="font-size: 10px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Wali Kelas</div>
                                    <div style="font-size: 13px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= SecurityHelper::escape($c['walas_name']) ?>">
                                        <?= SecurityHelper::escape($c['walas_name']) ?>
                                    </div>
                                    <?php if (!empty($c['walas_phone'])): ?>
                                        <div style="font-size: 11px; color: #0284c7; margin-top: 1px;">
                                            <i class="fa-solid fa-phone" style="font-size: 9px;"></i> <?= SecurityHelper::escape($c['walas_phone']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <div style="font-size: 10px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Wali Kelas</div>
                                    <div style="font-size: 12px; font-weight: 600; color: #d97706;">Wali kelas belum dipilih</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Bar Kapasitas Siswa -->
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-bottom: 6px;">
                                <span style="font-weight: 600; color: var(--text-main);">Jumlah Siswa</span>
                                <span style="font-weight: 800; color: #2563eb;"><?= (int)$c['student_count'] ?> Siswa</span>
                            </div>
                            <?php 
                                $percent = min(100, round(((int)$c['student_count'] / 32) * 100));
                            ?>
                            <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                <div style="width: <?= $percent ?>%; height: 100%; background: <?= $percent > 0 ? '#3b82f6' : '#cbd5e1' ?>; border-radius: 3px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Kartu: Tombol Aksi -->
                    <div style="padding: 12px 18px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; gap: 8px;">
                        <a href="<?= $baseUrl ?>/classes/placement?class_id=<?= $c['id'] ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center; font-weight: 600; color: var(--primary);">
                            <i class="fa-solid fa-users"></i> Atur Siswa
                        </a>
                        <?php if ($canEdit || $isYayasan): ?>
                            <button type="button" class="btn btn-outline btn-sm" onclick='editClass(<?= json_encode($c) ?>)' title="Ubah data rombel">
                                <i class="fa-solid fa-gear"></i>
                            </button>
                            <button type="button" class="btn btn-outline btn-sm text-danger" onclick='confirmDeleteClass(<?= (int)$c['id'] ?>, <?= json_encode($c['name']) ?>, <?= (int)$c['student_count'] ?>)' title="Hapus rombel" style="color: #dc2626; border-color: #fca5a5;">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================== -->
<!-- 2. TAMPILAN TABEL KELAS (TABLE VIEW)       -->
<!-- ========================================== -->
<div id="classesTableView" style="display: none;">
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Nama Rombel</th>
                        <th>Tingkat / Jenjang</th>
                        <th>Wali Kelas</th>
                        <th style="text-align: center;">Jumlah Siswa</th>
                        <th style="text-align: center; width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($classes)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">Belum ada kelas di unit ini.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($classes as $c): 
                            $bStyle = getLevelBadgeStyle($c['level']);
                        ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++ ?></td>
                                <td>
                                    <strong><?= SecurityHelper::escape($c['name']) ?></strong>
                                </td>
                                <td>
                                    <span style="font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 12px; background: <?= $bStyle['bg'] ?>; color: <?= $bStyle['color'] ?>; border: 1px solid <?= $bStyle['border'] ?>;">
                                        <?= SecurityHelper::escape($c['level']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($c['walas_name'])): ?>
                                        <i class="fa-solid fa-user-tie" style="color: var(--primary); margin-right: 6px;"></i>
                                        <strong><?= SecurityHelper::escape($c['walas_name']) ?></strong>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="font-size: 11px; font-weight: 600;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Wali kelas belum dipilih
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-info"><?= (int)$c['student_count'] ?> Siswa</span>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <a href="<?= $baseUrl ?>/classes/placement?class_id=<?= $c['id'] ?>" class="btn btn-outline btn-sm" title="Kelola Penempatan Siswa">
                                            <i class="fa-solid fa-users"></i>
                                        </a>
                                        <?php if ($canEdit || $isYayasan): ?>
                                            <button type="button" class="btn btn-outline btn-sm" onclick='editClass(<?= json_encode($c) ?>)' title="Edit Rombel">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-outline btn-sm text-danger" onclick='confirmDeleteClass(<?= (int)$c['id'] ?>, <?= json_encode($c['name']) ?>, <?= (int)$c['student_count'] ?>)' title="Hapus Rombel" style="color: #dc2626; border-color: #fca5a5;">
                                                <i class="fa-solid fa-trash-can"></i> Hapus
                                            </button>
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

<!-- ========================================== -->
<!-- 3. MODAL TAMBAH / EDIT KELAS               -->
<!-- ========================================== -->
<?php if ($canEdit || $isYayasan): ?>
<div class="modal-overlay" id="classModal">
    <div class="modal-container" style="max-width: 500px;">
        <form method="POST" action="<?= $baseUrl ?>/classes/save">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="cls_id" value="0">
            <?php if ($isYayasan): ?>
                <input type="hidden" name="unit_id" value="<?= (int)$unitId ?>">
            <?php endif; ?>
            
            <div class="modal-header">
                <h3 class="card-title" id="modalClsTitle">Tambah Kelas Baru</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeClassModal()">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Nama Kelas -->
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 700;">
                        Nama Rombongan Belajar (Kelas) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="name" id="cls_name" class="form-control" required placeholder="Contoh: Kelas 1A Abu Bakar / Kelas 7A">
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Nama lengkap rombel beserta abjad atau gelar kelas.
                    </div>
                </div>

                <!-- Tingkat / Jenjang FIXED SELECT -->
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 700;">
                        Tingkat / Jenjang Rombel <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="level" id="cls_level" class="form-control" required>
                        <option value="">-- Pilih Tingkat Kelas --</option>
                        <?php if (!empty($fixedLevels)): ?>
                            <optgroup label="Tingkat Resmi Unit <?= SecurityHelper::escape($unit['code'] ?? '') ?> (<?= SecurityHelper::escape($unitLevel) ?>)">
                                <?php foreach ($fixedLevels as $fl): ?>
                                    <option value="<?= SecurityHelper::escape($fl) ?>"><?= SecurityHelper::escape($fl) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        
                        <?php if ($isYayasan): ?>
                            <?php foreach ($allFixedLevels as $groupJenjang => $levels): ?>
                                <?php if ($groupJenjang !== $unitLevel): ?>
                                    <optgroup label="Jenjang <?= $groupJenjang ?>">
                                        <?php foreach ($levels as $fl): ?>
                                            <option value="<?= SecurityHelper::escape($fl) ?>"><?= SecurityHelper::escape($fl) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Tingkat telah distandarkan tetap (KB, TK A/B, SD Kelas 1-6, SMP 7-9, SMA 10-12) untuk otomasi kenaikan kelas.
                    </div>
                </div>

                <!-- Guru Wali Kelas -->
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 700;">Guru Wali Kelas</label>
                    <select name="walas_id" id="cls_walas_id" class="form-control">
                        <option value="">-- Belum Ditugaskan / Pilih Nanti --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= SecurityHelper::escape($t['nama']) ?> (<?= SecurityHelper::escape($t['level_posisi']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeClassModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Kelas</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ========================================== -->
<!-- 4. MODAL KENAIKAN KELAS (PROMOSI ROMBEL)   -->
<!-- ========================================== -->
<div class="modal-overlay" id="promoteModal">
    <div class="modal-container" style="max-width: 760px;">
        <form method="POST" action="<?= $baseUrl ?>/classes/promote" onsubmit="return confirm('Apakah Anda yakin ingin memproses kenaikan kelas untuk tahun ajaran baru ini? Tindakan ini akan mendaftarkan siswa ke rombel baru.')">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="unit_id" value="<?= (int)$unitId ?>">

            <div class="modal-header">
                <h3 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-graduation-cap" style="color: #4f46e5;"></i>
                    Kenaikan Kelas & Kelulusan Siswa
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closePromoteModal()">&times;</button>
            </div>

            <div class="modal-body">
                <!-- Banner Penjelasan Syarat Kenaikan Kelas -->
                <div style="padding: 14px 16px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px; margin-bottom: 20px; font-size: 13px; color: #3730a3; line-height: 1.5;">
                    <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-info"></i> Ketentuan Otomasi Kenaikan Kelas:
                    </div>
                    <div>
                        Kenaikan kelas berlaku saat perpindahan dari <strong>Semester Genap</strong> ke <strong>Semester Ganjil</strong> di tahun ajaran baru berikutnya.
                        Siswa tingkat akhir (<em>TK B, SD Kelas 6, SMP Kelas 9, SMA Kelas 12</em>) otomatis berstatus <strong>Lulus</strong>, sedangkan siswa tingkat lainnya dipromosikan ke tingkat berikutnya pada rombel baru.
                    </div>
                </div>

                <!-- Pilihan Tahun Ajaran -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700;">Tahun Ajaran Asal (Semester Genap)</label>
                        <select name="from_ta_id" id="promote_from_ta" class="form-control" onchange="fetchPromotionPreview()" required>
                            <option value="">-- Pilih Tahun Ajaran Asal --</option>
                            <?php foreach ($previousGenapYears as $pta): ?>
                                <option value="<?= $pta['id'] ?>">
                                    <?= SecurityHelper::escape($pta['name']) ?> - <?= SecurityHelper::escape($pta['semester']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700;">Tahun Ajaran Tujuan (Aktif)</label>
                        <input type="text" class="form-control" value="<?= SecurityHelper::escape($currentTa['name'] ?? '-') ?> - <?= SecurityHelper::escape($currentTa['semester'] ?? '-') ?>" readonly style="background: #f1f5f9; font-weight: 600;">
                    </div>
                </div>

                <!-- Kotak Pratinjau Promosi Rombel -->
                <div id="promotePreviewContainer">
                    <div style="padding: 30px; text-align: center; color: var(--text-muted); background: #f8fafc; border: 1px dashed var(--border-color); border-radius: 10px;">
                        <i class="fa-solid fa-list-check" style="font-size: 32px; margin-bottom: 8px; opacity: 0.5;"></i>
                        <div>Silakan pilih <strong>Tahun Ajaran Asal (Genap)</strong> di atas untuk memuat daftar rombel yang akan dipromosikan.</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn btn-outline" onclick="closePromoteModal()">Tutup</button>
                <button type="submit" id="btnSubmitPromote" class="btn btn-primary" style="background: #4f46e5; border-color: #4338ca;" disabled>
                    <i class="fa-solid fa-check-double"></i> Konfirmasi & Eksekusi Kenaikan Kelas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- 5. MODAL HAPUS KELAS / ROMBEL              -->
<!-- ========================================== -->
<?php if ($canEdit || $isYayasan): ?>
<div class="modal-overlay" id="deleteClassModal">
    <div class="modal-container" style="max-width: 520px;">
        <form method="POST" action="<?= $baseUrl ?>/classes/delete">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="del_cls_id" value="0">
            
            <div class="modal-header" style="background: #fef2f2; border-bottom: 1px solid #fee2e2;">
                <h3 class="card-title" style="color: #b91c1c;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Hapus Rombongan Belajar (Kelas)
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeDeleteClassModal()">&times;</button>
            </div>
            
            <div class="modal-body" style="padding: 20px;">
                <p style="font-size: 14px; color: #1e293b; margin-bottom: 14px;">
                    Apakah Anda yakin ingin menghapus kelas <strong id="del_cls_name" style="color: #b91c1c;"></strong>?
                </p>
                
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase;">
                        <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> Pembersihan Data Database Terintegrasi:
                    </div>
                    <ul style="font-size: 12px; color: #64748b; line-height: 1.6; margin: 0; padding-left: 18px;">
                        <li>Penempatan siswa (<strong id="del_cls_student_count">0</strong> siswa) pada kelas ini akan dicabut. <em>Data master siswa tetap aman dan statusnya menjadi unassigned/siap ditempatkan kembali.</em></li>
                        <li>Seluruh data terkait: <strong>jadwal pelajaran kelas</strong>, <strong>penugasan guru mapel</strong>, <strong>absensi</strong>, <strong>nilai</strong>, dan <strong>supervisi kelas</strong> ini akan dibersihkan secara tuntas dari SQL tanpa menyisakan data menggantung.</li>
                    </ul>
                </div>

                <div class="alert alert-danger" style="font-size: 12px; margin-bottom: 0; padding: 10px 14px; border-radius: 6px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;">
                    <i class="fa-solid fa-circle-exclamation"></i> Tindakan ini permanen. Pastikan Anda telah memeriksa kembali rombel yang akan dihapus.
                </div>
            </div>
            
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--border-color); padding: 12px 20px;">
                <button type="button" class="btn btn-outline" onclick="closeDeleteClassModal()">Batal</button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; border-color: #b91c1c; color: white;">
                    <i class="fa-solid fa-trash-can"></i> Ya, Hapus Kelas Ini
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
// Modal Hapus Kelas
function confirmDeleteClass(id, name, studentCount) {
    document.getElementById('del_cls_id').value = id;
    document.getElementById('del_cls_name').innerText = name;
    document.getElementById('del_cls_student_count').innerText = studentCount;
    document.getElementById('deleteClassModal').classList.add('active');
}

function closeDeleteClassModal() {
    document.getElementById('deleteClassModal').classList.remove('active');
}
// Switcher tampilan Kartu / Tabel
function switchView(mode) {
    var cardView = document.getElementById('classesCardView');
    var tableView = document.getElementById('classesTableView');
    var btnCard = document.getElementById('btnViewCard');
    var btnTable = document.getElementById('btnViewTable');

    if (mode === 'table') {
        cardView.style.display = 'none';
        tableView.style.display = 'block';
        btnTable.style.background = 'var(--primary)';
        btnTable.style.color = '#ffffff';
        btnCard.style.background = '#ffffff';
        btnCard.style.color = 'var(--text-muted)';
    } else {
        cardView.style.display = 'block';
        tableView.style.display = 'none';
        btnCard.style.background = 'var(--primary)';
        btnCard.style.color = '#ffffff';
        btnTable.style.background = '#ffffff';
        btnTable.style.color = 'var(--text-muted)';
    }
}

// Modal Form Kelas
function openClassModal() {
    document.getElementById('cls_id').value = '0';
    document.getElementById('modalClsTitle').innerText = 'Tambah Kelas Baru';
    document.getElementById('cls_name').value = '';
    document.getElementById('cls_level').value = '';
    document.getElementById('cls_walas_id').value = '';
    document.getElementById('classModal').classList.add('active');
}

function editClass(c) {
    document.getElementById('cls_id').value = c.id;
    document.getElementById('modalClsTitle').innerText = 'Edit Kelas: ' + c.name;
    document.getElementById('cls_name').value = c.name;
    document.getElementById('cls_level').value = c.level;
    document.getElementById('cls_walas_id').value = c.walas_id || '';
    document.getElementById('classModal').classList.add('active');
}

function closeClassModal() {
    document.getElementById('classModal').classList.remove('active');
}

// Modal Kenaikan Kelas
function openPromoteModal() {
    document.getElementById('promoteModal').classList.add('active');
}

function closePromoteModal() {
    document.getElementById('promoteModal').classList.remove('active');
}

function fetchPromotionPreview() {
    var fromTaId = document.getElementById('promote_from_ta').value;
    var container = document.getElementById('promotePreviewContainer');
    var btnSubmit = document.getElementById('btnSubmitPromote');

    if (!fromTaId) {
        container.innerHTML = '<div style="padding: 30px; text-align: center; color: var(--text-muted); background: #f8fafc; border: 1px dashed var(--border-color); border-radius: 10px;">Silakan pilih Tahun Ajaran Asal (Genap).</div>';
        btnSubmit.disabled = true;
        return;
    }

    container.innerHTML = '<div style="padding: 30px; text-align: center; color: var(--primary);"><i class="fa-solid fa-spinner fa-spin" style="font-size: 24px;"></i> Memuat pratinjau kenaikan kelas...</div>';

    fetch('<?= $baseUrl ?>/classes/promote-preview?from_ta_id=' + fromTaId + '&unit_id=<?= (int)$unitId ?>')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data.success) {
                container.innerHTML = '<div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> ' + (data.message || 'Gagal memuat pratinjau') + '</div>';
                btnSubmit.disabled = true;
                return;
            }

            if (!data.classes || data.classes.length === 0) {
                container.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--text-muted); background: #f8fafc; border-radius: 8px;">Tidak ditemukan rombel di tahun ajaran asal tersebut.</div>';
                btnSubmit.disabled = true;
                return;
            }

            var html = '<div style="max-height: 340px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">';
            var totalStudents = 0;

            data.classes.forEach(function(cls) {
                totalStudents += cls.students ? cls.students.length : 0;
                var isGrad = cls.is_graduating;

                html += '<div style="padding: 14px; border: 1px solid var(--border-color); border-radius: 10px; background: ' + (isGrad ? '#f0fdf4' : '#ffffff') + ';">';
                html += '  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">';
                html += '    <div>';
                html += '      <strong style="font-size: 14px; color: var(--text-main);">' + cls.current_name + '</strong>';
                html += '      <span style="font-size: 11px; margin-left: 6px; padding: 2px 8px; border-radius: 10px; background: #e2e8f0; color: #475569;">' + cls.current_level + '</span>';
                html += '    </div>';
                html += '    <div style="font-size: 12px; font-weight: 700; color: ' + (isGrad ? '#15803d' : '#2563eb') + ';">';
                html += '      <i class="fa-solid fa-arrow-right" style="margin-right: 4px;"></i> ' + (isGrad ? '<i class="fa-solid fa-graduation-cap"></i> LULUS' : cls.target_name);
                html += '    </div>';
                html += '  </div>';

                html += '  <div style="font-size: 12px; color: var(--text-muted); display: flex; justify-content: space-between; align-items: center;">';
                html += '    <span>Total: <strong>' + (cls.students ? cls.students.length : 0) + ' Siswa</strong></span>';
                if (!isGrad && cls.students && cls.students.length > 0) {
                    html += '    <span style="font-size: 11px; color: #64748b;">(Semua siswa otomatis naik ke ' + cls.target_level + ')</span>';
                }
                html += '  </div>';
                html += '</div>';
            });

            html += '</div>';
            html += '<div style="margin-top: 12px; padding: 10px 14px; background: #f8fafc; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--text-main); display: flex; justify-content: space-between;">';
            html += '  <span>Total Rombel: ' + data.classes.length + ' Kelas</span>';
            html += '  <span style="color: #2563eb;">Total Siswa: ' + totalStudents + ' Siswa</span>';
            html += '</div>';

            container.innerHTML = html;
            btnSubmit.disabled = false;
        })
        .catch(function(err) {
            container.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan saat memuat pratinjau kenaikan kelas.</div>';
            btnSubmit.disabled = true;
        });
}
</script>
