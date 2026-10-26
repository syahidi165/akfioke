<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
$totalPrestasi = count($list ?? []);
$tingkatSatuan = 0;
$tingkatYayasan = 0;
$tingkatNasional = 0;

foreach ($list as $item) {
    $lvl = $item['level'] ?? '';
    if (strpos($lvl, 'Satuan') !== false) {
        $tingkatSatuan++;
    } elseif (strpos($lvl, 'Yayasan') !== false) {
        $tingkatYayasan++;
    } else {
        $tingkatNasional++;
    }
}
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Dokumen No. 10</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Administrasi Kepala Sekolah</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-award" style="color: #f59e0b;"></i> Penetapan Guru &amp; Tenaga Pendidik Berprestasi
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pemberian penghargaan dan penetapan resmi guru berprestasi atas dedikasi, unggah sertifikat bukti prestasi, serta monitoring status validasi.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Terpilih</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalPrestasi ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Guru</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Ditetapkan aktif</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Tingkat Satuan Unit</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0d9488; margin: 6px 0 2px 0;">
            <?= $tingkatSatuan ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Guru</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0d9488; font-weight: 500;">Apresiasi internal unit</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Tingkat Yayasan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $tingkatYayasan ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Guru</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Penghargaan Al Kahfi</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Kota / Nasional</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #8b5cf6; margin: 6px 0 2px 0;">
            <?= $tingkatNasional ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Guru</span>
        </div>
        <div style="font-size: 0.8125rem; color: #8b5cf6; font-weight: 500;">Prestasi eksternal</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">
    <!-- Form Card -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-plus" style="color: #f59e0b;"></i> Form Tetapkan Guru Berprestasi
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= $baseUrl ?>/guru/prestasi-tetapkan/save" method="POST" enctype="multipart/form-data">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Pilih Guru <span style="color: #ef4444;">*</span></label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">-- Pilih Guru / Tenaga Pendidik --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= SecurityHelper::escape($t['nama']) ?> (<?= SecurityHelper::escape($t['position']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Kategori / Nama Penghargaan <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Guru Teladan Pembelajaran Inovatif" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tingkat</label>
                        <select name="level" class="form-control">
                            <option value="Satuan Unit">Satuan Unit</option>
                            <option value="Tingkat Yayasan">Tingkat Yayasan</option>
                            <option value="Kota / Nasional">Kota / Nasional</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tahun</label>
                        <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" style="text-align: center;" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Status Penetapan</label>
                    <select name="status" class="form-control">
                        <option value="Terverifikasi">Terverifikasi</option>
                        <option value="Dalam Proses">Dalam Proses</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Unggah Bukti / Sertifikat (PDF/JPG/PNG)</label>
                    <input type="file" name="certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <small style="color: var(--text-muted, #64748b); font-size: 0.75rem;">Maksimal 5MB. Akan tampil di dashboard guru.</small>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Keterangan / Alasan Penetapan</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="Apresiasi atas dedikasi dan prestasi luar biasa..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Penetapan
                </button>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-trophy" style="color: #f59e0b;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Daftar Guru Berprestasi Terpilih</h3>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                    <input type="text" id="searchPrestasi" class="form-control" placeholder="Cari guru / penghargaan..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 220px;">
                </div>
                <span class="badge badge-secondary"><?= $totalPrestasi ?> Guru</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th>Guru / Tenaga Pendidik</th>
                            <th>Kategori Penghargaan</th>
                            <th style="width: 130px; text-align: center;">Tingkat</th>
                            <th style="width: 110px; text-align: center;">Status</th>
                            <th style="width: 90px; text-align: center;">Bukti</th>
                            <th style="width: 80px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($list)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-medal" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada penetapan guru berprestasi untuk tahun ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($list as $idx => $item): 
                                $lvl = $item['level'] ?? '';
                                $badgeClass = 'badge-primary';
                                if (strpos($lvl, 'Yayasan') !== false) {
                                    $badgeClass = 'badge-info';
                                } elseif (strpos($lvl, 'Nasional') !== false || strpos($lvl, 'Kota') !== false) {
                                    $badgeClass = 'badge-warning';
                                }

                                $status = $item['status'] ?? 'Terverifikasi';
                                $statusBadge = match($status) {
                                    'Terverifikasi' => 'badge-success',
                                    'Dalam Proses'  => 'badge-warning',
                                    'Selesai'       => 'badge-primary',
                                    'Dibatalkan'    => 'badge-danger',
                                    default         => 'badge-secondary'
                                };
                            ?>
                                <tr class="prestasi-row" data-search="<?= strtolower(SecurityHelper::escape($item['teacher_name'] . ' ' . $item['title'] . ' ' . $item['level'] . ' ' . ($item['nipy'] ?? '') . ' ' . $status)) ?>">
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main, #0f172a); font-size: 0.9375rem;">
                                            <?= SecurityHelper::escape($item['teacher_name']) ?>
                                        </div>
                                        <small style="color: var(--text-muted, #64748b); font-family: monospace;">NIPY: <?= SecurityHelper::escape($item['nipy'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <strong style="color: #0d9488;"><?= SecurityHelper::escape($item['title']) ?></strong>
                                        <span style="font-size: 0.75rem; color: #64748b; margin-left: 6px;">(<?= SecurityHelper::escape($item['year']) ?>)</span>
                                        <?php if (!empty($item['notes'])): ?>
                                            <div style="font-size: 0.75rem; color: var(--text-muted, #64748b); margin-top: 2px;">
                                                <?= SecurityHelper::escape($item['notes']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $badgeClass ?>"><?= SecurityHelper::escape($item['level']) ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $statusBadge ?>"><?= SecurityHelper::escape($status) ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($item['proof_document'])): ?>
                                            <a href="<?= $baseUrl ?>/<?= SecurityHelper::escape($item['proof_document']) ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.75rem; color: #0d9488;" title="Lihat Berkas Sertifikat">
                                                <i class="fa-solid fa-file-shield"></i> Bukti
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 0.75rem;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn btn-outline btn-sm btn-edit-prestasi" data-id="<?= $item['id'] ?>" style="padding: 3px 8px; font-size: 0.75rem;">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
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

<!-- Modal Edit Status & Bukti Sertifikat Guru Berprestasi -->
<div class="modal-overlay" id="editPrestasiModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="modal-container" style="background: #fff; width: 100%; max-width: 540px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden;">
        <form action="<?= $baseUrl ?>/guru/prestasi-tetapkan/update" method="POST" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <input type="hidden" name="id" id="editPrestasiId">

            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <div>
                    <h3 class="modal-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-pen-to-square" style="color: #f59e0b;"></i> Edit Status &amp; Berkas Prestasi
                    </h3>
                    <div id="editPrestasiTeacherName" style="font-size: 0.8125rem; color: #64748b; margin-top: 2px;"></div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" style="border: none; font-size: 1.25rem; line-height: 1; padding: 4px 8px;" onclick="closeEditPrestasiModal()">&times;</button>
            </div>

            <div class="modal-body" style="padding: 20px; max-height: 65vh; overflow-y: auto;">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Kategori / Nama Penghargaan <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="title" id="editTitle" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tingkat</label>
                        <select name="level" id="editLevel" class="form-control">
                            <option value="Satuan Unit">Satuan Unit</option>
                            <option value="Tingkat Yayasan">Tingkat Yayasan</option>
                            <option value="Kota / Nasional">Kota / Nasional</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tahun</label>
                        <input type="number" name="year" id="editYear" class="form-control" style="text-align: center;" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Status Prestasi <span style="color: #ef4444;">*</span></label>
                    <select name="status" id="editStatus" class="form-control" required>
                        <option value="Terverifikasi">Terverifikasi</option>
                        <option value="Dalam Proses">Dalam Proses</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Unggah / Ganti Berkas Sertifikat (PDF/Gambar)</label>
                    <input type="file" name="certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <div id="currentCertPreview" style="font-size: 0.75rem; color: #0d9488; margin-top: 4px;"></div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Keterangan / Alasan</label>
                    <textarea name="notes" id="editNotes" rows="3" class="form-control"></textarea>
                </div>
            </div>

            <div class="modal-footer" style="padding: 14px 20px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 8px; background: #f8fafc;">
                <button type="button" class="btn btn-outline" onclick="closeEditPrestasiModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JSON Data Guru Berprestasi -->
<script id="prestasiDataJson" type="application/json">
<?= json_encode($list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>

<style>
#editPrestasiModal.active,
#editPrestasiModal[style*="display: flex"],
#editPrestasiModal[style*="display:flex"] {
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    z-index: 99999 !important;
}
</style>

<script>
let prestasiMap = {};
try {
    const rawPrestasi = document.getElementById('prestasiDataJson');
    if (rawPrestasi) {
        const arr = JSON.parse(rawPrestasi.textContent);
        if (Array.isArray(arr)) {
            arr.forEach(function(it) {
                prestasiMap[it.id] = it;
            });
        }
    }
} catch (e) {
    console.error('Error parsing prestasi JSON:', e);
}

document.addEventListener('DOMContentLoaded', function() {
    const search = document.getElementById('searchPrestasi');
    const rows = document.querySelectorAll('.prestasi-row');
    if (search) {
        search.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            rows.forEach(function(row) {
                const text = row.getAttribute('data-search') || '';
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    document.querySelectorAll('.btn-edit-prestasi').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const item = prestasiMap[id];
            if (item) {
                openEditPrestasiModal(item);
            }
        });
    });

    const editModal = document.getElementById('editPrestasiModal');
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditPrestasiModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditPrestasiModal();
        }
    });
});

