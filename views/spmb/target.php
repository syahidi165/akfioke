<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$totalTarget = 0;
$totalActual = 0;
$unitTercapai = 0;
$totalUnitsDitetapkan = count($targets ?? []);

foreach ($targets as $t) {
    $tgt = (int)$t['target_count'];
    $act = (int)$t['actual_registrations'];
    $totalTarget += $tgt;
    $totalActual += $act;
    if ($tgt > 0 && $act >= $tgt) {
        $unitTercapai++;
    }
}

$capaianGlobal = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 1) : 0;
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Deputi Direktur Pendidikan</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tahun Ajaran <?= SecurityHelper::escape(Auth::academicYearName()) ?></span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-bullseye" style="color: #0d9488;"></i> Target &amp; Realisasi SPMB Seluruh Satuan Unit
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Penetapan kuota target penerimaan santri baru serta pemantauan realisasi pendaftar aktual lintas 5 unit sekolah.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Target Global Yayasan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= number_format($totalTarget, 0, ',', '.') ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Akumulasi seluruh unit</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Realisasi Pendaftar</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= number_format($totalActual, 0, ',', '.') ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Pendaftar baru terdata</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid <?= $capaianGlobal >= 100 ? '#10b981' : ($capaianGlobal >= 75 ? '#0284c7' : '#f59e0b') ?>;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Capaian SPMB Global</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: <?= $capaianGlobal >= 100 ? '#10b981' : ($capaianGlobal >= 75 ? '#0284c7' : '#f59e0b') ?>; margin: 6px 0 2px 0;">
            <?= $capaianGlobal ?>%
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Persentase target yayasan</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Unit Memenuhi Target</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $unitTercapai ?>/<?= count($units ?? []) ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Unit</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">Realisasi &ge; 100% kuota</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- Form Penetapan Target per Unit -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-pen-ruler" style="color: #0d9488;"></i> Tetapkan Target SPMB Unit
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= Auth::baseUrl() ?>/spmb/target/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Pilih Satuan Unit <span style="color: #ef4444;">*</span></label>
                    <select name="unit_id" id="unitSelect" class="form-control" required>
                        <option value="">-- Pilih Satuan Unit --</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= SecurityHelper::escape($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Target Jumlah Santri Baru <span style="color: #ef4444;">*</span></label>
                    <div style="position: relative;">
                        <input type="number" min="1" max="1000" name="target_count" id="targetCountInput" class="form-control" placeholder="Contoh: 120" style="font-size: 1.125rem; font-weight: 700; color: #0d9488;" required>
                    </div>
                </div>

                <!-- Quick Presets -->
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 0.75rem; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase; margin-bottom: 6px; display: block;">Preset Kuota Cepat</label>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setPreset(40)" style="padding: 4px 8px; font-size: 0.75rem;">40 Santri</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setPreset(60)" style="padding: 4px 8px; font-size: 0.75rem;">60 Santri</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setPreset(90)" style="padding: 4px 8px; font-size: 0.75rem;">90 Santri</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setPreset(120)" style="padding: 4px 8px; font-size: 0.75rem;">120 Santri</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Target SPMB
                </button>
            </form>
        </div>
    </div>

    <!-- Table Realisasi Capaian Target Seluruh Unit -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-chart-pie" style="color: #0d9488;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Realisasi Capaian Target SPMB Seluruh Unit</h3>
            </div>
            <span class="badge badge-secondary"><?= $totalUnitsDitetapkan ?> Unit Ditetapkan</span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th>Satuan Unit Sekolah</th>
                            <th style="width: 110px; text-align: center;">Target</th>
                            <th style="width: 110px; text-align: center;">Realisasi</th>
                            <th style="width: 220px;">Progress Capaian</th>
                            <th style="width: 130px; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($targets)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-bullseye" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada target SPMB yang ditetapkan untuk tahun ajaran aktif ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($targets as $idx => $t): 
                                $act = (int)$t['actual_registrations'];
                                $tgt = (int)$t['target_count'];
                                $pct = $tgt > 0 ? round(($act / $tgt) * 100, 1) : 0;
                                $diff = $act - $tgt;
                                $barColor = $pct >= 100 ? '#10b981' : ($pct >= 75 ? '#0284c7' : '#f59e0b');
                            ?>
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                            <?= SecurityHelper::escape($t['unit_name']) ?>
                                        </div>
                                        <small style="color: var(--text-muted, #64748b);">
                                            <?php if ($diff >= 0): ?>
                                                <span style="color: #10b981; font-weight: 600;">+<?= $diff ?> surplus santri</span>
                                            <?php else: ?>
                                                <span style="color: #ef4444; font-weight: 500;">Kurang <?= abs($diff) ?> santri</span>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td style="text-align: center; font-weight: 700; color: var(--text-main, #0f172a);">
                                        <?= $tgt ?> <small style="font-weight: normal; color: var(--text-muted, #64748b);">Santri</small>
                                    </td>
                                    <td style="text-align: center; font-weight: 700; color: #0d9488;">
                                        <?= $act ?> <small style="font-weight: normal; color: var(--text-muted, #64748b);">Santri</small>
                                    </td>
                                    <td>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; font-size: 0.8125rem;">
                                            <span style="color: var(--text-muted, #64748b);"><?= $act ?> dari <?= $tgt ?></span>
                                            <strong style="color: <?= $barColor ?>;"><?= $pct ?>%</strong>
                                        </div>
                                        <div style="height: 8px; background: #e2e8f0; border-radius: 9999px; overflow: hidden;">
                                            <div style="height: 100%; width: <?= min(100, $pct) ?>%; background: <?= $barColor ?>; border-radius: 9999px; transition: width 0.3s ease;"></div>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($pct >= 100): ?>
                                            <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-circle-check"></i> Tercapai
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning" style="display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-clock"></i> Dalam Proses
                                            </span>
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

<script>
function setPreset(val) {
    const input = document.getElementById('targetCountInput');
    if (input) {
        input.value = val;
        input.focus();
    }
}
</script>
