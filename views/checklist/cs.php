<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalChecklists = count($checklists ?? []);
$harianCount = 0;
$bulananCount = 0;
$gotongCount = 0;

foreach ($checklists as $c) {
    $tp = $c['check_type'] ?? '';
    if (strpos($tp, 'Harian') !== false) {
        $harianCount++;
    } elseif (strpos($tp, 'Bulanan') !== false) {
        $bulananCount++;
    } else {
        $gotongCount++;
    }
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Cleaning Service</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Kebersihan &amp; Sanitasi Lingkungan</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-broom" style="color: #0d9488;"></i> Checklist Petugas Cleaning Service (Rutin &amp; Piket)
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pencatatan tugas kebersihan harian (toilet, koridor, kelas), piket mingguan/bulanan, dan gotong royong area luar.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Laporan CS</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalChecklists ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Aktivitas</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tercatat pada tahun ini</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Rutin Harian</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $harianCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Hari</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">Sapu, pel &amp; toilet 2x</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Piket Bulanan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $bulananCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Sesi</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Kaca, kipas, plafon</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Gotong Royong</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b; margin: 6px 0 2px 0;">
            <?= $gotongCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Kegiatan</span>
        </div>
        <div style="font-size: 0.8125rem; color: #f59e0b; font-weight: 500;">Saluran &amp; halaman luar</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- Form Input CS -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-plus" style="color: #0d9488;"></i> Input Checklist Kebersihan
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/checklist/cs/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Jenis Tugas Kebersihan <span style="color: #ef4444;">*</span></label>
                    <select name="check_type" class="form-control" required>
                        <option value="Rutin Harian">Rutin Harian (Sapu, Pel, Toilet 2x, Sampah)</option>
                        <option value="Piket Bulanan">Piket Bulanan (Pembersihan Kaca, Kipas, Sarang Laba-laba)</option>
                        <option value="Gotong Royong">Gotong Royong Bersama (Area Luar &amp; Saluran Air)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tanggal Pelaksanaan <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="check_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Keterangan / Area yang Dikerjakan</label>
                    <textarea name="notes" rows="4" class="form-control" placeholder="Contoh: Toilet lantai 1 dan 2 telah dikuras dan wangi, selasar depan bersih dan sampah telah dibuang..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Laporan Kebersihan
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Rekap CS -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check" style="color: #0d9488;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Rekap Pelaksanaan Tugas Cleaning Service</h3>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                    <input type="text" id="searchCs" class="form-control" placeholder="Cari petugas / area..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 200px;">
                </div>
                <span class="badge badge-secondary"><?= $totalChecklists ?> Catatan</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="width: 120px;">Tanggal</th>
                            <th>Petugas CS</th>
                            <th style="width: 130px; text-align: center;">Kategori Tugas</th>
                            <th>Keterangan Area</th>
                            <th style="width: 120px; text-align: center;">Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($checklists)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-broom" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada catatan tugas kebersihan yang tersimpan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($checklists as $idx => $c): 
                                $tp = $c['check_type'] ?? '';
                                $badgeClass = 'badge-primary';
                                if (strpos($tp, 'Harian') !== false) {
                                    $badgeClass = 'badge-success';
                                } elseif (strpos($tp, 'Bulanan') !== false) {
                                    $badgeClass = 'badge-info';
                                } else {
                                    $badgeClass = 'badge-warning';
                                }
                            ?>
                                <tr class="cs-row" data-search="<?= strtolower(SecurityHelper::escape(($c['officer_name'] ?? '') . ' ' . $tp . ' ' . ($c['notes'] ?? ''))) ?>">
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td style="font-size: 0.8125rem; font-family: monospace;">
                                        <?= SecurityHelper::formatTanggalIndo($c['check_date']) ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a);">
                                            <?= SecurityHelper::escape($c['officer_name'] ?? 'Petugas CS') ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $badgeClass ?>"><?= SecurityHelper::escape($tp) ?></span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #334155;">
                                        <?= SecurityHelper::escape($c['notes']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-circle-check" style="margin-right: 3px;"></i> Terverifikasi
                                        </span>
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
    const search = document.getElementById('searchCs');
    const rows = document.querySelectorAll('.cs-row');
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
