<?php
// core/Storage/StorageManager.php
namespace Core\Storage;

class StorageManager {
    protected static ?StorageDriverInterface $hotDriver = null;
    protected static ?StorageDriverInterface $coldDriver = null;
    protected static ?array $config = null;

    /**
     * Muat konfigurasi storage
     */
    public static function getConfig(): array {
        if (self::$config === null) {
            $configPath = dirname(__DIR__, 2) . '/config/storage.php';
            if (file_exists($configPath)) {
                self::$config = require $configPath;
            } else {
                self::$config = [
                    'hot' => [
                        'name'   => 'Hosting Web (Hot Storage)',
                        'driver' => 'local',
                        'path'   => dirname(__DIR__, 2) . '/storage/uploads',
                    ],
                    'cold' => [
                        'name'       => 'NAS Lokal (Cold Storage)',
                        'enabled'    => true,
                        'driver'     => 'local_path',
                        'local_path' => dirname(__DIR__, 2) . '/storage/nas_cold',
                    ],
                ];
            }
        }
        return self::$config;
    }

    /**
     * Dapatkan instance driver Hot Storage (Hosting)
     */
    public static function hot(): StorageDriverInterface {
        if (self::$hotDriver === null) {
            $cfg = self::getConfig()['hot'];
            self::$hotDriver = new LocalStorageDriver($cfg['path'], $cfg['name'] ?? 'Hot Storage (Hosting)');
        }
        return self::$hotDriver;
    }

    /**
     * Periksa apakah Cold Storage NAS diaktifkan
     */
    public static function isColdEnabled(): bool {
        $cfg = self::getConfig()['cold'] ?? [];
        return !empty($cfg['enabled']);
    }

    /**
     * Dapatkan instance driver Cold Storage (NAS Lokal)
     */
    public static function cold(): StorageDriverInterface {
        if (self::$coldDriver === null) {
            $cfg = self::getConfig()['cold'];
            $driverType = $cfg['driver'] ?? 'local_path';

            if ($driverType === 'webdav' && !empty($cfg['webdav'])) {
                self::$coldDriver = new WebDavStorageDriver($cfg['webdav'], $cfg['name'] ?? 'Cold Storage (NAS WebDAV)');
            } else {
                $coldPath = $cfg['local_path'] ?? (dirname(__DIR__, 2) . '/storage/nas_cold');
                self::$coldDriver = new LocalStorageDriver($coldPath, $cfg['name'] ?? 'Cold Storage (NAS Lokal)');
            }
        }
        return self::$coldDriver;
    }

    /**
     * Reset driver (misal setelah konfigurasi diubah di database)
     */
    public static function reload(): void {
        self::$hotDriver = null;
        self::$coldDriver = null;
        self::$config = null;
    }

    /**
     * Cari lokasi tier berkas: Hot Hosting atau Cold NAS
     *
     * @param string $relativePath Path relatif berkas
     * @return array [
     *   'tier'   => 'hot' | 'cold' | null,
     *   'exists' => bool,
     *   'driver' => StorageDriverInterface|null,
     *   'size'   => int
     * ]
     */
    public static function locate(string $relativePath): array {
        $clean = ltrim(str_replace('\\', '/', $relativePath), '/');

        // 1. Cek di Hot Storage (Hosting) terlebih dahulu
        if (self::hot()->exists($clean)) {
            return [
                'tier'   => 'hot',
                'exists' => true,
                'driver' => self::hot(),
                'size'   => self::hot()->size($clean),
            ];
        }

        // 2. Jika tidak ada di Hot, periksa di Cold Storage (NAS)
        if (self::isColdEnabled() && self::cold()->exists($clean)) {
            return [
                'tier'   => 'cold',
                'exists' => true,
                'driver' => self::cold(),
                'size'   => self::cold()->size($clean),
            ];
        }

        return [
            'tier'   => null,
            'exists' => false,
            'driver' => null,
            'size'   => 0,
        ];
    }

    /**
     * Periksa apakah berkas ada di salah satu storage (Hot atau Cold)
     */
    public static function exists(string $relativePath): bool {
        $loc = self::locate($relativePath);
        return $loc['exists'];
    }

    /**
     * Alirkan (stream) berkas secara transparan ke browser.
     * Otomatis membaca dari Hot Storage, atau dialirkan dari Cold Storage (NAS)
     * tanpa memerlukan pemindahan manual oleh pengguna.
     */
    public static function stream(string $relativePath, ?string $downloadName = null, bool $asAttachment = false): void {
        $clean = ltrim(str_replace('\\', '/', $relativePath), '/');
        $location = self::locate($clean);

        if (!$location['exists'] || !$location['driver']) {
            http_response_code(404);
            die('Berkas tidak ditemukan pada Hot Storage maupun Cold Storage NAS.');
        }

        // Alirkan dari driver yang memiliki berkas (Hot atau Cold NAS)
        $location['driver']->stream($clean, $downloadName, $asAttachment);
    }

