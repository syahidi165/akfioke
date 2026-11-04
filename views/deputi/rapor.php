<?php
// views/deputi/rapor.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge badge-primary">Direktur Pendidikan</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Evaluasi Kinerja Eksekutif Deputi SOP 2017 Rev A</span>
            </div>
            <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading, #1e293b); margin: 0;">
                <i class="fa-solid fa-file-shield" style="color: #4f46e5; margin-right: 8px;"></i>
                Form &amp; Evaluasi Rapor AKFI Deputi Pendidikan
            </h1>
            <p style="margin: 4px 0 0 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pegawai: <strong><?= SecurityHelper::escape($deputiUser['full_name'] ?? 'Deputi') ?></strong> &bull; Semester <?= SecurityHelper::escape($semester) ?>
            </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="<?= $baseUrl ?>/deputi/rapor/print?deputi_id=<?= $deputiUserId ?>&semester=<?= $semester ?>" target="_blank" class="btn btn-outline" style="font-weight: 600;">
                <i class="fa-solid fa-print"></i> Cetak Lembar Rapor Resmi
            </a>
            <?php if ($isDirektur): ?>
                <button type="button" class="btn btn-outline" onclick="applyAutoMetrics()" style="font-weight: 600; color: #0284c7; border-color: #bae6fd; background: #f0f9ff;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Tarik Data Otomatis Sistem
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Header Info Cards -->
<div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; margin-bottom: 24px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div>
            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Nama Pegawai</div>
            <div style="font-size: 1rem; font-weight: 700; color: #1e293b;"><?= SecurityHelper::escape($deputiUser['full_name'] ?? '-') ?></div>
            <div style="font-size: 0.8125rem; color: #64748b;">NIPY: <?= SecurityHelper::escape($deputiUser['username'] ?? '-') ?></div>
        </div>
        <div>
            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Jabatan</div>
            <div style="font-size: 1rem; font-weight: 700; color: #1e293b;">Deputi Divisi Pendidikan</div>
            <div style="font-size: 0.8125rem; color: #64748b;">Yayasan Islam Al-Kahfi Batam</div>
        </div>
        <div>
            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Penilai / Evaluator</div>
            <div style="font-size: 1rem; font-weight: 700; color: #1e293b;"><?= SecurityHelper::escape($evaluation['evaluator_name'] ?? 'Direktur Pendidikan') ?></div>
            <div style="font-size: 0.8125rem; color: #64748b;">Jabatan: Direktur Pendidikan</div>
        </div>
        <div>
            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Nilai Akhir &amp; Predikat</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: #4338ca;">
                <span id="finalScoreDisplay"><?= number_format((float)($evaluation['final_score'] ?? 0), 2) ?></span>
                <span id="predicateDisplay" class="badge" style="font-size: 12px; margin-left: 6px;"><?= SecurityHelper::escape($evaluation['predicate'] ?? 'Belum Dinilai') ?></span>
            </div>
        </div>
    </div>
</div>

