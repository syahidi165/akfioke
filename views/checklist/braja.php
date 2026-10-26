<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalTrx = $totalTrx ?? 0;
$totalNominal = $totalNominal ?? 0;
$totalError = $totalError ?? 0;
$brajaList = $brajaList ?? [];
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Tata Usaha</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tertib Administrasi Keuangan</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-book-open" style="color: #0d9488;"></i> Buku Rawat Jaga (Braja) &amp; Rekonsiliasi Belanja
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pencatatan nota belanja operasional sekolah, verifikasi bukti kas keluar, dan monitoring kesalahan belanja untuk indikator AKFI TU.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Transaksi</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalTrx ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Nota</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tercatat pada tahun ini</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Belanja</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= SecurityHelper::formatRupiah((float)$totalNominal) ?>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Akumulasi kas keluar</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid <?= $totalError === 0 ? '#10b981' : '#ef4444' ?>;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Kesalahan Nota / Selisih</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: <?= $totalError === 0 ? '#10b981' : '#ef4444' ?>; margin: 6px 0 2px 0;">
            <?= $totalError ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Kasus</span>
        </div>
        <div style="font-size: 0.8125rem; color: <?= $totalError === 0 ? '#10b981' : '#ef4444' ?>; font-weight: 500;">
            <?= $totalError === 0 ? 'Tertib 100% (Target = 0)' : 'Perlu Perbaikan / Koreksi' ?>
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Ketepatan Rekonsiliasi</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #8b5cf6; margin: 6px 0 2px 0;">
            <?= $totalTrx > 0 ? round((($totalTrx - $totalError) / $totalTrx) * 100, 1) : 100 ?>%
        </div>
        <div style="font-size: 0.8125rem; color: #8b5cf6; font-weight: 500;">Validitas pembukuan</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- Form Input Braja -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-receipt" style="color: #0d9488;"></i> Input Transaksi Belanja Braja
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/checklist/braja/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tanggal Nota / Belanja <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="trx_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Uraian Kebutuhan / Barang <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="item_desc" class="form-control" placeholder="Contoh: Kertas HVS 10 Rim + Tinta Printer" required>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nominal Belanja (Rp) <span style="color: #ef4444;">*</span></label>
                    <input type="number" step="1000" name="amount" class="form-control" placeholder="Contoh: 350000" required>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Status Rekonsiliasi &amp; Kesalahan Nota</label>
                    <select name="is_error" class="form-control">
                        <option value="0">Tepat &amp; Sesuai Anggaran (0 Kesalahan)</option>
                        <option value="1">Terdapat Koreksi / Selisih Nota (1 Kesalahan)</option>
                    </select>
                    <small style="color: var(--text-muted, #64748b); font-size: 0.75rem; margin-top: 4px; display: block;">
                        Indikator AKFI TU menghendaki 0 kesalahan belanja per semester.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Catatan Braja
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Jurnal Braja -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check" style="color: #0d9488;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Jurnal Buku Rawat Jaga (Braja)</h3>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                    <input type="text" id="searchBraja" class="form-control" placeholder="Cari uraian belanja..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 220px;">
                </div>
                <span class="badge badge-secondary"><?= $totalTrx ?> Transaksi</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="width: 120px;">Tanggal</th>
                            <th>Uraian Pengadaan / Belanja</th>
                            <th style="text-align: right; width: 140px;">Nominal</th>
                            <th style="width: 140px; text-align: center;">Status Rekonsiliasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($brajaList)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada transaksi belanja yang dicatat pada Buku Rawat Jaga.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($brajaList as $idx => $b): 
                                $isErr = ((int)$b['is_error'] === 1);
                            ?>
                                <tr class="braja-row" data-search="<?= strtolower(SecurityHelper::escape($b['item_desc'] . ' ' . $b['trx_date'])) ?>">
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td style="font-size: 0.8125rem; font-family: monospace;">
                                        <?= SecurityHelper::formatTanggalIndo($b['trx_date']) ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a);">
                                            <?= SecurityHelper::escape($b['item_desc']) ?>
                                        </div>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; font-family: monospace; color: #0f172a;">
                                        <?= SecurityHelper::formatRupiah((float)$b['amount']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($isErr): ?>
                                            <span class="badge badge-danger">
                                                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 3px;"></i> Ada Koreksi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-success">
                                                <i class="fa-solid fa-circle-check" style="margin-right: 3px;"></i> Valid &amp; Sesuai
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
    const search = document.getElementById('searchBraja');
    const rows = document.querySelectorAll('.braja-row');
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
