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
            <a href="<?= $baseUrl ?>/tagihan/backup-offline" class="btn btn-outline" style="color: #475569; border-color: rgba(71, 85, 105, 0.4); font-weight: 600;" title="Unduh cadangan lengkap Tagihan, Buku Cicilan & Riwayat Angsuran dalam format Excel offline">
                <i class="fa-solid fa-download" style="margin-right: 6px;"></i> Backup Offline (.xlsx)
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

<!-- Versi Data Impor Tagihan TU -->
<div style="background: #f0fdfa; border: 1px solid #99f6e4; border-left: 4px solid #0d9488; border-radius: 8px; padding: 10px 16px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 0.8125rem;">
    <div style="display: flex; align-items: center; gap: 8px; color: #115e59;">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <?php if (!empty($lastImportMeta['imported_at'])):
            $impTs = strtotime($lastImportMeta['imported_at']);
            $fmtLabel = !empty($lastImportMeta['format']) ? strtoupper((string)$lastImportMeta['format']) : 'EXCEL';
        ?>
            <span>
                <strong>Versi Data Impor TU Terakhir:</strong>
                <?= SecurityHelper::formatTanggalIndo(date('Y-m-d', $impTs)) ?>, pukul <?= date('H:i', $impTs) ?> WIB
                &bull; Format: <span class="badge" style="background: #ccfbf1; color: #0f766e; font-size: 11px;"><?= SecurityHelper::escape($fmtLabel) ?></span>
                <?php if (!empty($lastImportMeta['filename'])): ?>
                    &bull; File: <code><?= SecurityHelper::escape($lastImportMeta['filename']) ?></code>
                <?php endif; ?>
                <?php if (!empty($lastImportMeta['by_name'])): ?>
                    &bull; Oleh: <strong><?= SecurityHelper::escape($lastImportMeta['by_name']) ?></strong>
                <?php endif; ?>
            </span>
        <?php else: ?>
            <span><strong>Versi Data Impor TU:</strong> Belum ada riwayat impor berkas tagihan Excel pada unit ini.</span>
        <?php endif; ?>
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
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem; margin: 0;">Pilih Santri <span style="color: #ef4444;">*</span></label>
                        <span style="font-size: 11px; color: var(--text-muted);"><i class="fa-solid fa-magnifying-glass"></i> Cari cepat santri</span>
                    </div>
                    <div style="position: relative; margin-bottom: 6px;">
                        <input type="text" id="searchStudentInput" class="form-control" placeholder="Ketik nama atau NIS santri untuk mencari..." oninput="filterStudentDropdown(this.value)" style="font-size: 0.825rem; padding-left: 32px; border-radius: 6px;">
                        <i class="fa-solid fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px;"></i>
                    </div>
                    <select name="student_id" id="selectStudent" class="form-control" required style="width: 100%; font-size: 0.875rem;">
                        <option value="">-- Pilih Santri Aktif --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>" data-nis="<?= SecurityHelper::escape($st['nis']) ?>" data-nama="<?= SecurityHelper::escape(strtolower($st['nama'])) ?>" data-class="<?= SecurityHelper::escape($st['class_name'] ?? 'Non-Kelas') ?>" data-parent="<?= SecurityHelper::escape($st['parent_name'] ?? 'Orang Tua') ?>">
                                <?= SecurityHelper::escape($st['nama']) ?> (NIS: <?= SecurityHelper::escape($st['nis']) ?> - <?= SecurityHelper::escape($st['class_name'] ?? 'Tanpa Rombel') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="studentNotFoundAlert" style="display: none; font-size: 11.5px; color: #dc2626; margin-top: 4px;">Santri tidak ditemukan dengan kata kunci tersebut.</div>
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
    <?php
        $classNames = [];
        foreach ($classes ?? [] as $cls) {
            if (!empty($cls['name'])) {
                $classNames[$cls['name']] = $cls['name'];
            }
        }
        $hasNonClass = false;
        foreach ($bills ?? [] as $bItem) {
            $cName = trim((string)($bItem['class_name'] ?? ''));
            if ($cName === '') {
                $hasNonClass = true;
            } else {
                $classNames[$cName] = $cName;
            }
        }
    ?>
    <form id="formBulkFR8001B" action="<?= $baseUrl ?>/tagihan/bulk-kirim-fr8001b" method="POST" style="display: none;">
        <?= CSRF::field() ?>
        <div id="bulkBillInputs"></div>
    </form>

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
                <label style="display: inline-flex; align-items: center; gap: 5px; font-size: 0.8125rem; color: #334155; cursor: pointer; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 5px 10px; margin: 0;">
                    <input type="checkbox" id="toggleHideLunas"> Sembunyikan Lunas
                </label>
                <button type="button" class="btn btn-primary btn-sm" onclick="submitBulkFR8001B()" style="height: 34px; background: #4338ca; border-color: #4338ca; font-weight: 600; font-size: 0.8125rem;" title="Kirim Email + PDF FR 8001B untuk seluruh baris yang dicentang">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 5px;"></i> Kirim Massal FR 8001B
                </button>
            </div>
        </div>

        <!-- Pill Tab Filter Per Kelas (diurutkan A-Z) -->
        <div style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0; padding: 10px 20px; display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
            <span style="font-size: 0.75rem; font-weight: 700; color: #475569; margin-right: 4px;"><i class="fa-solid fa-layer-group"></i> Filter Kelas:</span>
            <button type="button" class="class-tab-btn active" data-class-filter="" onclick="selectClassTab(this, '')" style="padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; border: 1px solid #0d9488; background: #0d9488; color: #fff; cursor: pointer;">
                Semua Kelas
            </button>
            <?php foreach ($classNames as $cName): ?>
                <button type="button" class="class-tab-btn" data-class-filter="<?= strtolower(SecurityHelper::escape($cName)) ?>" onclick="selectClassTab(this, '<?= strtolower(SecurityHelper::escape($cName)) ?>')" style="padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; border: 1px solid #cbd5e1; background: #fff; color: #334155; cursor: pointer;">
                    <?= SecurityHelper::escape($cName) ?>
                </button>
            <?php endforeach; ?>
            <?php if ($hasNonClass): ?>
                <button type="button" class="class-tab-btn" data-class-filter="non-kelas" onclick="selectClassTab(this, 'non-kelas')" style="padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; border: 1px solid #cbd5e1; background: #fff; color: #64748b; cursor: pointer;">
                    Non-Kelas
                </button>
            <?php endif; ?>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0; width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1; font-size: 0.8125rem; text-transform: uppercase;">
                            <th style="width: 36px; text-align: center; padding: 10px;">
                                <input type="checkbox" id="checkAllBills" title="Pilih Semua Tagihan yang Tampil">
                            </th>
                            <th style="width: 40px; text-align: center; padding: 10px;">No</th>
                            <th style="padding: 10px;">Santri &amp; Orang Tua</th>
                            <th style="padding: 10px; width: 110px;">Kelas</th>
                            <th style="padding: 10px; width: 220px;">Rincian Uraian (FR 8001A)</th>
                            <th style="text-align: right; width: 130px; padding: 10px;">Total Tagihan</th>
                            <th style="text-align: center; width: 150px; padding: 10px;">Tahap Penagihan</th>
                            <th style="text-align: center; width: 200px; padding: 10px;">Aksi Alur TU</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bills)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-receipt" style="font-size: 2.5rem; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                                    Belum ada data tagihan santri pada unit &amp; tahun ajaran ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bills as $idx => $b): 
                                $isLunas = ($b['status'] === 'Lunas');
                                $stage = $b['pipeline_stage'] ?? 'tu_draft';
                                $items = json_decode($b['items_detail'] ?? '{}', true) ?: [];
                                $clsLabel = !empty($b['class_name']) ? $b['class_name'] : 'Non-Kelas';
                            ?>
                                <tr class="bill-row" 
                                    data-search="<?= strtolower(SecurityHelper::escape($b['student_name'] . ' ' . $b['nis'] . ' ' . $clsLabel . ' ' . ($b['parent_name'] ?? ''))) ?>" 
                                    data-status="<?= strtolower($b['status']) ?>"
                                    data-stage="<?= strtolower($stage) ?>"
                                    data-class="<?= strtolower(SecurityHelper::escape($clsLabel)) ?>"
                                    style="border-bottom: 1px solid #f1f5f9;">
                                    
                                    <td style="text-align: center; padding: 10px;">
                                        <?php if (!$isLunas): ?>
                                            <input type="checkbox" class="bill-cb" value="<?= (int)$b['id'] ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center; color: #64748b; font-size: 0.8125rem; padding: 10px;"><?= $idx + 1 ?></td>
                                    
                                    <td style="padding: 10px;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.9375rem;">
                                            <?= SecurityHelper::escape($b['student_name']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                            NIS: <span style="font-family: monospace; font-weight: 600;"><?= SecurityHelper::escape($b['nis']) ?></span>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #475569; margin-top: 2px;">
                                            <i class="fa-solid fa-user-tie" style="color: #94a3b8;"></i> Wali: <?= SecurityHelper::escape($b['parent_name'] ?: 'Wali') ?> 
                                            <?php if (!empty($b['parent_phone'])): ?>
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $b['parent_phone']) ?>" target="_blank" style="color: #10b981; margin-left: 4px; text-decoration: none;" title="Chat WhatsApp">
                                                    <i class="fa-brands fa-whatsapp"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($b['parent_email'])): ?>
                                                <span style="color: #0284c7; margin-left: 6px;" title="Email Orang Tua: <?= SecurityHelper::escape($b['parent_email']) ?>">
                                                    <i class="fa-solid fa-envelope"></i> <?= SecurityHelper::escape($b['parent_email']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: #dc2626; margin-left: 6px; font-style: italic;" title="Email orang tua belum terdaftar di data siswa">
                                                    <i class="fa-regular fa-envelope" style="opacity: 0.6;"></i> (Email belum diisi)
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td style="padding: 10px;">
                                        <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.75rem; font-weight: 600;">
                                            <?= SecurityHelper::escape($clsLabel) ?>
                                        </span>
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
                                        <?php if (!empty($b['signed_fr8001c_path']) || !empty($b['signed_fr8001d_path'])): ?>
                                            <div style="margin-top: 5px; display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                                <?php if (!empty($b['signed_fr8001c_path'])): ?>
                                                    <a href="<?= $baseUrl ?>/tagihan/signed-doc/<?= (int)$b['id'] ?>/c" target="_blank" class="badge" style="background: #dcfce7; color: #15803d; font-size: 10px; text-decoration: none;" title="Lihat Scan FR 8001C Bertanda Tangan">
                                                        <i class="fa-solid fa-file-signature"></i> TTD 8001C
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($b['signed_fr8001d_path'])): ?>
                                                    <a href="<?= $baseUrl ?>/tagihan/signed-doc/<?= (int)$b['id'] ?>/d" target="_blank" class="badge" style="background: #dbeafe; color: #1d4ed8; font-size: 10px; text-decoration: none;" title="Lihat Scan FR 8001D Bertanda Tangan">
                                                        <i class="fa-solid fa-file-signature"></i> TTD 8001D
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Aksi Alur TU -->
                                    <td style="text-align: center; padding: 10px;">
                                        <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                            
                                            <!-- Cetak Surat FR 8001B -->
                                            <a href="<?= $baseUrl ?>/tagihan/cetak-fr8001b/<?= $b['id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #0f766e; border-color: #0f766e;" title="Cetak Surat Reminder FR 8001B">
                                                <i class="fa-solid fa-print"></i> FR 8001B
                                            </a>

                                            <!-- Upload Dokumen TTD FR 8001C / FR 8001D (Opsional) -->
                                            <button type="button" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #0284c7; border-color: #0284c7;" onclick="openUploadSignedModal(<?= (int)$b['id'] ?>, '<?= SecurityHelper::escape($b['student_name']) ?>')" title="Unggah Scan FR 8001C / FR 8001D Bertanda Tangan Orang Tua">
                                                <i class="fa-solid fa-file-arrow-up"></i> Upload TTD
                                            </button>

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

