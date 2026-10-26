<?php
// views/parent/bills.php
use Core\SecurityHelper;
use Core\Auth;
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= SecurityHelper::escape($pageTitle) ?></h1>
        <div class="page-subtitle">
            Informasi status kewajiban SPP, rekening resmi pembayaran, dan riwayat tagihan ananda.
        </div>
    </div>
</div>

<!-- KARTU INFORMASI UTAMA & REKENING PEMBAYARAN -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card" style="border-left: 4px solid <?= $totalTunggakan > 0 ? '#ef4444' : '#10b981' ?>;">
        <div class="stat-icon <?= $totalTunggakan > 0 ? 'rose' : 'emerald' ?>">
            <i class="fa-solid <?= $totalTunggakan > 0 ? 'fa-clock-rotate-left' : 'fa-circle-check' ?>"></i>
        </div>
        <div>
            <div class="stat-value" style="color: <?= $totalTunggakan > 0 ? '#dc2626' : '#166534' ?>;">
                <?= SecurityHelper::formatRupiah((float)$totalTunggakan) ?>
            </div>
            <div class="stat-label">
                <?= $totalTunggakan > 0 ? 'Total Tagihan Belum Lunas' : 'Seluruh Tagihan Lunas' ?>
            </div>
        </div>
    </div>

    <!-- Rekening Pembayaran Resmi per Unit Sekolah Anak -->
    <?php if (!empty($unitAccounts)): ?>
        <?php foreach ($unitAccounts as $ua): ?>
            <div class="stat-card" style="border-left: 4px solid #0d9488;">
                <div class="stat-icon emerald">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 4px;">
                        <span class="badge badge-info" style="font-size: 10px;">
                            <i class="fa-solid fa-school" style="margin-right: 3px;"></i> <?= SecurityHelper::escape($ua['unit_name']) ?>
                        </span>
                        <?php if (!empty($ua['bank_account']) && $ua['bank_account'] !== '-'): ?>
                            <button type="button" class="btn btn-outline btn-xs" style="padding: 2px 7px; font-size: 10px;" onclick="copyRekening('<?= SecurityHelper::escape($ua['bank_account']) ?>', this)">
                                <i class="fa-regular fa-copy"></i> Salin
                            </button>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 13.5px; font-weight: 700; color: var(--text-main);">
                        <?= SecurityHelper::escape($ua['bank_name']) ?>
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f766e; letter-spacing: 0.5px; margin-top: 2px;">
                        No. Rek: <?= SecurityHelper::escape($ua['bank_account']) ?>
                    </div>
                    <div class="stat-label" style="font-size: 11px;">a.n. <?= SecurityHelper::escape($ua['bank_holder']) ?></div>
                    <?php if (!empty($ua['children'])): ?>
                        <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 4px; border-top: 1px dashed #e2e8f0; padding-top: 3px;">
                            Kewajiban untuk: <strong><?= SecurityHelper::escape(implode(', ', $ua['children'])) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="stat-card" style="border-left: 4px solid #0d9488;">
            <div class="stat-icon emerald">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--text-main);">
                    Bank Syariah Indonesia (BSI)
                </div>
                <div style="font-size: 14px; font-weight: 700; color: #0f766e; letter-spacing: 0.5px; margin-top: 2px;">
                    No. Rek: 7123456789
                </div>
                <div class="stat-label" style="font-size: 11px;">a.n. Yayasan Islam Al Kahfi Batam</div>
            </div>
        </div>
    <?php endif; ?>

    <div class="stat-card" style="border-left: 4px solid #3b82f6;">
        <div class="stat-icon blue">
            <i class="fa-solid fa-headset"></i>
        </div>
        <div>
            <div style="font-size: 14px; font-weight: 700; color: var(--text-main);">
                Konfirmasi Pembayaran
            </div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                Loket Tata Usaha / Keuangan
            </div>
            <div class="stat-label" style="font-size: 11px;">Kirim bukti transfer ke WhatsApp TU</div>
        </div>
    </div>
