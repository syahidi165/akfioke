<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalPatrols = count($patrols ?? []);
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Security</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Kontrol Lingkungan &amp; Patroli</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-person-military-pointing" style="color: #0d9488;"></i> Buku Jurnal Patroli Keliling Security
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pencatatan putaran kontrol rute perimeter sekolah, gerbang, ruang kelas malam, masjid, dan temuan kondisi keamanan fisik.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="<?= $baseUrl ?>/checklist/security" class="btn btn-outline" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-calendar-check"></i> Checklist Harian
            </a>
            <a href="<?= $baseUrl ?>/security/incidents" class="btn btn-outline" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-triangle-exclamation"></i> Buku Insiden
            </a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- Form Input Patroli -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-plus" style="color: #0d9488;"></i> Input Putaran Patroli
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/security/patrol/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Jadwal Jam Patroli <span style="color: #ef4444;">*</span></label>
                    <input type="time" name="schedule_time" class="form-control" value="<?= date('H:00') ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Lokasi Rute Kontrol <span style="color: #ef4444;">*</span></label>
                    <select name="location" class="form-control" required>
                        <option value="Perimeter & Pagar Luar">Perimeter &amp; Pagar Luar</option>
                        <option value="Gedung Kelas & Koridor">Gedung Kelas &amp; Koridor</option>
                        <option value="Area Parkir & Lapangan">Area Parkir &amp; Lapangan</option>
                        <option value="Masjid & Tempat Wudhu">Masjid &amp; Tempat Wudhu</option>
                        <option value="Kantin & Area Belakang">Kantin &amp; Area Belakang</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Temuan / Kondisi Lapangan</label>
                    <textarea name="findings" rows="4" class="form-control" placeholder="Contoh: Kondisi aman, pintu jendela kelas terkunci rapat, lampu lorong menyala normal, pagar terkunci..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Catatan Patroli
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Jurnal Patroli -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check" style="color: #0d9488;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Jurnal Putaran Patroli Sekolah</h3>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                    <input type="text" id="searchPatrol" class="form-control" placeholder="Cari rute / temuan..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 200px;">
                </div>
                <span class="badge badge-secondary"><?= $totalPatrols ?> Putaran</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="width: 150px;">Tanggal &amp; Waktu</th>
                            <th>Petugas Security</th>
                            <th style="width: 170px;">Lokasi Rute</th>
                            <th>Hasil Temuan Patroli</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patrols)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-person-walking" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada catatan jurnal patroli yang tersimpan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patrols as $idx => $p): ?>
                                <tr class="patrol-row" data-search="<?= strtolower(SecurityHelper::escape(($p['officer_name'] ?? '') . ' ' . $p['location'] . ' ' . ($p['findings'] ?? ''))) ?>">
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.875rem;">
                                            <?= SecurityHelper::formatTanggalIndo($p['patrol_date']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted, #64748b); font-family: monospace;">
                                            Jadwal: <?= substr($p['schedule_time'], 0, 5) ?> | Aktual: <?= substr($p['actual_time'], 0, 5) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #1e293b;">
                                            <?= SecurityHelper::escape($p['officer_name'] ?? 'Petugas Security') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-location-dot" style="margin-right: 3px;"></i> <?= SecurityHelper::escape($p['location']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #334155;">
                                        <?= SecurityHelper::escape($p['findings'] ?: 'Aman terkendali.') ?>
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
    const search = document.getElementById('searchPatrol');
    const rows = document.querySelectorAll('.patrol-row');
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
