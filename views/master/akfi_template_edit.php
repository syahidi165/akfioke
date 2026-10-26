<?php
// views/master/akfi_template_edit.php
use Core\SecurityHelper;
use Core\CSRF;

// Hitung subtotal awal
$initialA = 0;
$initialB = 0;
$initialC = 0;
foreach ($items as $it) {
    $w = (float)$it['weight'];
    if ($it['section'] === 'A') $initialA += $w;
    elseif ($it['section'] === 'B') $initialB += $w;
    elseif ($it['section'] === 'C') $initialC += $w;
}
$initialTotal = $initialA + $initialB + $initialC;
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Template AKFI: <?= SecurityHelper::escape($template['position_name']) ?></h1>
        <div class="page-subtitle">Bobot Komponen Utama otomatis terakumulasi dari rincian bobot indikator penilaian per posisi.</div>
    </div>
    <div>
        <a href="<?= $baseUrl ?>/master/akfi-templates" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<form method="POST" action="<?= $baseUrl ?>/master/akfi-templates/update" id="templateForm">
    <?= CSRF::inputField() ?>
    <input type="hidden" name="template_id" value="<?= $template['id'] ?>">

    <!-- Bobot Komponen Utama (Otomatis / Akumulatif) -->
    <div class="card" style="margin-bottom: 24px; border-left: 4px solid var(--primary);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 class="card-title" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-calculator" style="color: var(--primary);"></i>
                    Bobot Komponen Utama (Akumulasi Otomatis dari Butir Indikator)
                </h3>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 3px;">
                    Nilai di bawah ini terkunci dan otomatis menghitung jumlah bobot indikator pada masing-masing Bagian.
                </div>
            </div>
            <div id="grandTotalBadgeWrapper" style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 13px; font-weight: 600; color: var(--text-main);">Grand Total Bobot:</span>
                <span id="grandTotalBadge" class="badge <?= abs($initialTotal - 100.0) < 0.01 ? 'badge-success' : 'badge-danger' ?>" style="font-size: 14px; padding: 6px 14px; font-weight: 700;">
                    <span id="grandTotalVal"><?= number_format($initialTotal, 1) ?></span>%
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="grid-cols-3">
                <!-- Komponen A: Kinerja -->
                <div class="form-group" style="margin-bottom: 0; background: #eff6ff; padding: 14px; border-radius: 8px; border: 1px solid #bfdbfe;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin: 0; color: #1e40af; font-weight: 700;">
                            <i class="fa-solid fa-briefcase" style="margin-right: 4px;"></i> Bobot Kinerja (Bagian A)
                        </label>
                        <span class="badge badge-info" style="font-size: 10px;">Otomatis</span>
                    </div>
                    <div style="position: relative;">
                        <input type="number" step="0.1" name="bobot_kinerja" id="bobot_kinerja" class="form-control" 
                               value="<?= $initialA ?>" readonly 
                               style="background: #ffffff; font-weight: 800; font-size: 18px; color: #1e40af; cursor: not-allowed;">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: #1e40af;">%</span>
                    </div>
                    <div style="font-size: 11px; color: #3b82f6; margin-top: 5px;">
                        Akumulasi seluruh butir Bagian A
                    </div>
                </div>

                <!-- Komponen B: Sikap / Kompetensi -->
                <div class="form-group" style="margin-bottom: 0; background: #f0fdf4; padding: 14px; border-radius: 8px; border: 1px solid #bbf7d0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin: 0; color: #166534; font-weight: 700;">
                            <i class="fa-solid fa-heart" style="margin-right: 4px;"></i> Bobot Sikap (Bagian B)
                        </label>
                        <span class="badge badge-success" style="font-size: 10px;">Otomatis</span>
                    </div>
                    <div style="position: relative;">
                        <input type="number" step="0.1" name="bobot_sikap" id="bobot_sikap" class="form-control" 
                               value="<?= $initialB ?>" readonly 
                               style="background: #ffffff; font-weight: 800; font-size: 18px; color: #166534; cursor: not-allowed;">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: #166534;">%</span>
                    </div>
                    <div style="font-size: 11px; color: #16a34a; margin-top: 5px;">
                        Akumulasi seluruh butir Bagian B
                    </div>
                </div>

                <!-- Komponen C: Absensi -->
                <div class="form-group" style="margin-bottom: 0; background: #fefce8; padding: 14px; border-radius: 8px; border: 1px solid #fef08a;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin: 0; color: #854d0e; font-weight: 700;">
                            <i class="fa-solid fa-clock" style="margin-right: 4px;"></i> Bobot Absensi (Bagian C)
                        </label>
                        <span class="badge badge-warning" style="font-size: 10px;">Otomatis</span>
                    </div>
                    <div style="position: relative;">
                        <input type="number" step="0.1" name="bobot_absensi" id="bobot_absensi" class="form-control" 
                               value="<?= $initialC ?>" readonly 
                               style="background: #ffffff; font-weight: 800; font-size: 18px; color: #854d0e; cursor: not-allowed;">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: #854d0e;">%</span>
                    </div>
                    <div style="font-size: 11px; color: #ca8a04; margin-top: 5px;">
                        Akumulasi seluruh butir Bagian C
                    </div>
                </div>
            </div>

            <div id="weightAlertBox" style="margin-top: 14px; display: <?= abs($initialTotal - 100.0) < 0.01 ? 'none' : 'block' ?>;">
                <div class="alert alert-warning" style="margin: 0; font-size: 13px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><strong>Perhatian:</strong> Total akumulasi bobot saat ini adalah <strong><span id="alertTotalVal"><?= number_format($initialTotal, 1) ?></span>%</strong>. Idealnya total bobot seluruh indikator adalah tepat 100.0%.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Indikator -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 class="card-title" style="margin: 0;">Rincian Butir Indikator Penilaian</h3>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    Ubah bobot butir indikator di kolom "Bobot (%)". Bobot komponen utama di atas akan otomatis menyesuaikan seketika.
                </div>
            </div>
            <span class="badge badge-secondary"><?= count($items) ?> Butir Indikator</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Bagian</th>
                            <th>Kategori</th>
                            <th>Kode</th>
                            <th>Nama Indikator</th>
                            <th>Satuan</th>
                            <th style="width: 120px;">Target</th>
                            <th style="width: 130px;">Bobot (%)</th>
                            <th>Arah Penilaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td>
                                    <span class="badge <?= $it['section'] === 'A' ? 'badge-info' : ($it['section'] === 'B' ? 'badge-success' : 'badge-warning') ?>">
                                        Bagian <?= $it['section'] ?>
                                    </span>
                                </td>
                                <td><?= SecurityHelper::escape($it['category']) ?></td>
                                <td><code><?= SecurityHelper::escape($it['item_code']) ?></code></td>
                                <td><strong><?= SecurityHelper::escape($it['item_name']) ?></strong></td>
                                <td><?= SecurityHelper::escape($it['unit_type']) ?></td>
                                <td>
                                    <input type="number" step="0.1" name="items[<?= $it['id'] ?>][target_val]" class="form-control form-control-sm" value="<?= $it['target_val'] ?>" required>
                                </td>
                                <td>
                                    <input type="number" step="0.1" min="0" max="100" 
                                           name="items[<?= $it['id'] ?>][weight]" 
                                           class="form-control form-control-sm item-weight-input" 
                                           data-section="<?= $it['section'] ?>" 
                                           value="<?= $it['weight'] ?>" 
                                           oninput="recalcWeights()" required
                                           style="font-weight: 700;">
                                </td>
                                <td>
                                    <span style="font-size: 11px; color: <?= $it['direction'] === 'higher_better' ? '#10b981' : '#ef4444' ?>;">
                                        <?= $it['direction'] === 'higher_better' ? 'Makin tinggi makin baik' : 'Makin rendah makin baik' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body" style="border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" id="btnSubmitTemplate">
                <i class="fa-solid fa-save"></i> Simpan Perubahan Template
            </button>
        </div>
    </div>
