<?php
// views/tahfidz/rekap_siswa.php
use Core\SecurityHelper;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalSantri = count($rekap);
$tuntasCount = 0;
$totalLines = 0;
foreach ($rekap as $r) {
    if (!empty($r['is_tuntas'])) $tuntasCount++;
    $totalLines += (int)$r['total_lines'];
}
$avgLines = $totalSantri > 0 ? round($totalLines / $totalSantri, 1) : 0;
$persenTuntas = $totalSantri > 0 ? round(($tuntasCount / $totalSantri) * 100, 1) : 0;
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Capaian & Rekapitulasi Tahfidz Santri</h1>
        <div class="page-subtitle">Monitoring capaian akumulasi baris Al-Qur'an, evaluasi ketercapaian target bulanan, dan cetak rapor tahfidz santri.</div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <button onclick="window.print()" class="btn btn-outline">
            <i class="fa-solid fa-print"></i> Cetak Rekapitulasi
        </button>
        <a href="<?= $baseUrl ?>/tahfidz/setoran-siswa" class="btn btn-primary">
            <i class="fa-solid fa-quran"></i> Setoran Hafalan
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalSantri ?></div>
            <div class="stat-label">Total Santri Aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $tuntasCount ?> <span style="font-size: 0.85rem; font-weight: normal;">(<?= $persenTuntas ?>%)</span></div>
            <div class="stat-label">Santri Tuntas Target</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $totalSantri - $tuntasCount ?></div>
            <div class="stat-label">Sedang Berproses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $avgLines ?> Baris</div>
            <div class="stat-label">Rata-rata Baris / Santri</div>
        </div>
    </div>
</div>

<!-- Card Tabel Capaian Santri -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <h3 class="card-title" style="display: flex; align-items: center; gap: 8px; font-size: 1rem; margin: 0;">
            <i class="fa-solid fa-list-ol" style="color: #0d9488;"></i> Matriks Capaian Hafalan Santri
        </h3>
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" id="searchSantri" placeholder="Cari nama, kelas, halaqah..." class="form-control" style="width: 240px; padding: 6px 12px; font-size: 0.85rem;" onkeyup="filterRekap()">
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table" id="tableRekap">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th style="width: 110px;">NIS</th>
                        <th>Nama Santri</th>
                        <th>Kelas</th>
                        <th>Halaqah</th>
                        <th style="text-align: center;">Setoran</th>
                        <th style="text-align: center;">Akumulasi Baris</th>
                        <th style="text-align: center;">Status Capaian</th>
                        <th style="width: 120px; text-align: center;">Rapor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rekap)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                Belum ada data capaian tahfidz santri yang tercatat.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekap as $idx => $r): ?>
                            <tr class="rekap-row">
                                <td style="text-align: center;"><?= $idx + 1 ?></td>
                                <td><code><?= SecurityHelper::escape($r['nis']) ?></code></td>
                                <td>
                                    <strong style="color: var(--text-main, #0f172a);"><?= SecurityHelper::escape($r['nama']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?= SecurityHelper::escape($r['class_name'] ?? 'Non-Kelas') ?></span>
                                </td>
                                <td>
                                    <?= SecurityHelper::escape($r['halaqah_name'] ?? 'Belum ada') ?>
                                </td>
                                <td style="text-align: center;">
                                    <?= (int)$r['setoran_count'] ?> kali
                                </td>
                                <td style="text-align: center;">
                                    <strong style="color: #0f766e;"><?= (int)$r['total_lines'] ?></strong> baris
                                    <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">(&plusmn; <?= round((int)$r['total_lines'] / 15, 1) ?> hal)</div>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($r['is_tuntas']): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-check" style="margin-right: 4px;"></i> Tuntas Target</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="background: rgba(245, 158, 11, 0.15); color: #d97706;">
                                            <i class="fa-solid fa-hourglass-half" style="margin-right: 4px;"></i> Berproses
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="<?= $baseUrl ?>/tahfidz/rapor-pdf/<?= $r['id'] ?>" target="_blank" class="btn btn-outline btn-sm" title="Cetak Rapor Tahfidz PDF" style="color: #0d9488; border-color: rgba(13, 148, 136, 0.3);">
                                        <i class="fa-solid fa-print"></i> Rapor
                                    </a>
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
function filterRekap() {
    const q = document.getElementById('searchSantri').value.toLowerCase();
    const rows = document.querySelectorAll('#tableRekap .rekap-row');
    rows.forEach(r => {
        r.style.display = r.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
