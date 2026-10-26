<?php
// views/spmb/operator.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$isMet = $actualCount >= $targetVal && $targetVal > 0;
$shortage = max(0, $targetVal - $actualCount);
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= SecurityHelper::escape($pageTitle) ?></h1>
        <div class="page-subtitle">
            Pelaporan jumlah realisasi pendaftar santri baru <?= !empty($unitInfo['name']) ? 'untuk <strong>' . SecurityHelper::escape($unitInfo['name']) . '</strong>' : '' ?>. Angka ini terhubung langsung ke instrumen penilaian AKFI Kepala Sekolah (K-KS-10).
        </div>
    </div>
    <?php if ($isYayasan && !empty($allUnits)): ?>
        <form method="GET" action="<?= $baseUrl ?>/spmb/operator" style="display: flex; align-items: center; gap: 8px;">
            <label class="form-label" style="margin-bottom: 0; white-space: nowrap; font-size: 13px;">Satuan Unit:</label>
            <select name="unit_id" class="form-control" onchange="this.form.submit()" style="min-width: 180px; padding: 6px 12px;">
                <?php foreach ($allUnits as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $unitId == $u['id'] ? 'selected' : '' ?>>
                        <?= SecurityHelper::escape($u['code']) ?> - <?= SecurityHelper::escape($u['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php endif; ?>
</div>

<!-- 4 KARTU STATISTIK METRIK SPMB -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-bullseye"></i>
        </div>
        <div>
            <div class="stat-value"><?= number_format($targetVal) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Santri</span></div>
            <div class="stat-label">Target SPMB (Deputi)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div class="stat-value" style="color: var(--accent);"><?= number_format($actualCount) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">Santri</span></div>
            <div class="stat-label">Realisasi Pendaftar Masuk</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div>
            <div class="stat-value"><?= $percentTarget ?>%</div>
            <div class="stat-label">Persentase Capaian</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= $isMet ? 'emerald' : 'rose' ?>">
            <i class="fa-solid <?= $isMet ? 'fa-circle-check' : 'fa-clock' ?>"></i>
        </div>
        <div>
            <div class="stat-value" style="font-size: 15px;">
                <?php if ($isMet): ?>
                    <span class="badge badge-success">Target Terpenuhi</span>
                <?php else: ?>
                    <span class="badge badge-warning">Kurang <?= number_format($shortage) ?> Santri</span>
                <?php endif; ?>
            </div>
            <div class="stat-label">Status AKFI K-KS-10</div>
        </div>
    </div>
</div>

<div class="grid-cols-2">
    <!-- FORM INPUT ANGKA SPMB -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-pen-to-square" style="color: var(--primary); margin-right: 8px;"></i>
                Input Angka Realisasi Pendaftar SPMB
            </div>
            <span class="badge badge-info">TA <?= SecurityHelper::escape(Auth::academicYearName()) ?></span>
        </div>
        <div class="card-body">
            <form action="<?= $baseUrl ?>/spmb/operator/save" method="POST">
                <?= CSRF::field() ?>
                <?php if ($isYayasan): ?>
                    <input type="hidden" name="unit_id" value="<?= $unitId ?>">
                <?php endif; ?>

                <!-- Target Display Box -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Target Ditetapkan Deputi</div>
                        <div style="font-size: 20px; font-weight: 700; color: var(--text-main); margin-top: 2px;">
                            <?= number_format($targetVal) ?> Santri Baru
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-info" style="font-size: 12px; padding: 6px 12px;">Bobot AKFI: 4.0</span>
                    </div>
                </div>

                <!-- Input Angka Realisasi -->
                <div class="form-group">
                    <label class="form-label" style="font-size: 14px;">
                        Total Jumlah Siswa Baru yang Mendaftar <span style="color: var(--danger);">*</span>
                    </label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="number" name="actual_count" min="0" value="<?= $actualCount ?>" 
                               class="form-control" 
                               style="font-size: 24px; font-weight: 700; text-align: center; height: 52px; color: #0f766e; border: 2px solid #0d9488; border-radius: 8px;" 
                               required autofocus>
                        <div style="background: #f1f5f9; padding: 0 16px; height: 52px; display: flex; align-items: center; border-radius: 8px; border: 1px solid var(--border-color); font-weight: 600; color: var(--text-muted); white-space: nowrap;">
                            Santri / Anak
                        </div>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px; line-height: 1.4;">
                        <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i>
                        Cukup masukkan total angka akumulasi pendaftar santri baru saat ini. Tidak perlu input detail biodata per siswa.
                    </div>
                </div>

                <!-- Catatan / Keterangan Opsional -->
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">Catatan / Keterangan Tambahan (Opsional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Contoh: Gelombang 1 & 2 telah tuntas, kuota kelas terpenuhi">
                </div>

                <button type="submit" class="btn btn-accent" style="width: 100%; justify-content: center; padding: 12px; font-size: 14px; font-weight: 600; margin-top: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Realisasi & Masukkan ke AKFI
                </button>
            </form>
        </div>
    </div>

    <!-- INFORMASI INTEGRASI AKFI -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-circle-nodes" style="color: var(--accent); margin-right: 8px;"></i>
                Integrasi Otomatis ke Instrumen AKFI
            </div>
        </div>
        <div class="card-body">
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px; line-height: 1.6;">
                Angka realisasi pendaftar yang Anda simpan di halaman ini secara otomatis langsung terhubung ke instrumen penilaian <strong>Al Kahfi Focus Indicator (AKFI)</strong> Kepala Sekolah.
            </p>

            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span class="badge badge-secondary">Kode: K-KS-10</span>
                    <span style="font-size: 12px; font-weight: 600; color: var(--primary);">Kategori: Performance</span>
                </div>
                <div style="font-weight: 700; font-size: 14px; color: var(--text-main); margin-bottom: 4px;">
                    % Siswa baru dalam SPMB vs Target Deputi
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Instrumen penilaian kinerja Kepala Sekolah dan unit satuan pendidikan oleh Deputi Pendidikan.
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; text-align: center;">
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Target Satuan</div>
                    <div style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-top: 4px;">100 %</div>
                </div>
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Bobot AKFI</div>
                    <div style="font-size: 16px; font-weight: 700; color: var(--primary); margin-top: 4px;">4.0 Poin</div>
                </div>
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Capaian Unit</div>
                    <div style="font-size: 16px; font-weight: 700; color: <?= $isMet ? 'var(--success)' : 'var(--warning)' ?>; margin-top: 4px;"><?= $percentTarget ?>%</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                    <span style="font-weight: 600; color: var(--text-muted);">Progress Pencapaian Target</span>
                    <span style="font-weight: 700; color: <?= $isMet ? 'var(--success)' : 'var(--primary)' ?>;"><?= $percentTarget ?>%</span>
                </div>
                <div style="background: #e2e8f0; height: 10px; border-radius: 5px; overflow: hidden;">
                    <div style="background: <?= $isMet ? 'var(--success)' : ($percentTarget >= 75 ? 'var(--primary)' : 'var(--warning)') ?>; width: <?= min(100, $percentTarget) ?>%; height: 100%; transition: width 0.3s;"></div>
                </div>
            </div>

            <div class="alert <?= $isMet ? 'alert-success' : 'alert-info' ?>" style="margin-bottom: 0;">
                <i class="fa-solid <?= $isMet ? 'fa-circle-check' : 'fa-circle-info' ?>" style="font-size: 18px;"></i>
                <div>
                    <?php if ($isMet): ?>
                        <strong>Target Telah Tercapai!</strong> Realisasi telah memenuhi/melebihi target deputi (<?= $percentTarget ?>%), instrumen AKFI Kepala Sekolah otomatis mendapatkan skor maksimal.
                    <?php else: ?>
                        <strong>Proses Pendaftaran:</strong> Capaian saat ini <?= $percentTarget ?>%. Begitu angka mencapai <?= number_format($targetVal) ?>, target AKFI otomatis terpenuhi 100%.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
