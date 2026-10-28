<?php
// views/attendance/staff.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$totalStaff = count($staff);
$totHadir = 0;
$totSakit = 0;
$totIzin = 0;
$totAlpha = 0;
$totKajianHadir = 0;
$totKajianTotal = 0;

foreach ($staff as $s) {
    $totHadir += (int)($s['hadir'] ?? 24);
    $totSakit += (int)($s['sakit'] ?? 0);
    $totIzin += (int)($s['izin'] ?? 0);
    $totAlpha += (int)($s['alpha'] ?? 0);
    $totKajianHadir += (int)($s['kajian_hadir'] ?? 4);
    $totKajianTotal += (int)($s['kajian_total'] ?? 4);
}

$effectiveDays = $totHadir + $totSakit + $totIzin + $totAlpha;
$avgAttendance = $effectiveDays > 0 ? round(($totHadir / $effectiveDays) * 100, 1) : 0;
$avgKajian = $totKajianTotal > 0 ? round(($totKajianHadir / $totKajianTotal) * 100, 1) : 0;
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <span class="badge badge-success"><i class="fa-solid fa-user-pen"></i> Tata Usaha</span>
            <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Staf Non-Guru</span>
        </div>
        <h1 class="page-title">
            <i class="fa-solid fa-id-card-clip" style="color: #0d9488; margin-right: 8px;"></i>
            Presensi Kehadiran Staf TU, CS &amp; Security
        </h1>
        <p class="page-subtitle">
            Pencatatan rekap kehadiran bulanan, ketepatan jam kerja, dan presensi kajian Sabtu untuk staf operasional sekolah.
        </p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="<?= $baseUrl ?>/attendance/staff/template?month_num=<?= $monthNum ?>" class="btn btn-outline" style="font-size: 0.8125rem;">
            <i class="fa-solid fa-file-excel" style="color: #10b981;"></i> Unduh Format Excel (Staf Pre-filled)
        </a>
        <button type="button" class="btn btn-primary" onclick="openImportStafModal()" style="font-size: 0.8125rem;">
            <i class="fa-solid fa-file-import"></i> Import Presensi Staf
        </button>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card" style="border-left: 4px solid #0d9488;">
        <div class="stat-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalStaff ?> <span style="font-size: 0.9rem; font-weight: normal; color: #64748b;">Orang</span></div>
            <div class="stat-label">Total Staf Aktif</div>
        </div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #10b981;">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: #10b981;"><?= $avgAttendance ?>%</div>
            <div class="stat-label">Rata-rata Kehadiran Kerja</div>
        </div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #6366f1;">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
            <i class="fa-solid fa-mosque"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: #6366f1;"><?= $avgKajian ?>%</div>
            <div class="stat-label">Kehadiran Kajian Sabtu</div>
        </div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #ef4444;">
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: <?= ($totAlpha > 0) ? '#ef4444' : '#64748b' ?>;"><?= $totAlpha ?> <span style="font-size: 0.9rem; font-weight: normal; color: #64748b;">Hari</span></div>
            <div class="stat-label">Total Alpha / Mangkir</div>
        </div>
    </div>
</div>

