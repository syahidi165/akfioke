<?php
// views/layouts/main.php
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="app-main">
    <?php if (!empty($isImpersonating)): ?>
        <!-- Banner Mode Impersonasi / Alih Akun Operator Yayasan -->
        <div class="impersonation-topbar" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #ffffff; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 500; z-index: 1050; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); border-bottom: 2px solid rgba(255,255,255,0.2); position: sticky; top: 0;">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span style="background: rgba(255,255,255,0.2); padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-user-secret" style="color: #fde047;"></i> Mode Alih Akun
                </span>
                <span>
                    Operator Yayasan sedang mengakses sebagai: <strong><?= \Core\SecurityHelper::escape($currentUser['name'] ?? 'User') ?></strong> 
                    <span style="opacity: 0.85;">(@<?= \Core\SecurityHelper::escape($currentUser['username'] ?? '') ?> &bull; Peran: <?= \Core\SecurityHelper::escape($activeRole['display_name'] ?? '') ?>)</span>
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" class="btn btn-sm" onclick="openSwitchUserModal()" style="background: rgba(255,255,255,0.2); color: #ffffff; border: 1px solid rgba(255,255,255,0.3); font-weight: 600; padding: 5px 12px; font-size: 12px; border-radius: 6px; cursor: pointer;" title="Ganti ke pengguna lain">
                    <i class="fa-solid fa-people-arrows"></i> Ganti User
                </button>
                <form method="POST" action="<?= $baseUrl ?>/revert-impersonate" style="margin: 0; display: inline;">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn-sm" style="background: #f59e0b; color: #1e1b4b; border: none; font-weight: 700; padding: 5px 14px; font-size: 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.15); cursor: pointer;">
                        <i class="fa-solid fa-arrow-rotate-left"></i> Kembali ke Operator Yayasan
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php include __DIR__ . '/navbar.php'; ?>
    
    <main class="app-content">
        <!-- Flash Alert Messages -->
        <?php if (!empty($_SESSION['flash'])): ?>
            <?php 
                $flash = $_SESSION['flash'];
                unset($_SESSION['flash']);
                $alertClass = match($flash['type']) {
                    'success' => 'alert-success',
                    'error'   => 'alert-danger',
                    'warning' => 'alert-warning',
                    default   => 'alert-info'
                };
                $iconClass = match($flash['type']) {
                    'success' => 'fa-circle-check',
                    'error'   => 'fa-triangle-exclamation',
                    'warning' => 'fa-circle-exclamation',
                    default   => 'fa-circle-info'
                };
            ?>
            <div class="alert <?= $alertClass ?>">
                <i class="fa-solid <?= $iconClass ?>" style="font-size: 16px;"></i>
                <div><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <!-- Dynamic Page View Content -->
        <?= $content ?>
    </main>

    <?php if (!empty($isOperatorYayasanOrImpersonating)): ?>
        <!-- ======================================================== -->
        <!-- MODAL SWITCH USER (KHUSUS OPERATOR YAYASAN)             -->
        <!-- ======================================================== -->
        <div id="switchUserModal" class="modal-backdrop-custom" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
            <div class="modal-dialog-custom" style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 780px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; animation: modalPop 0.2s ease-out;">
                <!-- Header Modal -->
                <div style="padding: 18px 24px; background: linear-gradient(135deg, #0284c7 0%, #1e40af 100%); color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                            <i class="fa-solid fa-people-arrows"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #fff;">Alih Akun / Switch User</h3>
                            <div style="font-size: 12px; opacity: 0.9; margin-top: 2px;">
                                Masuk dan operasikan aplikasi sebagai pengguna lain yang terdaftar di sistem.
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="closeSwitchUserModal()" style="background: rgba(255,255,255,0.15); border: none; color: #fff; width: 32px; height: 32px; border-radius: 8px; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Search & Filters -->
                <div style="padding: 16px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0); background: #f8fafc;">
                    <div style="position: relative; margin-bottom: 12px;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"></i>
                        <input type="text" id="switchUserSearch" placeholder="Ketik nama pengguna, username, peranan/jabatan, atau nama unit sekolah..." style="width: 100%; padding: 10px 14px 10px 38px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 13px; outline: none; transition: all 0.15s;" onfocus="this.style.borderColor='#0284c7'; this.style.boxShadow='0 0 0 3px rgba(2,132,199,0.15)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'" oninput="filterSwitchUsers()">
                    </div>

                    <!-- Filter Kategori Cepat -->
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" class="btn-quick-cat active" onclick="filterSwitchCat('', this)">Semua (<?= count($switchableUsers ?? []) ?>)</button>
                        <button type="button" class="btn-quick-cat" onclick="filterSwitchCat('Direktur|Deputi|Yayasan', this)">Pimpinan / Deputi</button>
                        <button type="button" class="btn-quick-cat" onclick="filterSwitchCat('Kepala Sekolah', this)">Kepala Sekolah</button>
                        <button type="button" class="btn-quick-cat" onclick="filterSwitchCat('Guru|Walas', this)">Guru &amp; Walas</button>
                        <button type="button" class="btn-quick-cat" onclick="filterSwitchCat('Operator', this)">Operator</button>
                    </div>
                </div>

                <!-- List Users -->
                <div style="flex: 1; overflow-y: auto; padding: 12px 24px; max-height: calc(85vh - 220px);" id="switchUserList">
                    <?php if (empty($switchableUsers)): ?>
                        <div style="text-align: center; padding: 40px; color: #94a3b8;">
                            <i class="fa-solid fa-users-slash" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                            Tidak ada data pengguna yang tersedia.
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php foreach ($switchableUsers as $u): 
                                $isCurrent = ($currentUser && (int)$currentUser['id'] === (int)$u['id']);
                                $roleDisplay = $u['role_display'] ?? 'Pengguna';
                                $unitName = $u['unit_name'] ?? 'Yayasan';
                            ?>
                                <div class="switch-user-row" data-search="<?= strtolower(htmlspecialchars($u['full_name'] . ' ' . $u['username'] . ' ' . $roleDisplay . ' ' . $unitName, ENT_QUOTES, 'UTF-8')) ?>" data-role="<?= strtolower(htmlspecialchars($roleDisplay, ENT_QUOTES, 'UTF-8')) ?>" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px; background: <?= $isCurrent ? '#f0fdf4' : '#ffffff' ?>; transition: all 0.15s; gap: 12px;">
                                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                        <div style="width: 38px; height: 38px; border-radius: 50%; background: <?= $isCurrent ? '#16a34a' : '#e0f2fe' ?>; color: <?= $isCurrent ? '#ffffff' : '#0284c7' ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                            <?= strtoupper(substr($u['full_name'] ?: 'U', 0, 1)) ?>
                                        </div>
                                        <div style="min-width: 0;">
                                            <div style="font-weight: 700; font-size: 13px; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= \Core\SecurityHelper::escape($u['full_name']) ?>
                                                <?php if ($isCurrent): ?>
                                                    <span class="badge badge-success" style="font-size: 10px; margin-left: 4px; padding: 2px 6px;">Sedang Aktif</span>
                                                <?php endif; ?>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b; margin-top: 2px; flex-wrap: wrap;">
                                                <span style="font-family: monospace; color: #0284c7; font-weight: 600;">@<?= \Core\SecurityHelper::escape($u['username']) ?></span>
                                                <span>&bull;</span>
                                                <span class="badge badge-secondary" style="font-size: 10.5px; padding: 2px 6px;"><?= \Core\SecurityHelper::escape($roleDisplay) ?></span>
                                                <span>&bull;</span>
                                                <span style="color: #64748b;"><i class="fa-solid fa-school" style="font-size: 9.5px; margin-right: 2px;"></i><?= \Core\SecurityHelper::escape($unitName) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <?php if ($isCurrent): ?>
                                            <span style="font-size: 11px; font-weight: 600; color: #16a34a; padding: 6px 12px; background: #dcfce7; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-check"></i> Sesi Aktif
                                            </span>
                                        <?php else: ?>
                                            <form method="POST" action="<?= $baseUrl ?>/switch-user" style="margin: 0;" onsubmit="return confirm('Beralih masuk sebagai pengguna <?= \Core\SecurityHelper::escape($u['full_name']) ?> (@<?= \Core\SecurityHelper::escape($u['username']) ?>)?')">
                                                <?= $csrfField ?>
                                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-primary" style="padding: 6px 14px; font-size: 12px; font-weight: 600; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; cursor: pointer;">
                                                    <i class="fa-solid fa-right-to-bracket"></i> Masuk
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer Modal -->
                <div style="padding: 12px 24px; border-top: 1px solid var(--border-color, #e2e8f0); background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 11.5px; color: #64748b;">
                        <i class="fa-solid fa-shield-halved" style="color: #0284c7; margin-right: 4px;"></i> Hanya dapat diakses oleh hak istimewa <strong>Operator Yayasan</strong>.
                    </div>
                    <button type="button" onclick="closeSwitchUserModal()" class="btn btn-sm btn-outline" style="padding: 5px 14px; font-size: 12px; cursor: pointer;">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <style>
            @keyframes modalPop {
                from { transform: scale(0.95); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
            .btn-quick-cat {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                color: #475569;
                padding: 4px 10px;
                font-size: 11px;
                font-weight: 600;
                border-radius: 20px;
                cursor: pointer;
                transition: all 0.15s;
            }
            .btn-quick-cat:hover {
                border-color: #0284c7;
                color: #0284c7;
            }
            .btn-quick-cat.active {
                background: #0284c7;
                border-color: #0284c7;
                color: #ffffff;
            }
            .switch-user-row:hover {
                border-color: #38bdf8 !important;
                box-shadow: 0 2px 8px rgba(2, 132, 199, 0.08);
            }
        </style>

        <script>
            let currentCatFilter = '';

            function openSwitchUserModal() {
                const modal = document.getElementById('switchUserModal');
                if (modal) {
                    modal.style.display = 'flex';
                    const inp = document.getElementById('switchUserSearch');
                    if (inp) {
                        inp.value = '';
                        setTimeout(() => inp.focus(), 100);
                    }
                    filterSwitchUsers();
                }
            }

            function closeSwitchUserModal() {
                const modal = document.getElementById('switchUserModal');
                if (modal) {
                    modal.style.display = 'none';
                }
            }

            function filterSwitchCat(pattern, btn) {
                currentCatFilter = pattern.toLowerCase();
                document.querySelectorAll('.btn-quick-cat').forEach(b => b.classList.remove('active'));
                if (btn) btn.classList.add('active');
                filterSwitchUsers();
            }

            function filterSwitchUsers() {
                const query = (document.getElementById('switchUserSearch')?.value || '').toLowerCase().trim();
                const rows = document.querySelectorAll('.switch-user-row');
                
                rows.forEach(row => {
                    const searchData = row.getAttribute('data-search') || '';
                    const roleData = row.getAttribute('data-role') || '';
                    
                    let matchesText = !query || searchData.includes(query);
                    let matchesCat = true;
                    if (currentCatFilter) {
                        const pats = currentCatFilter.split('|');
                        matchesCat = pats.some(p => searchData.includes(p) || roleData.includes(p));
                    }

                    row.style.display = (matchesText && matchesCat) ? 'flex' : 'none';
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeSwitchUserModal();
                }
            });

            document.getElementById('switchUserModal')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeSwitchUserModal();
                }
            });
        </script>
    <?php endif; ?>
<?php include __DIR__ . '/footer.php'; ?>
