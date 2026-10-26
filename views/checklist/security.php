<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalHistory = count($history ?? []);
$onTimeCount = 0;
$completeCount = 0;

foreach ($history as $h) {
    if (!empty($h['is_on_time'])) $onTimeCount++;
    if (!empty($h['is_complete'])) $completeCount++;
}

$onTimePct = $totalHistory > 0 ? round(($onTimeCount / $totalHistory) * 100) : 100;
$completePct = $totalHistory > 0 ? round(($completeCount / $totalHistory) * 100) : 100;
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Security</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Keamanan &amp; Ketertiban Lingkungan</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-shield-halved" style="color: #0d9488;"></i> Checklist Harian Petugas Keamanan (Security)
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pengecekan kesiapan pos jaga, kepatuhan atribut SOP, kontrol perimeter sekolah, dan kedisiplinan waktu pengisian real-time.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="<?= $baseUrl ?>/security/patrol" class="btn btn-outline" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-person-military-pointing"></i> Buku Jurnal Patroli
            </a>
            <a href="<?= $baseUrl ?>/security/incidents" class="btn btn-outline" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-triangle-exclamation"></i> Buku Insiden
            </a>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Riwayat Jaga Terpantau</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalHistory ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Hari</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tercatat 30 hari terakhir</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Ketepatan Waktu (&le; 07:15)</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $onTimePct ?>%
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">
            <?= $onTimeCount ?> dari <?= $totalHistory ?> hari On-Time
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Kepatuhan SOP Lengkap</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $completePct ?>%
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">
            <?= $completeCount ?> hari lengkap 7/7 poin
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Status Hari Ini</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: <?= !empty($todayCheck) ? '#10b981' : '#f59e0b' ?>; margin: 8px 0 4px 0;">
            <?= !empty($todayCheck) ? 'Sudah Diisi' : 'Belum Diisi' ?>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">
            Tanggal: <?= SecurityHelper::formatTanggalIndo($today) ?>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start; margin-bottom: 24px;">
    <!-- Form Checklist Hari Ini -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check" style="color: #0d9488;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Checklist Pos Jaga Hari Ini</h3>
            </div>
            <span class="badge badge-primary" style="font-family: monospace; font-size: 0.8125rem;">
                <?= SecurityHelper::formatTanggalIndo($today) ?>
            </span>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/checklist/security/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem; margin-bottom: 10px; display: block;">
                        Daftar 7 Butir Pengecekan Pos Standar SOP:
                    </label>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 12px 16px; display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($items as $num => $txt): 
                            $isChecked = !empty($savedItems[$num]);
                        ?>
                            <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 0.875rem; color: #1e293b;">
                                <input type="checkbox" name="check[<?= $num ?>]" value="1" <?= $isChecked ? 'checked' : '' ?> style="margin-top: 3px; cursor: pointer;">
                                <span><strong><?= $num ?>.</strong> <?= SecurityHelper::escape($txt) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Catatan Mutasi Jaga &amp; Kondisi Pos</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="Contoh: Situasi pos aman kondusif, serah terima kunci lengkap, cuaca cerah..."><?= SecurityHelper::escape($todayCheck['notes'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Checklist Pos Jaga
                </button>
            </form>
        </div>
    </div>

    <!-- Informasi Standar SOP Security -->
    <div class="card" style="background: #f0fdfa; border-color: #ccfbf1;">
        <div class="card-header" style="border-bottom: 1px solid #ccfbf1; padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 0.95rem; color: #0f766e; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-info" style="color: #0d9488;"></i> Ketentuan &amp; SOP Pos Security
            </h3>
        </div>
        <div class="card-body" style="padding: 18px 20px; font-size: 0.8125rem; color: #134e4a; line-height: 1.6;">
            <ul style="margin: 0; padding-left: 18px; display: flex; flex-direction: column; gap: 10px;">
                <li><strong>Terkunci Server Date:</strong> Checklist otomatis mengikat pada tanggal server hari ini guna menjamin akuntabilitas pengisian secara real-time.</li>
                <li><strong>Ketepatan Waktu:</strong> Pengisian sebelum pukul <strong>07:15 WIB</strong> dinilai sebagai <em>On-Time</em> yang berpengaruh langsung ke skor AKFI Security.</li>
                <li><strong>Kelengkapan Poin:</strong> Seluruh 7 poin harus tercentang agar berpredikat <strong>Lengkap (100%)</strong>.</li>
                <li><strong>Buku Patroli:</strong> Gunakan menu patroli setiap selesai putaran kontrol gerbang, kelas, dan perimeter.</li>
            </ul>
        </div>
    </div>
</div>

<!-- Riwayat Checklist Security -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-clock-rotate-left" style="color: #0d9488;"></i>
            <h3 class="card-title" style="margin: 0; font-size: 1rem;">Riwayat Checklist Pos Jaga Security (30 Hari Terakhir)</h3>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <div style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                <input type="text" id="searchSecurity" class="form-control" placeholder="Cari tanggal / petugas..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 220px;">
            </div>
            <span class="badge badge-secondary"><?= $totalHistory ?> Hari</span>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 130px;">Tanggal</th>
                        <th>Petugas Jaga</th>
                        <th style="width: 140px; text-align: center;">Ketepatan Waktu</th>
                        <th style="width: 140px; text-align: center;">Kelengkapan SOP</th>
                        <th>Catatan Pos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-shield-slash" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                Belum ada riwayat checklist security.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $idx => $h): ?>
                            <tr class="sec-row" data-search="<?= strtolower(SecurityHelper::escape($h['officer_name'] . ' ' . $h['check_date'] . ' ' . ($h['notes'] ?? ''))) ?>">
                                <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                <td style="font-family: monospace; font-size: 0.8125rem;">
                                    <?= SecurityHelper::formatTanggalIndo($h['check_date']) ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main, #0f172a);">
                                        <?= SecurityHelper::escape($h['officer_name'] ?? 'Petugas Security') ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($h['is_on_time'])): ?>
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-clock" style="margin-right: 3px;"></i> On-Time
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            <i class="fa-solid fa-clock" style="margin-right: 3px;"></i> Terlambat
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if (!empty($h['is_complete'])): ?>
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-circle-check" style="margin-right: 3px;"></i> Lengkap (7/7)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Sebagian</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.8125rem; color: #475569;">
                                    <?= SecurityHelper::escape($h['notes'] ?: '-') ?>
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
    const search = document.getElementById('searchSecurity');
    const rows = document.querySelectorAll('.sec-row');
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