<!-- Filter Bulan Card -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 14px 20px;">
        <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 0.95rem; margin: 0;">
            <i class="fa-solid fa-calendar-days" style="color: #0d9488;"></i> Pilih Bulan Rekapitulasi Presensi
        </h3>
    </div>
    <div class="card-body" style="padding: 16px 20px;">
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <?php foreach ($months as $num => $nm): 
                $isActive = ($num == $monthNum);
            ?>
                <a href="<?= $baseUrl ?>/attendance/staff?month_num=<?= $num ?>" 
                   class="btn <?= $isActive ? 'btn-primary' : 'btn-outline' ?>" 
                   style="padding: 8px 16px; font-size: 0.85rem; font-weight: 600; border-radius: 6px; <?= $isActive ? '' : 'background: white;' ?>">
                    <?= $nm ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-clipboard-user" style="color: #0d9488; font-size: 1.1rem;"></i>
            <h3 class="card-title" style="margin: 0; font-size: 1rem;">
                Lembar Presensi Staf - Bulan <?= $months[$monthNum] ?>
            </h3>
        </div>
        <span class="badge badge-secondary"><?= $totalStaff ?> Staf Terdaftar</span>
    </div>

    <div class="card-body" style="padding: 0;">
        <form action="<?= $baseUrl ?>/attendance/staff/save" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="month_num" value="<?= $monthNum ?>">

            <div style="overflow-x: auto;">
                <table class="table" style="text-align: center;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="width: 45px; text-align: center;" rowspan="2">No</th>
                            <th style="text-align: left; min-width: 200px;" rowspan="2">Nama Staf &amp; NIPY</th>
                            <th style="width: 140px;" rowspan="2">Posisi / Bagian</th>
                            <th colspan="7" style="border-bottom: 1px solid var(--border-color, #e2e8f0); background: #f0fdf4; color: #166534;">Kehadiran &amp; Kedisiplinan Jam Kerja (Hari / Kali)</th>
                            <th colspan="2" style="border-bottom: 1px solid var(--border-color, #e2e8f0); background: #eff6ff; color: #1e40af;">Kajian Sabtu</th>
                        </tr>
                        <tr style="background: #f1f5f9; font-size: 0.8rem;">
                            <th style="min-width: 90px; width: 90px; color: #16a34a;">Hadir</th>
                            <th style="min-width: 80px; width: 80px; color: #0284c7;">Sakit</th>
                            <th style="min-width: 80px; width: 80px; color: #d97706;">Izin</th>
                            <th style="min-width: 95px; width: 95px; color: #dc2626;" title="Tidak Hadir / Mangkir / Alpha">Tidak Hadir</th>
                            <th style="min-width: 95px; width: 95px; color: #ea580c;" title="Terlambat Masuk Kerja (Hari/Kali)">Terlambat</th>
                            <th style="min-width: 105px; width: 105px; color: #b45309;" title="Pulang Cepat Sebelum Jam Kerja Berakhir (Hari/Kali)">Pulang Cepat</th>
                            <th style="min-width: 100px; width: 100px; color: #7c3aed;" title="Lupa Catat / Lupa Absen Datang/Pulang (Hari/Kali)">Lupa Catat</th>
                            <th style="min-width: 85px; width: 85px; color: #4338ca;">Hadir</th>
                            <th style="min-width: 85px; width: 85px; color: #64748b;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($staff)): ?>
                            <tr>
                                <td colspan="12" style="text-align: center; padding: 48px 20px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-users-slash" style="font-size: 2.5rem; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                                    Belum ada data staf operasional (TU, CS, Security) terdaftar di unit ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($staff as $idx => $s): 
                                $pos = $s['position'] ?? $s['level_posisi'] ?? 'Staf';
                                $badgeStyle = 'background: #f1f5f9; color: #475569;';
                                if (stripos($pos, 'Tata Usaha') !== false) {
                                    $badgeStyle = 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;';
                                } elseif (stripos($pos, 'Cleaning') !== false) {
                                    $badgeStyle = 'background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;';
                                } elseif (stripos($pos, 'Security') !== false) {
                                    $badgeStyle = 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;';
                                }
                            ?>
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td style="text-align: left;">
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a);"><?= SecurityHelper::escape($s['nama']) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted, #64748b); font-family: monospace;">NIPY: <?= SecurityHelper::escape($s['nipy'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge" style="font-size: 0.75rem; padding: 4px 8px; <?= $badgeStyle ?>">
                                            <?= SecurityHelper::escape($pos) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $s['id'] ?>][hadir]" 
                                               class="form-control" style="text-align: center; font-weight: 700; color: #16a34a; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px;" 
                                               value="<?= $s['hadir'] ?? 24 ?>" required>
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $s['id'] ?>][sakit]" 
                                               class="form-control" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px;" 
                                               value="<?= $s['sakit'] ?? 0 ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $s['id'] ?>][izin]" 
                                               class="form-control" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px;" 
                                               value="<?= $s['izin'] ?? 0 ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $s['id'] ?>][alpha]" 
                                               class="form-control" style="text-align: center; font-weight: 700; color: #dc2626; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px;" 
                                               value="<?= $s['mangkir'] ?? $s['alpha'] ?? 0 ?>" title="Tidak Hadir / Mangkir (Hari)">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $s['id'] ?>][terlambat]" 
                                               class="form-control" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px; color: #ea580c; font-weight: 600;" 
                                               value="<?= $s['terlambat'] ?? ($s['terlambat_menit'] > 0 ? 1 : 0) ?>" placeholder="Hari/x" title="Terlambat (Hari/Kali)">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $s['id'] ?>][pulang_cepat]" 
                                               class="form-control" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px; color: #b45309; font-weight: 600;" 
                                               value="<?= $s['pulang_cepat'] ?? 0 ?>" placeholder="Hari/x" title="Pulang Cepat (Hari/Kali)">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $s['id'] ?>][lupa_catat]" 
                                               class="form-control" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px; color: #7c3aed; font-weight: 600;" 
                                               value="<?= $s['lupa_catat'] ?? 0 ?>" placeholder="Hari/x" title="Lupa Catat (Hari/Kali)">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" name="att[<?= $s['id'] ?>][kajian_hadir]" 
                                               class="form-control" style="text-align: center; font-weight: 700; color: #4338ca; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px;" 
                                               value="<?= $s['kajian_hadir'] ?? 4 ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" name="att[<?= $s['id'] ?>][kajian_total]" 
                                               class="form-control" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px;" 
                                               value="<?= $s['kajian_total'] ?? 4 ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($staff)): ?>
                <div style="padding: 16px 20px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; align-items: center; background: #fafafa;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Presensi Staf
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Modal Import Presensi Staf Excel -->
<div class="modal-overlay" id="modalImportStaf">
    <div class="modal-container" style="max-width: 500px;">
        <form method="POST" action="<?= $baseUrl ?>/attendance/staff/import" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <div class="modal-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-file-excel" style="color: #10b981; margin-right: 8px;"></i> 
                    Import Presensi Staf by Excel
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeImportStafModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" style="font-size: 12.5px; line-height: 1.5; margin-bottom: 16px;">
                    <i class="fa-solid fa-circle-info"></i> <strong>Kemudahan untuk TU:</strong> Seluruh nama pegawai non-guru (TU, Cleaning Service, Security) sudah terisi otomatis pada template Excel yang diunduh. Anda tidak perlu mengetik nama staf lagi secara manual.
                </div>
                <div class="form-group">
                    <label class="form-label">Pilih Periode Bulan Presensi</label>
                    <select name="month_num" class="form-control" required>
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= $num == $monthNum ? 'selected' : '' ?>>
                                Bulan <?= $num ?> &ndash; <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Pilih Berkas Excel (.xlsx / .xls)</label>
                    <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Gunakan tombol <strong>"Unduh Format Excel (Staf Pre-filled)"</strong> terlebih dahulu untuk mendapatkan template resmi.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeImportStafModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Unggah &amp; Simpan Presensi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openImportStafModal() {
    document.getElementById('modalImportStaf').classList.add('active');
}
function closeImportStafModal() {
    document.getElementById('modalImportStaf').classList.remove('active');
}
</script>
