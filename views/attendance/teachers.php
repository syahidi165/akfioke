<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$totalGuru = count($teachers ?? []);
$filledCount = 0;
$totalKajianHadir = 0;
$totalKajianSesi = 0;

foreach ($teachers as $t) {
    if (isset($t['hadir']) && $t['hadir'] !== null) {
        $filledCount++;
    }
    $totalKajianHadir += (int)($t['kajian_hadir'] ?? 0);
    $totalKajianSesi += (int)($t['kajian_total'] ?? 0);
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-success"><i class="fa-solid fa-user-pen"></i> Tata Usaha</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Pengelolaan Presensi Guru &amp; Kajian</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-clipboard-user" style="color: #0d9488;"></i> Presensi Kehadiran Guru &amp; Kajian Sabtu
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pencatatan presensi dinas KBM, ketepatan waktu, dan kehadiran kajian rutin Sabtu per bulan untuk indikator AKFI Guru.
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="<?= $baseUrl ?>/attendance/teachers/template?month_num=<?= $monthNum ?>" class="btn btn-outline" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-file-excel" style="color: #10b981;"></i> Unduh Format Excel (Guru Pre-filled)
            </a>
            <button type="button" class="btn btn-primary" onclick="openImportGuruModal()" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-file-import"></i> Import Presensi Excel
            </button>
            <button type="button" class="btn btn-outline" onclick="quickFillAll()" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-wand-magic-sparkles" style="color: #0d9488;"></i> Isi Cepat Default
            </button>
        </div>
    </div>
</div>

<!-- 12-Month Visual Timeline Tracker -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="padding: 14px 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="margin: 0; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-calendar-days" style="color: #0d9488;"></i> Status Pengisian Presensi 12 Bulan (Tahun Ajaran Berjalan)
        </h3>
        <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Klik pada bulan untuk beralih</span>
    </div>
    <div class="card-body" style="padding: 16px 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(85px, 1fr)); gap: 10px;">
            <?php foreach ($months as $mNum => $mName): 
                $isCurrent = ($mNum === (int)$monthNum);
                $isFilled = !empty($filledMonths[$mNum]);
                $recCount = $filledMonths[$mNum] ?? 0;

                $bg = '#f8fafc';
                $border = '#e2e8f0';
                $textColor = '#64748b';
                $icon = 'fa-regular fa-circle';
                $statusText = 'Belum Ada';

                if ($isFilled) {
                    $bg = '#f0fdf4';
                    $border = '#bbf7d0';
                    $textColor = '#166534';
                    $icon = 'fa-solid fa-circle-check';
                    $statusText = 'Terisi';
                }

                if ($isCurrent) {
                    $border = '#0d9488';
                    $bg = $isFilled ? '#ccfbf1' : '#f0fdfa';
                }
            ?>
                <a href="<?= $baseUrl ?>/attendance/teachers?month_num=<?= $mNum ?>" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 10px 6px; border-radius: 8px; border: 2px solid <?= $border ?>; background: <?= $bg ?>; transition: all 0.2s ease; cursor: pointer; text-align: center; position: relative;">
                    <?php if ($isCurrent): ?>
                        <span style="position: absolute; top: -8px; font-size: 0.65rem; background: #0d9488; color: #fff; padding: 1px 6px; border-radius: 999px; font-weight: 600;">Aktif</span>
                    <?php endif; ?>
                    <div style="font-weight: 700; font-size: 0.875rem; color: <?= $isCurrent ? '#0d9488' : '#1e293b' ?>; margin-top: <?= $isCurrent ? '4px' : '0' ?>;">
                        <?= substr($mName, 0, 3) ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px; font-size: 0.72rem; color: <?= $textColor ?>; margin-top: 4px; font-weight: 500;">
                        <i class="<?= $icon ?>" style="font-size: 0.75rem;"></i> <?= $statusText ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-user-check" style="color: #0d9488; font-size: 1.1rem;"></i>
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Presensi Bulan: <strong style="color: #0d9488;"><?= $months[$monthNum] ?? "Bulan $monthNum" ?></strong></h3>
                <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Total <?= $totalGuru ?> Guru di Satuan Unit</div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                <input type="text" id="searchTeacher" class="form-control" placeholder="Cari nama guru..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 200px;">
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <form action="<?= $baseUrl ?>/attendance/teachers/save" method="POST" id="attendanceForm">
            <?= CSRF::field() ?>
            <input type="hidden" name="month_num" value="<?= $monthNum ?>">

            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 45px; text-align: center; vertical-align: middle;">No</th>
                            <th rowspan="2" style="min-width: 220px; vertical-align: middle;">Nama Lengkap &amp; Jabatan Guru</th>
                            <th colspan="7" style="text-align: center; border-bottom: 1px solid var(--border-color, #e2e8f0); background: #f8fafc;">Presensi KBM &amp; Kedisiplinan Kerja (Hari / Kali)</th>
                            <th colspan="2" style="text-align: center; border-bottom: 1px solid var(--border-color, #e2e8f0); background: #f0fdf4;">Kajian Rutin Sabtu</th>
                        </tr>
                        <tr>
                            <th style="min-width: 90px; width: 90px; text-align: center; background: #f8fafc; color: #16a34a;">Hadir</th>
                            <th style="min-width: 80px; width: 80px; text-align: center; background: #f8fafc; color: #0284c7;">Sakit</th>
                            <th style="min-width: 80px; width: 80px; text-align: center; background: #f8fafc; color: #d97706;">Izin</th>
                            <th style="min-width: 95px; width: 95px; text-align: center; background: #f8fafc; color: #ef4444;" title="Tidak Hadir / Mangkir / Alpha">Tidak Hadir</th>
                            <th style="min-width: 95px; width: 95px; text-align: center; background: #f8fafc; color: #ea580c;" title="Terlambat Masuk Kerja / KBM (Hari/Kali)">Terlambat</th>
                            <th style="min-width: 105px; width: 105px; text-align: center; background: #f8fafc; color: #b45309;" title="Pulang Cepat Sebelum Waktu Selesai (Hari/Kali)">Pulang Cepat</th>
                            <th style="min-width: 100px; width: 100px; text-align: center; background: #f8fafc; color: #7c3aed;" title="Lupa Catat / Absen Datang/Pulang (Hari/Kali)">Lupa Catat</th>
                            <th style="min-width: 85px; width: 85px; text-align: center; background: #f0fdf4; color: #166534;">Hadir</th>
                            <th style="min-width: 85px; width: 85px; text-align: center; background: #f0fdf4; color: #166534;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teachers)): ?>
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-users-slash" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada guru terdaftar di satuan unit ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teachers as $idx => $t): ?>
                                <tr class="teacher-row" data-search="<?= strtolower(SecurityHelper::escape($t['nama'] . ' ' . $t['position'])) ?>">
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                            <?= SecurityHelper::escape($t['nama']) ?>
                                        </div>
                                        <div style="font-size: 0.78125rem; color: var(--text-muted, #64748b);">
                                            <?= SecurityHelper::escape($t['position']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $t['id'] ?>][hadir]" class="form-control att-hadir" value="<?= $t['hadir'] ?? 20 ?>" style="text-align: center; font-weight: 600; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px;">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $t['id'] ?>][sakit]" class="form-control att-sakit" value="<?= $t['sakit'] ?? 0 ?>" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px;">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $t['id'] ?>][izin]" class="form-control att-izin" value="<?= $t['izin'] ?? 0 ?>" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px;">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $t['id'] ?>][alpha]" class="form-control att-alpha" value="<?= $t['mangkir'] ?? $t['alpha'] ?? 0 ?>" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px; color: #ef4444; font-weight: 600;" title="Tidak Hadir / Mangkir (Hari)">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $t['id'] ?>][terlambat]" class="form-control att-terlambat" value="<?= $t['terlambat'] ?? ($t['terlambat_menit'] > 0 ? 1 : 0) ?>" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px; color: #ea580c; font-weight: 600;" placeholder="Hari/x" title="Terlambat (Hari/Kali)">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $t['id'] ?>][pulang_cepat]" class="form-control att-pulang-cepat" value="<?= $t['pulang_cepat'] ?? 0 ?>" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px; color: #b45309; font-weight: 600;" placeholder="Hari/x" title="Pulang Cepat (Hari/Kali)">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="31" name="att[<?= $t['id'] ?>][lupa_catat]" class="form-control att-lupa-catat" value="<?= $t['lupa_catat'] ?? 0 ?>" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px; color: #7c3aed; font-weight: 600;" placeholder="Hari/x" title="Lupa Catat (Hari/Kali)">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" name="att[<?= $t['id'] ?>][kajian_hadir]" class="form-control att-kajian-hadir" value="<?= $t['kajian_hadir'] ?? 4 ?>" style="text-align: center; font-weight: 600; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px; color: #0d9488;">
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="5" name="att[<?= $t['id'] ?>][kajian_total]" class="form-control att-kajian-total" value="<?= $t['kajian_total'] ?? 4 ?>" style="text-align: center; height: 38px; min-width: 65px; padding: 4px 6px; font-size: 14px;">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="padding: 16px 20px; background: #f8fafc; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">
                    <i class="fa-solid fa-circle-info" style="color: #0d9488; margin-right: 4px;"></i>
                    Presensi yang disimpan akan otomatis terintegrasi ke kalkulasi AKFI Guru komponen Kehadiran &amp; Kajian.
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Presensi <?= $months[$monthNum] ?? "Bulan $monthNum" ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const search = document.getElementById('searchTeacher');
    const rows = document.querySelectorAll('.teacher-row');
    if (search) {
        search.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            rows.forEach(function(row) {
                const text = row.getAttribute('data-search') || '';
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }
});

function quickFillAll() {
    if (!confirm('Isi semua guru dengan nilai standar (Hadir: 20, Sakit: 0, Izin: 0, Alpha: 0, Terlambat: 0, Kajian: 4/4)?')) {
        return;
    }
    document.querySelectorAll('.att-hadir').forEach(el => el.value = 20);
    document.querySelectorAll('.att-sakit').forEach(el => el.value = 0);
    document.querySelectorAll('.att-izin').forEach(el => el.value = 0);
    document.querySelectorAll('.att-alpha').forEach(el => el.value = 0);
    document.querySelectorAll('.att-terlambat').forEach(el => el.value = 0);
    document.querySelectorAll('.att-kajian-hadir').forEach(el => el.value = 4);
    document.querySelectorAll('.att-kajian-total').forEach(el => el.value = 4);
}

function openImportGuruModal() {
    document.getElementById('modalImportGuru').classList.add('active');
}

function closeImportGuruModal() {
    document.getElementById('modalImportGuru').classList.remove('active');
}
</script>

<!-- Modal Import Presensi Guru Excel -->
<div class="modal-overlay" id="modalImportGuru">
    <div class="modal-container" style="max-width: 500px;">
        <form method="POST" action="<?= $baseUrl ?>/attendance/teachers/import" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <div class="modal-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-file-excel" style="color: #10b981; margin-right: 8px;"></i> 
                    Import Presensi Guru by Excel
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeImportGuruModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" style="font-size: 12.5px; line-height: 1.5; margin-bottom: 16px;">
                    <i class="fa-solid fa-circle-info"></i> <strong>Kemudahan untuk TU:</strong> Seluruh nama dan data ID guru pada unit sekolah ini sudah terdaftar di dalam format Excel. Anda cukup mengisi angka kehadiran, sakit, izin, alpha, keterlambatan, dan kajian tanpa perlu mengetik ulang nama guru.
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
                        Gunakan tombol <strong>"Unduh Format Excel (Guru Pre-filled)"</strong> terlebih dahulu untuk mendapatkan template resmi.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeImportGuruModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Unggah &amp; Simpan Presensi</button>
            </div>
        </form>
    </div>
</div>
