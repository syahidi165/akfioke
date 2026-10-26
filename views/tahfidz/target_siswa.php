<?php
use Core\SecurityHelper;
use Core\CSRF;

$unitName = $activeConfig['name'] ?? 'Satuan Pendidikan';
$grades = $activeConfig['grades'] ?? [];
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary"><?= SecurityHelper::escape($unitName) ?></span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tahun Ajaran <?= SecurityHelper::escape(\Core\Auth::academicYearName()) ?></span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-bullseye" style="color: #0d9488;"></i> Target Tahunan Capaian Tahfidz Santri
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Penetapan baseline awal dan target tahunan hafalan santri per tingkat/jenjang sesuai kurikulum tahfidz satuan pendidikan.
            </p>
        </div>
        
        <?php if (!empty($isExecutive) || count($allUnits ?? []) > 1): ?>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b); font-weight: 500;">Pilih Satuan:</span>
                <div class="btn-group" style="display: flex; gap: 4px;">
                    <a href="<?= $baseUrl ?>/tahfidz/target-siswa?unit_id=2" class="btn btn-sm <?= ($selectedUnitId == 2 || $selectedUnitId == 1) ? 'btn-primary' : 'btn-secondary' ?>">
                        KB &amp; TK
                    </a>
                    <a href="<?= $baseUrl ?>/tahfidz/target-siswa?unit_id=3" class="btn btn-sm <?= ($selectedUnitId == 3) ? 'btn-primary' : 'btn-secondary' ?>">
                        SD (Kls 1-6)
                    </a>
                    <a href="<?= $baseUrl ?>/tahfidz/target-siswa?unit_id=4" class="btn btn-sm <?= ($selectedUnitId == 4) ? 'btn-primary' : 'btn-secondary' ?>">
                        SMP (Kls 1-3)
                    </a>
                    <a href="<?= $baseUrl ?>/tahfidz/target-siswa?unit_id=5" class="btn btn-sm <?= ($selectedUnitId == 5) ? 'btn-primary' : 'btn-secondary' ?>">
                        SMA (Kls 1-3)
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Grid Info -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Jenjang / Tingkat</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= count($grades) ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Tingkat Kelas</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Unit: <?= SecurityHelper::escape($unitName) ?></div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Standar Konversi</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            15 <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Baris/Halaman</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Standar Mushaf Madinah (1 Juz = 300 Baris)</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Status Konfigurasi</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= count($targetsByGrade) ?>/<?= count($grades) ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Ditetapkan</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">Tersimpan aktif di sistem</div>
    </div>
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-pen-ruler" style="color: #0d9488;"></i> Form Target Capaian Tahfidz Santri Per Jenjang
        </h3>
        <span class="badge badge-secondary"><?= SecurityHelper::escape($unitName) ?></span>
    </div>

    <div class="card-body" style="padding: 24px;">
        <form action="<?= $baseUrl ?>/tahfidz/target-siswa/save" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="unit_id" value="<?= (int)$selectedUnitId ?>">

            <div style="overflow-x: auto; margin-bottom: 24px;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid var(--border-color, #e2e8f0);">
                            <th style="width: 50px; text-align: center; padding: 12px 16px;">No</th>
                            <th style="width: 220px; padding: 12px 16px;">Tingkat / Jenjang</th>
                            <th style="width: 260px; padding: 12px 16px;">Baseline Hafalan Awal</th>
                            <th style="padding: 12px 16px;">Target Hafalan Akhir Tahun</th>
                            <th style="width: 170px; text-align: center; padding: 12px 16px;">Target Baris</th>
                            <th style="width: 120px; text-align: center; padding: 12px 16px;">Setara</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $idx => $g): 
                            $code = $g['code'];
                            $saved = $targetsByGrade[$code] ?? null;
                            $lines = $saved ? (int)$saved['target_lines'] : (int)$g['default_lines'];
                            $baseline = $saved ? ($saved['baseline_info'] ?? '') : ($g['default_baseline'] ?? '');
                            $targetInfo = $saved ? ($saved['target_info'] ?? '') : ($g['default_target'] ?? '');
                            $pages = round($lines / 15, 1);
                        ?>
                            <tr style="border-bottom: 1px solid var(--border-color, #e2e8f0);">
                                <td style="text-align: center; color: var(--text-muted, #64748b); vertical-align: middle; padding: 14px 16px;">
                                    <?= $idx + 1 ?>
                                </td>
                                <td style="vertical-align: middle; padding: 14px 16px;">
                                    <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                        <?= SecurityHelper::escape($g['label']) ?>
                                    </div>
                                    <span class="badge badge-info" style="font-size: 0.75rem; margin-top: 4px;">Kode: <?= SecurityHelper::escape($code) ?></span>
                                </td>
                                <td style="vertical-align: middle; padding: 14px 16px;">
                                    <input type="text" 
                                           name="grades[<?= SecurityHelper::escape($code) ?>][baseline_info]" 
                                           class="form-control" 
                                           value="<?= SecurityHelper::escape($baseline) ?>" 
                                           placeholder="Contoh: Surah An-Nas / Juz 30"
                                           required>
                                    <small style="color: var(--text-muted, #64748b); font-size: 0.75rem; display: block; margin-top: 4px;">Hafalan awal sebelum tahun ajaran</small>
                                </td>
                                <td style="vertical-align: middle; padding: 14px 16px;">
                                    <input type="text" 
                                           name="grades[<?= SecurityHelper::escape($code) ?>][target_info]" 
                                           class="form-control" 
                                           value="<?= SecurityHelper::escape($targetInfo) ?>" 
                                           placeholder="Contoh: Juz 30 (300 Baris) / 1 Juz"
                                           required>
                                    <small style="color: var(--text-muted, #64748b); font-size: 0.75rem; display: block; margin-top: 4px;">Target capaian saat akhir tahun ajaran</small>
                                </td>
                                <td style="vertical-align: middle; text-align: center; padding: 14px 16px;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <input type="number" 
                                               name="grades[<?= SecurityHelper::escape($code) ?>][target_lines]" 
                                               class="form-control target-line-input" 
                                               data-target-equiv="equiv-<?= $idx ?>"
                                               value="<?= $lines ?>" 
                                               min="1" 
                                               max="6000" 
                                               style="width: 100px; text-align: center; font-weight: 600;" 
                                               required>
                                        <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Baris</span>
                                    </div>
                                </td>
                                <td style="vertical-align: middle; text-align: center; padding: 14px 16px;">
                                    <span id="equiv-<?= $idx ?>" class="badge badge-secondary" style="font-weight: 600; font-size: 0.8125rem;">
                                        <?= $pages ?> Hlm (<?= round($pages / 20, 1) ?> Juz)
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding-top: 16px; border-top: 1px solid var(--border-color, #e2e8f0);">
                <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">
                    <i class="fa-solid fa-circle-info" style="color: #0d9488; margin-right: 4px;"></i> Target yang disimpan akan menjadi dasar evaluasi kelulusan &amp; persentase capaian di Rapor Tahfidz.
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Target Capaian Tahfidz
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lineInputs = document.querySelectorAll('.target-line-input');
    lineInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            const lines = parseInt(this.value) || 0;
            const targetId = this.getAttribute('data-target-equiv');
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                const pages = (lines / 15).toFixed(1);
                const juz = (pages / 20).toFixed(1);
                targetEl.textContent = pages + ' Hlm (' + juz + ' Juz)';
            }
        });
    });
});
</script>
