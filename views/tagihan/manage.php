<?php
// views/tagihan/manage.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalBills = count($bills ?? []);
$totalNominalTunggakan = 0;
$totalNominalLunas = 0;
$totalSpp = 0;
$totalNonSpp = 0;

foreach ($bills as $b) {
    if ($b['status'] === 'Tertunggak') {
        $totalNominalTunggakan += (float)$b['amount'];
    } else {
        $totalNominalLunas += (float)$b['amount'];
    }
    $totalSpp += (float)$b['amount_spp'];
    $totalNonSpp += (float)$b['amount_non_spp'];
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; font-weight: 600; padding: 4px 10px; border-radius: 6px;">Tata Usaha</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Pengelolaan Tagihan &amp; Alur Penagihan Siswa</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--text-main, #0f172a);">
                <i class="fa-solid fa-file-invoice-dollar" style="color: #0d9488;"></i> Input Tagihan (FR 8001A) &amp; Penagihan TU
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Entri rincian pembiayaan multi-kolom (FR 8001A), kirim surat reminder tunggakan (FR 8001B), tindak lanjut penyelesaian, dan eskalasi ke Kepala Sekolah.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?= $baseUrl ?>/tagihan/cicilan" class="btn btn-outline" style="color: #0284c7; border-color: rgba(2, 132, 199, 0.4); font-weight: 600;">
                <i class="fa-solid fa-book-bookmark" style="margin-right: 6px;"></i> Buka Buku Cicilan (FR 8001D)
            </a>
            <a href="<?= $baseUrl ?>/tagihan/template" class="btn btn-outline" style="color: #10b981; border-color: rgba(16, 185, 129, 0.4); font-weight: 600;">
                <i class="fa-solid fa-file-excel" style="margin-right: 6px;"></i> Unduh Format FR 8001A (.xlsx)
            </a>
            <button type="button" class="btn btn-primary" onclick="openImportModal()" style="font-weight: 600; background: #0d9488; border-color: #0d9488;">
                <i class="fa-solid fa-file-arrow-up" style="margin-right: 6px;"></i> Impor Excel FR 8001A
            </button>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-radius: 8px;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Santri Aktif Unit</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalStudents ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Siswa</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Terdaftar di rombel aktif</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #ef4444; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-radius: 8px;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #ef4444; font-weight: 600; letter-spacing: 0.5px;">Santri Tertunggak</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #ef4444; margin: 6px 0 2px 0;">
            <?= $tunggakanCount ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Siswa</span>
        </div>
        <div style="font-size: 0.8125rem; color: #ef4444; font-weight: 600;">
            Nominal: <?= SecurityHelper::formatRupiah($totalNominalTunggakan) ?>
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-radius: 8px;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Porsi SPP vs Non-SPP</div>
        <div style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 6px 0 2px 0;">
            SPP: <span style="color: #0284c7;"><?= SecurityHelper::formatRupiah($totalSpp) ?></span>
        </div>
        <div style="font-size: 0.8125rem; color: #64748b;">
            Non-SPP: <strong style="color: #d97706;"><?= SecurityHelper::formatRupiah($totalNonSpp) ?></strong>
        </div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-radius: 8px;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #10b981; font-weight: 600; letter-spacing: 0.5px;">Total Tagihan Lunas</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= SecurityHelper::formatRupiah($totalNominalLunas) ?>
        </div>
        <div style="font-size: 0.8125rem; color: #64748b;">
            Rasio Tunggakan: <strong><?= $rasioTunggakan ?>%</strong> (Target &le; 10%)
        </div>
    </div>
</div>

<!-- Main Section: Form Input Multi-Kolom & Tabel Tagihan -->
<div style="display: grid; grid-template-columns: 410px 1fr; gap: 24px; align-items: start;">
    
    <!-- Formulir Input Manual TU (Multi-Kolom FR 8001A) -->
    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a;">
                <i class="fa-solid fa-list-check" style="color: #0d9488;"></i> Input Rincian Tagihan (FR 8001A)
            </h3>
            <span style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: block;">Minimal 7 uraian tagihan bawaan + opsi tambah item dinamis</span>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/tagihan/save" method="POST" id="billForm">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Pilih Santri <span style="color: #ef4444;">*</span></label>
                    <select name="student_id" id="selectStudent" class="form-control" required style="width: 100%; font-size: 0.875rem;">
                        <option value="">-- Pilih Santri Aktif --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>" data-nis="<?= SecurityHelper::escape($st['nis']) ?>" data-class="<?= SecurityHelper::escape($st['class_name'] ?? 'Non-Kelas') ?>" data-parent="<?= SecurityHelper::escape($st['parent_name'] ?? 'Orang Tua') ?>">
                                <?= SecurityHelper::escape($st['nama']) ?> (NIS: <?= SecurityHelper::escape($st['nis']) ?> - <?= SecurityHelper::escape($st['class_name'] ?? 'Tanpa Rombel') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Judul / Periode Tagihan</label>
                    <input type="text" name="title" class="form-control" value="Tagihan Siswa &amp; SPP Bulanan" required style="font-size: 0.875rem;">
                </div>

                <!-- 7 Kolom Rincian Biaya Bawaan FR 8001A -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px; margin-bottom: 14px;">
                    <div style="font-size: 0.8125rem; font-weight: 700; color: #0f766e; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa-solid fa-receipt"></i> Uraian / Jenis Biaya (FR 8001A)</span>
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px;">7 Kolom Bawaan</span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 0.8125rem; color: #334155;">1. Buku Paket</span>
                            <input type="text" name="buku_paket" class="form-control cost-input" placeholder="0" value="0" style="width: 140px; text-align: right; font-size: 0.8125rem;" oninput="calculateTotal()">
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 0.8125rem; color: #334155;">2. Kegiatan Siswa</span>
                            <input type="text" name="kegiatan_siswa" class="form-control cost-input" placeholder="0" value="0" style="width: 140px; text-align: right; font-size: 0.8125rem;" oninput="calculateTotal()">
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 0.8125rem; color: #334155;">3. Komite</span>
                            <input type="text" name="komite" class="form-control cost-input" placeholder="0" value="0" style="width: 140px; text-align: right; font-size: 0.8125rem;" oninput="calculateTotal()">
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 0.8125rem; color: #334155;">4. Peningkatan Mutu</span>
                            <input type="text" name="peningkatan_mutu" class="form-control cost-input" placeholder="0" value="0" style="width: 140px; text-align: right; font-size: 0.8125rem;" oninput="calculateTotal()">
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 0.8125rem; font-weight: 600; color: #0369a1;">5. SPP (Bulan Ini)</span>
                            <input type="text" name="spp" class="form-control cost-input" placeholder="0" value="500000" style="width: 140px; text-align: right; font-size: 0.8125rem; font-weight: 700; color: #0369a1;" oninput="calculateTotal()">
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 0.8125rem; color: #334155;">6. Uang Ujian</span>
                            <input type="text" name="uang_ujian" class="form-control cost-input" placeholder="0" value="0" style="width: 140px; text-align: right; font-size: 0.8125rem;" oninput="calculateTotal()">
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 0.8125rem; color: #b91c1c;">7. Tunggakan Tahun Lalu</span>
                            <input type="text" name="tunggakan_lalu" class="form-control cost-input" placeholder="0" value="0" style="width: 140px; text-align: right; font-size: 0.8125rem;" oninput="calculateTotal()">
                        </div>
                    </div>

                    <!-- Wadah Item Custom Tambahan -->
                    <div id="customItemsContainer" style="margin-top: 10px;"></div>

                    <button type="button" class="btn btn-outline btn-sm" onclick="addCustomItemRow()" style="margin-top: 10px; width: 100%; justify-content: center; font-size: 0.75rem; border-style: dashed;">
                        <i class="fa-solid fa-plus-circle" style="margin-right: 4px;"></i> Tambah Jenis Biaya Lainnya
                    </button>
                </div>

                <!-- Total Otomatis Terhitung Realtime -->
                <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #166534;">Total Nominal (FR 8001A)</div>
                        <div style="font-size: 0.75rem; color: #15803d;">Penjumlahan seluruh uraian biaya</div>
                    </div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: #15803d; font-family: monospace;" id="displayGrandTotal">
                        Rp 500.000
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Bulan</label>
                        <select name="bill_month" class="form-control" style="font-size: 0.8125rem;">
                            <?php 
                            $monthNames = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
                            foreach ($monthNames as $num => $nm): ?>
                                <option value="<?= $num ?>" <?= $num == date('n') ? 'selected' : '' ?>><?= $nm ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Tahun</label>
                        <input type="number" name="bill_year" class="form-control" value="<?= date('Y') ?>" style="font-size: 0.8125rem; text-align: center;" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Jatuh Tempo</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-10') ?>" required style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 600;">Status Awal</label>
                        <select name="status" class="form-control" style="font-size: 0.8125rem;">
                            <option value="Tertunggak">Tertunggak</option>
                            <option value="Lunas">Lunas</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 700; background: #0d9488; border-color: #0d9488;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Tagihan Siswa
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Tagihan & Alur Penagihan -->
    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-table-list" style="color: #0d9488;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a;">Rekap Tagihan &amp; Alur Penagihan Santri</h3>
            </div>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <input type="text" id="searchBill" class="form-control" placeholder="Cari santri / NIS..." style="font-size: 0.8125rem; height: 34px; width: 170px;">
                <select id="filterStatus" class="form-control" style="height: 34px; font-size: 0.8125rem; width: 130px;">
                    <option value="">Semua Status</option>
                    <option value="tertunggak">Tertunggak</option>
                    <option value="lunas">Lunas</option>
                </select>
                <select id="filterStage" class="form-control" style="height: 34px; font-size: 0.8125rem; width: 160px;">
                    <option value="">Semua Tahap Alur</option>
                    <option value="tu_draft">Draft TU</option>
                    <option value="tu_reminder_sent">Reminder FR 8001B</option>
                    <option value="escalated_kepsek">Di Kepala Sekolah</option>
                    <option value="escalated_deputi">Di Yayasan/Deputi</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0; width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1; font-size: 0.8125rem; text-transform: uppercase;">
                            <th style="width: 40px; text-align: center; padding: 10px;">No</th>
                            <th style="padding: 10px;">Santri &amp; Orang Tua</th>
                            <th style="padding: 10px; width: 220px;">Rincian Uraian (FR 8001A)</th>
                            <th style="text-align: right; width: 130px; padding: 10px;">Total Tagihan</th>
                            <th style="text-align: center; width: 140px; padding: 10px;">Tahap Penagihan</th>
                            <th style="text-align: center; width: 180px; padding: 10px;">Aksi Alur TU</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bills)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-receipt" style="font-size: 2.5rem; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                                    Belum ada data tagihan santri pada unit &amp; tahun ajaran ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bills as $idx => $b): 
                                $isLunas = ($b['status'] === 'Lunas');
                                $stage = $b['pipeline_stage'] ?? 'tu_draft';
                                $items = json_decode($b['items_detail'] ?? '{}', true) ?: [];
                            ?>
                                <tr class="bill-row" 
                                    data-search="<?= strtolower(SecurityHelper::escape($b['student_name'] . ' ' . $b['nis'] . ' ' . ($b['class_name'] ?? '') . ' ' . ($b['parent_name'] ?? ''))) ?>" 
                                    data-status="<?= strtolower($b['status']) ?>"
                                    data-stage="<?= strtolower($stage) ?>"
                                    style="border-bottom: 1px solid #f1f5f9;">
                                    
                                    <td style="text-align: center; color: #64748b; font-size: 0.8125rem; padding: 10px;"><?= $idx + 1 ?></td>
                                    
                                    <td style="padding: 10px;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.9375rem;">
                                            <?= SecurityHelper::escape($b['student_name']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                            NIS: <span style="font-family: monospace; font-weight: 600;"><?= SecurityHelper::escape($b['nis']) ?></span> &bull; 
                                            <?= SecurityHelper::escape($b['class_name'] ?? 'Non-Kelas') ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #475569; margin-top: 2px;">
                                            <i class="fa-solid fa-user-tie" style="color: #94a3b8;"></i> Wali: <?= SecurityHelper::escape($b['parent_name'] ?: 'Wali') ?> 
                                            <?php if (!empty($b['parent_phone'])): ?>
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $b['parent_phone']) ?>" target="_blank" style="color: #10b981; margin-left: 4px; text-decoration: none;" title="Chat WhatsApp">
                                                    <i class="fa-brands fa-whatsapp"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Uraian Multi-Kolom FR 8001A -->
                                    <td style="padding: 10px; font-size: 0.8125rem;">
                                        <div style="display: flex; gap: 6px; margin-bottom: 4px;">
                                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px;">SPP: <?= SecurityHelper::formatRupiah((float)$b['amount_spp']) ?></span>
                                            <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 11px;">Non-SPP: <?= SecurityHelper::formatRupiah((float)$b['amount_non_spp']) ?></span>
                                        </div>
                                        <details style="cursor: pointer; color: #0d9488; font-size: 0.75rem;">
                                            <summary style="font-weight: 600;">Lihat Rincian Biaya (7 Kolom)</summary>
                                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; margin-top: 4px; color: #334155;">
                                                <div>Buku Paket: Rp <?= number_format((float)($items['buku_paket'] ?? 0), 0, ',', '.') ?></div>
                                                <div>Kegiatan Siswa: Rp <?= number_format((float)($items['kegiatan_siswa'] ?? 0), 0, ',', '.') ?></div>
                                                <div>Komite: Rp <?= number_format((float)($items['komite'] ?? 0), 0, ',', '.') ?></div>
                                                <div>Peningkatan Mutu: Rp <?= number_format((float)($items['peningkatan_mutu'] ?? 0), 0, ',', '.') ?></div>
                                                <div>SPP: Rp <?= number_format((float)($items['spp'] ?? 0), 0, ',', '.') ?></div>
                                                <div>Uang Ujian: Rp <?= number_format((float)($items['uang_ujian'] ?? 0), 0, ',', '.') ?></div>
                                                <div>Tunggakan Lalu: Rp <?= number_format((float)($items['tunggakan_lalu'] ?? 0), 0, ',', '.') ?></div>
                                                <?php if (!empty($items['custom_items'])): ?>
                                                    <?php foreach ($items['custom_items'] as $ci): ?>
                                                        <div><?= SecurityHelper::escape($ci['name']) ?>: Rp <?= number_format((float)$ci['amount'], 0, ',', '.') ?></div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </details>
                                    </td>

                                    <!-- Total Nominal -->
                                    <td style="text-align: right; padding: 10px;">
                                        <div style="font-weight: 800; font-family: monospace; font-size: 0.9375rem; color: <?= $isLunas ? '#10b981' : '#ef4444' ?>;">
                                            <?= SecurityHelper::formatRupiah((float)$b['amount']) ?>
                                        </div>
                                        <span class="badge" style="font-size: 10px; background: <?= $isLunas ? '#dcfce7' : '#fee2e2' ?>; color: <?= $isLunas ? '#15803d' : '#b91c1c' ?>;">
                                            <?= $b['status'] ?>
                                        </span>
                                    </td>

                                    <!-- Tahap Alur Penagihan -->
                                    <td style="text-align: center; padding: 10px;">
                                        <?php if ($stage === 'selesai_tu' || ($isLunas && $stage === 'tu_draft')): ?>
                                            <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 11px; padding: 4px 8px;">
                                                <i class="fa-solid fa-circle-check"></i> Selesai di TU
                                            </span>
                                        <?php elseif ($stage === 'tu_reminder_sent'): ?>
                                            <span class="badge" style="background: #e0e7ff; color: #4338ca; font-size: 11px; padding: 4px 8px;">
                                                <i class="fa-solid fa-paper-plane"></i> Reminder FR 8001B
                                            </span>
                                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                                <?= date('d/m/Y H:i', strtotime($b['tu_reminder_sent_at'])) ?>
                                            </div>
                                        <?php elseif ($stage === 'escalated_kepsek'): ?>
                                            <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 11px; padding: 4px 8px;">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Diajukan ke Kepsek
                                            </span>
                                        <?php elseif ($stage === 'kepsek_summon_sent'): ?>
                                            <span class="badge" style="background: #ffedd5; color: #c2410c; font-size: 11px; padding: 4px 8px;">
                                                <i class="fa-solid fa-envelope-open-text"></i> Panggilan Kepsek (8001C)
                                            </span>
                                        <?php elseif ($stage === 'selesai_kepsek'): ?>
                                            <span class="badge" style="background: #ecfdf5; color: #047857; font-size: 11px; padding: 4px 8px;">
                                                <i class="fa-solid fa-handshake"></i> Selesai di Kepsek
                                            </span>
                                        <?php elseif ($stage === 'escalated_deputi' || $stage === 'deputi_summon_sent'): ?>
                                            <span class="badge" style="background: #fdf2f8; color: #be185d; font-size: 11px; padding: 4px 8px;">
                                                <i class="fa-solid fa-crown"></i> Di Yayasan (Deputi)
                                            </span>
                                        <?php elseif ($stage === 'selesai_deputi'): ?>
                                            <span class="badge" style="background: #ecfdf5; color: #047857; font-size: 11px; padding: 4px 8px;">
                                                <i class="fa-solid fa-circle-check"></i> Selesai di Yayasan
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px; padding: 4px 8px;">
                                                Draft Tagihan TU
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Aksi Alur TU -->
                                    <td style="text-align: center; padding: 10px;">
                                        <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                            
                                            <!-- Cetak Surat FR 8001B -->
                                            <a href="<?= $baseUrl ?>/tagihan/cetak-fr8001b/<?= $b['id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #0f766e; border-color: #0f766e;" title="Cetak Surat Reminder FR 8001B">
                                                <i class="fa-solid fa-print"></i> FR 8001B
                                            </a>

                                            <?php if (!$isLunas): ?>
                                                <!-- Kirim Reminder FR 8001B -->
                                                <form action="<?= $baseUrl ?>/tagihan/kirim-fr8001b" method="POST" style="display: inline;">
                                                    <?= CSRF::field() ?>
                                                    <input type="hidden" name="bill_id" value="<?= $b['id'] ?>">
                                                    <button type="submit" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #4338ca; border-color: #4338ca;" title="Tandai / Kirim Surat Reminder FR 8001B">
                                                        <i class="fa-solid fa-paper-plane"></i> Kirim
                                                    </button>
                                                </form>

                                                <!-- Mark Selesai TU -->
                                                <form action="<?= $baseUrl ?>/tagihan/mark-selesai-tu" method="POST" style="display: inline;" onsubmit="return confirm('Tandai tagihan ini sebagai SELESAI / LUNAS di tingkat TU?');">
                                                    <?= CSRF::field() ?>
                                                    <input type="hidden" name="bill_id" value="<?= $b['id'] ?>">
                                                    <button type="submit" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #16a34a; border-color: #16a34a;" title="Tandai Selesai di TU (Lunas)">
                                                        <i class="fa-solid fa-check"></i> Selesai
                                                    </button>
                                                </form>

                                                <!-- Lanjutkan / Eskalasi ke Kepala Sekolah -->
                                                <?php if (!in_array($stage, ['escalated_kepsek', 'kepsek_summon_sent', 'escalated_deputi', 'deputi_summon_sent'])): ?>
                                                    <button type="button" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #d97706; border-color: #d97706;" onclick="openEskalasiModal(<?= $b['id'] ?>, '<?= SecurityHelper::escape($b['student_name']) ?>', '<?= SecurityHelper::formatRupiah((float)$b['amount']) ?>')" title="Lanjutkan pengajuan penagihan ke Kepala Sekolah">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Ke Kepsek
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <!-- Hapus Tagihan -->
                                            <form action="<?= $baseUrl ?>/tagihan/delete" method="POST" style="display: inline;" onsubmit="return confirm('Hapus data tagihan ini?');">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                                <button type="submit" class="btn btn-outline btn-sm" style="padding: 4px 6px; font-size: 0.75rem; color: #ef4444; border-color: #fca5a5;" title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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

