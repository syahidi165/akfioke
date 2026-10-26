<?php
// views/master/akfi_templates.php
use Core\SecurityHelper;
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Master Template AKFI Seluruh Posisi</h1>
        <div class="page-subtitle">Atur bobot Kinerja, Sikap, Absensi, serta target indikator agar tidak ter-hardcode di sistem.</div>
    </div>
</div>

<div class="grid-cols-2">
    <?php foreach ($templates as $t): ?>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <div>
                    <h3 class="card-title" style="color: var(--primary);"><?= SecurityHelper::escape($t['position_name']) ?></h3>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Kode Posisi: <code><?= SecurityHelper::escape($t['position_code']) ?></code></div>
                </div>
                <a href="<?= $baseUrl ?>/master/akfi-templates/edit/<?= $t['id'] ?>" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Indikator
                </a>
            </div>
            <div class="card-body">
                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div style="flex: 1; background: #eff6ff; padding: 10px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; color: #1e40af; font-weight: 600;">Kinerja (A)</div>
                        <div style="font-size: 18px; font-weight: 700; color: #1e40af;"><?= (int)$t['bobot_kinerja'] ?>%</div>
                    </div>
                    <div style="flex: 1; background: #f0fdf4; padding: 10px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; color: #15803d; font-weight: 600;">Sikap (B)</div>
                        <div style="font-size: 18px; font-weight: 700; color: #15803d;"><?= (int)$t['bobot_sikap'] ?>%</div>
                    </div>
                    <div style="flex: 1; background: #fefce8; padding: 10px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; color: #a16207; font-weight: 600;">Absensi (C)</div>
                        <div style="font-size: 18px; font-weight: 700; color: #a16207;"><?= (int)$t['bobot_absensi'] ?>%</div>
                    </div>
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Rentang Nilai Huruf: A (&ge; <?= (int)$t['grade_a_min'] ?>), B (&ge; <?= (int)$t['grade_b_min'] ?>), C (&ge; <?= (int)$t['grade_c_min'] ?>), D (&ge; <?= (int)$t['grade_d_min'] ?>), E (&lt; <?= (int)$t['grade_d_min'] ?>)
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
