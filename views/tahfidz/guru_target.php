<?php
// views/tahfidz/guru_target.php
use Core\SecurityHelper;
use Core\CSRF;
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Target & Baseline Hafalan Guru</h1>
        <div class="page-subtitle">Atur target capaian hafalan Al-Qur'an tahunan untuk dewan guru dan karyawan unit kerja.</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa-solid fa-bullseye" style="color: var(--primary); margin-right: 6px;"></i>
            Daftar Target Hafalan Guru (Standar: TK 150 Baris, SD/SMP/SMA 300 Baris)
        </h3>
        <div>
            <span class="badge badge-info" style="font-size: 12px;"><?= count($teachers) ?> Pegawai</span>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 48px; text-align: center;">No</th>
                    <th>Nama Guru / Pegawai</th>
                    <th>Posisi / Bagian</th>
                    <th style="width: 150px; text-align: center;">Target Tahunan</th>
                    <th>Baseline Awal</th>
                    <th>Detail Sasaran Surat / Juz</th>
                    <th style="width: 120px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr><td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">Belum ada data guru di unit ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($teachers as $idx => $t): ?>
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-muted);"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= SecurityHelper::escape($t['nama']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);">NIPY: <?= SecurityHelper::escape($t['nipy']) ?></div>
                            </td>
                            <td><span class="badge badge-secondary"><?= SecurityHelper::escape($t['level_posisi']) ?></span></td>
                            <td style="text-align: center;">
                                <span class="badge badge-success font-semibold" style="font-size: 12px;">
                                    <?= (int)($t['target_lines'] ?? 300) ?> Baris
                                </span>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                    <?= ((int)($t['target_lines'] ?? 300) >= 300) ? '(1 Juz)' : '(1/2 Juz)' ?>
                                </div>
                            </td>
                            <td><?= SecurityHelper::escape($t['baseline_info'] ?? 'Juz 30') ?></td>
                            <td><?= SecurityHelper::escape($t['target_info'] ?? 'Target Juz Baru') ?></td>
                            <td style="text-align: center;">
                                <button type="button" class="btn btn-outline btn-sm" onclick='openTargetModal(<?= json_encode($t) ?>)'>
                                    <i class="fa-solid fa-pen-to-square"></i> Set Target
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Set Target Hafalan Guru -->
<div class="modal-overlay" id="targetModal">
    <div class="modal-container" style="max-width: 480px;">
        <form method="POST" action="<?= $baseUrl ?>/tahfidz/guru-target/save">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="employee_id" id="tgt_employee_id">

            <div class="modal-header">
                <h3 class="card-title">Atur Target Hafalan Guru</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('targetModal').classList.remove('active')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Guru</label>
                    <div id="tgt_teacher_name" style="font-weight: 700; color: var(--primary); font-size: 15px;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Target Jumlah Baris Hafalan <span style="color: var(--danger);">*</span></label>
                    <select name="target_lines" id="tgt_lines" class="form-control" required>
                        <option value="300">300 Baris (1 Juz Penuh - Standar SD/SMP/SMA)</option>
                        <option value="150">150 Baris (Setengah Juz - Standar TK)</option>
                        <option value="450">450 Baris (1.5 Juz)</option>
                        <option value="600">600 Baris (2 Juz)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Baseline Capaian Awal Guru</label>
                    <input type="text" name="baseline_info" id="tgt_baseline" class="form-control" placeholder="Contoh: Sudah hafal Juz 30 mutqin">
                </div>

                <div class="form-group">
                    <label class="form-label">Target Spesifik Surat / Juz Tahun Ini</label>
                    <input type="text" name="target_info" id="tgt_info" class="form-control" placeholder="Contoh: Menghafal Juz 29 (Al-Mulk s/d Al-Mursalat)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('targetModal').classList.remove('active')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Target</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTargetModal(t) {
    document.getElementById('tgt_employee_id').value = t.id;
    document.getElementById('tgt_teacher_name').innerText = t.nama;
    document.getElementById('tgt_lines').value = t.target_lines || 300;
    document.getElementById('tgt_baseline').value = t.baseline_info || '';
    document.getElementById('tgt_info').value = t.target_info || '';
    document.getElementById('targetModal').classList.add('active');
}
</script>
