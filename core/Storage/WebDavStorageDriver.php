<?php
// core/Storage/WebDavStorageDriver.php
namespace Core\Storage;

class WebDavStorageDriver implements StorageDriverInterface {
    protected string $baseUrl;
    protected string $basePath;
    protected string $username;
    protected string $password;
    protected int $timeout;
    protected string $name;

    public function __construct(array $config, string $name = 'NAS WebDAV Storage') {
        $this->baseUrl = rtrim($config['base_url'] ?? 'http://127.0.0.1:5005', '/');
        $rawPath = trim($config['path'] ?? '/cold_storage');
        $this->basePath = ($rawPath === '' || $rawPath === '/') ? '' : ('/' . trim($rawPath, '/'));
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->timeout = (int)($config['timeout'] ?? 30);
        $this->name = $name;
    }

    public function getAbsolutePath(string $relativePath): ?string {
        // WebDAV adalah media jarak jauh, tidak memiliki path absolut local filesystem
        return null;
    }

    protected function getFullUrl(string $relativePath): string {
        $clean = ltrim(str_replace('\\', '/', $relativePath), '/');
        $base = $this->baseUrl . $this->basePath;
        return rtrim($base, '/') . '/' . $clean;
    }

    protected function request(string $method, string $url, $body = null, array $headers = []): array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AKFIOKE-WebDAV/1.0');

        if (!empty($this->username)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        }

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'code'           => (int)$httpCode,
            'response'       => $response,
            'error'          => $error,
            'content_length' => $contentLength > 0 ? (int)$contentLength : 0
        ];
    }

    public function exists(string $relativePath): bool {
        $url = $this->getFullUrl($relativePath);
        $res = $this->request('PROPFIND', $url, null, ['Depth: 0']);
        return ($res['code'] === 207 || $res['code'] === 200);
    }

    public function get(string $relativePath): ?string {
        $url = $this->getFullUrl($relativePath);
        $res = $this->request('GET', $url);
        if ($res['code'] === 200 && $res['response'] !== false) {
            return $res['response'];
        }
        return null;
    }

    public function put(string $relativePath, $contents): bool {
        $url = $this->getFullUrl($relativePath);
        
        // Pastikan folder induk ada via MKCOL
        $this->ensureParentDirectoryExists($relativePath);

        $body = is_resource($contents) ? stream_get_contents($contents) : $contents;
        $res = $this->request('PUT', $url, $body);
        return ($res['code'] === 201 || $res['code'] === 204 || $res['code'] === 200);
    }

    public function putFile(string $sourceAbsolutePath, string $targetRelativePath): bool {
        if (!file_exists($sourceAbsolutePath) || !is_file($sourceAbsolutePath)) {
            return false;
        }
        $contents = @file_get_contents($sourceAbsolutePath);
        if ($contents === false) return false;
        return $this->put($targetRelativePath, $contents);
    }

    protected function ensureParentDirectoryExists(string $relativePath): void {
        $clean = ltrim(str_replace('\\', '/', $relativePath), '/');
        $parts = explode('/', dirname($clean));
        $current = '';
        foreach ($parts as $p) {
            if ($p === '.' || $p === '') continue;
            $current .= '/' . $p;
            $folderUrl = $this->baseUrl . $this->basePath . $current;
            $this->request('MKCOL', $folderUrl);
        }
    }

    public function delete(string $relativePath): bool {
        $url = $this->getFullUrl($relativePath);
        $res = $this->request('DELETE', $url);
        return ($res['code'] === 204 || $res['code'] === 200);
    }

    public function size(string $relativePath): int {
        $url = $this->getFullUrl($relativePath);
        $res = $this->request('HEAD', $url);
        if ($res['code'] === 200 && ($res['content_length'] ?? 0) > 0) {
            return (int)$res['content_length'];
        }
        return 0;
    }

    public function stream(string $relativePath, ?string $downloadName = null, bool $asAttachment = false): void {
        $url = $this->getFullUrl($relativePath);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = $downloadName ? basename($downloadName) : basename($relativePath);
        $encodedName = rawurlencode($filename);
        $disposition = $asAttachment ? 'attachment' : 'inline';

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedName);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0, private');
        header('Pragma: public');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($this->username)) {
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        }
        curl_exec($ch);
        curl_close($ch);
        exit;
    }

    public function testConnection(): array {
        $url = $this->baseUrl . ($this->basePath ?: '/');
        $res = $this->request('PROPFIND', $url, null, ['Depth: 0']);

        if (in_array($res['code'], [200, 204, 207], true)) {
            return [
                'success'     => true,
                'message'     => "Koneksi ke NAS WebDAV ({$url}) BERHASIL terhubung.",
                'free_space'  => null,
                'total_space' => null,
                'used_space'  => null,
            ];
        }

        // Jika server WebDAV menolak PROPFIND di root (HTTP 405), coba uji dengan OPTIONS
        if ($res['code'] === 405 || $res['code'] === 501) {
            $opt = $this->request('OPTIONS', $url);
            if (in_array($opt['code'], [200, 204], true)) {
                return [
                    'success'     => true,
                    'message'     => "Koneksi ke NAS WebDAV ({$url}) BERHASIL terhubung (OPTIONS OK).",
                    'free_space'  => null,
                    'total_space' => null,
                    'used_space'  => null,
                ];
            }
        }

        $detailMsg = $res['error'] ?: 'Akses ditolak atau direktori tidak ditemukan.';
        if ($res['code'] === 401) {
            $detailMsg = 'HTTP 401 Unauthorized (Autentikasi gagal: Username/Password salah, atau akun belum diizinkan mengakses WebDAV).';
        } elseif ($res['code'] === 404) {
            $detailMsg = 'HTTP 404 Not Found (Path direktori tidak ditemukan di server WebDAV).';
        } elseif ($res['code'] === 403) {
            $detailMsg = 'HTTP 403 Forbidden (Akses dilarang oleh server atau terhalang proteksi Cloudflare).';
        }

        return [
            'success'     => false,
            'message'     => "Gagal terhubung ke NAS WebDAV ({$url}): {$detailMsg}",
            'free_space'  => null,
            'total_space' => null,
            'used_space'  => null,
        ];
    }
}