function openEditPrestasiModal(item) {
    const modal = document.getElementById('editPrestasiModal');
    if (!modal) return;
    document.getElementById('editPrestasiId').value = item.id;
    document.getElementById('editPrestasiTeacherName').innerText = 'Guru: ' + (item.teacher_name || '') + ' (NIPY: ' + (item.nipy || '-') + ')';
    document.getElementById('editTitle').value = item.title || '';
    document.getElementById('editLevel').value = item.level || 'Satuan Unit';
    document.getElementById('editYear').value = item.year || new Date().getFullYear();
    document.getElementById('editStatus').value = item.status || 'Terverifikasi';
    document.getElementById('editNotes').value = item.notes || '';

    const preview = document.getElementById('currentCertPreview');
    if (item.proof_document) {
        preview.innerHTML = '<i class="fa-solid fa-paperclip"></i> Berkas tersimpan: <a href="' + '<?= $baseUrl ?>/' + item.proof_document + '" target="_blank" style="text-decoration: underline;">Lihat Bukti Saat Ini</a>';
    } else {
        preview.innerText = 'Belum ada berkas sertifikat yang diunggah.';
    }

    modal.classList.add('active');
    modal.style.display = 'flex';
    modal.style.opacity = '1';
    modal.style.visibility = 'visible';
}

function closeEditPrestasiModal() {
    const modal = document.getElementById('editPrestasiModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        modal.style.opacity = '0';
        modal.style.visibility = 'hidden';
    }
}
</script>
