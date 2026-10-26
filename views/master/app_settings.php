<?php
// views/master/app_settings.php
use Core\SecurityHelper;
use Core\CSRF;

$curLogo = $settings['app_logo'] ?? '';
$curLogoUrl = '';
if (!empty($curLogo)) {
    $curLogoPath = dirname(__DIR__, 2) . '/public/' . ltrim($curLogo, '/');
    if (file_exists($curLogoPath)) {
        $curLogoUrl = $baseUrl . '/public/' . htmlspecialchars(ltrim($curLogo, '/'));
    }
}
$curFavicon = $settings['app_favicon'] ?? '';
$curFaviconUrl = '';
if (!empty($curFavicon)) {
    $curFaviconPath = dirname(__DIR__, 2) . '/public/' . ltrim($curFavicon, '/');
    if (file_exists($curFaviconPath)) {
        $curFaviconUrl = $baseUrl . '/public/' . htmlspecialchars(ltrim($curFavicon, '/'));
    }
}
$curShortName = $settings['app_short_name'] ?? 'MASEKO';
$curSubName = $settings['app_sub_name'] ?? 'Manajemen Sekolah Berbasis Komputer';
$curFullName = $settings['app_name'] ?? 'Manajemen Sekolah Berbasis Komputer (MASEKO)';
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-sliders" style="color: var(--primary);"></i>
            Pengaturan Identitas & Logo Aplikasi
        </h1>
        <p class="page-subtitle" style="margin-top: 4px; color: var(--text-muted);">
            Kustomisasi nama sistem, singkatan, dan logo utama aplikasi (MASEKO).
        </p>
    </div>
</div>

