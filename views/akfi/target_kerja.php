<?php
// views/akfi/target_kerja.php
use Core\SecurityHelper;
use Core\Auth;

$activeTaId = $activeTa['id'] ?? 0;
$activeWorkdays = (int)($activeTa['target_workdays'] ?? 264);
$activeKajian   = (int)($activeTa['target_kajian'] ?? 48);
?>

<!-- Executive Banner -->
<div class="card" style="background: linear-gradient(135deg, #0f766e 0%, #1e3a8a 100%); color: #ffffff; border: none; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(15, 118, 110, 0.15);">
    <div class="card-body" style="padding: 24px 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: rgba(255, 255, 255, 0.15); border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                    <i class="fa-solid fa-crown" style="color: #fde047;"></i> Otoritas Deputi Pendidikan
                </div>
                <h1 style="font-size: 22px; font-weight: 700; margin: 0 0 6px 0; color: #ffffff; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-business-time"></i>
                    Pengaturan Target Hari Kerja & Kajian Tahunan
                </h1>
                <p style="font-size: 13px; color: #e2e8f0; margin: 0; max-width: 680px; line-height: 1.5;">
                    Atur standar akumulasi hari dinas efektif dan pertemuan kajian Sabtu per tahun ajaran. Nilai ini menjadi basis otomatis bagi <strong>Auto-Data Collector AKFI</strong> untuk menghitung persentase kehadiran pegawai dan satuan pendidikan.
                </p>
            </div>
            <div style="text-align: right; background: rgba(255, 255, 255, 0.1); padding: 12px 18px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                <div style="font-size: 11px; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Tahun Ajaran Berjalan</div>
                <div style="font-size: 18px; font-weight: 800; color: #ffffff; margin-top: 2px;">
                    <?= SecurityHelper::escape($activeTa['name'] ?? '-') ?>
                </div>
                <div style="margin-top: 4px;">
                    <span class="badge" style="background: #22c55e; color: #ffffff; font-size: 11px; font-weight: 600;">
                        Semester <?= SecurityHelper::escape($activeTa['semester'] ?? '-') ?> &bull; Aktif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-briefcase"></i>
        </div>
        <div>
            <div class="stat-value" id="statWorkdaysDisplay"><?= $activeWorkdays ?> <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">Hari</span></div>
            <div class="stat-label">Target Kerja Dinas / Thn</div>
            <div style="font-size: 11px; color: #0d9488; font-weight: 600; margin-top: 2px;" id="statWorkdaysMonthly">
                &plusmn; <?= round($activeWorkdays / 12, 1) ?> Hari / Bulan
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fa-solid fa-book-open-reader"></i>
        </div>
        <div>
            <div class="stat-value" id="statKajianDisplay"><?= $activeKajian ?> <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">Pertemuan</span></div>
            <div class="stat-label">Target Kajian Sabtu / Thn</div>
            <div style="font-size: 11px; color: #d97706; font-weight: 600; margin-top: 2px;" id="statKajianMonthly">
                &plusmn; <?= round($activeKajian / 12, 1) ?> Pekan / Bulan
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-calculator"></i>
        </div>
        <div>
            <div class="stat-value" style="font-size: 18px; color: var(--primary);">Akumulatif</div>
            <div class="stat-label">Metode Perhitungan AKFI</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                (Total Hadir &divide; Target) &times; 100%
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon rose">
            <i class="fa-solid fa-robot"></i>
        </div>
        <div>
            <div class="stat-value" style="font-size: 18px; color: #e11d48;">Real-Time</div>
            <div class="stat-label">Auto-Data Collector</div>
            <div style="font-size: 11px; color: #16a34a; font-weight: 600; margin-top: 2px;">
                <i class="fa-solid fa-circle-check"></i> Terkoneksi Sistem
            </div>
        </div>
    </div>
</div>