<!-- Modal Upload Dokumen TTD FR 8001C / 8001D (Opsional oleh TU) -->
<div id="modalUploadSignedTu" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 500px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #f0f9ff; border-bottom: 1px solid #bae6fd; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #0369a1; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-signature"></i> Upload Berkas TTD Orang Tua (FR 8001C / 8001D)
            </h4>
            <button type="button" onclick="closeUploadSignedModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/upload-signed-doc" method="POST" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <input type="hidden" name="bill_id" id="uploadSignedBillId" value="">
            <div style="padding: 20px;">
                <p style="font-size: 0.875rem; color: #334155; margin-top: 0;">
                    Santri: <strong id="uploadSignedStudentName">-</strong>
                </p>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Scan FR 8001C Bertanda Tangan (PDF/JPG/PNG, Opsional)</label>
                    <input type="file" name="signed_fr8001c" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="padding: 6px;">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Scan FR 8001D (Berita Acara &amp; Surat Pernyataan) Bertanda Tangan (PDF/JPG/PNG, Opsional)</label>
                    <input type="file" name="signed_fr8001d" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="padding: 6px;">
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeUploadSignedModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #0284c7; border-color: #0284c7; font-weight: 600;">
                    <i class="fa-solid fa-cloud-arrow-up" style="margin-right: 6px;"></i> Simpan Berkas TTD
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor Tagihan Braja / Excel (FR 8001A) -> Model Timpa & Verifikasi Pratinjau -->
<div id="modalImportTagihan" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 560px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 20px;">
        <div style="background: #f0fdf4; border-bottom: 1px solid #dcfce7; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-excel"></i> Impor Laporan Braja / FR 8001A (Model Timpa Saldo)
            </h4>
            <button type="button" onclick="closeImportModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form action="<?= $baseUrl ?>/tagihan/verify-import" method="POST" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <div style="padding: 20px;">
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px 12px; font-size: 0.8125rem; color: #1e3a8a; margin-bottom: 14px; line-height: 1.55;">
                    <strong><i class="fa-solid fa-circle-info"></i> Sinkronisasi Saldo Langsung dari Braja (Model Timpa):</strong>
                    <ul style="margin: 4px 0 0 18px; padding: 0;">
                        <li>Dapat langsung mengunggah file mentah ekspor <strong>Braja</strong> (<code>.xlsx</code> / <code>.xls</code> / <code>.csv</code>) ataupun templat <strong>FR 8001A</strong>. Kop, baris <code>TOTAL</code>, serta pos tambahan (<em>Gedung, Seragam, dll.</em>) diproses otomatis.</li>
                        <li>Unit <strong>KB, TK, SD, SMP, SMA tetap terpisah</strong>: baris siswa milik unit lain dilewati otomatis tanpa dicampur.</li>
                        <li>Tagihan lama ditimpa saldo terbaru; siswa yang di Braja menjadi <strong>Rp 0</strong> otomatis ditandai <strong>Lunas</strong>.</li>
                        <li>Jika siswa punya <strong>Buku Cicilan Aktif</strong>, total hutang &amp; sisa cicilan ikut diperbarui otomatis beserta riwayat catatannya.</li>
                    </ul>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.75rem;">Periode s/d Bulan</label>
                        <select name="bill_month" class="form-control" style="font-size: 0.8125rem;">
                            <?php $curM = (int)date('n'); $bulanOpts = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember']; foreach ($bulanOpts as $mNum => $mLbl): ?>
                                <option value="<?= $mNum ?>" <?= ($mNum === $curM) ? 'selected' : '' ?>><?= $mLbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.75rem;">Tahun</label>
                        <input type="number" name="bill_year" class="form-control" value="<?= date('Y') ?>" min="2020" max="2035" style="font-size: 0.8125rem;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.75rem;">Jatuh Tempo</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-10') ?>" style="font-size: 0.8125rem;">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Pilih Berkas Braja / Excel (.xlsx, .xls, .csv) <span style="color: #ef4444;">*</span></label>
                    <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required style="padding: 8px;">
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeImportModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #0d9488; border-color: #0d9488; font-weight: 600;">
                    <i class="fa-solid fa-magnifying-glass-chart" style="margin-right: 6px;"></i> Verifikasi Data Impor
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($importPreview)):
    $validRows        = $importPreview['valid'] ?? [];
    $invalidRows      = $importPreview['invalid'] ?? [];
    $detectedFmt      = (($importPreview['format'] ?? '') === 'braja') ? 'Format Laporan Braja' : 'Format FR 8001A';
    $skippedZero      = (int)($importPreview['skipped_zero'] ?? 0);
    $skippedOtherUnit = (int)($importPreview['skipped_other_unit'] ?? 0);
    $otherBreakdown   = $importPreview['other_unit_breakdown'] ?? [];