<!-- Modal Eskalasi ke Kepala Sekolah -->
<div id="modalEskalasiKepsek" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 480px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #fffbeb; border-bottom: 1px solid #fef3c7; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #b45309; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Ajukan Penagihan ke Kepala Sekolah
            </h4>
            <button type="button" onclick="closeEskalasiModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/eskalasi-kepsek" method="POST">
            <?= CSRF::field() ?>
            <input type="hidden" name="bill_id" id="eskalasiBillId" value="">
            <div style="padding: 20px;">
                <p style="font-size: 0.875rem; color: #334155; margin-top: 0; line-height: 1.5;">
                    Pengingat telah dikirim oleh TU namun belum ada penyelesaian. Data ini akan diteruskan ke antrean penagihan Kepala Sekolah untuk pemanggilan resmi orang tua (format FR 8001C).
                </p>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; font-size: 0.875rem;">
                    <div>Santri: <strong id="eskalasiStudentName">-</strong></div>
                    <div>Total Tagihan: <strong id="eskalasiAmount" style="color: #ef4444;">-</strong></div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Catatan Rekomendasi TU (Opsional)</label>
                    <textarea name="escalation_notes" class="form-control" rows="3" placeholder="Contoh: Orang tua belum merespons pesan reminder, direkomendasikan pemanggilan langsung ke sekolah." style="font-size: 0.8125rem;">Orang tua belum merespons pesan reminder TU, diajukan untuk pemanggilan Kepala Sekolah.</textarea>
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeEskalasiModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #d97706; border-color: #d97706; font-weight: 600;">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Teruskan ke Kepala Sekolah
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor Tagihan Excel (FR 8001A) -->
<div id="modalImportTagihan" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 520px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #f0fdf4; border-bottom: 1px solid #dcfce7; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-excel"></i> Impor Berkas Tagihan Excel (FR 8001A)
            </h4>
            <button type="button" onclick="closeImportModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/import" method="POST" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <div style="padding: 20px;">
                <p style="font-size: 0.875rem; color: #334155; margin-top: 0; line-height: 1.5;">
                    Gunakan berkas format resmi <strong>FR 8001A</strong> yang telah diunduh dari tombol <em>"Unduh Format FR 8001A"</em>.
                    Sistem akan membaca 7 kolom rincian biaya (Buku Paket, Kegiatan, Komite, Mutu, SPP, Uang Ujian, Tunggakan Lalu) dan menghitung total secara otomatis.
                </p>
                <div class="form-group" style="margin-top: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Pilih Berkas Excel (.xlsx) <span style="color: #ef4444;">*</span></label>
                    <input type="file" name="excel_file" class="form-control" accept=".xlsx" required style="padding: 8px;">
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeImportModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #16a34a; border-color: #16a34a; font-weight: 600;">
                    <i class="fa-solid fa-cloud-arrow-up" style="margin-right: 6px;"></i> Mulai Impor Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Fungsi hitung penjumlahan realtime seluruh kolom biaya FR 8001A
function calculateTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.cost-input').forEach(function(input) {
        let clean = input.value.replace(/[^0-9]/g, '');
        let val = parseFloat(clean) || 0;
        grandTotal += val;
    });

    let formatted = 'Rp ' + grandTotal.toLocaleString('id-ID');
    document.getElementById('displayGrandTotal').innerText = formatted;
}

// Tambah baris jenis biaya custom dinamis
let customItemIndex = 0;
function addCustomItemRow() {
    customItemIndex++;
    const container = document.getElementById('customItemsContainer');
    const row = document.createElement('div');
    row.id = 'customRow_' + customItemIndex;
    row.style.cssText = 'display: flex; gap: 6px; align-items: center; margin-top: 6px;';
    row.innerHTML = `
        <input type="text" name="custom_item_name[]" class="form-control" placeholder="Nama Biaya Lainnya..." style="font-size: 0.8125rem; flex: 1;" required>
        <input type="text" name="custom_item_amount[]" class="form-control cost-input" placeholder="0" value="0" style="width: 130px; text-align: right; font-size: 0.8125rem;" oninput="calculateTotal()" required>
        <button type="button" class="btn btn-outline btn-sm" onclick="removeCustomRow('${row.id}')" style="padding: 4px 8px; color: #ef4444; border-color: #fca5a5;">
            <i class="fa-solid fa-times"></i>
        </button>
    `;
    container.appendChild(row);
}

