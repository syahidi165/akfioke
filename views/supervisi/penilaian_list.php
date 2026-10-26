<?php
// views/supervisi/penilaian_list.php
use Core\SecurityHelper;
use Core\Auth;
?>

<div class="page-header" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-clipboard-check" style="color: var(--primary);"></i>
                Penilaian Instrumen Supervisi Kelas
            </h1>
            <div class="page-subtitle">
                Daftar sesi observasi klinis pembelajaran guru yang dijadwalkan untuk dievaluasi instrumennya.
            </div>
        </div>
        <div>
            <span class="badge badge-info" style="font-size: 12px; padding: 6px 12px;">
                <i class="fa-solid fa-calendar-check" style="margin-right: 5px;"></i> <?= count($sessions) ?> Sesi Terjadwal
            </span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-list" style="color: var(--primary); margin-right: 6px;"></i> Sesi Supervisi Mengajar</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th>Guru yang Disupervisi</th>
                        <th>Mata Pelajaran</th>
                        <th style="width: 110px;">Kelas</th>
                        <th>Supervisor / Penilai</th>
                        <th style="width: 140px;">Tgl Pelaksanaan</th>
                        <th style="width: 90px; text-align: center;">Skor Akhir</th>
                        <th style="width: 140px; text-align: center;">Predikat</th>
                        <th style="width: 160px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                <i class="fa-solid fa-calendar-xmark" style="font-size: 32px; color: #cbd5e1; margin-bottom: 8px; display: block;"></i>
                                Belum ada sesi supervisi pembelajaran yang ditugaskan kepada Anda pada tahun ajaran ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $idx => $s): ?>
                            <tr>
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                <td>
                                    <strong style="color: var(--text-main);"><?= SecurityHelper::escape($s['teacher_name']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;">NIPY: <?= SecurityHelper::escape($s['teacher_nipy']) ?></div>
                                </td>
                                <td><strong><?= SecurityHelper::escape($s['subject_name']) ?></strong></td>
                                <td><span class="badge badge-secondary"><?= SecurityHelper::escape($s['class_name']) ?></span></td>
                                <td><?= SecurityHelper::escape($s['supervisor_name']) ?></td>
                                <td><?= SecurityHelper::formatTanggalIndo($s['session_date']) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($s['total_score'] > 0): ?>
                                        <strong style="font-size: 15px; color: <?= $s['total_score'] >= 86 ? '#15803d' : ($s['total_score'] >= 71 ? '#2563eb' : '#dc2626') ?>;">
                                            <?= number_format((float)$s['total_score'], 1) ?>
                                        </strong>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($s['total_score'] > 0): ?>
                                        <?php if ($s['achievement_predicate'] === 'SANGAT BAIK'): ?>
                                            <span class="badge badge-success"><i class="fa-solid fa-star"></i> SANGAT BAIK</span>
                                        <?php elseif ($s['achievement_predicate'] === 'BAIK'): ?>
                                            <span class="badge badge-info"><i class="fa-solid fa-check"></i> BAIK</span>
                                        <?php elseif ($s['achievement_predicate'] === 'CUKUP'): ?>
                                            <span class="badge badge-warning">CUKUP</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">PERLU PENINGKATAN</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" style="background: #f1f5f9; color: #64748b;">Belum Dinilai</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <a href="<?= Auth::baseUrl() ?>/supervisi/form/<?= $s['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-pen-to-square"></i> <?= $s['total_score'] > 0 ? 'Edit Nilai' : 'Nilai' ?>
                                        </a>
                                        <?php if ($s['total_score'] > 0): ?>
                                            <a href="<?= Auth::baseUrl() ?>/supervisi/print/<?= $s['id'] ?>" target="_blank" class="btn btn-outline btn-sm" title="Cetak PDF">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                        <?php endif; ?>
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
