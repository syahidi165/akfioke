<?php
// views/deputi/bobot.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <span class="badge badge-primary">Direktur Pendidikan</span>
            <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tahun Ajaran <?= SecurityHelper::escape($currentTa['name'] ?? '') ?></span>
        </div>
        <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading, #1e293b); margin: 0;">
            <i class="fa-solid fa-sliders" style="color: #0284c7; margin-right: 8px;"></i>
            Pengaturan Bobot Rapor Deputi Pendidikan
        </h1>
        <p style="margin: 4px 0 0 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
            Bobot indikator kinerja Deputi bersifat dinamis dan dapat disesuaikan. <strong>Total akumulasi seluruh bobot wajib tepat 100.00%</strong>.
        </p>
    </div>
</div>

<form action="<?= $baseUrl ?>/deputi/bobot/save" method="POST" id="formBobot">
    <?= CSRF::inputField() ?>

    <!-- Sticky Counter Bar -->
    <div style="position: sticky; top: 70px; z-index: 100; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 14px 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-weight: 600; font-size: 0.9375rem; color: #334155;">Total Akumulasi Bobot:</span>
            <span id="totalBadge" class="badge" style="font-size: 1.125rem; padding: 6px 14px; font-weight: 700; border-radius: 20px; background: #fee2e2; color: #b91c1c;">
                <span id="totalDisplay">0.00</span>%
            </span>
            <span id="statusMessage" style="font-size: 0.8125rem; color: #dc2626; font-weight: 600;">
                <i class="fa-solid fa-triangle-exclamation"></i> Total harus tepat 100.00%
            </span>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn btn-outline" onclick="resetToDefaults()" style="font-size: 0.875rem;">
                <i class="fa-solid fa-rotate-left"></i> Reset Default SOP
            </button>
            <button type="submit" id="btnSave" class="btn btn-primary" style="font-weight: 700; padding: 8px 24px;" disabled>
                <i class="fa-solid fa-save"></i> Simpan Bobot (100%)
            </button>
        </div>
    </div>

    <?php
    $grouped = [];
    foreach ($indicators as $ind) {
        $grouped[$ind['area_no']]['area_name'] = $ind['area_name'];
        $grouped[$ind['area_no']]['items'][] = $ind;
    }
    ?>

    <div style="display: flex; flex-direction: column; gap: 24px;">
        <?php foreach ($grouped as $areaNo => $area): ?>
            <div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
                <div style="background: #f1f5f9; padding: 14px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-weight: 700; font-size: 1rem; color: #1e293b;">
                        <?= SecurityHelper::escape($area['area_name']) ?>
                    </div>
                    <div style="font-size: 0.8125rem; color: #64748b; font-weight: 600;">
                        Subtotal Area: <span class="area-subtotal" id="area_subtotal_<?= $areaNo ?>">0.00</span>%
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #64748b;">
                                <th style="padding: 10px 16px; width: 60px;">Kode</th>
                                <th style="padding: 10px 16px;">Indikator Kinerja Utama (KPI SOP)</th>
                                <th style="padding: 10px 16px;">Target / Standar Kerja</th>
                                <th style="padding: 10px 16px; width: 120px;">Sifat Data</th>
                                <th style="padding: 10px 16px; width: 150px; text-align: right;">Bobot (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($area['items'] as $item): ?>
                                <?php 
                                    $iId = $item['id'];
                                    $currentWeight = isset($weightMap[$iId]) ? ($weightMap[$iId] * 100) : ((float)$item['default_weight'] * 100);
                                    $defaultWeightPct = (float)$item['default_weight'] * 100;
                                ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 16px; font-weight: 700; color: #64748b;">
                                        <?= SecurityHelper::escape($item['indicator_code']) ?>
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <div style="font-weight: 600; color: #1e293b; font-size: 0.875rem;">
                                            <?= SecurityHelper::escape($item['indicator_name']) ?>
                                        </div>
                                        <?php if (!empty($item['guidance_note'])): ?>
                                            <div style="font-size: 0.75rem; color: #0284c7; margin-top: 2px;">
                                                <i class="fa-solid fa-circle-info"></i> <?= SecurityHelper::escape($item['guidance_note']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 0.8125rem; color: #475569;">
                                        <?= SecurityHelper::escape($item['target_standard']) ?>
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <?php if ($item['data_source_type'] === 'auto_system'): ?>
                                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px;">Otomatis Sistem</span>
                                        <?php elseif ($item['data_source_type'] === 'app_calendar'): ?>
                                            <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 11px;">App Kalender/MoM</span>
                                        <?php elseif ($item['data_source_type'] === 'app_rapbs'): ?>
                                            <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 11px;">App RAPBS</span>
                                        <?php elseif ($item['data_source_type'] === 'app_kpi'): ?>
                                            <span class="badge" style="background: #f3e8ff; color: #7e22ce; font-size: 11px;">Standar KPI Siswa</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px;">Review Subjektif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px 16px; text-align: right;">
                                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                            <input type="number" step="0.01" min="0" max="100" 
                                                   name="weights[<?= $iId ?>]" 
                                                   value="<?= number_format($currentWeight, 2, '.', '') ?>" 
                                                   data-default="<?= number_format($defaultWeightPct, 2, '.', '') ?>"
                                                   data-area="<?= $areaNo ?>"
                                                   class="form-control weight-input" 
                                                   style="width: 85px; text-align: right; font-weight: 700;"
                                                   oninput="recalculateTotals()">
                                            <span style="font-weight: 600; color: #64748b;">%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</form>

<script>
function recalculateTotals() {
    var inputs = document.querySelectorAll('.weight-input');
    var grandTotal = 0;
    var areaTotals = {};

    inputs.forEach(function(inp) {
        var val = parseFloat(inp.value) || 0;
        var area = inp.getAttribute('data-area');
        grandTotal += val;
        areaTotals[area] = (areaTotals[area] || 0) + val;
    });

    // Update Area Subtotals
    for (var a in areaTotals) {
        var el = document.getElementById('area_subtotal_' + a);
        if (el) {
            el.innerText = areaTotals[a].toFixed(2);
        }
    }

    var totalDisplay = document.getElementById('totalDisplay');
    var totalBadge = document.getElementById('totalBadge');
    var statusMessage = document.getElementById('statusMessage');
    var btnSave = document.getElementById('btnSave');

    totalDisplay.innerText = grandTotal.toFixed(2);

    // Cek apakah tepat 100% (toleransi 0.05%)
    var isExact100 = Math.abs(grandTotal - 100.0) <= 0.05;

    if (isExact100) {
        totalBadge.style.background = '#dcfce7';
        totalBadge.style.color = '#15803d';
        statusMessage.style.color = '#15803d';
        statusMessage.innerHTML = '<i class="fa-solid fa-circle-check"></i> Total pas 100.00%, siap disimpan';
        btnSave.disabled = false;
        btnSave.style.opacity = '1';
        btnSave.style.cursor = 'pointer';
    } else {
        totalBadge.style.background = '#fee2e2';
        totalBadge.style.color = '#b91c1c';
        statusMessage.style.color = '#dc2626';
        var selisih = (100.0 - grandTotal).toFixed(2);
        if (grandTotal < 100) {
            statusMessage.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Kurang ' + selisih + '%, total harus 100.00%';
        } else {
            statusMessage.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Lebih ' + Math.abs(selisih) + '%, total harus 100.00%';
        }
        btnSave.disabled = true;
        btnSave.style.opacity = '0.5';
        btnSave.style.cursor = 'not-allowed';
    }
}

function resetToDefaults() {
    if (!confirm('Kembalikan bobot ke standar default SOP?')) return;
    var inputs = document.querySelectorAll('.weight-input');
    inputs.forEach(function(inp) {
        inp.value = inp.getAttribute('data-default');
    });
    recalculateTotals();
}

document.addEventListener('DOMContentLoaded', function() {
    recalculateTotals();
});
</script>