function removeCustomRow(rowId) {
    const el = document.getElementById(rowId);
    if (el) {
        el.remove();
        calculateTotal();
    }
}

// Filter dan search tabel tagihan
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchBill');
    const filterStatus = document.getElementById('filterStatus');
    const filterStage = document.getElementById('filterStage');
    const rows = document.querySelectorAll('.bill-row');

    function applyFilter() {
        const query = (searchInput.value || '').toLowerCase();
        const stat = (filterStatus.value || '').toLowerCase();
        const stg = (filterStage.value || '').toLowerCase();

        rows.forEach(function(row) {
            const dataSearch = row.getAttribute('data-search') || '';
            const dataStatus = row.getAttribute('data-status') || '';
            const dataStage = row.getAttribute('data-stage') || '';

            const matchQuery = dataSearch.includes(query);
            const matchStatus = !stat || dataStatus === stat;
            let matchStage = true;
            if (stg) {
                if (stg === 'selesai') {
                    matchStage = dataStage.includes('selesai');
                } else {
                    matchStage = dataStage.includes(stg);
                }
            }

            if (matchQuery && matchStatus && matchStage) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilter);
    if (filterStatus) filterStatus.addEventListener('change', applyFilter);
    if (filterStage) filterStage.addEventListener('change', applyFilter);
});

// Modal Eskalasi ke Kepala Sekolah
function openEskalasiModal(billId, studentName, amount) {
    document.getElementById('eskalasiBillId').value = billId;
    document.getElementById('eskalasiStudentName').innerText = studentName;
    document.getElementById('eskalasiAmount').innerText = amount;
    const modal = document.getElementById('modalEskalasiKepsek');
    modal.style.display = 'flex';
}

function closeEskalasiModal() {
    document.getElementById('modalEskalasiKepsek').style.display = 'none';
}

// Modal Impor Excel
function openImportModal() {
    document.getElementById('modalImportTagihan').style.display = 'flex';
}

function closeImportModal() {
    document.getElementById('modalImportTagihan').style.display = 'none';
}
</script>