</form>

<script>
function recalcWeights() {
    let sumA = 0;
    let sumB = 0;
    let sumC = 0;

    const inputs = document.querySelectorAll('.item-weight-input');
    inputs.forEach(input => {
        const val = parseFloat(input.value) || 0;
        const section = input.getAttribute('data-section');
        if (section === 'A') sumA += val;
        else if (section === 'B') sumB += val;
        else if (section === 'C') sumC += val;
    });

    sumA = Math.round(sumA * 10) / 10;
    sumB = Math.round(sumB * 10) / 10;
    sumC = Math.round(sumC * 10) / 10;
    const grandTotal = Math.round((sumA + sumB + sumC) * 10) / 10;

    // Update input utama yang statis/terkunci
    document.getElementById('bobot_kinerja').value = sumA.toFixed(1);
    document.getElementById('bobot_sikap').value = sumB.toFixed(1);
    document.getElementById('bobot_absensi').value = sumC.toFixed(1);

    // Update badge grand total
    const totalBadge = document.getElementById('grandTotalBadge');
    document.getElementById('grandTotalVal').innerText = grandTotal.toFixed(1);

    const alertBox = document.getElementById('weightAlertBox');
    const alertTotalVal = document.getElementById('alertTotalVal');

    if (Math.abs(grandTotal - 100.0) < 0.05) {
        totalBadge.className = 'badge badge-success';
        alertBox.style.display = 'none';
    } else {
        totalBadge.className = 'badge badge-danger';
        alertBox.style.display = 'block';
        if (alertTotalVal) alertTotalVal.innerText = grandTotal.toFixed(1);
    }
}
</script>
