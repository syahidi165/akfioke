<?php
// views/alumni/walas_rekomendasi.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$totalSantri = count($students);
$totalRekomendasi = count($savedRecommendations);
$belumRekomendasi = max(0, $totalSantri - $totalRekomendasi);
$favoriteCount = count(array_filter($savedRecommendations, fn($r) => !empty($r['is_favorite'])));
$favoritePct = $totalRekomendasi > 0 ? round(($favoriteCount / $totalRekomendasi) * 100, 1) : 0;
?>

<!-- Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fa-solid fa-graduation-cap" style="color: #0d9488; margin-right: 8px;"></i>
            Rekomendasi Studi Lanjut Santri Tingkat Akhir
        </h1>
        <div class="page-subtitle">
            Bimbingan dan arahan kelanjutan sekolah/madrasah/perguruan tinggi bagi santri tingkat akhir untuk pelacakan jejak alumni.
        </div>
    </div>
    <div>
        <a href="<?= $baseUrl ?>/alumni" class="btn btn-outline">
            <i class="fa-solid fa-user-graduate"></i> Database Alumni Unit
        </a>
    </div>
</div>

<?php if ($isWalas && !$isFinalGrade): ?>
    <div class="card" style="text-align: center; padding: 48px 24px; border-top: 4px solid #f59e0b; margin-bottom: 24px;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #fef3c7; color: #b45309; display: inline-flex; align-items: center; justify-content: center; font-size: 30px; margin-bottom: 16px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
            Fitur Khusus Wali Kelas Tingkat Akhir
        </h3>
        <p style="color: var(--text-muted); font-size: 13.5px; max-width: 580px; margin: 0 auto; line-height: 1.6;">
            Akun Anda tercatat sebagai Wali Kelas <strong><?= SecurityHelper::escape($myClass['name'] ?? 'Bukan Kelas Akhir') ?></strong>. Menu bimbingan dan rekomendasi studi lanjut alumni difokuskan khusus bagi wali kelas pada jenjang tingkat akhir (<strong>TK B, SD Kelas 6, SMP Kelas 9, dan SMA Kelas 12</strong>).
        </p>
    </div>
<?php else: ?>
    <?php if (!empty($myClass)): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
            <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 18px;"></i>
            <div style="font-size: 13px; color: #047857;">
                Anda terverifikasi sebagai Wali Kelas Tingkat Akhir: <strong><?= SecurityHelper::escape($myClass['name']) ?></strong>. Data santri di bawah ini disesuaikan khusus untuk rombel yang Anda ampu.
            </div>
        </div>
    <?php endif; ?>

<!-- Stats Grid -->
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div class="stat-value"><?= $totalSantri ?></div>
            <div class="stat-label">Santri Tingkat Akhir</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-file-circle-check"></i>
        </div>
        <div>
            <div class="stat-value"><?= $totalRekomendasi ?></div>
            <div class="stat-label">Sudah Direkomendasikan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <div>
            <div class="stat-value"><?= $belumRekomendasi ?></div>
            <div class="stat-label">Menunggu Rekomendasi</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon rose">
            <i class="fa-solid fa-star"></i>
        </div>
        <div>
            <div class="stat-value"><?= $favoriteCount ?> <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">(<?= $favoritePct ?>%)</span></div>
            <div class="stat-label">Lembaga / Kampus Favorit</div>
        </div>
    </div>
</div>

