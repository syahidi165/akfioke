<?php
// app/Controllers/PwaController.php
declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Setting;
use Core\View;

class PwaController extends Controller {

    /**
     * Sajikan manifest.json dinamis sesuai konfigurasi aplikasi dan base URL.
     */
    public function manifest(): void {
        $baseUrl = Auth::baseUrl();
        $appName = Setting::get('app_name', 'Manajemen Sekolah Berbasis Komputer (MASEKO)');
        $appShortName = Setting::get('app_short_name', 'MASEKO');
        $appSubName = Setting::get('app_sub_name', 'Sistem Manajemen Sekolah Terpadu Yayasan Islam Al Kahfi Batam');

        $manifest = [
            'name'             => $appName,
            'short_name'        => $appShortName,
            'description'       => $appSubName,
            'id'                => $baseUrl . '/',
            'start_url'         => $baseUrl . '/login',
            'scope'             => $baseUrl . '/',
            'display'           => 'standalone',
            'orientation'       => 'any',
            'background_color'  => '#0f172a',
            'theme_color'       => '#1e3a8a',
            'categories'        => ['education', 'productivity'],
            'icons'             => [
                [
                    'src'     => $baseUrl . '/public/assets/img/icon-192.png',
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src'     => $baseUrl . '/public/assets/img/icon-512.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src'     => $baseUrl . '/public/assets/img/icon-maskable-512.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'maskable'
                ]
            ],
            'shortcuts'         => [
                [
                    'name'        => 'Masuk / Login',
                    'short_name'  => 'Login',
                    'description' => 'Buka halaman masuk MASEKO',
                    'url'         => $baseUrl . '/login',
                    'icons'       => [
                        [
                            'src'   => $baseUrl . '/public/assets/img/icon-192.png',
                            'sizes' => '192x192'
                        ]
                    ]
                ],
                [
                    'name'        => 'Dashboard',
                    'short_name'  => 'Dashboard',
                    'description' => 'Buka dashboard utama',
                    'url'         => $baseUrl . '/dashboard',
                    'icons'       => [
                        [
                            'src'   => $baseUrl . '/public/assets/img/icon-192.png',
                            'sizes' => '192x192'
                        ]
                    ]
                ]
            ]
        ];

        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Sajikan Service Worker (sw.js) dinamis dengan scope yang tepat dan aturan keamanan ketat.
     */
    public function serviceWorker(): void {
        $baseUrl = Auth::baseUrl();
        $cacheVersion = 'maseko-v1.0.4';

        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: /');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        echo <<<JS
/**
 * Service Worker MASEKO - Yayasan Islam Al Kahfi Batam
 * Versi Cache: {$cacheVersion}
 * 
 * KEBIJAKAN KEAMANAN & CACHE:
 * 1. STATIC ASSETS ONLY: Hanya menyimpan CSS, JS core, WebFonts, dan Logo.
 * 2. ZERO DYNAMIC CACHING: Tidak pernah meng-cache halaman PHP dinamis, form POST,
 *    token CSRF, sesi pengguna, maupun unduhan database/Cold Storage NAS.
 * 3. NETWORK FIRST DENGAN OFFLINE FALLBACK: Jika koneksi internet terputus,
 *    navigasi dialihkan ke halaman offline ramah pengguna.
 */

const CACHE_NAME = '{$cacheVersion}';
const BASE_URL = '{$baseUrl}';

const PRECACHE_ASSETS = [
    BASE_URL + '/public/assets/css/app.css',
    BASE_URL + '/public/assets/css/fontawesome.min.css',
    BASE_URL + '/public/assets/js/chart.min.js',
    BASE_URL + '/public/assets/img/icon-192.png',
    BASE_URL + '/public/assets/img/icon-512.png',
    BASE_URL + '/offline'
];

// 1. Install Event: Pra-cache aset inti & lewati antrean tunggu
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('[PWA] Precache warning:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// 2. Activate Event: Bersihkan cache versi lama dan klaim kontrol klien
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Fetch Event: Intersepsi permintaan jaringan dengan filter keamanan ketat
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Abaikan permintaan selain GET (POST, PUT, DELETE langsung ke server!)
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // KECUALIKAN TOTAL endpoint dinamis, unduhan arsip Cold Storage NAS, dan auth
    const sensitivePatterns = [
        '/logout',
        '/switch-role',
        '/switch-ta',
        '/export',
        '/download',
        '/backup',
        '/storage',
        '/uploads',
        '/test-nas',
        '/save-nas-config'
    ];

    if (sensitivePatterns.some(pattern => url.pathname.includes(pattern))) {
        return; // Lewati ke jaringan asli
    }

    // A. Permintaan Navigasi Halaman HTML (Network First dengan Offline Fallback)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match(BASE_URL + '/offline').then((offlineResponse) => {
                    return offlineResponse || new Response(
                        '<div style="text-align:center;padding:50px;font-family:sans-serif;">' +
                        '<h2>Koneksi Internet Terputus</h2>' +
                        '<p>Aplikasi MASEKO membutuhkan koneksi internet untuk memuat halaman ini.</p>' +
                        '<button onclick="location.reload()" style="padding:10px 20px;cursor:pointer;">Coba Lagi</button>' +
                        '</div>',
                        { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                    );
                });
            })
        );
        return;
    }

    // B. Aset Statis (CSS, JS, Font, Gambar di /public/assets/) -> Cache First dengan Network Fallback
    if (url.pathname.includes('/public/assets/')) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Update cache di background (Stale While Revalidate)
                    fetch(request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                        }
                    }).catch(() => {/* offline */});
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
                        return networkResponse;
                    }
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, responseToCache));
                    return networkResponse;
                });
            })
        );
        return;
    }

    // Default: Ambil langsung dari jaringan
    event.respondWith(fetch(request));
});
JS;
        exit;
    }

    /**
     * Halaman fallback saat perangkat HP/Desktop sedang tidak memiliki koneksi internet.
     */
    public function offline(): void {
        View::render('errors/offline', [
            'pageTitle' => 'Tidak Ada Koneksi Internet'
        ], 'layouts/plain');
    }
}
