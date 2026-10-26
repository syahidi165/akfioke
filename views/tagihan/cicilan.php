<?php
// views/tagihan/cicilan.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; padding: 4px 10px; border-radius: 6px; font-weight: 600;">Tata Usaha</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Pengelolaan Angsuran &amp; Kartu Kontrol Pembayaran</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px; font-weight: 700; color: #0f172a;">
                <i class="fa-solid fa-book-bookmark" style="color: #0284c7;"></i> Buku &amp; Kartu Cicilan Siswa (FR 8001D)
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Penerimaan pembayaran angsuran cicilan orang tua yang telah disetujui Kepala Sekolah atau Deputi Direktur Pendidikan. <strong>Data cicilan tetap tersimpan melintasi tahun ajaran baru hingga lunas</strong>.
            </p>
        </div>
        <div>
            <a href="<?= $baseUrl ?>/tagihan/manage" class="btn btn-outline" style="color: #0f766e; border-color: #0f766e; font-weight: 600;">
                <i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Kembali ke Input Tagihan (FR 8001A)
            </a>
        </div>
    </div>
</div>

<!-- Stats Card Cicilan -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #ef4444; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">Sisa Saldo Tunggakan Berjalan</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #ef4444; margin: 6px 0 2px 0; font-family: monospace;">
            <?= SecurityHelper::formatRupiah($totalSisa) ?>
        </div>
        <div style="font-size: 0.8125rem; color: #64748b;">
            Dari <strong><?= $activeCount ?> siswa</strong> yang sedang mencicil
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">Total Cicilan Masuk (Terbayar)</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #10b981; margin: 6px 0 2px 0; font-family: monospace;">
            <?= SecurityHelper::formatRupiah($totalTerbayar) ?>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 600;">
            Penerimaan kas kasir TU
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">Total Tunggakan Disepakati</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #0284c7; margin: 6px 0 2px 0; font-family: monospace;">
            <?= SecurityHelper::formatRupiah($totalTunggakanAktif) ?>
        </div>
        <div style="font-size: 0.8125rem; color: #64748b;">
            Komitmen perjanjian resmi FR 8001D
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #8b5cf6; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600;">Kesepakatan Telah Lunas</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #8b5cf6; margin: 6px 0 2px 0;">
            <?= $lunasCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Santri</span>
        </div>
        <div style="font-size: 0.8125rem; color: #8b5cf6; font-weight: 600;">
            Selesai 100% tanpa tunggakan
        </div>
    </div>
</div>

