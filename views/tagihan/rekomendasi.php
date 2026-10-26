<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalReminders = count($reminders ?? []);
$unremindedCount = count($unremindedBills ?? []);

$diajukanCount = 0;
$terkirimCount = 0;
$totalNominalDiajukan = 0;

foreach ($reminders as $r) {
    if ($r['status'] === 'Diajukan') {
        $diajukanCount++;
        $totalNominalDiajukan += (float)$r['amount'];
    } elseif ($r['status'] === 'Terkirim' || $r['status'] === 'Disetujui') {
        $terkirimCount++;
    }
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Tata Usaha</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Pengajuan Rekomendasi Surat</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-paper-plane" style="color: #0d9488;"></i> Ajukan Penagihan &amp; Rekomendasi SPP
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pilih dan ajukan daftar tagihan santri tertunggak kepada Kepala Sekolah secara masal (batch) untuk persetujuan penerbitan surat &amp; notifikasi email.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #ef4444;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Siap Diajukan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #ef4444; margin: 6px 0 2px 0;">
            <?= $unremindedCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Tagihan</span>
        </div>
        <div style="font-size: 0.8125rem; color: #ef4444; font-weight: 500;">Tertunggak belum diajukan</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Menunggu Persetujuan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #f59e0b; margin: 6px 0 2px 0;">
            <?= $diajukanCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Tagihan</span>
        </div>
        <div style="font-size: 0.8125rem; color: #f59e0b; font-weight: 500;">Di meja Kepala Sekolah</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Disetujui / Terkirim</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $terkirimCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Surat</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">Tuntas disetujui Kepsek</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Nominal Diajukan</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= SecurityHelper::formatRupiah($totalNominalDiajukan) ?>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Sedang dalam proses review</div>
    </div>
</div>

<!-- Section 1: Daftar Tagihan Tertunggak Belum Diajukan (Batch Bulk Submission) -->
<div class="card" style="margin-bottom: 24px;">
    <form action="<?= $baseUrl ?>/tagihan/rekomendasi/bulk-submit" method="POST" id="bulkSubmitForm">
        <?= CSRF::field() ?>

        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check" style="color: #ef4444;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Daftar Tagihan Tertunggak Siap Diajukan</h3>
                <span class="badge badge-danger"><?= $unremindedCount ?> Tagihan</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="btn btn-primary" id="btnBulkSubmit" style="font-weight: 600; display: inline-flex; align-items: center; gap: 6px;" <?= empty($unremindedBills) ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-paper-plane"></i> Ajukan Terpilih (<span id="bulkCounter">0</span>)
                </button>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllUnreminded" onchange="toggleSelectAll(this)" style="cursor: pointer;" <?= empty($unremindedBills) ? 'disabled' : '' ?>>
                            </th>
                            <th>Nama Santri &amp; NIS</th>
                            <th>Uraian Tagihan</th>
                            <th style="text-align: right; width: 140px;">Nominal</th>
                            <th style="text-align: center; width: 120px;">Jatuh Tempo</th>
                            <th>Email Orang Tua</th>
                            <th style="width: 90px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($unremindedBills)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-circle-check" style="font-size: 2rem; color: #10b981; margin-bottom: 8px; display: block;"></i>
                                    Alhamdulillah, seluruh tagihan tertunggak telah diajukan ke Kepala Sekolah.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($unremindedBills as $ub): ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="bill_ids[]" value="<?= $ub['id'] ?>" class="bill-chk" onchange="updateBulkCounter()" style="cursor: pointer;">
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a);">
                                            <?= SecurityHelper::escape($ub['student_name']) ?>
                                        </div>
                                        <small style="color: var(--text-muted, #64748b); font-family: monospace;">NIS: <?= SecurityHelper::escape($ub['nis']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= SecurityHelper::escape($ub['title']) ?></strong>
                                        <div style="font-size: 0.75rem; color: #64748b;">Bulan <?= $ub['bill_month'] ?>/<?= $ub['bill_year'] ?></div>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; font-family: monospace; color: #ef4444;">
                                        <?= SecurityHelper::formatRupiah((float)$ub['amount']) ?>
                                    </td>
                                    <td style="text-align: center; font-size: 0.8125rem;">
                                        <?= SecurityHelper::formatTanggalIndo($ub['due_date']) ?>
                                    </td>
                                    <td style="font-size: 0.8125rem; font-family: monospace; color: #475569;">
                                        <?= SecurityHelper::escape($ub['parent_email'] ?: '(Belum ada email)') ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.75rem;" onclick="submitSingle(<?= $ub['id'] ?>)">
                                            <i class="fa-solid fa-paper-plane"></i> Ajukan
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<!-- Hidden Form for Single Submit -->
<form id="singleSubmitForm" action="<?= $baseUrl ?>/tagihan/rekomendasi/submit" method="POST" style="display: none;">
    <?= CSRF::field() ?>
    <input type="hidden" name="bill_id" id="singleBillId">
</form>

<!-- Section 2: Riwayat Rekomendasi & Status Persetujuan Kepala Sekolah -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-clock-rotate-left" style="color: #0d9488;"></i>
            <h3 class="card-title" style="margin: 0; font-size: 1rem;">Riwayat Pengajuan &amp; Status Pengiriman Surat Tagihan</h3>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <div style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                <input type="text" id="searchReminder" class="form-control" placeholder="Cari santri / surat..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 220px;">
            </div>
            <span class="badge badge-secondary"><?= $totalReminders ?> Data</span>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Santri &amp; NIS</th>
                        <th>Uraian Tagihan</th>
                        <th style="text-align: right; width: 130px;">Nominal</th>
                        <th style="width: 140px; text-align: center;">No. Surat</th>
                        <th>Diajukan Oleh</th>
                        <th style="width: 130px; text-align: center;">Status</th>
                        <th style="width: 80px; text-align: center;">Surat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reminders)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                <i class="fa-solid fa-envelope-open" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                Belum ada riwayat pengajuan surat tagihan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reminders as $idx => $r): 
                            $status = $r['status'] ?? 'Diajukan';
                            $statusBadge = match($status) {
                                'Terkirim'  => 'badge-success',
                                'Disetujui' => 'badge-info',
                                'Ditolak'   => 'badge-danger',
                                default     => 'badge-warning'
                            };
                        ?>
                            <tr class="reminder-row" data-search="<?= strtolower(SecurityHelper::escape($r['student_name'] . ' ' . $r['nis'] . ' ' . ($r['letter_number'] ?? '') . ' ' . $status)) ?>">
                                <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                        <?= SecurityHelper::escape($r['student_name']) ?>
                                    </div>
                                    <small style="color: var(--text-muted, #64748b); font-family: monospace;">NIS: <?= SecurityHelper::escape($r['nis']) ?></small>
                                </td>
                                <td>
                                    <strong><?= SecurityHelper::escape($r['title']) ?></strong>
                                </td>
                                <td style="text-align: right; font-weight: 700; font-family: monospace; color: #ef4444;">
                                    <?= SecurityHelper::formatRupiah((float)$r['amount']) ?>
                                </td>
                                <td style="text-align: center; font-family: monospace; font-size: 0.8125rem;">
                                    <?= SecurityHelper::escape($r['letter_number']) ?>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; color: #334155;"><?= SecurityHelper::escape($r['proposer_name']) ?></div>
                                    <small style="color: #94a3b8;"><?= SecurityHelper::formatTanggalIndo($r['created_at']) ?></small>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge <?= $statusBadge ?>">
                                        <?= SecurityHelper::escape($status) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="<?= $baseUrl ?>/tagihan/print-surat/<?= $r['bill_id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.75rem; color: #ef4444;" title="Cetak Surat Tagihan PDF">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const search = document.getElementById('searchReminder');
    const rows = document.querySelectorAll('.reminder-row');
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

function toggleSelectAll(master) {
    const chks = document.querySelectorAll('.bill-chk');
    chks.forEach(function(chk) {
        chk.checked = master.checked;
    });
    updateBulkCounter();
}

function updateBulkCounter() {
    const checked = document.querySelectorAll('.bill-chk:checked').length;
    document.getElementById('bulkCounter').innerText = checked;
}

function submitSingle(billId) {
    if (!confirm('Ajukan rekomendasi surat tagihan untuk santri ini ke Kepala Sekolah?')) return;
    document.getElementById('singleBillId').value = billId;
    document.getElementById('singleSubmitForm').submit();
}

document.getElementById('bulkSubmitForm')?.addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.bill-chk:checked').length;
    if (checked === 0) {
        e.preventDefault();
        alert('Centang minimal satu tagihan untuk diajukan.');
        return false;
    }
    if (!confirm('Ajukan ' + checked + ' rekomendasi surat tagihan sekaligus ke Kepala Sekolah?')) {
        e.preventDefault();
        return false;
    }
});
</script>