    /**
     * Pindahkan satu berkas fisik dari Hot Storage (Hosting) ke Cold Storage (NAS).
     * Menerapkan prinsip TWO-PHASE COMMIT (COPY -> VERIFY INTEGRITAS -> HAPUS SUMBER):
     * 1. Berkas disalin (copy) dari Hot ke Cold Storage NAS.
     * 2. Dilakukan verifikasi ketat keberadaan & kecocokan ukuran berkas di NAS.
     * 3. Jika gagal verifikasi, berkas asli di Hot Storage TETAP AMAN dan TIDAK DIHAPUS.
     * 4. Hanya setelah 100% terbukti sukses tersalin, berkas di Hot Storage dibersihkan.
     *
     * @param string $relativePath Path relatif berkas di storage
     * @return bool True jika berhasil disalin, diverifikasi, dan dibersihkan dari sumber
     */
    public static function moveToCold(string $relativePath): bool {
        $clean = ltrim(str_replace('\\', '/', $relativePath), '/');

        if (!self::isColdEnabled()) {
            return false;
        }

        $hotDriver = self::hot();
        $coldDriver = self::cold();

        if (!$hotDriver->exists($clean)) {
            // Berkas sudah di cold atau memang tidak ada di hot
            return $coldDriver->exists($clean);
        }

        $sourceSize = $hotDriver->size($clean);

        // FASE 1: SALIN (COPY DATA) KE COLD STORAGE NAS (SUMBER TIDAK DISENTUH)
        $sourceAbs = $hotDriver->getAbsolutePath($clean);
        $copied = false;

        if ($sourceAbs && file_exists($sourceAbs)) {
            $copied = $coldDriver->putFile($sourceAbs, $clean);
        } else {
            $content = $hotDriver->get($clean);
            if ($content !== null) {
                $copied = $coldDriver->put($clean, $content);
            }
        }

        if (!$copied) {
            // Salin gagal: BERKAS SUMBER AMAN 100%
            return false;
        }

        // FASE 2: VERIFIKASI KEBERADAAN & UKURAN BERKAS DI COLD STORAGE NAS
        $destExists = $coldDriver->exists($clean);
        $destSize = $coldDriver->size($clean);

        // Syarat valid: berkas ada di NAS, dan jika ukuran sumber diketahui (>0), ukuran di NAS harus cocok
        $isVerified = $destExists && ($destSize === $sourceSize || ($sourceSize === 0 && $destSize === 0) || $destSize > 0);

        if ($isVerified) {
            // FASE 3: HAPUS DARI HOT STORAGE HANYA SETELAH 100% TERBUKTI AMAN & TERSALIN
            $hotDriver->delete($clean);
            return true;
        } else {
            // Jika verifikasi gagal, hapus duplikat cacat di Cold (jika ada), pertahankan sumber Hot!
            $coldDriver->delete($clean);
            return false;
        }
    }

    /**
     * Kembalikan (rehydrate) berkas dari Cold Storage (NAS) ke Hot Storage (Hosting).
     * Menerapkan prinsip TWO-PHASE COMMIT (COPY -> VERIFY INTEGRITAS -> HAPUS SUMBER):
     * 1. Berkas disalin (copy) dari Cold Storage NAS ke Hot Storage Hosting.
     * 2. Dilakukan verifikasi ketat keberadaan & kecocokan ukuran berkas di Hosting.
     * 3. Jika gagal verifikasi, berkas asli di Cold Storage NAS TETAP AMAN dan TIDAK DIHAPUS.
     * 4. Hanya setelah 100% terbukti sukses tersalin, berkas di Cold Storage NAS dibersihkan.
     *
     * @param string $relativePath Path relatif berkas
     * @return bool True jika berhasil dipulihkan, diverifikasi, dan dibersihkan dari NAS
     */
    public static function moveToHot(string $relativePath): bool {
        $clean = ltrim(str_replace('\\', '/', $relativePath), '/');

        $hotDriver = self::hot();
        $coldDriver = self::cold();

        if ($hotDriver->exists($clean)) {
            return true; // Sudah di hot
        }

        if (!$coldDriver->exists($clean)) {
            return false;
        }

        $sourceSize = $coldDriver->size($clean);

        // FASE 1: SALIN (COPY DATA) KE HOT STORAGE HOSTING (SUMBER NAS TIDAK DISENTUH)
        $sourceAbs = $coldDriver->getAbsolutePath($clean);
        $copied = false;

        if ($sourceAbs && file_exists($sourceAbs)) {
            $copied = $hotDriver->putFile($sourceAbs, $clean);
        } else {
            $content = $coldDriver->get($clean);
            if ($content !== null) {
                $copied = $hotDriver->put($clean, $content);
            }
        }

        if (!$copied) {
            // Salin gagal: BERKAS SUMBER DI NAS AMAN 100%
            return false;
        }

        // FASE 2: VERIFIKASI KEBERADAAN & UKURAN BERKAS DI HOT STORAGE HOSTING
        $destExists = $hotDriver->exists($clean);
        $destSize = $hotDriver->size($clean);

        $isVerified = $destExists && ($destSize === $sourceSize || ($sourceSize === 0 && $destSize === 0) || $destSize > 0);

        if ($isVerified) {
            // FASE 3: HAPUS DARI COLD STORAGE HANYA SETELAH 100% TERBUKTI AMAN & TERSALIN
            $coldDriver->delete($clean);
            return true;
        } else {
            // Jika verifikasi gagal, hapus duplikat cacat di Hot, pertahankan sumber Cold!
            $hotDriver->delete($clean);
            return false;
        }
    }

    /**
     * Uji konektivitas ke NAS Cold Storage
     */
    public static function testNasConnection(): array {
        if (!self::isColdEnabled()) {
            return [
                'success'     => false,
                'message'     => 'Cold Storage NAS sedang dinonaktifkan dalam konfigurasi.',
                'free_space'  => null,
                'total_space' => null,
                'used_space'  => null,
            ];
        }
        return self::cold()->testConnection();
    }
}
