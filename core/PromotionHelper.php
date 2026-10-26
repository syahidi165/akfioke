<?php
// core/PromotionHelper.php
namespace Core;

class PromotionHelper {
    public const FIXED_LEVELS = [
        'KB'  => ['KB'],
        'TK'  => ['TK A', 'TK B'],
        'SD'  => ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'],
        'SMP' => ['Kelas 7', 'Kelas 8', 'Kelas 9'],
        'SMA' => ['Kelas 10', 'Kelas 11', 'Kelas 12'],
    ];

    public const PROMOTION_TARGETS = [
        'KB'       => 'TK A',
        'TK A'     => 'TK B',
        'TK B'     => 'LULUS',
        'Kelas 1'  => 'Kelas 2',
        'Kelas 2'  => 'Kelas 3',
        'Kelas 3'  => 'Kelas 4',
        'Kelas 4'  => 'Kelas 5',
        'Kelas 5'  => 'Kelas 6',
        'Kelas 6'  => 'LULUS',
        'Kelas 7'  => 'Kelas 8',
        'Kelas 8'  => 'Kelas 9',
        'Kelas 9'  => 'LULUS',
        'Kelas 10' => 'Kelas 11',
        'Kelas 11' => 'Kelas 12',
        'Kelas 12' => 'LULUS',
    ];

    /**
     * Normalisasi teks level ke format standar
     */
    public static function normalizeLevel(string $rawLevel): string {
        $trimmed = trim($rawLevel);
        return match (strtoupper($trimmed)) {
            'SD 1', '1', 'KELAS 1'  => 'Kelas 1',
            'SD 2', '2', 'KELAS 2'  => 'Kelas 2',
            'SD 3', '3', 'KELAS 3'  => 'Kelas 3',
            'SD 4', '4', 'KELAS 4'  => 'Kelas 4',
            'SD 5', '5', 'KELAS 5'  => 'Kelas 5',
            'SD 6', '6', 'KELAS 6'  => 'Kelas 6',
            'SMP 7', '7', 'KELAS 7' => 'Kelas 7',
            'SMP 8', '8', 'KELAS 8' => 'Kelas 8',
            'SMP 9', '9', 'KELAS 9' => 'Kelas 9',
            'SMA 10', '10', 'KELAS 10' => 'Kelas 10',
            'SMA 11', '11', 'KELAS 11' => 'Kelas 11',
            'SMA 12', '12', 'KELAS 12' => 'Kelas 12',
            'TK A', 'A'             => 'TK A',
            'TK B', 'B'             => 'TK B',
            'KB', 'PLAYGROUP', 'KELOMPOK BERMAIN' => 'KB',
            default                 => $trimmed
        };
    }

    /**
     * Dapatkan daftar tingkat tetap untuk jenjang unit tertentu
     */
    public static function getFixedLevelsForUnit(string $unitLevel): array {
        $key = strtoupper(trim($unitLevel));
        return self::FIXED_LEVELS[$key] ?? ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
    }

    /**
     * Validasi apakah perpindahan tahun ajaran memenuhi syarat kenaikan kelas:
     * Hanya jika dari Genap ke Ganjil pada tahun ajaran yang berbeda.
     */
    public static function isPromotionEligible(array $fromTa, array $toTa): bool {
        $fromSemester = trim($fromTa['semester'] ?? '');
        $toSemester = trim($toTa['semester'] ?? '');
        $fromName = trim($fromTa['name'] ?? '');
        $toName = trim($toTa['name'] ?? '');

        return ($fromSemester === 'Genap' && $toSemester === 'Ganjil' && $fromName !== $toName);
    }

    /**
     * Dapatkan perkiraan nama kelas baru saat naik tingkat
     */
    public static function getTargetClassName(string $currentClassName, string $currentLevel, string $targetLevel): string {
        $normCurrent = self::normalizeLevel($currentLevel);
        
        // Ganti nama jika mengandung level persis (contoh: "Kelas 1A" -> "Kelas 2A")
        if (stripos($currentClassName, $normCurrent) !== false) {
            return preg_replace('/' . preg_quote($normCurrent, '/') . '/i', $targetLevel, $currentClassName, 1);
        }

        // Coba ganti angka tingkat (contoh: "1A Abu Bakar" -> "2A Abu Bakar")
        $currNum = preg_replace('/[^0-9]/', '', $normCurrent);
        $targetNum = preg_replace('/[^0-9]/', '', $targetLevel);
        if ($currNum !== '' && $targetNum !== '') {
            $replaced = preg_replace('/\b' . $currNum . '\b/', $targetNum, $currentClassName, 1);
            if ($replaced !== $currentClassName) {
                return $replaced;
            }
        }

        // Fallback untuk TK/KB (contoh: "TK A Al-Fatihah" -> "TK B Al-Fatihah")
        if (str_starts_with($normCurrent, 'TK') && str_starts_with($targetLevel, 'TK')) {
            $replaced = preg_replace('/TK\s*[A-B]/i', $targetLevel, $currentClassName, 1);
            if ($replaced !== $currentClassName) {
                return $replaced;
            }
        }

        return $targetLevel . ' (' . $currentClassName . ')';
    }

