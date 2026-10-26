<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-slate-50 font-semibold">
                <i class="fa fa-medal text-warning me-2"></i> Input Kejuaraan Lomba Resmi
            </div>
            <div class="card-body">
                <form action="<?= \Core\Auth::baseUrl() ?>/prestasi/panitia/save" method="POST" enctype="multipart/form-data">
                    <?= \Core\CSRF::field() ?>

                    <div class="mb-3">
                        <label class="form-label font-semibold">Santri Peraih Juara <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select" required>
                            <option value="">-- Pilih Santri --</option>
                            <?php foreach ($students as $st): ?>
                                <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['nama']) ?> (NIS: <?= htmlspecialchars($st['nis']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold">Kategori Lomba Resmi</label>
                        <select name="category" class="form-select" required>
                            <option value="OSN">OSN (Olimpiade Sains Nasional)</option>
                            <option value="O2SN">O2SN (Olimpiade Olahraga Siswa Nasional)</option>
                            <option value="FLS2N">FLS2N (Festival Lomba Seni Siswa Nasional)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold">Nama Lomba & Bidang <span class="text-danger">*</span></label>
                        <input type="text" name="event_name" class="form-control" placeholder="Contoh: OSN Matematika / Karate O2SN" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label font-semibold">Peringkat / Juara</label>
                            <select name="rank_position" class="form-select">
                                <option value="Juara 1">Juara 1</option>
                                <option value="Juara 2">Juara 2</option>
                                <option value="Juara 3">Juara 3</option>
                                <option value="Harapan 1">Harapan 1</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label font-semibold">Tingkat</label>
                            <select name="level" class="form-select">
                                <option value="Kota Batam">Kota Batam</option>
                                <option value="Provinsi Kepri">Provinsi Kepri</option>
                                <option value="Nasional">Nasional</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold">Unggah Sertifikat / Piagam (PDF/JPG)</label>
                        <input type="file" name="certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    <button type="submit" class="btn btn-warning w-100 font-bold">
                        <i class="fa fa-save me-1"></i> Simpan Prestasi Siswa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-trophy text-amber-500 me-2"></i> Rekapitulasi Prestasi Lomba Kedinasan</span>
                <span class="badge bg-secondary"><?= count($prestasi) ?> Prestasi</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">No</th>
                                <th>Santri</th>
                                <th>Kategori & Ajang</th>
                                <th>Juara & Tingkat</th>
                                <th>Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($prestasi)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data prestasi lomba kedinasan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($prestasi as $idx => $p): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($p['student_name']) ?></strong>
                                            <div class="small text-muted">NIS: <?= htmlspecialchars($p['nis']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary me-1"><?= $p['category'] ?></span>
                                            <?= htmlspecialchars($p['event_name']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?= htmlspecialchars($p['rank_position']) ?></span>
                                            <div class="small text-muted"><?= htmlspecialchars($p['level']) ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($p['certificate_path'])): ?>
                                                <span class="badge bg-light text-dark border"><i class="fa fa-file me-1"></i> Terlampir</span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
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
</div>