<div class="settings-grid">
    <!-- Formulir Pengaturan -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa-solid fa-pen-to-square" style="color: var(--primary); margin-right: 6px;"></i>
                Ubah Identitas & Logo Sistem
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $baseUrl ?>/settings/app/save" enctype="multipart/form-data">
                <?= CSRF::inputField() ?>

                <!-- Logo Section -->
                <div style="padding: 18px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 24px;">
                    <label class="form-label" style="font-weight: 700; font-size: 14px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 4px; margin-bottom: 8px;">
                        <span><i class="fa-solid fa-image" style="color: var(--primary); margin-right: 6px;"></i> Logo Utama Aplikasi</span>
                        <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">Format: PNG, JPG, WEBP, SVG (Maks. 2MB)</span>
                    </label>

                    <div class="settings-upload-row">
                        <!-- Logo Box Preview -->
                        <div style="width: 76px; height: 76px; border-radius: 14px; background: #0f172a; border: 2px dashed #475569; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <img id="app_logo_preview" src="<?= !empty($curLogoUrl) ? $curLogoUrl : '' ?>" 
                                 alt="Logo App" 
                                 style="max-width: 100%; max-height: 100%; object-fit: contain; padding: 4px; <?= empty($curLogoUrl) ? 'display: none;' : '' ?>">
                            <div id="app_logo_placeholder" style="color: #94a3b8; text-align: center; font-size: 26px; <?= !empty($curLogoUrl) ? 'display: none;' : '' ?>">
                                <i class="fa-solid fa-desktop"></i>
                            </div>
                        </div>

                        <!-- Upload Controls -->
                        <div style="flex: 1;">
                            <input type="file" name="app_logo" id="app_logo_input" class="form-control" accept="image/png, image/jpeg, image/webp, image/svg+xml" onchange="previewAppLogo(this)">
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                                Unggah logo institusi atau ikon sistem baru untuk menggantikan logo bawaan.
                            </div>

                            <?php if (!empty($curLogoUrl)): ?>
                                <div style="margin-top: 10px;">
                                    <label style="font-size: 12px; color: #dc2626; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                        <input type="checkbox" name="delete_logo" value="1" id="delete_logo_chk" onchange="toggleDeleteLogo(this)">
                                        <span><i class="fa-solid fa-trash-can"></i> Hapus logo kustom & kembalikan ke ikon default</span>
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Favicon Section -->
                <div style="padding: 18px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 24px;">
                    <label class="form-label" style="font-weight: 700; font-size: 14px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 4px; margin-bottom: 8px;">
                        <span><i class="fa-solid fa-globe" style="color: var(--primary); margin-right: 6px;"></i> Favicon Tab Browser</span>
                        <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">Format: ICO, PNG, SVG (Maks. 1MB)</span>
                    </label>

                    <div class="settings-upload-row">
                        <!-- Favicon Box Preview -->
                        <div style="width: 52px; height: 52px; border-radius: 10px; background: #ffffff; border: 2px dashed #94a3b8; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.06);">
                            <img id="app_favicon_preview" src="<?= !empty($curFaviconUrl) ? $curFaviconUrl : '' ?>" 
                                 alt="Favicon" 
                                 style="max-width: 32px; max-height: 32px; object-fit: contain; <?= empty($curFaviconUrl) ? 'display: none;' : '' ?>">
                            <div id="app_favicon_placeholder" style="color: #94a3b8; text-align: center; font-size: 22px; <?= !empty($curFaviconUrl) ? 'display: none;' : '' ?>">
                                <i class="fa-solid fa-window-maximize"></i>
                            </div>
                        </div>

                        <!-- Upload Controls -->
                        <div style="flex: 1;">
                            <input type="file" name="app_favicon" id="app_favicon_input" class="form-control" accept="image/x-icon, image/png, image/svg+xml, image/webp" onchange="previewAppFavicon(this)">
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                                Ikon website kecil yang tampil pada tab browser dan bookmark URL.
                            </div>

                            <?php if (!empty($curFaviconUrl)): ?>
                                <div style="margin-top: 8px;">
                                    <label style="font-size: 12px; color: #dc2626; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                        <input type="checkbox" name="delete_favicon" value="1">
                                        <span><i class="fa-solid fa-trash-can"></i> Hapus favicon kustom</span>
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Input Nama Singkat / Brand Acronym -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="app_short_name" style="font-weight: 700;">
                        Singkatan / Nama Brand Aplikasi <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="app_short_name" id="app_short_name" class="form-control" 
                           value="<?= SecurityHelper::escape($curShortName) ?>" required maxlength="30"
                           placeholder="Contoh: MASEKO" oninput="updateLivePreview()">
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Ditampilkan menonjol pada bagian atas sidebar navigasi dan judul tab browser.
                    </div>
                </div>

                <!-- Input Sub-Judul / Keterangan -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="app_sub_name" style="font-weight: 700;">
                        Keterangan / Kepanjangan Singkatan
                    </label>
                    <input type="text" name="app_sub_name" id="app_sub_name" class="form-control" 
                           value="<?= SecurityHelper::escape($curSubName) ?>" maxlength="100"
                           placeholder="Contoh: Manajemen Sekolah Berbasis Komputer" oninput="updateLivePreview()">
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Teks penjelas di bawah nama brand pada sidebar dan halaman login.
                    </div>
                </div>

                <!-- Input Nama Lengkap Sistem -->
                <div class="form-group" style="margin-bottom: 28px;">
                    <label class="form-label" for="app_name" style="font-weight: 700;">
                        Nama Lengkap Sistem Aplikasi
                    </label>
                    <input type="text" name="app_name" id="app_name" class="form-control" 
                           value="<?= SecurityHelper::escape($curFullName) ?>" maxlength="150"
                           placeholder="Contoh: Manajemen Sekolah Berbasis Komputer (MASEKO)">
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Digunakan untuk nama resmi sistem pada laporan dan dokumen global.
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--border-color);">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pratinjau Tampilan (Live Preview) -->
    <div>
        <div class="card" style="position: sticky; top: 80px;">
            <div class="card-header">
                <h3 class="card-title" style="font-size: 14px;">
                    <i class="fa-solid fa-eye" style="color: var(--primary); margin-right: 6px;"></i>
                    Pratinjau Sidebar
                </h3>
            </div>
            <div class="card-body" style="padding: 16px;">
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">
                    Tampilan pada sidebar navigasi:
                </div>

                <!-- Dark Theme Sidebar Header Simulation -->
                <div style="background: #0f172a; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);">
                    <div id="preview_sidebar_logo_box" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                        <img id="preview_sidebar_img" src="<?= !empty($curLogoUrl) ? $curLogoUrl : '' ?>" 
                             alt="Logo" 
                             style="max-width: 100%; max-height: 100%; object-fit: contain; <?= empty($curLogoUrl) ? 'display: none;' : '' ?>">
                        <div id="preview_sidebar_icon" style="color: white; font-size: 18px; <?= !empty($curLogoUrl) ? 'display: none;' : '' ?>">
                            <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #2563eb, #0d9488); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-desktop"></i>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; min-width: 0; line-height: 1.15; overflow: hidden;">
                        <span id="preview_text_short" style="font-weight: 800; font-size: 15px; letter-spacing: 0.5px; color: #38bdf8;">
                            <?= SecurityHelper::escape($curShortName) ?>
                        </span>
                        <span id="preview_text_sub" style="font-size: 9px; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 190px;">
                            <?= SecurityHelper::escape($curSubName) ?>
                        </span>
                    </div>
                </div>

                <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed var(--border-color); font-size: 12px; color: var(--text-muted); line-height: 1.5;">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 4px;"></i>
                    Setelah disimpan, perubahan logo dan nama ini langsung aktif untuk seluruh akun yang login ke sistem.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewAppLogo(input) {
    var preview = document.getElementById('app_logo_preview');
    var placeholder = document.getElementById('app_logo_placeholder');
    var sideImg = document.getElementById('preview_sidebar_img');
    var sideIcon = document.getElementById('preview_sidebar_icon');

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';

            sideImg.src = e.target.result;
            sideImg.style.display = 'block';
            sideIcon.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function previewAppFavicon(input) {
    var preview = document.getElementById('app_favicon_preview');
    var placeholder = document.getElementById('app_favicon_placeholder');

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleDeleteLogo(chk) {
    var input = document.getElementById('app_logo_input');
    var preview = document.getElementById('app_logo_preview');
    var placeholder = document.getElementById('app_logo_placeholder');
    var sideImg = document.getElementById('preview_sidebar_img');
    var sideIcon = document.getElementById('preview_sidebar_icon');

    if (chk.checked) {
        input.disabled = true;
        preview.style.display = 'none';
        placeholder.style.display = 'block';
        sideImg.style.display = 'none';
        sideIcon.style.display = 'block';
    } else {
        input.disabled = false;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
        sideImg.style.display = 'block';
        sideIcon.style.display = 'none';
    }
}

function updateLivePreview() {
    var shortName = document.getElementById('app_short_name').value.trim() || 'MASEKO';
    var subName = document.getElementById('app_sub_name').value.trim() || 'Manajemen Sekolah Berbasis Komputer';
    document.getElementById('preview_text_short').innerText = shortName;
    document.getElementById('preview_text_sub').innerText = subName;
}
</script>