    /**
     * Eksekusi kenaikan kelas otomatis atau manual per unit / seluruh unit
     * 
     * @param int $fromTaId Tahun ajaran asal (Genap)
     * @param int $toTaId Tahun ajaran tujuan (Ganjil tahun berikutnya)
     * @param int|null $unitId Unit spesifik (null jika semua unit)
     * @param array $retainedStudentIds ID siswa yang tinggal kelas
     * @return array Ringkasan hasil promosi
     */
    public static function executePromotion(int $fromTaId, int $toTaId, ?int $unitId = null, array $retainedStudentIds = []): array {
        $stats = [
            'promoted_count'  => 0,
            'graduated_count' => 0,
            'retained_count'  => 0,
            'classes_created' => 0
        ];

        // Ambil kelas-kelas di tahun ajaran asal
        $unitClause = $unitId ? " AND c.unit_id = {$unitId}" : "";
        $classes = Database::fetchAll(
            "SELECT c.*, u.name as unit_name, u.level as unit_level 
             FROM classes c 
             JOIN units u ON c.unit_id = u.id 
             WHERE c.academic_year_id = ? {$unitClause} 
             ORDER BY c.unit_id ASC, c.level ASC",
            [$fromTaId]
        );

        foreach ($classes as $cls) {
            $normLevel = self::normalizeLevel($cls['level']);
            $targetLevel = self::PROMOTION_TARGETS[$normLevel] ?? null;

            if (!$targetLevel) {
                continue; // Level tidak dikenal
            }

            // Ambil seluruh siswa aktif di rombel ini pada TA asal
            $students = Database::fetchAll(
                "SELECT s.id, s.nama, cs.id as cs_id 
                 FROM class_students cs 
                 JOIN students s ON cs.student_id = s.id 
                 WHERE cs.class_id = ? AND cs.academic_year_id = ? AND cs.status = 'Aktif'",
                [$cls['id'], $fromTaId]
            );

            if (empty($students)) {
                continue;
            }

            // KASUS 1: SISWA TINGKAT AKHIR -> LULUS
            if ($targetLevel === 'LULUS') {
                foreach ($students as $std) {
                    if (in_array((int)$std['id'], $retainedStudentIds, true)) {
                        // Siswa tinggal kelas di tingkat akhir
                        $stats['retained_count']++;
                        continue;
                    }

                    // Tandai lulus di data siswa & riwayat rombel
                    Database::execute("UPDATE students SET status = 'Lulus', updated_at = NOW() WHERE id = ?", [$std['id']]);
                    Database::execute("UPDATE class_students SET status = 'Lulus' WHERE id = ?", [$std['cs_id']]);
                    $stats['graduated_count']++;
                }
                continue;
            }

            // KASUS 2: SISWA NAIK KELAS KE TINGKAT BERIKUTNYA
            $targetClassName = self::getTargetClassName($cls['name'], $cls['level'], $targetLevel);

            // Cari atau buat kelas tujuan di TA baru
            $existingTargetClass = Database::fetch(
                "SELECT id FROM classes WHERE unit_id = ? AND academic_year_id = ? AND name = ?",
                [$cls['unit_id'], $toTaId, $targetClassName]
            );

            if ($existingTargetClass) {
                $targetClassId = (int)$existingTargetClass['id'];
            } else {
                // Buat rombel baru di tahun ajaran tujuan
                Database::execute(
                    "INSERT INTO classes (unit_id, academic_year_id, name, level, walas_id, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, NULL, NOW(), NOW())",
                    [$cls['unit_id'], $toTaId, $targetClassName, $targetLevel]
                );
                $targetClassId = (int)Database::lastInsertId();
                $stats['classes_created']++;
            }

            // Daftarkan siswa ke rombel baru di TA baru
            foreach ($students as $std) {
                $sId = (int)$std['id'];

                if (in_array($sId, $retainedStudentIds, true)) {
                    // Siswa tinggal kelas: masukkan ke kelas yang sama tingkatnya jika ada
                    Database::execute("UPDATE class_students SET status = 'Tinggal' WHERE id = ?", [$std['cs_id']]);
                    $stats['retained_count']++;
                    continue;
                }

                // Cek apakah siswa sudah terdaftar di TA tujuan
                $alreadyInTargetTa = Database::fetch(
                    "SELECT id FROM class_students WHERE student_id = ? AND academic_year_id = ?",
                    [$sId, $toTaId]
                );

                if (!$alreadyInTargetTa) {
                    Database::execute(
                        "INSERT INTO class_students (class_id, student_id, academic_year_id, status, created_at) 
                         VALUES (?, ?, ?, 'Aktif', NOW())",
                        [$targetClassId, $sId, $toTaId]
                    );
                    $stats['promoted_count']++;
                }
            }
        }

        return $stats;
    }
}