<form action="<?= $baseUrl ?>/deputi/rapor/save-evaluation" method="POST" id="formRaporDeputi">
    <?= CSRF::inputField() ?>
    <input type="hidden" name="deputi_user_id" value="<?= $deputiUserId ?>">
    <input type="hidden" name="semester" value="<?= $semester ?>">

    <!-- Table Rapor 23 Indikator -->
    <div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px;">
        <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 700; color: #1e293b; font-size: 0.9375rem;">
                FORM PENILAIAN KINERJA DEPUTI DIVISI PENDIDIKAN (SOP 2017 REV A)
            </div>
            <div style="font-size: 0.8125rem; color: #64748b;">
                Skala Nilai: 1 (Sangat Kurang) s.d 5 (Sangat Baik / $\ge$90%)
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #475569; border-bottom: 2px solid #cbd5e1;">
                        <th style="padding: 10px 14px; width: 50px;">No</th>
                        <th style="padding: 10px 14px;">Indikator Kinerja Utama (KPI SOP)</th>
                        <th style="padding: 10px 14px; width: 180px;">Target / Standar</th>
                        <th style="padding: 10px 14px; width: 90px; text-align: right;">Bobot (%)</th>
                        <th style="padding: 10px 14px; width: 100px; text-align: center;">Skala (1-5)</th>
                        <th style="padding: 10px 14px; width: 100px; text-align: right;">Capaian (%)</th>
                        <th style="padding: 10px 14px; width: 120px; text-align: right;">Skor Tertimbang</th>
                        <th style="padding: 10px 14px;">Catatan Bukti / Eviden</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $curArea = 0;
                    $rowNo = 1;

                    foreach ($indicators as $ind):
                        $iId = $ind['id'];
                        $code = $ind['indicator_code'];

                        // Nilai tersimpan atau default
                        $savedDetail = $evalDetails[$iId] ?? null;
                        $weight = isset($weightMap[$iId]) ? (float)$weightMap[$iId] : (float)$ind['default_weight'];
                        $weightPct = $weight * 100.0;
                        
                        $scale = $savedDetail ? (int)$savedDetail['scale_1_to_5'] : 4;
                        $achPct = ($scale / 5.0) * 100.0;
                        $weighted = $weight * ($achPct / 100.0);
                        $notes = $savedDetail ? $savedDetail['notes'] : '';

                        // Info auto metric jika ada
                        $auto = $autoScores[$code] ?? null;
                    ?>
                        <?php if ($ind['area_no'] !== $curArea): ?>
                            <?php $curArea = $ind['area_no']; ?>
                            <tr style="background: #e2e8f0; font-weight: 800; border-top: 2px solid #cbd5e1;">
                                <td style="padding: 8px 14px; text-align: center; color: #1e293b;"><?= $curArea ?></td>
                                <td style="padding: 8px 14px; color: #1e293b;" colspan="7">
                                    <?= SecurityHelper::escape($ind['area_name']) ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.8125rem;">
                            <td style="padding: 10px 14px; text-align: center; color: #64748b;"><?= $rowNo++ ?></td>
                            <td style="padding: 10px 14px;">
                                <div style="font-weight: 600; color: #1e293b;">
                                    <?= SecurityHelper::escape($ind['indicator_name']) ?>
                                </div>
                                <?php if ($auto): ?>
                                    <div style="font-size: 11px; color: #0284c7; margin-top: 2px;">
                                        <i class="fa-solid fa-circle-nodes"></i> Data Sistem: <?= SecurityHelper::escape($auto['info']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px 14px; font-size: 0.75rem; color: #64748b;">
                                <?= SecurityHelper::escape($ind['target_standard']) ?>
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 700;">
                                <input type="hidden" name="weight[<?= $iId ?>]" value="<?= $weight ?>" id="weight_<?= $iId ?>">
                                <?= number_format($weightPct, 1) ?>%
                            </td>
                            <td style="padding: 10px 14px; text-align: center;">
                                <select name="scale[<?= $iId ?>]" id="scale_<?= $iId ?>" 
                                        class="form-control scale-select" 
                                        style="font-size: 0.8125rem; font-weight: 700; width: 75px; text-align: center; margin: 0 auto;" 
                                        data-id="<?= $iId ?>" 
                                        data-code="<?= $code ?>"
                                        onchange="recalcRow(<?= $iId ?>)"
                                        <?= !$isDirektur ? 'disabled' : '' ?>>
                                    <option value="5" <?= $scale === 5 ? 'selected' : '' ?>>5 (SB)</option>
                                    <option value="4" <?= $scale === 4 ? 'selected' : '' ?>>4 (B)</option>
                                    <option value="3" <?= $scale === 3 ? 'selected' : '' ?>>3 (C)</option>
                                    <option value="2" <?= $scale === 2 ? 'selected' : '' ?>>2 (K)</option>
                                    <option value="1" <?= $scale === 1 ? 'selected' : '' ?>>1 (SK)</option>
                                </select>
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 600;">
                                <span id="ach_display_<?= $iId ?>"><?= number_format($achPct, 0) ?></span>%
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 700; color: #4338ca;">
                                <span id="weighted_display_<?= $iId ?>" class="weighted-val"><?= number_format($weighted * 100, 2) ?></span>%
                            </td>
                            <td style="padding: 8px 14px;">
                                <input type="text" name="notes[<?= $iId ?>]" value="<?= SecurityHelper::escape($notes) ?>" 
                                       class="form-control" style="font-size: 0.75rem;" placeholder="Catatan bukti / eviden..."
                                       <?= !$isDirektur ? 'disabled' : '' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Total Row -->
                    <tr style="background: #0f172a; color: #ffffff; font-weight: 800; font-size: 0.9375rem;">
                        <td colspan="3" style="padding: 12px 14px; text-align: right;">TOTAL BOBOT &amp; SKOR AKHIR TERTIMBANG</td>
                        <td style="padding: 12px 14px; text-align: right; color: #4ade80;">100.0%</td>
                        <td colspan="2"></td>
                        <td style="padding: 12px 14px; text-align: right; color: #38bdf8; font-size: 1.125rem;">
                            <span id="grandScoreDisplay"><?= number_format((float)($evaluation['final_score'] ?? 0), 2) ?></span>
                        </td>
                        <td style="padding: 12px 14px; color: #f8fafc;">
                            Skor Maks: 100.00
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recommendations & Evaluation Notes -->
    <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; margin-bottom: 24px;">
        <h3 style="margin: 0 0 12px 0; font-size: 1rem; font-weight: 700; color: #1e293b;">
            <i class="fa-solid fa-comment-dots" style="color: #4f46e5;"></i> Rekomendasi / Catatan Evaluator (Direktur Pendidikan)
        </h3>
        <textarea name="evaluator_notes" class="form-control" rows="3" placeholder="Tuliskan catatan pembinaan, apresiasi capaian, dan arahan peningkatan kinerja untuk Deputi..." <?= !$isDirektur ? 'disabled' : '' ?>><?= SecurityHelper::escape($evaluation['evaluator_notes'] ?? '') ?></textarea>
    </div>

    <?php if ($isDirektur): ?>
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 40px;">
            <button type="submit" name="status" value="Draft" class="btn btn-outline" style="font-weight: 600;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Draf
            </button>
            <button type="submit" name="status" value="Final" class="btn btn-primary" style="font-weight: 700; padding: 10px 28px;">
                <i class="fa-solid fa-check"></i> Simpan &amp; Kunci Nilai Rapor
            </button>
        </div>
    <?php endif; ?>
</form>

<script>
// JSON Auto metrics from backend
var autoMetrics = <?= json_encode($autoScores, JSON_UNESCAPED_UNICODE) ?>;

function recalcRow(id) {
    var scaleInp = document.getElementById('scale_' + id);
    var weightInp = document.getElementById('weight_' + id);
    var scale = parseInt(scaleInp.value) || 1;
    var weight = parseFloat(weightInp.value) || 0.04;

    var achPct = (scale / 5.0) * 100.0;
    var weightedScore = weight * (achPct / 100.0) * 100.0;

    document.getElementById('ach_display_' + id).innerText = achPct.toFixed(0);
    document.getElementById('weighted_display_' + id).innerText = weightedScore.toFixed(2);

    recalcGrandTotal();
}

function recalcGrandTotal() {
    var grandTotal = 0;
    document.querySelectorAll('.weighted-val').forEach(function(el) {
        grandTotal += parseFloat(el.innerText) || 0;
    });

    document.getElementById('grandScoreDisplay').innerText = grandTotal.toFixed(2);
    document.getElementById('finalScoreDisplay').innerText = grandTotal.toFixed(2);

    var pred = 'Kurang (D)';
    if (grandTotal >= 90.0) {
        pred = 'Sangat Baik (A)';
    } else if (grandTotal >= 80.0) {
        pred = 'Baik (B)';
    } else if (grandTotal >= 70.0) {
        pred = 'Cukup (C)';
    }
    document.getElementById('predicateDisplay').innerText = pred;
}

function applyAutoMetrics() {
    if (!confirm('Terapkan nilai otomatis hasil kalkulasi sistem untuk indikator yang tersedia?')) return;
    document.querySelectorAll('.scale-select').forEach(function(sel) {
        var code = sel.getAttribute('data-code');
        if (autoMetrics[code] && autoMetrics[code].scale) {
            sel.value = autoMetrics[code].scale;
            recalcRow(sel.getAttribute('data-id'));
        }
    });
    recalcGrandTotal();
    alert('Nilai otomatis sistem berhasil diterapkan pada tabel.');
}

document.addEventListener('DOMContentLoaded', function() {
    recalcGrandTotal();
});
</script>
