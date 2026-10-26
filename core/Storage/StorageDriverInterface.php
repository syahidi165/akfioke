<?php
// core/Storage/StorageDriverInterface.php
namespace Core\Storage;

interface StorageDriverInterface {
    /**
     * Periksa apakah berkas ada di media penyimpanan
     */
    public function exists(string $relativePath): bool;

    /**
     * Dapatkan path fisik absolut (jika ada pada local filesystem)
     */
    public function getAbsolutePath(string $relativePath): ?string;

    /**
     * Baca seluruh isi berkas
     */
    public function get(string $relativePath): ?string;

    /**
     * Simpan berkas ke media penyimpanan
     *
     * @param string $relativePath Path target relatif
     * @param string|resource $contents Isi data string atau stream resource
     */
    public function put(string $relativePath, $contents): bool;

    /**
     * Salin berkas fisik dari path lokal sumber ke path relatif di storage
     */
    public function putFile(string $sourceAbsolutePath, string $targetRelativePath): bool;

    /**
     * Hapus berkas dari media penyimpanan
     */
    public function delete(string $relativePath): bool;

    /**
     * Alirkan (stream) berkas secara hemat RAM ke browser
     */
    public function stream(string $relativePath, ?string $downloadName = null, bool $asAttachment = false): void;

    /**
     * Ukuran berkas dalam bytes
     */
    public function size(string $relativePath): int;

    /**
     * Uji konektivitas dan kapasitas media penyimpanan
     *
     * @return array [
     *   'success'     => bool,
     *   'message'     => string,
     *   'free_space'  => int|null (bytes),
     *   'total_space' => int|null (bytes),
     *   'used_space'  => int|null (bytes)
     * ]
     */
    public function testConnection(): array;
}