?>
<!-- Modal Verifikasi Hasil Pratinjau Impor Tagihan (Valid vs Bermasalah) -->
<div id="modalVerifyImportBills" class="modal-backdrop" style="display: flex; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 96%; max-width: 980px; max-height: 90vh; display: flex; flex-direction: column; border-radius: 10px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); overflow: hidden; margin: 16px;">
        <div style="background: #f0fdfa; border-bottom: 1px solid #ccfbf1; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <div>
                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f766e;">
                    <i class="fa-solid fa-clipboard-check" style="margin-right: 6px;"></i> Verifikasi Sinkronisasi Saldo: <?= SecurityHelper::escape($importPreview['filename'] ?? 'import.xlsx') ?>
                </h4>
                <div style="font-size: 0.78rem; color: #475569; margin-top: 2px;">
                    Terdeteksi: <strong><?= $detectedFmt ?></strong> &bull; Periode: Bulan <?= (int)$importPreview['bill_month'] ?>/<?= (int)$importPreview['bill_year'] ?> &bull; Jatuh Tempo: <?= SecurityHelper::escape($importPreview['due_date']) ?>
                </div>
            </div>
            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                <span class="badge" style="background: #dcfce7; color: #15803d; padding: 5px 10px;">Siap Proses: <?= count($validRows) ?></span>
                <span class="badge" style="background: #fee2e2; color: #b91c1c; padding: 5px 10px;">Bermasalah: <?= count($invalidRows) ?></span>
                <?php if ($skippedOtherUnit > 0): ?>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 5px 10px;" title="<?php $parts = []; foreach ($otherBreakdown as $uN => $cU) { $parts[] = $uN . ': ' . $cU; } echo SecurityHelper::escape(implode(', ', $parts)); ?>">
                        Dilewati Unit Lain: <?= $skippedOtherUnit ?>
                    </span>
                <?php endif; ?>
                <?php if ($skippedZero > 0): ?>
                    <span class="badge" style="background: #f1f5f9; color: #475569; padding: 5px 10px;">Dilewati Saldo Rp 0: <?= $skippedZero ?></span>
                <?php endif; ?>
            </div>
        </div>

        <form action="<?= $baseUrl ?>/tagihan/confirm-import" method="POST" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            <?= CSRF::field() ?>
            <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 20px; display: flex; gap: 8px;">
                <button type="button" id="btnVerifyTabValid" onclick="switchVerifyTab('valid')" style="padding: 6px 14px; border-radius: 6px; font-weight: 700; font-size: 0.8125rem; border: 1px solid #0d9488; background: #0d9488; color: #fff; cursor: pointer;">
                    Data Siap Simpan (<?= count($validRows) ?>)
                </button>
                <button type="button" id="btnVerifyTabInvalid" onclick="switchVerifyTab('invalid')" style="padding: 6px 14px; border-radius: 6px; font-weight: 700; font-size: 0.8125rem; border: 1px solid #cbd5e1; background: #fff; color: #dc2626; cursor: pointer;">
                    Data Bermasalah (<?= count($invalidRows) ?>)
                </button>
            </div>

            <div style="padding: 16px 20px; overflow-y: auto; flex: 1;">
                <div id="verifyPaneValid">
                    <?php if (empty($validRows)): ?>
                        <div style="text-align: center; padding: 30px; color: #64748b;">Tidak ada perubahan tagihan atau tagihan baru yang perlu disimpan.</div>
                    <?php else: ?>
                        <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
                            <thead>
                                <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                                    <th style="width: 34px; text-align: center; padding: 8px;"><input type="checkbox" checked onchange="document.querySelectorAll('.verify-keep-cb').forEach(c => c.checked = this.checked)"></th>
                                    <th style="padding: 8px;">Baris</th>
                                    <th style="padding: 8px;">Santri &amp; NIS</th>
                                    <th style="padding: 8px;">Kelas</th>
                                    <th style="padding: 8px; text-align: right;">Saldo Braja / File</th>
                                    <th style="padding: 8px;">Aksi Sinkronisasi &amp; Dampak Buku Cicilan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($validRows as $vIdx => $vr):
                                    $act = $vr['action_type'] ?? 'new';
                                ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="text-align: center; padding: 8px;">
                                            <input type="checkbox" name="keep_indices[]" value="<?= (int)$vIdx ?>" class="verify-keep-cb" checked>
                                        </td>
                                        <td style="padding: 8px; color: #64748b;">#<?= (int)$vr['row_num'] ?></td>
                                        <td style="padding: 8px;">
                                            <div style="font-weight: 700; color: #0f172a;"><?= SecurityHelper::escape($vr['nama']) ?></div>
                                            <div style="font-size: 0.75rem; color: #64748b;">NIS: <?= SecurityHelper::escape($vr['nis']) ?> &bull; <?= SecurityHelper::escape($vr['match_note'] ?? '') ?></div>
                                        </td>
                                        <td style="padding: 8px;"><?= SecurityHelper::escape($vr['class_name'] ?? '-') ?></td>
                                        <td style="padding: 8px; text-align: right; font-weight: 700; font-family: monospace;">
                                            <?= SecurityHelper::formatRupiah((float)$vr['amount']) ?>
                                            <?php if (!empty($vr['custom_items'])): ?>
                                                <div style="font-size: 10px; color: #64748b; font-family: sans-serif; font-weight: 400;">
                                                    (+<?= count($vr['custom_items']) ?> pos tambahan)
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 8px;">
                                            <?php if ($act === 'mark_lunas'): ?>
                                                <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 11px;">
                                                    <i class="fa-solid fa-circle-check"></i> Lunas di Braja (Rp 0 &mdash; Sebelumnya <?= SecurityHelper::formatRupiah((float)$vr['existing_amount']) ?>)
                                                </span>
                                            <?php elseif ($act === 'overwrite'): ?>
                                                <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 11px;">
                                                    Timpa Saldo: <?= SecurityHelper::formatRupiah((float)$vr['existing_amount']) ?> &rarr; <?= SecurityHelper::formatRupiah((float)$vr['new_total']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px;">Tagihan Baru</span>
                                            <?php endif; ?>
                                            <?php if (!empty($vr['has_installment'])):
                                                $d = (float)($vr['inst_delta'] ?? 0);
                                            ?>
                                                <div style="font-size: 11px; color: #0284c7; margin-top: 4px; line-height: 1.4;">
                                                    <i class="fa-solid fa-book-bookmark"></i> <strong>Buku Cicilan (<?= SecurityHelper::escape($vr['agreement_number']) ?>):</strong>
                                                    <?php if ($act === 'mark_lunas'): ?>
                                                        Otomatis ditandai <strong>Lunas</strong> + catatan otomatis.
                                                    <?php elseif ($d > 0.5): ?>
                                                        Hutang baru +<?= SecurityHelper::formatRupiah($d) ?> &rarr; Total Hutang <?= SecurityHelper::formatRupiah((float)$vr['inst_new_total']) ?>, Sisa <?= SecurityHelper::formatRupiah((float)$vr['new_total']) ?> (+ catatan).
                                                    <?php elseif ($d < -0.5): ?>
                                                        Saldo turun -<?= SecurityHelper::formatRupiah(abs($d)) ?> &rarr; Sisa <?= SecurityHelper::formatRupiah((float)$vr['new_total']) ?> (+ catatan).
                                                    <?php else: ?>
                                                        Saldo cicilan sudah sesuai.
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div id="verifyPaneInvalid" style="display: none;">
                    <?php if (empty($invalidRows)): ?>
                        <div style="text-align: center; padding: 30px; color: #16a34a;">
                            <i class="fa-solid fa-circle-check" style="font-size: 2rem; display: block; margin-bottom: 8px;"></i>
                            Seluruh baris sesuai tanpa data bermasalah.
                        </div>
                    <?php else: ?>
                        <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
                            <thead>
                                <tr style="background: #fef2f2; border-bottom: 2px solid #fecaca;">
                                    <th style="padding: 8px;">Baris</th>
                                    <th style="padding: 8px;">NIS &amp; Nama di File</th>
                                    <th style="padding: 8px;">Kelas</th>
                                    <th style="padding: 8px; text-align: right;">Nominal Terbaca</th>
                                    <th style="padding: 8px;">Keterangan Masalah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invalidRows as $ir): ?>
                                    <tr style="border-bottom: 1px solid #fee2e2;">
                                        <td style="padding: 8px; color: #64748b;">#<?= (int)$ir['row_num'] ?></td>
                                        <td style="padding: 8px;">
                                            <div style="font-weight: 700; color: #0f172a;"><?= SecurityHelper::escape($ir['nama'] ?? '-') ?></div>
                                            <div style="font-size: 0.75rem; color: #64748b;">NIS: <?= SecurityHelper::escape($ir['nis'] ?? '-') ?></div>
                                        </td>
                                        <td style="padding: 8px;"><?= SecurityHelper::escape($ir['class_name'] ?? '-') ?></td>
                                        <td style="padding: 8px; text-align: right; font-family: monospace;"><?= SecurityHelper::formatRupiah((float)($ir['amount'] ?? 0)) ?></td>
                                        <td style="padding: 8px;"><span class="badge" style="background: #fee2e2; color: #b91c1c;"><?= SecurityHelper::escape($ir['reason']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="submit" name="action" value="cancel" class="btn btn-outline">Batalkan Pratinjau</button>
                <?php if (!empty($validRows)): ?>
                    <button type="submit" name="action" value="commit" class="btn btn-primary" style="background: #16a34a; border-color: #16a34a; font-weight: 600;">
                        <i class="fa-solid fa-check-double" style="margin-right: 6px;"></i> Simpan Perubahan Terpilih
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

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

// Filter, tab kelas, sembunyikan lunas, dan kirim massal FR 8001B
let activeClassFilter = '';

function selectClassTab(btn, classVal) {
    activeClassFilter = (classVal || '').toLowerCase();
    document.querySelectorAll('.class-tab-btn').forEach(function(b) {
        const isCurrent = (b === btn);
        b.style.background = isCurrent ? '#0d9488' : '#fff';
        b.style.color = isCurrent ? '#fff' : '#334155';
        b.style.borderColor = isCurrent ? '#0d9488' : '#cbd5e1';
        b.classList.toggle('active', isCurrent);
    });
    if (window._applyBillFilter) window._applyBillFilter();
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchBill');
    const filterStatus = document.getElementById('filterStatus');
    const filterStage = document.getElementById('filterStage');
    const hideLunasToggle = document.getElementById('toggleHideLunas');
    const checkAllBills = document.getElementById('checkAllBills');
    const rows = document.querySelectorAll('.bill-row');

    function applyFilter() {
        const query = (searchInput ? searchInput.value : '').toLowerCase();
        const stat = (filterStatus ? filterStatus.value : '').toLowerCase();
        const stg = (filterStage ? filterStage.value : '').toLowerCase();
        const hideLunas = hideLunasToggle ? hideLunasToggle.checked : false;

        rows.forEach(function(row) {
            const dataSearch = row.getAttribute('data-search') || '';
            const dataStatus = row.getAttribute('data-status') || '';
            const dataStage = row.getAttribute('data-stage') || '';
            const dataClass = row.getAttribute('data-class') || '';

            const matchQuery = dataSearch.includes(query);
            const matchStatus = !stat || dataStatus === stat;
            const matchClass = !activeClassFilter || dataClass === activeClassFilter;
            const matchHideLunas = !hideLunas || (dataStatus !== 'lunas' && !dataStage.includes('selesai'));

            let matchStage = true;
            if (stg) {
                if (stg === 'selesai') {
                    matchStage = dataStage.includes('selesai');
                } else {
                    matchStage = dataStage.includes(stg);
                }
            }

            if (matchQuery && matchStatus && matchStage && matchClass && matchHideLunas) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    window._applyBillFilter = applyFilter;
    if (searchInput) searchInput.addEventListener('input', applyFilter);
    if (filterStatus) filterStatus.addEventListener('change', applyFilter);
    if (filterStage) filterStage.addEventListener('change', applyFilter);
    if (hideLunasToggle) hideLunasToggle.addEventListener('change', applyFilter);

    if (checkAllBills) {
        checkAllBills.addEventListener('change', function() {
            const checked = this.checked;
            rows.forEach(function(row) {
                if (row.style.display !== 'none') {
                    const cb = row.querySelector('.bill-cb');
                    if (cb) cb.checked = checked;
                }
            });
        });
    }
});

function submitBulkFR8001B() {
    const selected = [];
    document.querySelectorAll('.bill-row').forEach(function(row) {
        if (row.style.display !== 'none') {
            const cb = row.querySelector('.bill-cb');
            if (cb && cb.checked) selected.push(cb.value);
        }
    });
    if (selected.length === 0) {
        alert('Centang minimal satu tagihan tertunggak pada tabel terlebih dahulu untuk mengirim FR 8001B massal.');
        return;
    }
    if (!confirm('Kirimkan surat pengingat FR 8001B beserta lampiran PDF via email ke ' + selected.length + ' santri terpilih?')) {
        return;
    }
    const container = document.getElementById('bulkBillInputs');
    container.innerHTML = '';
    selected.forEach(function(id) {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'bill_ids[]';
        inp.value = id;
        container.appendChild(inp);
    });
    document.getElementById('formBulkFR8001B').submit();
}

function openUploadSignedModal(billId, studentName) {
    document.getElementById('uploadSignedBillId').value = billId;
    document.getElementById('uploadSignedStudentName').innerText = studentName;
    document.getElementById('modalUploadSignedTu').style.display = 'flex';
}

function closeUploadSignedModal() {
    document.getElementById('modalUploadSignedTu').style.display = 'none';
}

function switchVerifyTab(tab) {
    const validPane = document.getElementById('verifyPaneValid');
    const invalidPane = document.getElementById('verifyPaneInvalid');
    const btnValid = document.getElementById('btnVerifyTabValid');
    const btnInvalid = document.getElementById('btnVerifyTabInvalid');
    if (!validPane || !invalidPane) return;

    if (tab === 'valid') {
        validPane.style.display = 'block';
        invalidPane.style.display = 'none';
        btnValid.style.background = '#0d9488'; btnValid.style.color = '#fff'; btnValid.style.borderColor = '#0d9488';
        btnInvalid.style.background = '#fff'; btnInvalid.style.color = '#dc2626'; btnInvalid.style.borderColor = '#cbd5e1';
    } else {
        validPane.style.display = 'none';
        invalidPane.style.display = 'block';
        btnInvalid.style.background = '#dc2626'; btnInvalid.style.color = '#fff'; btnInvalid.style.borderColor = '#dc2626';
        btnValid.style.background = '#fff'; btnValid.style.color = '#334155'; btnValid.style.borderColor = '#cbd5e1';
    }
}

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

function filterStudentDropdown(query) {
    const q = (query || '').toLowerCase().trim();
    const select = document.getElementById('selectStudent');
    const alertBox = document.getElementById('studentNotFoundAlert');
    const options = select.querySelectorAll('option');
    let visibleCount = 0;
    let firstMatch = null;

    options.forEach(function(opt) {
        if (!opt.value) return; // skip placeholder
        const text = opt.textContent.toLowerCase();
        const nis = (opt.getAttribute('data-nis') || '').toLowerCase();
        const nama = (opt.getAttribute('data-nama') || '').toLowerCase();
        const matches = !q || text.includes(q) || nis.includes(q) || nama.includes(q);
        
        opt.hidden = !matches;
        opt.style.display = matches ? '' : 'none';
        if (matches) {
            visibleCount++;
            if (!firstMatch) firstMatch = opt;
        }
    });

    if (alertBox) alertBox.style.display = (visibleCount === 0 && q) ? 'block' : 'none';
    if (q && firstMatch && visibleCount === 1) {
        select.value = firstMatch.value;
    }
}
</script>
