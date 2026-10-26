<?php
use Core\SecurityHelper;

$totalSantri = count($students ?? []);
$tuntasJuz = 0;
$tuntasSetengah = 0;
$dalamPembinaan = 0;

foreach ($students as $st) {
    $maxLine = max((int)($st['p1'] ?? 0), (int)($st['p2'] ?? 0), (int)($st['p3'] ?? 0));
    if ($maxLine >= 300) {
        $tuntasJuz++;
    } elseif ($maxLine >= 150) {
        $tuntasSetengah++;
    } else {
        $dalamPembinaan++;
    }
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Dokumen No. 11</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Administrasi Kepala Sekolah</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-chart-line" style="color: #0d9488;"></i> Baseline Tahfidz Santri (3x Setahun)
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pemantauan berkala capaian hafalan santri: Periode 1 (Awal Tahun), Periode 2 (Tengah Tahun), dan Periode 3 (Akhir Tahun).
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button onclick="window.print()" class="btn btn-secondary" style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-print"></i> Cetak Dokumen Baseline
            </button>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Santri</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalSantri ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Santri aktif terdaftar</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Tuntas &ge; 1 Juz</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $tuntasJuz ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">
            <?= $totalSantri > 0 ? round(($tuntasJuz / $totalSantri) * 100, 1) : 0 ?>% dari total
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Tuntas &ge; &frac12; Juz</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $tuntasSetengah ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">
            Minimal 150 baris hafalan
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Dalam Pembinaan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b; margin: 6px 0 2px 0;">
            <?= $dalamPembinaan ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Perlu pendampingan intensif</div>
    </div>
</div>

<!-- Main Card Table -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-list-check" style="color: #0d9488;"></i>
            <h3 class="card-title" style="margin: 0; font-size: 1rem;">Matriks Capaian Hafalan Santri (Mushaf Madinah)</h3>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <div style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                <input type="text" id="baselineSearch" class="form-control" placeholder="Cari santri / kelas..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 220px;">
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table" id="baselineTable">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;" rowspan="2">No</th>
                        <th style="width: 120px; text-align: center;" rowspan="2">NIS</th>
                        <th rowspan="2">Nama Santri</th>
                        <th style="width: 110px; text-align: center;" rowspan="2">Kelas</th>
                        <th colspan="3" style="text-align: center; border-bottom: 1px solid var(--border-color, #e2e8f0);">Capaian Baris Hafalan (Standar Mushaf Madinah 15 Baris/Hlm)</th>
                        <th style="width: 170px; text-align: center;" rowspan="2">Status Capaian</th>
                    </tr>
                    <tr>
                        <th style="text-align: center; width: 140px; background: #f8fafc;">Periode 1<br><small style="color: var(--text-muted, #64748b); font-weight: normal;">(Awal Tahun)</small></th>
                        <th style="text-align: center; width: 140px; background: #f8fafc;">Periode 2<br><small style="color: var(--text-muted, #64748b); font-weight: normal;">(Tengah Tahun)</small></th>
                        <th style="text-align: center; width: 140px; background: #f8fafc;">Periode 3<br><small style="color: var(--text-muted, #64748b); font-weight: normal;">(Akhir Tahun)</small></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                Belum ada data baseline tahfidz santri untuk unit dan tahun ajaran ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $idx => $st): 
                            $maxLine = max((int)($st['p1'] ?? 0), (int)($st['p2'] ?? 0), (int)($st['p3'] ?? 0));
                        ?>
                            <tr class="baseline-row" data-search="<?= strtolower(SecurityHelper::escape($st['nama'] . ' ' . $st['nis'] . ' ' . ($st['class_name'] ?? ''))) ?>">
                                <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                <td style="text-align: center; font-family: monospace; font-size: 0.85rem; color: #475569;">
                                    <?= SecurityHelper::escape($st['nis']) ?>
                                </td>
                                <td>
                                    <strong style="color: var(--text-main, #0f172a); font-size: 0.9375rem;"><?= SecurityHelper::escape($st['nama']) ?></strong>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-secondary"><?= SecurityHelper::escape($st['class_name'] ?? 'Non-Kelas') ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-weight: 600; color: #0d9488;"><?= (int)$st['p1'] ?> Baris</span>
                                    <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);"><?= round((int)$st['p1'] / 15, 1) ?> Hlm</div>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-weight: 600; color: #0d9488;"><?= (int)$st['p2'] ?> Baris</span>
                                    <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);"><?= round((int)$st['p2'] / 15, 1) ?> Hlm</div>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-weight: 600; color: #0d9488;"><?= (int)$st['p3'] ?> Baris</span>
                                    <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);"><?= round((int)$st['p3'] / 15, 1) ?> Hlm</div>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($maxLine >= 300): ?>
                                        <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-circle-check"></i> Tuntas &ge; 1 Juz
                                        </span>
                                    <?php elseif ($maxLine >= 150): ?>
                                        <span class="badge badge-info" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-circle-half-stroke"></i> Tuntas &ge; &frac12; Juz
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa-solid fa-clock"></i> Dalam Pembinaan
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('baselineSearch');
    const rows = document.querySelectorAll('.baseline-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            rows.forEach(function(row) {
                const text = row.getAttribute('data-search') || '';
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});
</script>
