<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalPrestasi = count($prestasi ?? []);
$juara1Count = 0;
$juaraLainCount = 0;
$certCount = 0;

foreach ($prestasi as $p) {
    $rank = strtolower($p['rank_position'] ?? '');
    if (strpos($rank, '1') !== false || strpos($rank, 'pertama') !== false) {
        $juara1Count++;
    } else {
        $juaraLainCount++;
    }
    if (!empty($p['certificate_path'])) {
        $certCount++;
    }
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Wali Kelas</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Prestasi Eksternal Santri</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-trophy" style="color: #f59e0b;"></i> Prestasi Lomba Santri Non-Kedinasan / Swasta
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pencatatan prestasi santri pada ajang kejuaraan swasta, turnamen terbuka, dan kompetisi mandiri luar dinas beserta bukti sertifikat.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Prestasi Swasta</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalPrestasi ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Kejuaraan</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tercatat di rombel ini</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Juara 1 / Emas</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $juara1Count ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Piala</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">Peringkat Tertinggi</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Juara 2, 3 &amp; Harapan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $juaraLainCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Kejuaraan</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Podium Kejuaraan</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Bukti Sertifikat</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #8b5cf6; margin: 6px 0 2px 0;">
            <?= $certCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Berkas</span>
        </div>
        <div style="font-size: 0.8125rem; color: #8b5cf6; font-weight: 500;">Dokumen Terunggah</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">
    <!-- Form Card -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-plus" style="color: #f59e0b;"></i> Input Prestasi Santri Swasta
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/prestasi/walas/save" method="POST" enctype="multipart/form-data">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Pilih Santri di Kelas <span style="color: #ef4444;">*</span></label>
                    <select name="student_id" class="form-control" required>
                        <option value="">-- Pilih Santri --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>"><?= SecurityHelper::escape($st['nama']) ?> (NIS: <?= SecurityHelper::escape($st['nis']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nama Lomba / Event Swasta <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="event_name" class="form-control" placeholder="Contoh: Lomba Dai Cilik TVRI, Turnamen Taekwondo Open..." required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Capaian Juara</label>
                        <input type="text" name="rank_position" class="form-control" value="Juara 1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tingkat Event</label>
                        <input type="text" name="level" class="form-control" value="Tingkat Kota Batam" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Unggah Sertifikat / Piagam (PDF/JPG/PNG)</label>
                    <input type="file" name="certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <small style="color: var(--text-muted, #64748b); font-size: 0.75rem;">Maksimal 5MB. Menjadi dokumen bukti portofolio santri.</small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Prestasi Swasta
                </button>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check" style="color: #0d9488;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Rekap Prestasi Lomba Swasta &amp; Eksternal</h3>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                    <input type="text" id="searchPrestasiWalas" class="form-control" placeholder="Cari santri / lomba..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 220px;">
                </div>
                <span class="badge badge-secondary"><?= $totalPrestasi ?> Catatan</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th>Nama Santri &amp; NIS</th>
                            <th>Ajang Lomba / Kejuaraan</th>
                            <th style="width: 130px; text-align: center;">Peringkat</th>
                            <th style="width: 140px; text-align: center;">Tingkat</th>
                            <th style="width: 100px; text-align: center;">Sertifikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($prestasi)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-award" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada data prestasi lomba swasta yang dicatat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($prestasi as $idx => $p): 
                                $rankText = strtolower($p['rank_position'] ?? '');
                                $rankBadge = 'badge-primary';
                                if (strpos($rankText, '1') !== false || strpos($rankText, 'emas') !== false) {
                                    $rankBadge = 'badge-warning';
                                } elseif (strpos($rankText, '2') !== false || strpos($rankText, 'perak') !== false) {
                                    $rankBadge = 'badge-secondary';
                                } elseif (strpos($rankText, '3') !== false || strpos($rankText, 'perunggu') !== false) {
                                    $rankBadge = 'badge-info';
                                }
                            ?>
                                <tr class="prestasi-walas-row" data-search="<?= strtolower(SecurityHelper::escape($p['student_name'] . ' ' . $p['nis'] . ' ' . $p['event_name'] . ' ' . $p['rank_position'] . ' ' . ($p['level'] ?? ''))) ?>">
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                            <?= SecurityHelper::escape($p['student_name']) ?>
                                        </div>
                                        <small style="color: var(--text-muted, #64748b); font-family: monospace;">NIS: <?= SecurityHelper::escape($p['nis']) ?></small>
                                    </td>
                                    <td>
                                        <strong style="color: #0f172a;"><?= SecurityHelper::escape($p['event_name']) ?></strong>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $rankBadge ?>" style="font-weight: 600;">
                                            <i class="fa-solid fa-medal" style="margin-right: 4px;"></i> <?= SecurityHelper::escape($p['rank_position']) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-secondary"><?= SecurityHelper::escape($p['level']) ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($p['certificate_path'])): ?>
                                            <a href="<?= $baseUrl ?>/<?= SecurityHelper::escape($p['certificate_path']) ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.75rem; color: #0d9488;" title="Lihat Berkas Sertifikat">
                                                <i class="fa-solid fa-file-pdf"></i> Bukti
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 0.75rem;">-</span>
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
    const search = document.getElementById('searchPrestasiWalas');
    const rows = document.querySelectorAll('.prestasi-walas-row');
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
