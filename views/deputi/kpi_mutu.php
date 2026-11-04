<?php
// views/deputi/kpi_mutu.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge badge-primary">Deputi Pendidikan</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Quality Objective (QO) &amp; Standar Kelulusan Mapel</span>
            </div>
            <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading, #1e293b); margin: 0;">
                <i class="fa-solid fa-bullseye" style="color: #7c3aed; margin-right: 8px;"></i>
                Standar Mutu &amp; KPI KKM Mata Pelajaran Unit
            </h1>
            <p style="margin: 4px 0 0 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Tentukan batas KKM dan target ketuntasan siswa per mata pelajaran. Sistem akan otomatis membandingkan dengan nilai riil siswa.
            </p>
        </div>

        <button class="btn btn-primary" onclick="openAddKpiModal()" style="font-weight: 600;">
            <i class="fa-solid fa-plus"></i> Tambah Standar KPI Mapel
        </button>
    </div>
</div>

<!-- Skema KPI Info Banner -->
<div style="background: #eff6ff; border: 1px solid #bfdbfe; border-left: 4px solid #2563eb; border-radius: 8px; padding: 12px 18px; margin-bottom: 20px; font-size: 0.875rem; color: #1e3a8a;">
    <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-info" style="color: #2563eb;"></i>
        Skema Standar Mutu &amp; Keterkaitan Mapel Sekolah:
    </div>
    <div style="color: #334155; line-height: 1.5;">
        Standar KPI yang Anda tetapkan di bawah ini (Kategori, Standar KKM, &amp; % Syarat Kelulusan) akan menjadi referensi baku dan <strong>hanya akan muncul sebagai opsi</strong> saat Operator Sekolah mengatur mata pelajaran pada unit ini. Jika Anda belum menginput standar KPI pada suatu unit, maka pilihan KPI tidak akan tersedia bagi Operator Sekolah.
    </div>
</div>

<!-- Unit Selector Tabs -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px;">
    <?php foreach ($units as $u): ?>
        <?php $isActive = (int)$u['id'] === $selectedUnitId; ?>
        <a href="<?= $baseUrl ?>/deputi/kpi-mutu?unit_id=<?= $u['id'] ?>" 
           class="btn <?= $isActive ? 'btn-primary' : 'btn-outline' ?>" 
           style="font-size: 0.875rem; font-weight: 600; white-space: nowrap; border-radius: 8px;">
            <i class="fa-solid fa-school"></i> <?= SecurityHelper::escape($u['name']) ?> (<?= SecurityHelper::escape($u['level']) ?>)
        </a>
    <?php endforeach; ?>
</div>

<!-- KPI Standards Table -->
<div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: #1e293b;">
            Daftar Target Quality Objective (QO) Unit Terpilih
        </h3>
        <span style="font-size: 0.8125rem; color: #64748b;">
            Total: <strong><?= count($standards) ?></strong> Kategori KPI
        </span>
    </div>

    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #64748b;">
                    <th style="padding: 12px 16px;">Kategori Mapel</th>
                    <th style="padding: 12px 16px;">Sasaran Mutu (Objective)</th>
                    <th style="padding: 12px 16px; text-align: center;">Standar KKM</th>
                    <th style="padding: 12px 16px; text-align: center;">Target Tuntas</th>
                    <th style="padding: 12px 16px; text-align: center;">Realisasi Siswa Riil</th>
                    <th style="padding: 12px 16px; text-align: center;">Status Ketercapaian</th>
                    <th style="padding: 12px 16px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($standards)): ?>
                    <tr>
                        <td colspan="7" style="padding: 30px; text-align: center; color: var(--text-muted);">
                            Belum ada standar KPI yang ditambahkan untuk unit ini. Klik "Tambah Standar KPI Mapel" di atas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($standards as $std): ?>
                        <?php 
                            $ach = $achievementMap[$std['id']] ?? ['total' => 0, 'passed' => 0, 'pct' => 0, 'is_target' => false];
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.9375rem;">
                                    <?= SecurityHelper::escape($std['kpi_category']) ?>
                                </div>
                            </td>
                            <td style="padding: 12px 16px; font-size: 0.8125rem; color: #475569;">
                                <?= SecurityHelper::escape($std['objective_description'] ?: 'Pencapaian ketuntasan kurikulum syar\'i & nasional') ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                                    <?= number_format((float)$std['kkm_score'], 0) ?>
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <span class="badge" style="background: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                                    <?= number_format((float)$std['target_achievement_pct'], 0) ?>%
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <div style="font-weight: 700; font-size: 0.9375rem; color: <?= $ach['is_target'] ? '#15803d' : '#b91c1c' ?>;">
                                    <?= number_format($ach['pct'], 1) ?>%
                                </div>
                                <div style="font-size: 11px; color: #64748b;">
                                    (<?= $ach['passed'] ?> dari <?= $ach['total'] ?> siswa tuntas KKM)
                                </div>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <?php if ($ach['is_target']): ?>
                                    <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 11px; font-weight: 700; padding: 4px 10px;">
                                        <i class="fa-solid fa-circle-check"></i> Memenuhi Target
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: #fee2e2; color: #b91c1c; font-size: 11px; font-weight: 700; padding: 4px 10px;">
                                        <i class="fa-solid fa-circle-xmark"></i> Belum Tercapai
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: right; white-space: nowrap;">
                                <button type="button" class="btn btn-outline" style="padding: 4px 8px; font-size: 0.8125rem; color: #4338ca; border-color: #c7d2fe; margin-right: 4px;" onclick='openEditKpiModal(<?= json_encode($std) ?>)' title="Edit Standar KPI Ini">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="<?= $baseUrl ?>/deputi/kpi-mutu/delete" method="POST" style="display: inline-block;" onsubmit="return confirm('Hapus standar KPI ini?')">
                                    <?= CSRF::inputField() ?>
                                    <input type="hidden" name="id" value="<?= $std['id'] ?>">
                                    <input type="hidden" name="unit_id" value="<?= $selectedUnitId ?>">
                                    <button type="submit" class="btn btn-outline" style="padding: 4px 8px; font-size: 0.8125rem; color: #dc2626; border-color: #fecaca;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah / Edit KPI -->