<!-- Main 2-Column Content -->
<div style="display: grid; grid-template-columns: 1.05fr 1.35fr; gap: 24px; align-items: start;">

    <!-- Left Column: Form Rekomendasi -->
    <div class="card" id="formCard" style="border-top: 4px solid #0d9488;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-pen-nib" style="color: #0d9488; margin-right: 8px;"></i>
                Form Rekomendasi Studi Lanjut
            </div>
            <span class="badge badge-secondary" style="font-size: 11px;">Wali Kelas</span>
        </div>
        <div class="card-body">
            <form action="<?= $baseUrl ?>/walas/rekomendasi/submit" method="POST" id="rekomendasiForm">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">
                        Pilih Santri Tingkat Akhir <span style="color: var(--danger);">*</span>
                    </label>
                    <select name="student_id" id="selectStudent" class="form-control" required style="font-weight: 600; height: 42px;" onchange="onStudentSelect(this)">
                        <option value="">-- Pilih Santri Tingkat Akhir --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>" 
                                    data-nis="<?= SecurityHelper::escape($st['nis']) ?>"
                                    data-class="<?= SecurityHelper::escape($st['class_name']) ?>"
                                    data-dest="<?= SecurityHelper::escape($st['destination_name'] ?? '') ?>"
                                    data-type="<?= SecurityHelper::escape($st['destination_type'] ?? 'Sekolah') ?>"
                                    data-fav="<?= !empty($st['is_favorite']) ? 1 : 0 ?>"
                                    data-major="<?= SecurityHelper::escape($st['major'] ?? '') ?>"
                                    data-notes="<?= SecurityHelper::escape($st['alumni_notes'] ?? '') ?>">
                                <?= SecurityHelper::escape($st['nama']) ?> &bull; <?= SecurityHelper::escape($st['class_name']) ?> (NIS: <?= SecurityHelper::escape($st['nis']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        <i class="fa-solid fa-circle-check" style="color: #10b981;"></i> Hanya santri aktif tingkat akhir (Kelas 6 SD / Kelas 9 SMP / Kelas 12 SMA / TK B) yang tampil pada daftar ini.
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">
                        Jenis Tingkat Lanjutan <span style="color: var(--danger);">*</span>
                    </label>
                    <select name="destination_type" id="inputDestType" class="form-control" required style="height: 40px;">
                        <option value="Sekolah">Sekolah / Madrasah / Boarding School / Pesantren</option>
                        <option value="Universitas">Perguruan Tinggi / Universitas (PTN / PTS / Luar Negeri)</option>
                        <option value="Kerja/Lainnya">Wirausaha / Bekerja / Khidmat Lainnya</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">
                        Nama Lembaga / Sekolah / Kampus Tujuan <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="text" name="destination_name" id="inputDestName" class="form-control" 
                           placeholder="Contoh: SMP Islam Al Kahfi Batam / SMA Kharisma Bangsa / Univ. Al-Azhar Kairo..." required style="height: 42px; font-weight: 600;">
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">
                        Jurusan / Peminatan / Program (Opsional)
                    </label>
                    <input type="text" name="major" id="inputMajor" class="form-control" 
                           placeholder="Contoh: Tahfidz Al-Qur'an / MIPA / Kedokteran / Syariah Islamiyyah..." style="height: 40px;">
                </div>

                <!-- Checkbox Favorit -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 14px; margin-bottom: 18px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0; font-weight: 600; font-size: 13px; color: var(--text-main);">
                        <input type="checkbox" name="is_favorite" id="inputIsFav" value="1" style="width: 18px; height: 18px; accent-color: #0d9488;">
                        <span>
                            Tergolong Sekolah / Kampus Favorit / Prestisius
                            <div style="font-size: 11px; font-weight: normal; color: var(--text-muted); margin-top: 2px;">
                                Centang jika lembaga tujuan termasuk kategori sekolah/kampus unggulan (mempengaruhi indikator mutu AKFI Kepala Sekolah).
                            </div>
                        </span>
                    </label>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">
                        Catatan & Pertimbangan Wali Kelas
                    </label>
                    <textarea name="notes" id="inputNotes" rows="3" class="form-control" 
                              placeholder="Pertimbangan potensi santri, minat bakat, daya hafal tahfidz, atau capaian akademik..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; height: 44px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Rekomendasi Studi Lanjut
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Table Rekap Rekomendasi Tersimpan -->
    <div class="card" style="border-top: 4px solid #1e3a8a;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-list-check" style="color: var(--primary); margin-right: 8px;"></i>
                Rekap Rekomendasi Tersimpan
            </div>
            <span class="badge badge-secondary" style="font-size: 11px;">
                <?= count($savedRecommendations) ?> Rekomendasi
            </span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th>Santri & Kelas</th>
                            <th>Tujuan Rekomendasi</th>
                            <th style="text-align: center;">Kategori</th>
                            <th style="text-align: center; width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($savedRecommendations)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                    <i class="fa-solid fa-user-graduate" style="font-size: 32px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                                    Belum ada rekomendasi studi lanjut santri tingkat akhir yang disimpan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($savedRecommendations as $idx => $rec): ?>
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted); font-size: 12px;"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-main);">
                                            <?= SecurityHelper::escape($rec['student_name']) ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            <?= SecurityHelper::escape($rec['class_name'] ?? 'Tingkat Akhir') ?> &bull; NIS: <?= SecurityHelper::escape($rec['nis']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #0f766e;">
                                            <?= SecurityHelper::escape($rec['destination_name']) ?>
                                        </div>
                                        <?php if (!empty($rec['major'])): ?>
                                            <div style="font-size: 11px; color: var(--text-muted);">
                                                Jurusan: <strong><?= SecurityHelper::escape($rec['major']) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($rec['notes'])): ?>
                                            <div style="font-size: 11px; color: var(--text-muted); font-style: italic; margin-top: 2px;">
                                                "<?= SecurityHelper::escape($rec['notes']) ?>"
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($rec['is_favorite'])): ?>
                                            <span class="badge badge-success" style="font-size: 10px;">
                                                <i class="fa-solid fa-star"></i> Favorit
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary" style="font-size: 10px;">
                                                <?= SecurityHelper::escape($rec['destination_type']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 4px; justify-content: center;">
                                            <button type="button" class="btn btn-outline btn-sm" 
                                                    onclick="editRec(<?= $rec['student_id'] ?>, '<?= SecurityHelper::escape($rec['destination_type']) ?>', '<?= SecurityHelper::escape($rec['destination_name']) ?>', '<?= SecurityHelper::escape($rec['major'] ?? '') ?>', <?= !empty($rec['is_favorite']) ? 1 : 0 ?>, '<?= SecurityHelper::escape($rec['notes'] ?? '') ?>')"
                                                    title="Edit rekomendasi"
                                                    style="padding: 4px 8px; font-size: 11px; color: #0d9488; border-color: rgba(13, 148, 136, 0.4);">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <form action="<?= $baseUrl ?>/walas/rekomendasi/delete" method="POST" onsubmit="return confirm('Hapus rekomendasi santri ini?')" style="display: inline;">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="tracking_id" value="<?= $rec['id'] ?>">
                                                <button type="submit" class="btn btn-outline btn-sm" title="Hapus rekomendasi" style="padding: 4px 8px; font-size: 11px; color: var(--danger); border-color: rgba(239, 68, 68, 0.4);">
                                                    <i class="fa-solid fa-trash-can"></i>
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
<?php endif; ?>

<script>
function onStudentSelect(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        var dest = opt.getAttribute('data-dest') || '';
        var type = opt.getAttribute('data-type') || 'Sekolah';
        var fav = opt.getAttribute('data-fav') === '1';
        var major = opt.getAttribute('data-major') || '';
        var notes = opt.getAttribute('data-notes') || '';

        document.getElementById('inputDestName').value = dest;
        document.getElementById('inputDestType').value = type;
        document.getElementById('inputIsFav').checked = fav;
        document.getElementById('inputMajor').value = major;
        document.getElementById('inputNotes').value = notes;
    }
}

function editRec(studentId, destType, destName, major, isFav, notes) {
    var sel = document.getElementById('selectStudent');
    sel.value = studentId;
    document.getElementById('inputDestType').value = destType;
    document.getElementById('inputDestName').value = destName;
    document.getElementById('inputMajor').value = major;
    document.getElementById('inputIsFav').checked = (isFav === 1);
    document.getElementById('inputNotes').value = notes;

    var card = document.getElementById('formCard');
    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    card.style.transition = 'box-shadow 0.3s ease';
    card.style.boxShadow = '0 0 0 3px rgba(13, 148, 136, 0.25)';
    setTimeout(function() { card.style.boxShadow = ''; }, 1200);
    document.getElementById('inputDestName').focus();
}
</script>
