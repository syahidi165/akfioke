/**
 * pwa.js - Progressive Web App Handler untuk MASEKO
 * Yayasan Islam Al Kahfi Batam
 */
(function() {
    'use strict';

    let deferredPrompt = null;
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    // 1. Tampilkan tombol pasang jika BUKAN dalam mode standalone (aplikasi terpasang)
    function setupInstallButtons() {
        if (isStandalone) {
            // Sembunyikan tombol jika sudah berjalan sebagai aplikasi terpasang
            document.querySelectorAll('.pwa-install-btn, #pwaInstallBtn').forEach(function(btn) {
                btn.style.display = 'none';
            });
            return;
        }

        // Tampilkan tombol pasang di navbar dan login
        document.querySelectorAll('.pwa-install-btn, #pwaInstallBtn').forEach(function(btn) {
            btn.style.display = 'inline-flex';
        });
    }

    // Jalankan saat DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupInstallButtons);
    } else {
        setupInstallButtons();
    }

    // 2. Registrasi Service Worker jika didukung browser
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            const manifestLink = document.querySelector('link[rel="manifest"]');
            let swUrl = '/sw.js';
            if (manifestLink && manifestLink.getAttribute('href')) {
                const manifestHref = manifestLink.getAttribute('href');
                swUrl = manifestHref.replace(/manifest\.json$/, 'sw.js');
            }

            navigator.serviceWorker.register(swUrl)
                .then(function(registration) {
                    console.log('[MASEKO PWA] Service Worker aktif:', registration.scope);
                })
                .catch(function(error) {
                    console.warn('[MASEKO PWA] SW Notice (Normal jika di HTTP non-localhost):', error.message);
                });
        });
    }

    // 3. Tangani event Pemasangan Aplikasi Resmi (beforeinstallprompt)
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;
        setupInstallButtons();
        console.log('[MASEKO PWA] Event beforeinstallprompt siap.');
    });

    // 4. Modal Panduan Pemasangan Interaktif
    function showPwaGuideModal() {
        let modal = document.getElementById('pwaGuideModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'pwaGuideModal';
            modal.className = 'modal-overlay';
            modal.style.zIndex = '3000';
            
            const isIos = /iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase()) && !window.MSStream;
            
            modal.innerHTML = `
                <div class="modal-container" style="max-width: 480px; padding: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);">
                    <div style="background: linear-gradient(135deg, #1e3a8a 0%, #065f46 100%); color: white; padding: 20px 24px; position: relative;">
                        <button type="button" onclick="document.getElementById('pwaGuideModal').classList.remove('active')" 
                                style="position: absolute; right: 16px; top: 16px; background: rgba(255,255,255,0.2); border: none; color: white; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center;">
                            &times;
                        </button>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 44px; height: 44px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Pasang Aplikasi MASEKO</h3>
                                <p style="font-size: 12px; color: #d1fae5; margin: 2px 0 0;">Yayasan Islam Al Kahfi Batam</p>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 20px 24px; font-size: 13px; color: #334155; line-height: 1.6;">
                        ${isIos ? `
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                                <div style="font-weight: 700; color: #1e3a8a; margin-bottom: 8px;">
                                    <i class="fa-brands fa-apple" style="margin-right: 6px;"></i> Cara Pasang di iPhone / iPad (Safari):
                                </div>
                                <ol style="padding-left: 20px; margin: 0;">
                                    <li style="margin-bottom: 6px;">Tekan ikon <strong>Bagikan / Share</strong> (kotak dengan panah atas di bawah layar Safari).</li>
                                    <li style="margin-bottom: 6px;">Gulir ke bawah dan ketuk <strong>"Tambahkan ke Layar Utama" (Add to Home Screen)</strong>.</li>
                                    <li>Ketuk <strong>Tambah</strong> di pojok kanan atas. Selesai!</li>
                                </ol>
                            </div>
                        ` : `
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                                <div style="font-weight: 700; color: #166534; margin-bottom: 8px;">
                                    <i class="fa-brands fa-android" style="margin-right: 6px;"></i> Cara Pasang di HP Android (Google Chrome / Edge):
                                </div>
                                <ol style="padding-left: 20px; margin: 0;">
                                    <li style="margin-bottom: 6px;">Ketuk ikon <strong>Menu Titik Tiga (⋮)</strong> di pojok kanan atas browser Chrome.</li>
                                    <li style="margin-bottom: 6px;">Pilih opsi <strong>"Tambahkan ke Layar Utama"</strong> atau <strong>"Instal Aplikasi"</strong>.</li>
                                    <li>Ketuk <strong>Instal</strong>. Ikon MASEKO akan langsung muncul di menu HP Anda!</li>
                                </ol>
                            </div>
                            <div style="font-size: 11px; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <i class="fa-solid fa-circle-info" style="color: #0284c7; margin-right: 4px;"></i>
                                <strong>Catatan Pengujian Lokal:</strong> Jika Anda mengakses via IP Wi-Fi lokal (<code>192.168.x.x</code>), Google Chrome membatasi dialog otomatis (mewajibkan HTTPS). Namun menu manual <em>"Tambahkan ke Layar Utama"</em> tetap bekerja 100%. Saat sudah online di domain resmi HTTPS, tombol ini akan langsung otomatis 1 klik!
                            </div>
                        `}
                        <div style="margin-top: 18px; text-align: right;">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('pwaGuideModal').classList.remove('active')" 
                                    style="width: 100%; justify-content: center; padding: 10px; font-weight: 600;">
                                Saya Mengerti
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        modal.classList.add('active');
    }

    // 5. Fungsi Global Pemicu Pemasangan
    window.promptPwaInstall = function() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    console.log('[MASEKO PWA] Pengguna menyetujui instalasi.');
                    document.querySelectorAll('.pwa-install-btn, #pwaInstallBtn').forEach(function(btn) {
                        btn.style.display = 'none';
                    });
                }
                deferredPrompt = null;
            });
        } else {
            // Tampilkan panduan interaktif jika prompt native tidak tersedia
            showPwaGuideModal();
        }
    };

    window.addEventListener('appinstalled', function() {
        console.log('[MASEKO PWA] Berhasil terpasang.');
        deferredPrompt = null;
        document.querySelectorAll('.pwa-install-btn, #pwaInstallBtn').forEach(function(btn) {
            btn.style.display = 'none';
        });
    });
})();
