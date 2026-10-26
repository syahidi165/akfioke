<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalIncidents = count($incidents ?? []);
$resolvedCount = 0;
$escalatedCount = 0;

foreach ($incidents as $inc) {
    if (!empty($inc['is_prevented'])) {
        $resolvedCount++;
    } else {
        $escalatedCount++;
    }
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Security</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tanggap Darurat &amp; Kejadian</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444;"></i> Buku Kejadian &amp; Laporan Insiden Keamanan
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Dokumentasi kejadian khusus, kehilangan barang, kerusakan sarana, gangguan ketertiban, dan respon penanganan cepat security.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="<?= $baseUrl ?>/checklist/security" class="btn btn-outline" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-calendar-check"></i> Checklist Harian
            </a>
            <a href="<?= $baseUrl ?>/security/patrol" class="btn btn-outline" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-person-military-pointing"></i> Buku Jurnal Patroli
            </a>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #ef4444;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Insiden Tercatat</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalIncidents ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Kasus</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tercatat pada tahun ini</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Terselesaikan di Tempat</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $resolvedCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Kasus</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">Tuntas dicegah / ditangani</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Eskalasi / Tindak Lanjut</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b; margin: 6px 0 2px 0;">
            <?= $escalatedCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Kasus</span>
        </div>
        <div style="font-size: 0.8125rem; color: #f59e0b; font-weight: 500;">Perlu koordinasi pimpinan</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- Form Input Insiden -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-plus" style="color: #ef4444;"></i> Catat Insiden / Kejadian
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/security/incidents/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Kategori Insiden <span style="color: #ef4444;">*</span></label>
                    <select name="category" class="form-control" required>
                        <option value="Kehilangan / Barang Tertinggal">Kehilangan / Barang Tertinggal</option>
                        <option value="Kerusakan Fasilitas">Kerusakan Fasilitas</option>
                        <option value="Pelanggaran Disiplin / Konflik">Pelanggaran Disiplin / Konflik</option>
                        <option value="Penyusup / Tamu Tak Dikenal">Penyusup / Tamu Tak Dikenal</option>
                        <option value="Insiden Lalu Lintas / Parkir">Insiden Lalu Lintas / Parkir</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Kronologi Kejadian <span style="color: #ef4444;">*</span></label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Kronologi kejadian secara singkat, jelas, dan faktual..." required></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tindakan Cepat yang Diambil <span style="color: #ef4444;">*</span></label>
                    <textarea name="action_taken" rows="3" class="form-control" placeholder="Tindakan penanganan langsung oleh petugas security..." required></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.875rem; color: #1e293b;">
                        <input type="checkbox" name="is_prevented" value="1" checked style="cursor: pointer;">
                        <span>Berhasil dicegah / diselesaikan langsung di tempat</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Laporan Insiden
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Jurnal Insiden -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check" style="color: #0d9488;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Buku Jurnal Kejadian &amp; Insiden</h3>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                    <input type="text" id="searchIncident" class="form-control" placeholder="Cari insiden / kronologi..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 220px;">
                </div>
                <span class="badge badge-secondary"><?= $totalIncidents ?> Kasus</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="width: 130px;">Waktu</th>
                            <th style="width: 150px;">Kategori</th>
                            <th>Kronologi Kejadian</th>
                            <th>Tindakan Petugas</th>
                            <th style="width: 110px; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($incidents)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-shield-cat" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Alhamdulillah, lingkungan sekolah tertib dan aman tanpa catatan insiden.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($incidents as $idx => $inc): 
                                $isPrev = !empty($inc['is_prevented']);
                            ?>
                                <tr class="inc-row" data-search="<?= strtolower(SecurityHelper::escape($inc['category'] . ' ' . $inc['description'] . ' ' . $inc['action_taken'])) ?>">
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.875rem;">
                                            <?= SecurityHelper::formatTanggalIndo($inc['incident_date']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted, #64748b); font-family: monospace;">
                                            <?= substr($inc['incident_time'], 0, 5) ?> WIB
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger" style="font-size: 0.75rem;">
                                            <?= SecurityHelper::escape($inc['category']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #1e293b;">
                                        <?= SecurityHelper::escape($inc['description']) ?>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #0d9488;">
                                        <?= SecurityHelper::escape($inc['action_taken']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($isPrev): ?>
                                            <span class="badge badge-success">
                                                <i class="fa-solid fa-circle-check" style="margin-right: 3px;"></i> Selesai
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 3px;"></i> Eskalasi
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
document.addEventListener('DOMContentLoaded', function() {
    const search = document.getElementById('searchIncident');
    const rows = document.querySelectorAll('.inc-row');
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
</script>