<!-- Main 2-Column Content -->
<div style="display: grid; grid-template-columns: 1.15fr 1.35fr; gap: 24px; align-items: start;">
    
    <!-- LEFT COLUMN: Form Konfigurasi Target -->
    <div class="card" id="formCard" style="border-top: 4px solid #0d9488;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-sliders" style="color: #0d9488; margin-right: 8px;"></i>
                Form Konfigurasi Target
            </div>
            <span class="badge badge-info" id="editingTaBadge" style="font-size: 11px;">
                <?= SecurityHelper::escape($activeTa['name'] ?? '') ?> (<?= SecurityHelper::escape($activeTa['semester'] ?? '') ?>)
            </span>
        </div>
        <div class="card-body">
            <form action="<?= $baseUrl ?>/deputi/target-kerja/save" method="POST" id="targetKerjaForm">
                <?= Core\CSRF::field() ?>

                <!-- Pilih Tahun Ajaran -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; font-size: 13px; color: var(--text-main); margin-bottom: 6px; display: block;">
                        Tahun Ajaran <span style="color: var(--danger);">*</span>
                    </label>
                    <select name="academic_year_id" id="selectAcademicYear" class="form-control" style="font-weight: 600; height: 42px;" required onchange="onTaChange(this)">
                        <?php foreach ($allTa as $ta): ?>
                            <option value="<?= $ta['id'] ?>" 
                                    data-name="<?= SecurityHelper::escape($ta['name']) ?>"
                                    data-semester="<?= SecurityHelper::escape($ta['semester']) ?>"
                                    data-workdays="<?= (int)($ta['target_workdays'] ?? 264) ?>" 
                                    data-kajian="<?= (int)($ta['target_kajian'] ?? 48) ?>"
                                    <?= $ta['id'] == $activeTaId ? 'selected' : '' ?>>
                                Tahun Ajaran <?= SecurityHelper::escape($ta['name']) ?> - Semester <?= SecurityHelper::escape($ta['semester']) ?> <?= $ta['is_active'] ? '&#9733; (Aktif)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tombol Preset Cepat -->
                <div style="margin-bottom: 20px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 14px;">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-bolt" style="color: #f59e0b; margin-right: 4px;"></i> Preset Standar Kalender:
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-outline btn-sm" onclick="applyPreset(264, 48)" style="font-size: 11px; padding: 4px 10px;">
                            <strong>264 Hari</strong> (12 &times; 22 Hari / 48 Kajian)
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="applyPreset(250, 46)" style="font-size: 11px; padding: 4px 10px;">
                            <strong>250 Hari</strong> (Kalender Efektif 2024/2025)
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="applyPreset(240, 44)" style="font-size: 11px; padding: 4px 10px;">
                            <strong>240 Hari</strong> (5 Hari Kerja / Pekan)
                        </button>
                    </div>
                </div>

                <!-- Input Hari Kerja -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; font-size: 13px; color: var(--text-main); margin-bottom: 6px; display: flex; justify-content: space-between;">
                        <span>Target Hari Kerja Efektif Tahunan <span style="color: var(--danger);">*</span></span>
                        <span id="monthlyWorkdaysPreview" style="color: #0d9488; font-weight: 600; font-size: 12px;">&plusmn; <?= round($activeWorkdays / 12, 1) ?> Hari/Bulan</span>
                    </label>
                    <div style="position: relative;">
                        <input type="number" name="target_workdays" id="inputWorkdays" class="form-control" 
                               value="<?= $activeWorkdays ?>" 
                               min="1" max="365" step="1" required 
                               style="font-size: 16px; font-weight: 700; height: 44px; padding-left: 14px; padding-right: 90px; color: #0f766e; border-color: #cbd5e1;"
                               oninput="updateLiveCalculations()">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 12px; font-weight: 600; background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">
                            Hari / Thn
                        </span>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">
                        Jumlah total hari kerja dinas guru dan karyawan dalam 1 tahun ajaran (12 bulan).
                    </div>
                </div>

                <!-- Input Pertemuan Kajian -->
                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="font-weight: 600; font-size: 13px; color: var(--text-main); margin-bottom: 6px; display: flex; justify-content: space-between;">
                        <span>Target Kehadiran Kajian Sabtu Tahunan <span style="color: var(--danger);">*</span></span>
                        <span id="monthlyKajianPreview" style="color: #d97706; font-weight: 600; font-size: 12px;">&plusmn; <?= round($activeKajian / 12, 1) ?> Pertemuan/Bulan</span>
                    </label>
                    <div style="position: relative;">
                        <input type="number" name="target_kajian" id="inputKajian" class="form-control" 
                               value="<?= $activeKajian ?>" 
                               min="1" max="100" step="1" required 
                               style="font-size: 16px; font-weight: 700; height: 44px; padding-left: 14px; padding-right: 110px; color: #b45309; border-color: #cbd5e1;"
                               oninput="updateLiveCalculations()">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 12px; font-weight: 600; background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">
                            Pertemuan / Thn
                        </span>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">
                        Total pelaksanaan agenda pembinaan/kajian Sabtu Yayasan Islam Al Kahfi Batam selama 1 tahun.
                    </div>
                </div>

                <!-- Live Impact Preview Box -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; margin-bottom: 24px;">
                    <div style="font-weight: 600; font-size: 12px; color: #166534; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                        <i class="fa-solid fa-circle-check"></i> Simulasi Dampak ke Penilaian AKFI:
                    </div>
                    <div style="font-size: 12px; color: #15803d; line-height: 1.4;" id="impactFormulaDesc">
                        Pegawai yang hadir penuh selama 12 bulan (<strong><?= $activeWorkdays ?> hari</strong>) akan memperoleh capaian presensi <strong>100%</strong>. Jika baru berjalan 1 bulan (~22 hari), capaian tercatat <strong><?= round((22 / max(1, $activeWorkdays)) * 100, 1) ?>%</strong> secara proporsional.
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" style="width: 100%; height: 44px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Konfigurasi Target Hari Kerja
                </button>
            </form>
        </div>
    </div>

    <!-- RIGHT COLUMN: Matrix & Penjelasan Seluruh Tahun Ajaran -->
    <div>
        <!-- Table Card -->
        <div class="card" style="border-top: 4px solid #1e3a8a;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-table-list" style="color: var(--primary); margin-right: 8px;"></i>
                    Daftar Target Seluruh Tahun Ajaran
                </div>
                <span class="badge badge-secondary" style="font-size: 11px;">
                    <?= count($allTa) ?> Tahun Ajaran
                </span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Tahun Ajaran</th>
                                <th style="text-align: center;">Target Kerja</th>
                                <th style="text-align: center;">Target Kajian</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: center; width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allTa)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                        Belum ada data tahun ajaran.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allTa as $idx => $ta): 
                                    $w = (int)($ta['target_workdays'] ?? 264);
                                    $k = (int)($ta['target_kajian'] ?? 48);
                                    $isCurrent = ($ta['id'] == $activeTaId);
                                ?>
                                    <tr style="<?= $isCurrent ? 'background: rgba(13, 148, 136, 0.05); font-weight: 500;' : '' ?>">
                                        <td style="text-align: center; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                        <td>
                                            <div style="font-weight: 700; color: var(--text-main);">
                                                <?= SecurityHelper::escape($ta['name']) ?>
                                            </div>
                                            <div style="font-size: 11px; color: var(--text-muted);">
                                                Semester <?= SecurityHelper::escape($ta['semester']) ?>
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <div style="font-weight: 700; color: #0d9488; font-size: 14px;">
                                                <?= $w ?> Hari
                                            </div>
                                            <div style="font-size: 10px; color: var(--text-muted);">
                                                &plusmn; <?= round($w / 12, 1) ?> hr/bln
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <div style="font-weight: 700; color: #d97706; font-size: 14px;">
                                                <?= $k ?> Pekan
                                            </div>
                                            <div style="font-size: 10px; color: var(--text-muted);">
                                                &plusmn; <?= round($k / 12, 1) ?> pkn/bln
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($ta['is_active']): ?>
                                                <span class="badge badge-success" style="font-size: 10px;">
                                                    <i class="fa-solid fa-circle-check"></i> Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary" style="font-size: 10px;">Non-Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn btn-outline btn-sm" 
                                                    onclick="editTarget(<?= $ta['id'] ?>, '<?= SecurityHelper::escape($ta['name']) ?>', '<?= SecurityHelper::escape($ta['semester']) ?>', <?= $w ?>, <?= $k ?>)"
                                                    style="padding: 4px 10px; font-size: 11px; color: #0d9488; border-color: rgba(13, 148, 136, 0.4);">
                                                <i class="fa-solid fa-pen-to-square"></i> Atur
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pedoman Teknis Card -->
        <div class="card" style="background: #f8fafc;">
            <div class="card-header" style="background: transparent;">
                <div class="card-title" style="font-size: 14px;">
                    <i class="fa-solid fa-circle-question" style="color: #6366f1; margin-right: 6px;"></i>
                    Panduan Konfigurasi Kalender Kerja Deputi
                </div>
            </div>
            <div class="card-body" style="font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                <ul style="padding-left: 18px; margin: 0;">
                    <li style="margin-bottom: 6px;">
                        <strong>Fleksibel per Tahun:</strong> Setiap tahun ajaran memiliki jumlah hari efektif yang berbeda tergantung kalender pendidikan dinas dan hari libur keagamaan.
                    </li>
                    <li style="margin-bottom: 6px;">
                        <strong>Perhitungan Bulanan Bertahap:</strong> Karena absensi diinput per bulan oleh TU/Operator, capaian indikator presensi pada AKFI bertambah secara proporsional setiap bulannya.
                    </li>
                    <li style="margin-bottom: 6px;">
                        <strong>Kehadiran Satuan Kepala Sekolah:</strong> Indikator kehadiran guru & karyawan pada instrumen Kepala Sekolah dihitung dari: $\frac{\text{Total Hadir Seluruh Staf Unit}}{\text{Jumlah Pegawai} \times \text{Target Hari Kerja}} \times 100\%$.
                    </li>
                    <li>
                        <strong>Perubahan Berlaku Seketika:</strong> Begitu target disimpan, seluruh pratinjau AKFI dan auto-data collector langsung membaca target terbaru tanpa perlu kalkulasi ulang manual.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function onTaChange(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (opt) {
        var name = opt.getAttribute('data-name') || '';
        var sem = opt.getAttribute('data-semester') || '';
        var w = parseInt(opt.getAttribute('data-workdays')) || 264;
        var k = parseInt(opt.getAttribute('data-kajian')) || 48;
        
        document.getElementById('inputWorkdays').value = w;
        document.getElementById('inputKajian').value = k;
        document.getElementById('editingTaBadge').innerText = name + ' (' + sem + ')';
        updateLiveCalculations();
    }
}

