<?php
// views/deputi/sdm_supervisi.php
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
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Area 3 &amp; Area 6: SDM, 8 SNP &amp; Stakeholders</span>
            </div>
            <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading, #1e293b); margin: 0;">
                <i class="fa-solid fa-users-gear" style="color: #ea580c; margin-right: 8px;"></i>
                Pengawasan Mutu SDM, Supervisi 8 SNP &amp; Reward Guru
            </h1>
            <p style="margin: 4px 0 0 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Monitoring performa Kepala Sekolah, guru, hasil supervisi 8 SNP, dan pemberian lencana penghargaan karyawan berprestasi.
            </p>
        </div>

        <button class="btn btn-primary" onclick="openAwardBadgeModal()" style="font-weight: 600; background: #ea580c; border-color: #ea580c;">
            <i class="fa-solid fa-award"></i> Berikan Reward / Badge Guru (SOP 8.2.5.5)
        </button>
    </div>
</div>

<?php if (!empty($deputiList) && count($deputiList) > 1 && Auth::hasAnyRole(['direktur_pendidikan', 'operator_yayasan'])): ?>
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 14px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <span style="font-size: 0.875rem; font-weight: 600; color: #1e3a8a;">
            <i class="fa-solid fa-user-tie" style="margin-right: 6px;"></i> Wilayah Binaan Deputi:
        </span>
        <select onchange="window.location.href='<?= $baseUrl ?>/deputi/sdm-supervisi?deputi_id=' + this.value" class="form-control" style="width: auto; min-width: 260px; font-weight: 600;">
            <?php foreach ($deputiList as $dl): ?>
                <option value="<?= $dl['id'] ?>" <?= $dl['id'] == $deputiUserId ? 'selected' : '' ?>>
                    <?= SecurityHelper::escape($dl['full_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<?php endif; ?>

<!-- Stat Highlights -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-radius: 12px; border-left: 4px solid #3b82f6;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Kepala Sekolah Binaan</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-top: 4px;"><?= count($akfiKs) ?> Orang</div>
        <div style="font-size: 0.75rem; color: #0284c7; margin-top: 2px;">Tersinkronisasi penilaian sikap</div>
    </div>
    <div class="card" style="padding: 16px 20px; border-radius: 12px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Total Guru Unit Binaan</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-top: 4px;"><?= $totalGuru ?> Guru</div>
        <div style="font-size: 0.75rem; color: #047857; margin-top: 2px;">Terdaftar di siklus PKG</div>
    </div>
    <div class="card" style="padding: 16px 20px; border-radius: 12px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Reward Best Performance</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-top: 4px;"><?= count($badges) ?> Pegawai</div>
        <div style="font-size: 0.75rem; color: #b45309; margin-top: 2px;">Penerima lencana penghargaan</div>
    </div>
    <div class="card" style="padding: 16px 20px; border-radius: 12px; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Sesi Supervisi 8 SNP</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-top: 4px;"><?= count($supervisiList) ?> Unit</div>
        <div style="font-size: 0.75rem; color: #6d28d9; margin-top: 2px;">Ketercapaian akreditasi</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
    <!-- Box 1: Reward Best Performance Badge -->
    <div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); padding: 20px;">
        <h3 style="margin: 0 0 14px 0; font-size: 1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-medal" style="color: #f59e0b;"></i> Daftar Guru &amp; Tenaga Pendidik Terbaik (Reward Badge)
        </h3>
        <?php if (empty($badges)): ?>
            <p style="font-size: 0.8125rem; color: var(--text-muted); font-style: italic;">Belum ada lencana penghargaan yang disematkan untuk tahun ajaran ini.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php foreach ($badges as $b): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-award" style="font-size: 1.5rem; color: #d97706;"></i>
                            <div>
                                <div style="font-weight: 700; font-size: 0.875rem; color: #78350f;">
                                    <?= SecurityHelper::escape($b['emp_nama']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #92400e;">
                                    <?= SecurityHelper::escape($b['title']) ?> &bull; <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 10px;"><?= SecurityHelper::escape($b['unit_name']) ?></span>
                                </div>
                            </div>
                        </div>
                        <form action="<?= $baseUrl ?>/deputi/badges/delete" method="POST" onsubmit="return confirm('Hapus badge ini?')">
                            <?= CSRF::inputField() ?>
                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                            <button type="submit" class="btn btn-outline" style="padding: 2px 6px; font-size: 11px; color: #dc2626; border-color: #fca5a5;">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Box 2: Supervisi 8 SNP & Unit Tertinggi -->
    <div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); padding: 20px;">
        <h3 style="margin: 0 0 14px 0; font-size: 1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-chart-simple" style="color: #8b5cf6;"></i> Ketercapaian 8 Standar Nasional Pendidikan (SNP)
        </h3>
        <?php if (empty($supervisiList)): ?>
            <p style="font-size: 0.8125rem; color: var(--text-muted); font-style: italic;">Belum ada sesi supervisi 8 SNP yang terekam pada unit binaan.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <?php foreach ($supervisiList as $idx => $s): ?>
                    <?php 
                        $score = (float)($s['avg_snp_score'] ?? 80);
                        $isTop = $idx === 0;
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; background: <?= $isTop ? '#f5f3ff' : '#f8fafc' ?>; border: 1px solid <?= $isTop ? '#ddd6fe' : '#e2e8f0' ?>; border-radius: 8px; padding: 8px 12px;">
                        <div>
                            <div style="font-weight: 700; font-size: 0.875rem; color: #1e293b;">
                                <?= SecurityHelper::escape($s['unit_name']) ?>
                                <?php if ($isTop): ?>
                                    <span class="badge" style="background: #8b5cf6; color: #ffffff; font-size: 10px; padding: 2px 6px; margin-left: 4px;">Tertinggi</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                Tanggal: <?= date('d M Y', strtotime($s['session_date'] ?? date('Y-m-d'))) ?>
                            </div>
                        </div>
                        <div style="font-weight: 800; font-size: 1rem; color: #7c3aed;">
                            <?= number_format($score, 1) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Box 3: Rekap Kepuasan Stakeholders (Orang Tua, Guru, Siswa) -->
<div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); padding: 20px; margin-bottom: 24px;">
    <h3 style="margin: 0 0 14px 0; font-size: 1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-comments" style="color: #0284c7;"></i> Rekap Indeks Kepuasan Stakeholders (SOP 8.11)
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
        <?php if (empty($surveyStats)): ?>
            <div style="grid-column: 1 / -1; font-size: 0.8125rem; color: var(--text-muted); font-style: italic;">Belum ada hasil survei tercatat untuk unit binaan ini.</div>
        <?php else: ?>
            <?php foreach ($surveyStats as $surv): ?>
                <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 12px;">
                    <div style="font-weight: 700; font-size: 0.875rem; color: #0369a1;">
                        <?= SecurityHelper::escape($surv['target_respondent']) ?> &bull; <?= SecurityHelper::escape($surv['unit_name']) ?>
                    </div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: #0284c7; margin-top: 4px;">
                        <?= number_format((float)$surv['avg_satisfaction'], 1) ?>%
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                        Dari <?= (int)$surv['total_responses'] ?> responden
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Box 4: Rekap Ketepatan Pengumpulan Laporan Bulanan KS (tgl 28) -->
<div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); padding: 20px; margin-bottom: 24px;">
    <h3 style="margin: 0 0 14px 0; font-size: 1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-clock" style="color: #059669;"></i> Riwayat Pengumpulan Laporan Bulanan Unit Pendidikan (SOP 8.8.1 - Target Tgl 28)
    </h3>
    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
            <thead>
                <tr style="background: #f8fafc; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 8px 12px;">Unit Sekolah</th>
                    <th style="padding: 8px 12px;">Jenis Laporan</th>
                    <th style="padding: 8px 12px;">Bulan</th>
                    <th style="padding: 8px 12px;">Tanggal Upload</th>
                    <th style="padding: 8px 12px; text-align: center;">Ketepatan Waktu</th>
                    <th style="padding: 8px 12px; text-align: center;">Status Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($monthlyUploads)): ?>
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center; color: var(--text-muted); font-style: italic;">
                            Belum ada riwayat unggahan laporan administrasi bulanan dari Kepala Sekolah binaan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($monthlyUploads as $upl): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 8px 12px; font-weight: 600; color: #1e293b;">
                                <?= SecurityHelper::escape($upl['unit_name']) ?>
                            </td>
                            <td style="padding: 8px 12px;">
                                <?= SecurityHelper::escape($upl['nama_administrasi'] ?: ($upl['sub_title'] ?: 'Laporan Bulanan')) ?>
                            </td>
                            <td style="padding: 8px 12px; color: #475569;">
                                Bulan ke-<?= (int)$upl['month_num'] ?>
                            </td>
                            <td style="padding: 8px 12px; color: #64748b;">
                                <?= date('d M Y H:i', strtotime($upl['uploaded_at'])) ?>
                            </td>
                            <td style="padding: 8px 12px; text-align: center;">
                                <?php if (!empty($upl['is_on_time'])): ?>
                                    <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 11px;">
                                        <i class="fa-solid fa-check"></i> Tepat Waktu (&le; Tgl 28)
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: #fee2e2; color: #b91c1c; font-size: 11px;">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Terlambat
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 8px 12px; text-align: center;">
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px;">
                                    <?= SecurityHelper::escape($upl['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Berikan Badge -->
<div class="modal-overlay" id="awardBadgeModal">
    <div class="modal-container" style="max-width: 500px;">
        <form action="<?= $baseUrl ?>/deputi/badges/award" method="POST">
            <?= CSRF::inputField() ?>
            <div class="modal-header">
                <h3 class="card-title" style="margin: 0; font-size: 1.125rem;">
                    <i class="fa-solid fa-medal" style="color: #ea580c;"></i> Berikan Lencana Reward Tenaga Pendidik
                </h3>
            </div>
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Pilih Guru / Karyawan *</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">-- Pilih Pegawai Unit Binaan --</option>
                        <?php foreach ($candidateEmployees as $ce): ?>
                            <option value="<?= $ce['id'] ?>">
                                <?= SecurityHelper::escape($ce['nama']) ?> (<?= SecurityHelper::escape($ce['unit_name']) ?> - <?= SecurityHelper::escape($ce['level_posisi']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Judul Penghargaan (SOP 8.2.5.5) *</label>
                    <input type="text" name="title" class="form-control" value="Best Performance Guru Teladan" required>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Tanggal Penetapan</label>
                    <input type="date" name="awarded_at" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Catatan / Alasan Penghargaan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Dedikasi tinggi, kedisiplinan dan capaian target KBM..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeAwardBadgeModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: #ea580c; border-color: #ea580c;"><i class="fa-solid fa-award"></i> Sematkan Badge</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAwardBadgeModal() {
    document.getElementById('awardBadgeModal').classList.add('active');
}
function closeAwardBadgeModal() {
    document.getElementById('awardBadgeModal').classList.remove('active');
}
</script>