<div class="modal-overlay" id="kpiModal">
    <div class="modal-container" style="max-width: 520px;">
        <form action="<?= $baseUrl ?>/deputi/kpi-mutu/save" method="POST">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="id" id="kpi_id" value="0">
            <input type="hidden" name="unit_id" value="<?= $selectedUnitId ?>">
            <input type="hidden" name="deputi_user_id" value="<?= $deputiUserId ?>">

            <div class="modal-header">
                <h3 class="card-title" id="kpiModalTitle" style="margin: 0; font-size: 1.125rem;">
                    <i class="fa-solid fa-plus-circle" style="color: #7c3aed;"></i> Tambah Standar Mutu KPI Mapel
                </h3>
            </div>
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Kategori Mata Pelajaran *</label>
                    <div style="display: flex; gap: 8px;">
                        <select id="kpi_category_select" class="form-control" onchange="syncKpiCategory(this.value)">
                            <option value="TAHFIZH">TAHFIZH (Hafalan Al-Qur'an)</option>
                            <option value="TAHSIN">TAHSIN (Membaca Al-Qur'an / Tajwid)</option>
                            <option value="TUHFATUL ATHFAL">TUHFATUL ATHFAL (Matan Tajwid)</option>
                            <option value="INDONESIAN">INDONESIAN (Bahasa Indonesia)</option>
                            <option value="SCIENCE">SCIENCE (IPA / Sains)</option>
                            <option value="MATH">MATH (Matematika)</option>
                            <option value="ENGLISH">ENGLISH (Bahasa Inggris)</option>
                            <option value="ARABIC">ARABIC (Bahasa Arab)</option>
                            <option value="PHYSIC">PHYSIC (Fisika)</option>
                            <option value="CHEMISTRY">CHEMISTRY (Kimia)</option>
                            <option value="BIOLOGY">BIOLOGY (Biologi)</option>
                            <option value="DINIYAH">DINIYAH (Fiqih / Aqidah / SKI)</option>
                            <option value="CUSTOM">-- Kategori Lainnya (Ketik Manual) --</option>
                        </select>
                    </div>
                    <input type="text" name="kpi_category" id="kpi_category_input" class="form-control" style="margin-top: 6px; text-transform: uppercase;" placeholder="Nama Kategori KPI..." value="TAHFIZH" required>
                    <small style="color: #64748b; font-size: 11px;">Kategori ini yang akan dipilih oleh Operator Sekolah saat menambahkan/mengatur mata pelajaran.</small>
                </div>
                <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Standar KKM Minimum *</label>
                        <input type="number" step="1" min="50" max="100" name="kkm_score" id="kpi_kkm_score" class="form-control" value="80" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">% Syarat Kelulusan (Target) *</label>
                        <input type="number" step="1" min="10" max="100" name="target_achievement_pct" id="kpi_target_pct" class="form-control" value="85" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Deskripsi Sasaran Mutu (Objective)</label>
                    <input type="text" name="objective_description" id="kpi_desc" class="form-control" placeholder="Contoh: 1/2 juz 30, properly reading, min score 80">
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeKpiModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Standar KPI</button>
            </div>
        </form>
    </div>
</div>

<script>
function syncKpiCategory(val) {
    var inp = document.getElementById('kpi_category_input');
    if (val === 'CUSTOM') {
        inp.value = '';
        inp.focus();
    } else {
        inp.value = val;
    }
}

function openAddKpiModal() {
    document.getElementById('kpi_id').value = '0';
    document.getElementById('kpiModalTitle').innerHTML = '<i class="fa-solid fa-plus-circle" style="color: #7c3aed;"></i> Tambah Standar Mutu KPI Mapel';
    document.getElementById('kpi_category_select').value = 'TAHFIZH';
    document.getElementById('kpi_category_input').value = 'TAHFIZH';
    document.getElementById('kpi_kkm_score').value = '80';
    document.getElementById('kpi_target_pct').value = '85';
    document.getElementById('kpi_desc').value = '';
    document.getElementById('kpiModal').classList.add('active');
}

function openEditKpiModal(std) {
    document.getElementById('kpi_id').value = std.id;
    document.getElementById('kpiModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square" style="color: #4338ca;"></i> Edit Standar Mutu KPI Mapel';
    
    var sel = document.getElementById('kpi_category_select');
    var found = false;
    for (var i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === std.kpi_category) {
            sel.selectedIndex = i;
            found = true;
            break;
        }
    }
    if (!found) {
        sel.value = 'CUSTOM';
    }
    document.getElementById('kpi_category_input').value = std.kpi_category;
    document.getElementById('kpi_kkm_score').value = parseInt(std.kkm_score) || 80;
    document.getElementById('kpi_target_pct').value = parseInt(std.target_achievement_pct) || 85;
    document.getElementById('kpi_desc').value = std.objective_description || '';
    document.getElementById('kpiModal').classList.add('active');
}

function closeKpiModal() {
    document.getElementById('kpiModal').classList.remove('active');
}
</script>

