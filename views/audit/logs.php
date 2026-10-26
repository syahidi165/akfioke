<div class="card mb-4">
    <div class="card-header bg-slate-50 font-semibold">
        <i class="fa fa-filter text-primary me-2"></i> Filter Log Audit Keamanan
    </div>
    <div class="card-body">
        <form method="GET" action="<?= \Core\Auth::baseUrl() ?>/audit/logs" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="action" class="form-control" placeholder="Cari Jenis Aksi (e.g. LOGIN, SAVE, UPDATE)..." value="<?= htmlspecialchars($actionFilter) ?>">
            </div>
            <div class="col-md-5">
                <input type="text" name="username" class="form-control" placeholder="Cari Nama Pengguna..." value="<?= htmlspecialchars($userFilter) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-shield-alt text-emerald-600 me-2"></i> Log Audit Aktivitas Sistem (Append-Only)</span>
        <span class="badge bg-secondary"><?= count($logs) ?> Entri Terakhir</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 font-mono small">
                <thead class="table-light">
                    <tr>
                        <th width="40">ID</th>
                        <th>Waktu (WIB)</th>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>IP Address</th>
                        <th>Aksi</th>
                        <th>Tabel</th>
                        <th>Perubahan Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat log audit.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td><?= $l['id'] ?></td>
                                <td><?= htmlspecialchars($l['created_at']) ?></td>
                                <td><strong><?= htmlspecialchars($l['username'] ?: 'Anonim') ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($l['role_name'] ?: 'System') ?></span></td>
                                <td><?= htmlspecialchars($l['ip_address']) ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($l['action']) ?></span></td>
                                <td><?= htmlspecialchars($l['table_name']) ?></td>
                                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php if (!empty($l['new_values'])): ?>
                                        <span class="text-success"><?= htmlspecialchars($l['new_values']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
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