</div>

<script>
function copyRekening(text, btn) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            const oldHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Disalin!';
            btn.classList.add('btn-success');
            setTimeout(() => {
                btn.innerHTML = oldHtml;
                btn.classList.remove('btn-success');
            }, 2000);
        });
    }
}
</script>

<!-- INFORMASI SANTRI TERDAFTAR -->
<?php if (!empty($children)): ?>
    <div class="card" style="margin-bottom: 24px; background: #f8fafc;">
        <div class="card-body" style="padding: 16px 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-children" style="color: var(--primary); font-size: 18px;"></i>
                    <strong style="font-size: 13px; color: var(--text-main);">Santri Terdaftar di Akun Ini:</strong>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <?php foreach ($children as $ch): ?>
                        <span class="badge badge-info" style="font-size: 12px; padding: 6px 12px;">
                            <i class="fa-solid fa-user-graduate me-1" style="margin-right: 4px;"></i>
                            <?= SecurityHelper::escape($ch['nama']) ?> 
                            (<?= SecurityHelper::escape($ch['unit_name']) ?> - NIS: <?= SecurityHelper::escape($ch['nis']) ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- TABEL RIWAYAT TAGIHAN -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-receipt" style="color: var(--primary); margin-right: 8px;"></i>
            Riwayat Tagihan & Pembayaran SPP
        </div>
        <span class="badge badge-secondary"><?= count($bills) ?> Catatan</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($bills)): ?>
            <div style="text-align: center; padding: 48px 20px;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #dcfce7; color: #166534; display: inline-flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 12px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h4 style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                    Tidak Ada Catatan Tunggakan SPP
                </h4>
                <p style="font-size: 13px; color: var(--text-muted); max-width: 520px; margin: 0 auto; line-height: 1.5;">
                    <?php if (!empty($children)): ?>
                        Alhamdulillah, seluruh kewajiban untuk ananda <strong><?= SecurityHelper::escape(implode(', ', array_column($children, 'nama'))) ?></strong> telah lunas atau belum ada tagihan SPP baru yang diterbitkan oleh Tata Usaha.
                    <?php else: ?>
                        Alhamdulillah, belum ada catatan tagihan SPP aktif untuk akun Anda.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Nama Santri</th>
                            <th>Uraian Tagihan</th>
                            <th>Periode</th>
                            <th>Nominal</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th style="width: 140px; text-align: center;">Surat Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $idx => $b): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong><?= SecurityHelper::escape($b['student_name']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);">NIS: <?= SecurityHelper::escape($b['nis']) ?> | <?= SecurityHelper::escape($b['unit_name']) ?></div>
                                </td>
                                <td>
                                    <strong><?= SecurityHelper::escape($b['title']) ?></strong>
                                    <?php if (!empty($b['notes'])): ?>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?= SecurityHelper::escape($b['notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    Bulan <?= SecurityHelper::getNamaBulan((int)$b['bill_month']) ?> <?= $b['bill_year'] ?>
                                </td>
                                <td style="font-weight: 700; color: var(--text-main);">
                                    <?= SecurityHelper::formatRupiah((float)$b['amount']) ?>
                                </td>
                                <td style="font-size: 12px;"><?= SecurityHelper::formatTanggalIndo($b['due_date']) ?></td>
                                <td>
                                    <?php if ($b['status'] === 'Lunas'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Lunas
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">
                                            <i class="fa-solid fa-clock" style="margin-right: 4px;"></i> Belum Lunas
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($b['status'] === 'Tertunggak'): ?>
                                        <a href="<?= Auth::baseUrl() ?>/parent/surat-tagihan/<?= $b['id'] ?>" target="_blank" class="btn btn-outline btn-sm">
                                            <i class="fa-solid fa-file-pdf"></i> Surat PDF
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: var(--success); font-weight: 600;">
                                            <i class="fa-solid fa-check"></i> Selesai
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
