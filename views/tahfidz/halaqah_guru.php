<?php
// views/tahfidz/halaqah_guru.php
use Core\SecurityHelper;
use Core\CSRF;
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Halaqah Guru & Tutor Sebaya</h1>
        <div class="page-subtitle">Bagi guru dan karyawan ke dalam kelompok halaqah pembinaan tahfidz Al-Qur'an mingguan.</div>
    </div>
</div>

<div class="grid-cols-3" style="align-items: start;">
    <!-- Form Buat Halaqah Baru -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 6px;"></i>
                Tambah Halaqah Guru Baru
            </h3>
        </div>
        <div class="card-body">
            <form action="<?= \Core\Auth::baseUrl() ?>/tahfidz/halaqah-guru/save" method="POST">
                <?= CSRF::inputField() ?>

                <div class="form-group">
                    <label class="form-label">Nama Halaqah Guru <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Halaqah Guru SD Al Kahfi A" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Tutor Sebaya / Pembina <span style="color: var(--danger);">*</span></label>
                    <select name="tutor_employee_id" class="form-control" required>
                        <option value="">-- Pilih Tutor Sebaya --</option>
                        <?php foreach ($tutors as $tutor): ?>
                            <option value="<?= $tutor['id'] ?>"><?= SecurityHelper::escape($tutor['nama']) ?> (<?= SecurityHelper::escape($tutor['level_posisi']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Pilih Anggota Guru / Karyawan</label>
                    <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; max-height: 220px; overflow-y: auto;">
                        <?php if (empty($teachers)): ?>
                            <div style="color: var(--text-muted); font-size: 12px;">Belum ada data guru terdaftar.</div>
                        <?php else: ?>
                            <?php foreach ($teachers as $tc): ?>
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 6px; cursor: pointer;">
                                    <input type="checkbox" name="teacher_ids[]" value="<?= $tc['id'] ?>">
                                    <span><?= SecurityHelper::escape($tc['nama']) ?> <small style="color: var(--text-muted);">(<?= SecurityHelper::escape($tc['level_posisi']) ?>)</small></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fa-solid fa-save"></i> Buat Halaqah Guru
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Halaqah Guru -->
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa-solid fa-users" style="color: var(--accent); margin-right: 6px;"></i>
                Daftar Halaqah Guru Aktif
            </h3>
            <span class="badge badge-info" style="font-size: 12px;"><?= count($halaqahs) ?> Halaqah</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 48px; text-align: center;">No</th>
                        <th>Nama Halaqah</th>
                        <th>Tutor Sebaya</th>
                        <th style="width: 130px; text-align: center;">Jumlah Guru</th>
                        <th style="width: 100px; text-align: center;">Tipe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($halaqahs)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 24px; color: var(--text-muted);">Belum ada kelompok halaqah guru yang dibuat.</td></tr>
                    <?php else: ?>
                        <?php foreach ($halaqahs as $idx => $hq): ?>
                            <tr>
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                <td><strong><?= SecurityHelper::escape($hq['name']) ?></strong></td>
                                <td>
                                    <i class="fa-solid fa-chalkboard-user" style="color: var(--primary); margin-right: 6px;"></i>
                                    <strong><?= SecurityHelper::escape($hq['tutor_name'] ?? 'Belum ditentukan') ?></strong>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-info font-semibold">
                                        <?= (int)$hq['member_count'] ?> Guru
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-secondary"><?= SecurityHelper::escape($hq['type']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