<!-- Tabel Daftar Buku Cicilan Siswa -->
<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-address-book" style="color: #0284c7;"></i>
            <h3 class="card-title" style="margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a;">Daftar Buku &amp; Kartu Kontrol Cicilan Siswa</h3>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" id="searchCicilan" class="form-control" placeholder="Cari santri / nomor..." style="font-size: 0.8125rem; height: 34px; width: 200px;">
            <select id="filterCicilanStatus" class="form-control" style="height: 34px; font-size: 0.8125rem; width: 140px;">
                <option value="">Semua Status</option>
                <option value="aktif">Sedang Dicicil</option>
                <option value="lunas">Lunas</option>
            </select>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table" style="margin-bottom: 0; width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1; font-size: 0.8125rem; text-transform: uppercase;">
                        <th style="width: 40px; text-align: center; padding: 10px;">No</th>
                        <th style="padding: 10px;">No. Kesepakatan &amp; Santri</th>
                        <th style="padding: 10px;">Orang Tua / Wali</th>
                        <th style="padding: 10px; width: 190px;">Skema Pembayaran</th>
                        <th style="padding: 10px; width: 180px;">Progress Pelunasan</th>
                        <th style="text-align: right; width: 130px; padding: 10px;">Sisa Tunggakan</th>
                        <th style="text-align: center; width: 100px; padding: 10px;">Status</th>
                        <th style="text-align: center; width: 200px; padding: 10px;">Aksi Kasir TU</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($installments)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                                <i class="fa-solid fa-book" style="font-size: 2.5rem; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                                Belum ada data kesepakatan cicilan (FR 8001D) yang disetujui untuk unit ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($installments as $idx => $ins): 
                            $isLunas = ($ins['status'] === 'Lunas');
                            $totalDebt = (float)$ins['total_debt'];
                            $paidTotal = (float)$ins['paid_total'];
                            $remain = (float)$ins['remaining_debt'];
                            $percent = $totalDebt > 0 ? min(100, round(($paidTotal / $totalDebt) * 100, 1)) : 0;
                            $history = $paymentsByInstallment[$ins['id']] ?? [];

                            $skemaText = '';
                            if ($ins['scheme_type'] === 'lunas_tanggal') {
                                $skemaText = 'Lunas tgl ' . SecurityHelper::formatTanggalIndo($ins['scheme_lunas_date']);
                            } elseif ($ins['scheme_type'] === 'cicil_bulan') {
                                $skemaText = 'Dicicil ' . $ins['scheme_months'] . ' bulan (Mulai ' . SecurityHelper::formatTanggalIndo($ins['scheme_start_date']) . ')';
                            } elseif ($ins['scheme_type'] === 'cicil_nominal') {
                                $skemaText = 'Min ' . SecurityHelper::formatRupiah((float)$ins['scheme_min_monthly']) . '/bln (s.d ' . SecurityHelper::formatTanggalIndo($ins['scheme_end_date']) . ')';
                            }
                        ?>
                            <tr class="cicil-row" 
                                data-search="<?= strtolower(SecurityHelper::escape($ins['agreement_number'] . ' ' . $ins['student_name'] . ' ' . $ins['nis'] . ' ' . $ins['parent_name'])) ?>" 
                                data-status="<?= strtolower($ins['status']) ?>"
                                style="border-bottom: 1px solid #f1f5f9;">
                                
                                <td style="text-align: center; color: #64748b; font-size: 0.8125rem; padding: 10px;"><?= $idx + 1 ?></td>

                                <td style="padding: 10px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #0284c7; font-size: 0.8125rem;">
                                        <?= SecurityHelper::escape($ins['agreement_number']) ?>
                                    </div>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.9375rem; margin-top: 2px;">
                                        <?= SecurityHelper::escape($ins['student_name']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b;">
                                        NIS: <?= SecurityHelper::escape($ins['nis']) ?> &bull; <?= SecurityHelper::escape($ins['class_name'] ?? 'Aktif') ?>
                                    </div>
                                </td>

                                <td style="padding: 10px; font-size: 0.8125rem;">
                                    <div style="font-weight: 600; color: #334155;"><?= SecurityHelper::escape($ins['parent_name']) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b;">HP: <?= SecurityHelper::escape($ins['parent_phone'] ?: '-') ?></div>
                                    <span class="badge" style="font-size: 10px; background: <?= $ins['approved_by_level'] === 'deputi_pendidikan' ? '#fdf2f8' : '#f0fdfa' ?>; color: <?= $ins['approved_by_level'] === 'deputi_pendidikan' ? '#be185d' : '#0f766e' ?>;">
                                        Disetujui: <?= $ins['approved_by_level'] === 'deputi_pendidikan' ? 'Deputi Yayasan' : 'Kepala Sekolah' ?>
                                    </span>
                                </td>

                                <td style="padding: 10px; font-size: 0.8125rem;">
                                    <div style="font-weight: 600; color: #0f172a;"><?= $skemaText ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                        Tgl Akad: <?= SecurityHelper::formatTanggalIndo($ins['agreement_date']) ?>
                                    </div>
                                </td>

                                <td style="padding: 10px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 3px;">
                                        <span style="color: #047857; font-weight: 600;"><?= SecurityHelper::formatRupiah($paidTotal) ?></span>
                                        <span style="color: #64748b;"><?= $percent ?>%</span>
                                    </div>
                                    <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                        <div style="width: <?= $percent ?>%; height: 100%; background: <?= $isLunas ? '#10b981' : '#0284c7' ?>; border-radius: 4px;"></div>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 3px;">
                                        Total Awal: <?= SecurityHelper::formatRupiah($totalDebt) ?>
                                    </div>
                                </td>

                                <td style="text-align: right; padding: 10px;">
                                    <div style="font-weight: 800; font-family: monospace; font-size: 0.9375rem; color: <?= $isLunas ? '#10b981' : '#b91c1c' ?>;">
                                        <?= SecurityHelper::formatRupiah($remain) ?>
                                    </div>
                                </td>

                                <td style="text-align: center; padding: 10px;">
                                    <span class="badge" style="font-size: 11px; padding: 4px 8px; background: <?= $isLunas ? '#dcfce7' : '#fee2e2' ?>; color: <?= $isLunas ? '#15803d' : '#b91c1c' ?>;">
                                        <?= $isLunas ? 'Lunas' : 'Sedang Dicicil' ?>
                                    </span>
                                </td>

                                <td style="text-align: center; padding: 10px;">
                                    <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                        
                                        <!-- Tombol Bayar Angsuran -->
                                        <?php if (!$isLunas): ?>
                                            <button type="button" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.75rem; background: #0284c7; border-color: #0284c7; font-weight: 600;" onclick="openPaymentModal(<?= $ins['id'] ?>, '<?= SecurityHelper::escape($ins['agreement_number']) ?>', '<?= SecurityHelper::escape($ins['student_name']) ?>', <?= $remain ?>)" title="Terima Pembayaran Angsuran Cicilan">
                                                <i class="fa-solid fa-money-bill-wave"></i> Bayar
                                            </button>
                                        <?php endif; ?>

                                        <!-- Cetak Kartu / Buku Cicilan PDF -->
                                        <a href="<?= $baseUrl ?>/tagihan/cicilan/kartu-pdf/<?= $ins['id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #0f766e; border-color: #0f766e;" title="Cetak Buku & Kartu Cicilan (PDF)">
                                            <i class="fa-solid fa-print"></i> Kartu PDF
                                        </a>

                                        <!-- Kirim Email ke Ortu -->
                                        <?php if (!empty($ins['parent_email'])): ?>
                                            <form action="<?= $baseUrl ?>/tagihan/cicilan/kirim-pdf" method="POST" style="display: inline;">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="installment_id" value="<?= $ins['id'] ?>">
                                                <button type="submit" class="btn btn-outline btn-sm" style="padding: 4px 6px; font-size: 0.75rem; color: #4338ca; border-color: #4338ca;" title="Kirim status kartu ke email orang tua">
                                                    <i class="fa-solid fa-envelope"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Toggle Detail Riwayat -->
                                        <button type="button" class="btn btn-outline btn-sm" style="padding: 4px 6px; font-size: 0.75rem; color: #475569;" onclick="toggleHistory(<?= $ins['id'] ?>)" title="Lihat Histori Angsuran">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris Riwayat Pembayaran Angsuran (Accordion) -->
                            <tr id="historyRow_<?= $ins['id'] ?>" style="display: none; background: #f8fafc;">
                                <td colspan="8" style="padding: 12px 24px; border-bottom: 2px solid #e2e8f0;">
                                    <div style="font-weight: 700; font-size: 0.8125rem; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-receipt" style="color: #0284c7;"></i> Riwayat Angsuran yang Disetorkan ke TU:
                                    </div>
                                    <?php if (empty($history)): ?>
                                        <div style="font-size: 0.75rem; color: #64748b; font-style: italic;">
                                            Belum ada catatan setoran pembayaran cicilan.
                                        </div>
                                    <?php else: ?>
                                        <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px;">
                                            <thead>
                                                <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
                                                    <th style="padding: 6px 10px; width: 40px; text-align: center;">No</th>
                                                    <th style="padding: 6px 10px; width: 140px;">Tanggal Bayar</th>
                                                    <th style="padding: 6px 10px; width: 180px;">No. Kuitansi</th>
                                                    <th style="padding: 6px 10px; width: 140px; text-align: right;">Jumlah Dibayar</th>
                                                    <th style="padding: 6px 10px; width: 130px;">Metode</th>
                                                    <th style="padding: 6px 10px;">Petugas TU Penerima</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($history as $hIdx => $h): ?>
                                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                                        <td style="text-align: center; padding: 6px 10px;"><?= $hIdx + 1 ?></td>
                                                        <td style="padding: 6px 10px;"><?= SecurityHelper::formatTanggalIndo($h['payment_date']) ?></td>
                                                        <td style="padding: 6px 10px; font-family: monospace; font-weight: 600; color: #0369a1;"><?= SecurityHelper::escape($h['receipt_number']) ?></td>
                                                        <td style="padding: 6px 10px; text-align: right; font-weight: 700; color: #059669; font-family: monospace;"><?= SecurityHelper::formatRupiah((float)$h['amount']) ?></td>
                                                        <td style="padding: 6px 10px;"><?= SecurityHelper::escape($h['payment_method']) ?></td>
                                                        <td style="padding: 6px 10px; color: #475569;"><?= SecurityHelper::escape($h['receiver_name']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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

<!-- Modal Terima Pembayaran Angsuran -->
<div id="modalPayment" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 460px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #f0fdf4; border-bottom: 1px solid #dcfce7; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-cash-register"></i> Terima Pembayaran Angsuran Cicilan
            </h4>
            <button type="button" onclick="closePaymentModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/cicilan/bayar" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="installment_id" id="payInstallmentId" value="">

            <div style="padding: 20px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;">
                    <div style="font-size: 0.8125rem; color: #475569;">No Kesepakatan: <strong id="payAgreementNo" style="color: #0284c7;">-</strong></div>
                    <div style="font-size: 0.8125rem; color: #475569; margin-top: 2px;">Santri: <strong id="payStudentName" style="color: #0f172a;">-</strong></div>
                    <div style="font-size: 0.875rem; color: #b91c1c; font-weight: 700; margin-top: 4px;">
                        Sisa Tunggakan: <span id="payRemainingText">Rp 0</span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Jumlah yang Dibayarkan (Rp) <span style="color: #ef4444;">*</span></label>
                    <input type="number" step="1000" name="amount" id="payAmountInput" class="form-control" placeholder="Contoh: 500000" required style="font-size: 1rem; font-weight: 700; color: #047857;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Tanggal Pembayaran <span style="color: #ef4444;">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Metode Pembayaran</label>
                        <select name="payment_method" class="form-control" style="font-size: 0.8125rem;">
                            <option value="Cash (Tunai TU)">Cash (Tunai Loket TU)</option>
                            <option value="Transfer BSI">Transfer Bank BSI</option>
                            <option value="Transfer Bank Lain">Transfer Bank Lain</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Catatan / Keterangan</label>
                    <input type="text" name="notes" class="form-control" placeholder="Contoh: Angsuran ke-1 lunas via loket TU" style="font-size: 0.8125rem;">
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closePaymentModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #059669; border-color: #059669; font-weight: 700;">
                    <i class="fa-solid fa-receipt" style="margin-right: 6px;"></i> Simpan Pembayaran Angsuran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Pencarian dan Filter Cicilan
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchCicilan');
    const filterStatus = document.getElementById('filterCicilanStatus');
    const rows = document.querySelectorAll('.cicil-row');

    function applyFilter() {
        const query = (searchInput.value || '').toLowerCase();
        const stat = (filterStatus.value || '').toLowerCase();

        rows.forEach(function(row) {
            const dataSearch = row.getAttribute('data-search') || '';
            const dataStatus = row.getAttribute('data-status') || '';

            const matchQuery = dataSearch.includes(query);
            const matchStatus = !stat || dataStatus === stat;

            if (matchQuery && matchStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilter);
    if (filterStatus) filterStatus.addEventListener('change', applyFilter);
});

// Modal Pembayaran
function openPaymentModal(installmentId, agreementNo, studentName, remainingDebt) {
    document.getElementById('payInstallmentId').value = installmentId;
    document.getElementById('payAgreementNo').innerText = agreementNo;
    document.getElementById('payStudentName').innerText = studentName;
    document.getElementById('payRemainingText').innerText = 'Rp ' + remainingDebt.toLocaleString('id-ID');
    document.getElementById('payAmountInput').value = remainingDebt > 500000 ? 500000 : remainingDebt;
    document.getElementById('payAmountInput').max = remainingDebt;
    document.getElementById('modalPayment').style.display = 'flex';
}
function closePaymentModal() {
    document.getElementById('modalPayment').style.display = 'none';
}

// Toggle Riwayat
function toggleHistory(id) {
    const row = document.getElementById('historyRow_' + id);
    if (row) {
        row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
    }
}
</script>