function applyPreset(workdays, kajian) {
    document.getElementById('inputWorkdays').value = workdays;
    document.getElementById('inputKajian').value = kajian;
    updateLiveCalculations();
}

function updateLiveCalculations() {
    var w = parseInt(document.getElementById('inputWorkdays').value) || 264;
    var k = parseInt(document.getElementById('inputKajian').value) || 48;
    
    var monthlyW = (w / 12).toFixed(1);
    var monthlyK = (k / 12).toFixed(1);
    
    document.getElementById('monthlyWorkdaysPreview').innerText = '± ' + monthlyW + ' Hari/Bulan';
    document.getElementById('monthlyKajianPreview').innerText = '± ' + monthlyK + ' Pertemuan/Bulan';
    
    var sim1Month = ((22 / Math.max(1, w)) * 100).toFixed(1);
    var desc = 'Pegawai yang hadir penuh selama 12 bulan (<strong>' + w + ' hari</strong>) akan memperoleh capaian presensi <strong>100%</strong>. Jika baru berjalan 1 bulan (~22 hari), capaian tercatat <strong>' + sim1Month + '%</strong> secara proporsional.';
    document.getElementById('impactFormulaDesc').innerHTML = desc;
}

function editTarget(taId, name, sem, workdays, kajian) {
    var sel = document.getElementById('selectAcademicYear');
    sel.value = taId;
    document.getElementById('editingTaBadge').innerText = name + ' (' + sem + ')';
    document.getElementById('inputWorkdays').value = workdays;
    document.getElementById('inputKajian').value = kajian;
    updateLiveCalculations();
    
    var formCard = document.getElementById('formCard');
    formCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Highlight animation
    formCard.style.transition = 'box-shadow 0.3s ease, border-color 0.3s ease';
    formCard.style.borderColor = '#0d9488';
    formCard.style.boxShadow = '0 0 0 3px rgba(13, 148, 136, 0.25)';
    setTimeout(function() {
        formCard.style.boxShadow = '';
    }, 1200);
    
    document.getElementById('inputWorkdays').focus();
}
</script>
