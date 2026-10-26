<?php
// app/Controllers/JadwalController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;

class JadwalController extends Controller {

    protected array $allowedRoles = [
        'waka_kurikulum',
        'koor_tahfidz',
        'kepala_sekolah',
        'operator_sekolah',
        'deputi_pendidikan',
        'operator_yayasan',
        'guru_mapel',
        'guru_mapel_kbtk',
        'walas',
        'guru_tahfidz',
        'guru_supervisor'
    ];

    protected array $manageRoles = [
        'waka_kurikulum',
        'koor_tahfidz',
        'kepala_sekolah',
        'operator_sekolah',
        'operator_yayasan'
    ];

    /**
     * Tampilan Utama Tabel Jadwal Pelajaran Interaktif (Waka Kurikulum / Umum)
     */
    public function index(): void {
        $this->renderSchedule(false);
    }

    /**
     * Tampilan Khusus Koordinator Tahfidz: Fokus pada Jadwal Guru Tahfidz di Halaqah
     */
    public function tahfidz(): void {
        $this->renderSchedule(true);
    }

    /**
     * Core Render Engine untuk Tabel Jadwal (Mendukung Mode Umum & Mode Fokus Tahfidz)
     */
    protected function renderSchedule(bool $isTahfidzOnly = false): void {
        $this->requireRole($this->allowedRoles);
        
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $canManage = Auth::hasAnyRole($this->manageRoles);

        $viewMode = $_GET['view_mode'] ?? 'mingguan'; // mingguan | harian
        $selectedDay = $_GET['day'] ?? 'Senin';
        $filterTeacherId = !empty($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : null;

        // Ambil Data Unit & Tahun Ajaran
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        $ta = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);

        // Ambil Daftar Kelas di Unit & Tahun Ajaran ini (Grouped by Level)
        $classes = Database::fetchAll(
            "SELECT c.*, e.nama as walas_name 
             FROM classes c 
             LEFT JOIN employees e ON c.walas_id = e.id 
             WHERE c.unit_id = ? AND c.academic_year_id = ? 
             ORDER BY c.level ASC, c.name ASC",
            [$unitId, $taId]
        );

        // Group classes by level
        $classesByLevel = [];
        foreach ($classes as $c) {
            $levelKey = $c['level'] ?: 'Lainnya';
            $classesByLevel[$levelKey][] = $c;
        }

        // Ambil Daftar Mata Pelajaran
        $subjects = Database::fetchAll(
            "SELECT * FROM subjects WHERE unit_id = ? ORDER BY name ASC",
            [$unitId]
        );

        // Ambil Semua Guru di Unit Ini
        $teachers = Database::fetchAll(
            "SELECT id, nipy, nama, level_posisi 
             FROM employees 
             WHERE unit_id = ? AND is_deleted = 0 AND status = 'Aktif' 
             ORDER BY nama ASC",
            [$unitId]
        );

        // Ambil Khusus Guru Tahfidz di Unit Ini (untuk selector halaqah & filter radar)
        $tahfidzTeachers = Database::fetchAll(
            "SELECT DISTINCT e.id, e.nipy, e.nama, e.level_posisi 
             FROM employees e 
             LEFT JOIN teacher_subjects ts ON ts.employee_id = e.id 
             LEFT JOIN subjects s ON ts.subject_id = s.id 
             LEFT JOIN users u ON e.user_id = u.id 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE e.unit_id = ? AND e.is_deleted = 0 AND e.status = 'Aktif' 
               AND (e.level_posisi LIKE '%Tahfidz%' OR s.type IN ('Tahfidz', 'Tahsin') OR r.name IN ('guru_tahfidz', 'koor_tahfidz'))
             ORDER BY e.nama ASC",
            [$unitId]
        );
        // Fallback jika belum ada mapping khusus, gunakan semua guru
        if (empty($tahfidzTeachers)) {
            $tahfidzTeachers = $teachers;
        }

        // Ambil Mapping Guru-Mapel dari Operator Sekolah (teacher_subjects)
        $teacherSubjectsRaw = Database::fetchAll(
            "SELECT ts.employee_id, ts.subject_id, ts.class_id, e.nama as teacher_name, s.name as subject_name
             FROM teacher_subjects ts
             JOIN employees e ON ts.employee_id = e.id
             JOIN subjects s ON ts.subject_id = s.id
             JOIN classes c ON ts.class_id = c.id
             WHERE c.unit_id = ? AND ts.academic_year_id = ?",
            [$unitId, $taId]
        );

        // Buat map subject_id -> [teachers]
        $subjectTeachersMap = [];
        foreach ($teacherSubjectsRaw as $ts) {
            $subjectTeachersMap[$ts['subject_id']][$ts['employee_id']] = [
                'id' => $ts['employee_id'],
                'nama' => $ts['teacher_name']
            ];
        }

        // Ambil Time Slots
        $timeSlotsRaw = Database::fetchAll(
            "SELECT * FROM schedule_time_slots 
             WHERE unit_id = ? AND academic_year_id = ? 
             ORDER BY day ASC, jam_ke ASC",
            [$unitId, $taId]
        );

        $timeSlotsByDay = $this->buildTimeSlots($timeSlotsRaw);

        // Ambil Semua Jadwal Pelajaran
        $schedulesRaw = Database::fetchAll(
            "SELECT cs.*, 
                    s.name as subject_name, s.code as subject_code, s.type as subject_type,
                    t.nama as teacher_name, t.nipy as teacher_nipy,
                    sub_t.nama as substitute_teacher_name, sub_t.nipy as substitute_teacher_nipy,
                    c.name as class_name, c.level as class_level
             FROM class_schedules cs
             LEFT JOIN subjects s ON cs.subject_id = s.id
             LEFT JOIN employees t ON cs.teacher_id = t.id
             LEFT JOIN employees sub_t ON cs.substitute_teacher_id = sub_t.id
             JOIN classes c ON cs.class_id = c.id
             WHERE cs.unit_id = ? AND cs.academic_year_id = ?",
            [$unitId, $taId]
        );

        // Ambil Rincian Halaqah dari class_schedule_halaqahs untuk seluruh jadwal
        $scheduleIds = array_column($schedulesRaw, 'id');
        $halaqahsBySchedule = [];
        if (!empty($scheduleIds)) {
            $inPlaceholders = implode(',', array_fill(0, count($scheduleIds), '?'));
            $halaqahsRaw = Database::fetchAll(
                "SELECT csh.*, 
                        t.nama as teacher_name, t.nipy as teacher_nipy,
                        sub_t.nama as substitute_teacher_name, sub_t.nipy as substitute_teacher_nipy
                 FROM class_schedule_halaqahs csh
                 LEFT JOIN employees t ON csh.teacher_id = t.id
                 LEFT JOIN employees sub_t ON csh.substitute_teacher_id = sub_t.id
                 WHERE csh.schedule_id IN ({$inPlaceholders})
                 ORDER BY csh.schedule_id ASC, csh.halaqah_number ASC",
                $scheduleIds
            );
            foreach ($halaqahsRaw as $h) {
                $halaqahsBySchedule[$h['schedule_id']][] = $h;
            }
        }

        // Map schedules by day, jam_ke, class_id
        // and Detect Conflicts (Mencakup Guru Halaqah Maupun Guru Mapel Biasa)
        $scheduleMatrix = [];
        $teacherSlotBookings = []; // [$day][$jam_ke][$teacherId] => [classes/halaqahs]
        $conflicts = [];
        $substitutions = [];
        $totalHalaqahCount = 0;
        $tahfidzTeacherIdsActive = [];

        foreach ($schedulesRaw as &$s) {
            $day = $s['day'];
            $jam = (int)$s['jam_ke'];
            $cId = (int)$s['class_id'];
            $sId = (int)$s['id'];
            
            // Pasang list halaqah ke dalam row jadwal
            $s['halaqahs'] = $halaqahsBySchedule[$sId] ?? [];

            $scheduleMatrix[$day][$jam][$cId] = $s;

            if ($s['session_type'] === 'pelajaran') {
                if (!empty($s['halaqahs'])) {
                    // Sesi Halaqah Tahfidz (Bisa sampai 3 halaqah dalam 1 kelas)
                    foreach ($s['halaqahs'] as $h) {
                        $totalHalaqahCount++;
                        $hActiveTId = !empty($h['substitute_teacher_id']) ? (int)$h['substitute_teacher_id'] : (int)$h['teacher_id'];
                        if ($hActiveTId > 0) {
                            $tahfidzTeacherIdsActive[$hActiveTId] = true;
                            $teacherSlotBookings[$day][$jam][$hActiveTId][] = [
                                'class_id' => $cId,
                                'class_name' => $s['class_name'],
                                'subject_name' => $s['subject_name'],
                                'halaqah_number' => $h['halaqah_number'],
                                'halaqah_name' => $h['halaqah_name'],
                                'teacher_name' => !empty($h['substitute_teacher_id']) ? $h['substitute_teacher_name'] : $h['teacher_name'],
                                'is_substitute' => !empty($h['substitute_teacher_id']),
                                'is_tahfidz' => true
                            ];
                        }
                        if (!empty($h['substitute_teacher_id'])) {
                            $substitutions[] = [
                                'schedule_id' => $sId,
                                'halaqah_number' => $h['halaqah_number'],
                                'halaqah_name' => $h['halaqah_name'],
                                'class_name' => $s['class_name'],
                                'day' => $day,
                                'jam_ke' => $jam,
                                'original_teacher_name' => $h['teacher_name'],
                                'substitute_teacher_name' => $h['substitute_teacher_name'],
                                'substitute_reason' => $h['substitute_reason'] ?: 'Izin / Sakit',
                                'substitute_date' => $h['substitute_date']
                            ];
                        }
                    }
                } else {
                    // Sesi Mapel Reguler (1 Guru)
                    $teacherId = !empty($s['substitute_teacher_id']) ? (int)$s['substitute_teacher_id'] : (!empty($s['teacher_id']) ? (int)$s['teacher_id'] : null);
                    if ($teacherId) {
                        $teacherSlotBookings[$day][$jam][$teacherId][] = [
                            'class_id' => $cId,
                            'class_name' => $s['class_name'],
                            'subject_name' => $s['subject_name'],
                            'halaqah_number' => null,
                            'halaqah_name' => null,
                            'teacher_name' => !empty($s['substitute_teacher_id']) ? $s['substitute_teacher_name'] : $s['teacher_name'],
                            'is_substitute' => !empty($s['substitute_teacher_id']),
                            'is_tahfidz' => false
                        ];
                    }
                    if (!empty($s['substitute_teacher_id'])) {
                        $substitutions[] = [
                            'schedule_id' => $sId,
                            'halaqah_number' => null,
                            'halaqah_name' => null,
                            'class_name' => $s['class_name'],
                            'day' => $day,
                            'jam_ke' => $jam,
                            'original_teacher_name' => $s['teacher_name'],
                            'substitute_teacher_name' => $s['substitute_teacher_name'],
                            'substitute_reason' => $s['substitute_reason'] ?: 'Izin / Sakit',
                            'substitute_date' => $s['substitute_date']
                        ];
                    }
                }
            }
        }
        unset($s);

        // Deteksi bentrok (jika 1 guru terplot di >1 kelas/halaqah pada hari & jam yang sama)
        $conflictCells = [];
        $tahfidzConflicts = [];
        foreach ($teacherSlotBookings as $day => $jamGroup) {
            foreach ($jamGroup as $jam => $teacherGroup) {
                foreach ($teacherGroup as $tId => $classList) {
                    if (count($classList) > 1) {
                        $classDescriptions = [];
                        $hasTahfidzInvolved = false;
                        foreach ($classList as $cl) {
                            $desc = $cl['class_name'];
                            if (!empty($cl['halaqah_name'])) {
                                $desc .= " (" . $cl['halaqah_name'] . ")";
                                $hasTahfidzInvolved = true;
                            }
                            $classDescriptions[] = $desc;
                        }
                        $confObj = [
                            'day' => $day,
                            'jam_ke' => $jam,
                            'teacher_id' => $tId,
                            'teacher_name' => $classList[0]['teacher_name'],
                            'classes' => $classList,
                            'is_tahfidz' => $hasTahfidzInvolved,
                            'description' => "Guru " . $classList[0]['teacher_name'] . " dijadwalkan bersamaan di: " . implode(', ', $classDescriptions) . " pada Hari {$day}, Jam Ke-{$jam}"
                        ];
                        $conflicts[] = $confObj;
                        if ($hasTahfidzInvolved) {
                            $tahfidzConflicts[] = $confObj;
                        }
                        foreach ($classList as $cl) {
                            $conflictCells["{$day}_{$jam}_{$cl['class_id']}"] = $classList;
                        }
                    }
                }
            }
        }

        // Days to display
        $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $this->view('jadwal/index', [
            'pageTitle'               => $isTahfidzOnly ? 'Tabel Jadwal Halaqah Guru Tahfidz' : 'Tabel Jadwal Pelajaran Kelas',
            'unit'                    => $unit,
            'ta'                      => $ta,
            'classes'                 => $classes,
            'classesByLevel'          => $classesByLevel,
            'subjects'                => $subjects,
            'teachers'                => $teachers,
            'tahfidzTeachers'         => $tahfidzTeachers,
            'subjectTeachersMap'      => $subjectTeachersMap,
            'timeSlotsByDay'          => $timeSlotsByDay,
            'scheduleMatrix'          => $scheduleMatrix,
            'conflicts'               => $conflicts,
            'tahfidzConflicts'        => $tahfidzConflicts,
            'conflictCells'           => $conflictCells,
            'substitutions'           => $substitutions,
            'viewMode'                => $viewMode,
            'selectedDay'             => $selectedDay,
            'filterTeacherId'         => $filterTeacherId,
            'daysList'                => $daysList,
            'canManage'               => $canManage,
            'isTahfidzOnly'           => $isTahfidzOnly,
            'totalHalaqahCount'       => $totalHalaqahCount,
            'activeTahfidzTeacherCount' => count($tahfidzTeacherIdsActive),
            'returnRoute'             => $isTahfidzOnly ? 'tahfidz' : 'kelas'
        ]);
    }

    /**
     * Simpan / Tambah / Update Jadwal Pelajaran per Sel (Mendukung Struktur 1-3 Halaqah Tahfidz)
     */
    public function save(): void {
        $this->requireRole($this->manageRoles);

        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $classId = (int)($_POST['class_id'] ?? 0);
        $day = trim($_POST['day'] ?? '');
        $jamKe = (int)($_POST['jam_ke'] ?? 0);
        $timeStart = trim($_POST['time_start'] ?? '07.15');
        $timeEnd = trim($_POST['time_end'] ?? '07.55');
        $subjectId = !empty($_POST['subject_id']) ? (int)$_POST['subject_id'] : null;
        $teacherId = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;
        $sessionType = trim($_POST['session_type'] ?? 'pelajaran');
        $customLabel = trim($_POST['custom_label'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Parameter Struktur Halaqah Tahfidz
        $isHalaqah = !empty($_POST['is_halaqah']);
        $halaqahCount = max(1, min(3, (int)($_POST['halaqah_count'] ?? 1)));
        $halaqahTeachers = $_POST['halaqah_teachers'] ?? [];
        $halaqahNames = $_POST['halaqah_names'] ?? [];

        $returnRoute = $_POST['return_route'] ?? 'kelas';
        $viewMode = $_POST['return_view_mode'] ?? 'mingguan';
        $redirectUrl = Auth::baseUrl() . '/' . ($returnRoute === 'tahfidz' ? 'jadwal/tahfidz' : 'jadwal/kelas') . '?view_mode=' . urlencode($viewMode) . '&day=' . urlencode($day);

        if (!$classId || empty($day) || $jamKe < 0) {
            $this->setFlash('error', 'Parameter jadwal tidak lengkap.');
            $this->redirect($redirectUrl);
            return;
        }

        // Jika mode halaqah, gunakan guru halaqah 1 sebagai teacher_id fallback utama di tabel class_schedules
        if ($isHalaqah && !empty($halaqahTeachers[1])) {
            $teacherId = (int)$halaqahTeachers[1];
        }

        // Cek apakah sel sudah ada
        $existingCell = Database::fetch(
            "SELECT * FROM class_schedules WHERE unit_id = ? AND academic_year_id = ? AND class_id = ? AND day = ? AND jam_ke = ?",
            [$unitId, $taId, $classId, $day, $jamKe]
        );

        $scheduleId = null;

        if ($existingCell) {
            $scheduleId = (int)$existingCell['id'];
            Database::execute(
                "UPDATE class_schedules 
                 SET time_start = ?, time_end = ?, subject_id = ?, teacher_id = ?, session_type = ?, custom_label = ?, notes = ?, updated_at = NOW() 
                 WHERE id = ?",
                [$timeStart, $timeEnd, $subjectId, $teacherId, $sessionType, $customLabel ?: null, $notes ?: null, $scheduleId]
            );
            AuditLog::log('UPDATE_SCHEDULE', 'class_schedules', $scheduleId, $existingCell, [
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId,
                'is_halaqah' => $isHalaqah,
                'halaqah_count' => $halaqahCount
            ]);
        } else {
            Database::execute(
                "INSERT INTO class_schedules 
                 (unit_id, academic_year_id, class_id, day, jam_ke, time_start, time_end, subject_id, teacher_id, session_type, custom_label, notes, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [$unitId, $taId, $classId, $day, $jamKe, $timeStart, $timeEnd, $subjectId, $teacherId, $sessionType, $customLabel ?: null, $notes ?: null]
            );
            $scheduleId = (int)Database::lastInsertId();
            AuditLog::log('CREATE_SCHEDULE', 'class_schedules', $scheduleId, null, [
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId,
                'is_halaqah' => $isHalaqah,
                'halaqah_count' => $halaqahCount
            ]);
        }

        // Sinkronisasi class_schedule_halaqahs
        if ($scheduleId) {
            if ($isHalaqah && $sessionType === 'pelajaran') {
                // Hapus halaqah yang melebihi jumlah baru (misal sebelumnya 3 sekarang 2)
                Database::execute("DELETE FROM class_schedule_halaqahs WHERE schedule_id = ? AND halaqah_number > ?", [$scheduleId, $halaqahCount]);

                for ($num = 1; $num <= $halaqahCount; $num++) {
                    $hTeacher = !empty($halaqahTeachers[$num]) ? (int)$halaqahTeachers[$num] : 0;
                    $hName = !empty($halaqahNames[$num]) ? trim($halaqahNames[$num]) : "Halaqah {$num}";

                    if ($hTeacher > 0) {
                        $existingH = Database::fetch("SELECT id FROM class_schedule_halaqahs WHERE schedule_id = ? AND halaqah_number = ?", [$scheduleId, $num]);
                        if ($existingH) {
                            Database::execute(
                                "UPDATE class_schedule_halaqahs SET halaqah_name = ?, teacher_id = ?, updated_at = NOW() WHERE id = ?",
                                [$hName, $hTeacher, $existingH['id']]
                            );
                        } else {
                            Database::execute(
                                "INSERT INTO class_schedule_halaqahs (schedule_id, halaqah_number, halaqah_name, teacher_id, created_at) VALUES (?, ?, ?, ?, NOW())",
                                [$scheduleId, $num, $hName, $hTeacher]
                            );
                        }
                    }
                }
            } else {
                // Jika mapel non-tahfidz atau bukan pelajaran biasa, bersihkan record halaqah lama
                Database::execute("DELETE FROM class_schedule_halaqahs WHERE schedule_id = ?", [$scheduleId]);
            }
        }

        $this->setFlash('success', 'Jadwal pelajaran' . ($isHalaqah ? ' dengan struktur halaqah tahfidz' : '') . ' berhasil disimpan.');
        $this->redirect($redirectUrl);
    }

    /**
     * Hapus / Kosongkan Jadwal di Sel Tertentu
     */
    public function delete(): void {
        $this->requireRole($this->manageRoles);

        $id = (int)($_POST['id'] ?? 0);
        $day = trim($_POST['day'] ?? 'Senin');
        $viewMode = $_POST['view_mode'] ?? 'mingguan';
        $returnRoute = $_POST['return_route'] ?? 'kelas';

        if ($id > 0) {
            $cell = Database::fetch("SELECT * FROM class_schedules WHERE id = ?", [$id]);
            if ($cell) {
                Database::execute("DELETE FROM class_schedules WHERE id = ?", [$id]);
                AuditLog::log('DELETE_SCHEDULE', 'class_schedules', $id, $cell, null);
                $this->setFlash('success', 'Jadwal pada sel tersebut berhasil dikosongkan.');
            }
        }

        $this->redirect(Auth::baseUrl() . '/' . ($returnRoute === 'tahfidz' ? 'jadwal/tahfidz' : 'jadwal/kelas') . '?view_mode=' . urlencode($viewMode) . '&day=' . urlencode($day));
    }

    /**
     * Menugaskan Guru Pengganti (Inval / Guru Badal) - Mendukung Inval per Halaqah Tertentu
     */
    public function substitute(): void {
        $this->requireRole($this->manageRoles);

        $id = (int)($_POST['schedule_id'] ?? 0);
        $halaqahNumber = !empty($_POST['halaqah_number']) ? (int)$_POST['halaqah_number'] : 0;
        $substituteTeacherId = (int)($_POST['substitute_teacher_id'] ?? 0);
        $substituteDate = trim($_POST['substitute_date'] ?? date('Y-m-d'));
        $substituteReason = trim($_POST['substitute_reason'] ?? 'Izin / Sakit');
        $substituteNote = trim($_POST['substitute_note'] ?? '');
        $day = trim($_POST['day'] ?? 'Senin');
        $viewMode = $_POST['view_mode'] ?? 'mingguan';
        $returnRoute = $_POST['return_route'] ?? 'kelas';
        $redirectUrl = Auth::baseUrl() . '/' . ($returnRoute === 'tahfidz' ? 'jadwal/tahfidz' : 'jadwal/kelas') . '?view_mode=' . urlencode($viewMode) . '&day=' . urlencode($day);

        if (!$id || !$substituteTeacherId) {
            $this->setFlash('error', 'Silakan pilih guru pengganti yang valid.');
            $this->redirect($redirectUrl);
            return;
        }

        $schedule = Database::fetch("SELECT * FROM class_schedules WHERE id = ?", [$id]);
        if (!$schedule) {
            $this->setFlash('error', 'Data jadwal tidak ditemukan.');
            $this->redirect($redirectUrl);
            return;
        }

        $subTeacher = Database::fetch("SELECT nama FROM employees WHERE id = ?", [$substituteTeacherId]);
        $teacherName = $subTeacher['nama'] ?? 'Guru Pengganti';

        if ($halaqahNumber > 0) {
            // Penugasan Inval Khusus Halaqah Tertentu
            $halaqah = Database::fetch("SELECT * FROM class_schedule_halaqahs WHERE schedule_id = ? AND halaqah_number = ?", [$id, $halaqahNumber]);
            if ($halaqah) {
                Database::execute(
                    "UPDATE class_schedule_halaqahs 
                     SET substitute_teacher_id = ?, substitute_date = ?, substitute_reason = ?, substitute_note = ?, updated_at = NOW() 
                     WHERE id = ?",
                    [$substituteTeacherId, $substituteDate, $substituteReason, $substituteNote, $halaqah['id']]
                );

                AuditLog::log('ASSIGN_HALAQAH_SUBSTITUTE', 'class_schedule_halaqahs', $halaqah['id'], $halaqah, [
                    'substitute_teacher_id' => $substituteTeacherId,
                    'halaqah_number' => $halaqahNumber
                ]);

                $this->setFlash('success', "Berhasil menugaskan {$teacherName} sebagai Guru Pengganti (Inval) pada {$halaqah['halaqah_name']}.");
            }
        } else {
            // Penugasan Inval Jadwal Mapel Biasa
            Database::execute(
                "UPDATE class_schedules 
                 SET substitute_teacher_id = ?, substitute_date = ?, substitute_reason = ?, substitute_note = ?, updated_at = NOW() 
                 WHERE id = ?",
                [$substituteTeacherId, $substituteDate, $substituteReason, $substituteNote, $id]
            );

            AuditLog::log('ASSIGN_SUBSTITUTE_TEACHER', 'class_schedules', $id, $schedule, [
                'substitute_teacher_id' => $substituteTeacherId,
                'substitute_date' => $substituteDate,
                'substitute_reason' => $substituteReason
            ]);

            $this->setFlash('success', "Berhasil menugaskan {$teacherName} sebagai Guru Pengganti (Inval).");
        }

        $this->redirect($redirectUrl);
    }

    /**
     * Hapus Guru Pengganti (Kembali ke Guru Asal / Halaqah Asal)
     */
    public function removeSubstitute(): void {
        $this->requireRole($this->manageRoles);

        $id = (int)($_POST['schedule_id'] ?? 0);
        $halaqahNumber = !empty($_POST['halaqah_number']) ? (int)$_POST['halaqah_number'] : 0;
        $day = trim($_POST['day'] ?? 'Senin');
        $viewMode = $_POST['view_mode'] ?? 'mingguan';
        $returnRoute = $_POST['return_route'] ?? 'kelas';
        $redirectUrl = Auth::baseUrl() . '/' . ($returnRoute === 'tahfidz' ? 'jadwal/tahfidz' : 'jadwal/kelas') . '?view_mode=' . urlencode($viewMode) . '&day=' . urlencode($day);

        if ($id > 0) {
            if ($halaqahNumber > 0) {
                Database::execute(
                    "UPDATE class_schedule_halaqahs 
                     SET substitute_teacher_id = NULL, substitute_date = NULL, substitute_reason = NULL, substitute_note = NULL, updated_at = NOW() 
                     WHERE schedule_id = ? AND halaqah_number = ?",
                    [$id, $halaqahNumber]
                );
                $this->setFlash('success', "Status inval pada Halaqah Ke-{$halaqahNumber} dibatalkan. Mengembalikan ke guru asal.");
            } else {
                Database::execute(
                    "UPDATE class_schedules 
                     SET substitute_teacher_id = NULL, substitute_date = NULL, substitute_reason = NULL, substitute_note = NULL, updated_at = NOW() 
                     WHERE id = ?",
                    [$id]
                );
                $this->setFlash('success', 'Status guru pengganti dibatalkan. Mengembalikan ke guru asal.');
            }
        }

        $this->redirect($redirectUrl);
    }

    /**
     * API Status Guru: Siapa yang Sedang Mengajar (termasuk di halaqah) dan Siapa yang FREE
     */
    public function apiTeacherStatus(): void {
        $this->requireRole($this->allowedRoles);

        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $day = trim($_GET['day'] ?? 'Senin');
        $jamKe = (int)($_GET['jam_ke'] ?? 1);
        $isTahfidzOnly = !empty($_GET['is_tahfidz_only']);

        // Ambil daftar guru (bisa difilter khusus Guru Tahfidz jika radar dijalankan dari konteks tahfidz)
        if ($isTahfidzOnly) {
            $allTeachers = Database::fetchAll(
                "SELECT DISTINCT e.id, e.nipy, e.nama, e.level_posisi 
                 FROM employees e 
                 LEFT JOIN teacher_subjects ts ON ts.employee_id = e.id 
                 LEFT JOIN subjects s ON ts.subject_id = s.id 
                 LEFT JOIN users u ON e.user_id = u.id 
                 LEFT JOIN user_roles ur ON u.id = ur.user_id 
                 LEFT JOIN roles r ON ur.role_id = r.id 
                 WHERE e.unit_id = ? AND e.is_deleted = 0 AND e.status = 'Aktif' 
                   AND (e.level_posisi LIKE '%Tahfidz%' OR s.type IN ('Tahfidz', 'Tahsin') OR r.name IN ('guru_tahfidz', 'koor_tahfidz'))
                 ORDER BY e.nama ASC",
                [$unitId]
            );
        } else {
            $allTeachers = Database::fetchAll(
                "SELECT id, nipy, nama, level_posisi 
                 FROM employees 
                 WHERE unit_id = ? AND is_deleted = 0 AND status = 'Aktif' 
                 ORDER BY nama ASC",
                [$unitId]
            );
        }

        // Ambil seluruh sesi KBM di jam & hari ini
        $busyRows = Database::fetchAll(
            "SELECT cs.id, cs.teacher_id, cs.substitute_teacher_id, cs.class_id, c.name as class_name, s.name as subject_name,
                    t.nama as teacher_name, sub_t.nama as substitute_teacher_name
             FROM class_schedules cs
             JOIN classes c ON cs.class_id = c.id
             LEFT JOIN subjects s ON cs.subject_id = s.id
             LEFT JOIN employees t ON cs.teacher_id = t.id
             LEFT JOIN employees sub_t ON cs.substitute_teacher_id = sub_t.id
             WHERE cs.unit_id = ? AND cs.academic_year_id = ? AND cs.day = ? AND cs.jam_ke = ? AND cs.session_type = 'pelajaran'",
            [$unitId, $taId, $day, $jamKe]
        );

        // Ambil juga jika ada halaqahs di slot ini
        $schedIds = array_column($busyRows, 'id');
        $halaqahRows = [];
        if (!empty($schedIds)) {
            $inPlaceholders = implode(',', array_fill(0, count($schedIds), '?'));
            $halaqahRows = Database::fetchAll(
                "SELECT csh.*, cs.class_id, c.name as class_name, s.name as subject_name,
                        t.nama as teacher_name, sub_t.nama as substitute_teacher_name
                 FROM class_schedule_halaqahs csh
                 JOIN class_schedules cs ON csh.schedule_id = cs.id
                 JOIN classes c ON cs.class_id = c.id
                 LEFT JOIN subjects s ON cs.subject_id = s.id
                 LEFT JOIN employees t ON csh.teacher_id = t.id
                 LEFT JOIN employees sub_t ON csh.substitute_teacher_id = sub_t.id
                 WHERE csh.schedule_id IN ({$inPlaceholders})",
                $schedIds
            );
        }

        $busyMap = [];
        $schedIdsWithHalaqah = [];

        // Catat guru yang mengajar di halaqah
        foreach ($halaqahRows as $h) {
            $schedIdsWithHalaqah[$h['schedule_id']] = true;
            $activeTeacherId = !empty($h['substitute_teacher_id']) ? (int)$h['substitute_teacher_id'] : (int)$h['teacher_id'];
            if ($activeTeacherId > 0) {
                $busyMap[$activeTeacherId][] = [
                    'class_id' => (int)$h['class_id'],
                    'class_name' => $h['class_name'] . ' (' . $h['halaqah_name'] . ')',
                    'subject_name' => $h['subject_name'] ?? 'Tahfidz',
                    'is_substitute' => !empty($h['substitute_teacher_id']),
                    'original_teacher_name' => $h['teacher_name'] ?? ''
                ];
            }
        }

        // Catat guru yang mengajar di kelas reguler (non-halaqah)
        foreach ($busyRows as $row) {
            if (!isset($schedIdsWithHalaqah[$row['id']])) {
                $activeTeacherId = !empty($row['substitute_teacher_id']) ? (int)$row['substitute_teacher_id'] : (int)$row['teacher_id'];
                if ($activeTeacherId > 0) {
                    $busyMap[$activeTeacherId][] = [
                        'class_id' => (int)$row['class_id'],
                        'class_name' => $row['class_name'],
                        'subject_name' => $row['subject_name'] ?? 'Mata Pelajaran',
                        'is_substitute' => !empty($row['substitute_teacher_id']),
                        'original_teacher_name' => $row['teacher_name'] ?? ''
                    ];
                }
            }
        }

        $freeTeachers = [];
        $busyTeachers = [];

        foreach ($allTeachers as $t) {
            $tId = (int)$t['id'];
            if (isset($busyMap[$tId])) {
                $busyTeachers[] = [
                    'id' => $tId,
                    'nama' => $t['nama'],
                    'nipy' => $t['nipy'],
                    'position' => $t['level_posisi'],
                    'assignments' => $busyMap[$tId]
                ];
            } else {
                $freeTeachers[] = [
                    'id' => $tId,
                    'nama' => $t['nama'],
                    'nipy' => $t['nipy'],
                    'position' => $t['level_posisi']
                ];
            }
        }

        $this->json([
            'status' => 'success',
            'day' => $day,
            'jam_ke' => $jamKe,
            'free_count' => count($freeTeachers),
            'busy_count' => count($busyTeachers),
            'free_teachers' => $freeTeachers,
            'busy_teachers' => $busyTeachers
        ]);
    }

    /**
     * Tampilan Cetak / Print Jadwal Pelajaran (Format Mirip Lampiran)
     */
    public function printJadwal(): void {
        $this->requireRole($this->allowedRoles);

        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $selectedDay = $_GET['day'] ?? null; // jika null, cetak seluruh minggu

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        $ta = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);

        $classes = Database::fetchAll(
            "SELECT * FROM classes WHERE unit_id = ? AND academic_year_id = ? ORDER BY level ASC, name ASC",
            [$unitId, $taId]
        );

        $classesByLevel = [];
        foreach ($classes as $c) {
            $levelKey = $c['level'] ?: 'Lainnya';
            $classesByLevel[$levelKey][] = $c;
        }

        $timeSlotsRaw = Database::fetchAll(
            "SELECT * FROM schedule_time_slots WHERE unit_id = ? AND academic_year_id = ? ORDER BY day ASC, jam_ke ASC",
            [$unitId, $taId]
        );
        $timeSlotsByDay = $this->buildTimeSlots($timeSlotsRaw);

        $schedulesRaw = Database::fetchAll(
            "SELECT cs.*, 
                    s.name as subject_name, s.code as subject_code,
                    t.nama as teacher_name,
                    sub_t.nama as substitute_teacher_name,
                    c.name as class_name
             FROM class_schedules cs
             LEFT JOIN subjects s ON cs.subject_id = s.id
             LEFT JOIN employees t ON cs.teacher_id = t.id
             LEFT JOIN employees sub_t ON cs.substitute_teacher_id = sub_t.id
             JOIN classes c ON cs.class_id = c.id
             WHERE cs.unit_id = ? AND cs.academic_year_id = ?",
            [$unitId, $taId]
        );

        $scheduleMatrix = [];
        foreach ($schedulesRaw as $s) {
            $scheduleMatrix[$s['day']][(int)$s['jam_ke']][(int)$s['class_id']] = $s;
        }

        $daysList = $selectedDay ? [$selectedDay] : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $this->view('jadwal/print', [
            'unit'           => $unit,
            'ta'             => $ta,
            'classes'        => $classes,
            'classesByLevel' => $classesByLevel,
            'timeSlotsByDay' => $timeSlotsByDay,
            'scheduleMatrix' => $scheduleMatrix,
            'daysList'       => $daysList,
            'selectedDay'    => $selectedDay
        ], 'layouts/plain');
    }

    /**
     * Helper: Build Time Slots with Fallbacks
     */
    protected function buildTimeSlots(array $slotsRaw): array {
        $result = [];
        foreach ($slotsRaw as $sl) {
            $result[$sl['day']][] = $sl;
        }

        $defaultDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $defaultSlots = [
            ['jam_ke' => 0, 'time_start' => '07.15', 'time_end' => '07.45', 'label' => 'Pembiasaan Pagi / Upacara', 'is_break' => 1, 'break_type' => 'upacara'],
            ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
            ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
            ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
            ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Dhuha', 'is_break' => 1, 'break_type' => 'istirahat'],
            ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
            ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
            ['jam_ke' => 7, 'time_start' => '11.10', 'time_end' => '11.45', 'label' => 'Jam Ke-6', 'is_break' => 0, 'break_type' => 'none'],
            ['jam_ke' => 8, 'time_start' => '11.45', 'time_end' => '12.45', 'label' => 'Istirahat II & Dzuhur', 'is_break' => 1, 'break_type' => 'sholat'],
            ['jam_ke' => 9, 'time_start' => '12.45', 'time_end' => '13.20', 'label' => 'Jam Ke-7', 'is_break' => 0, 'break_type' => 'none'],
            ['jam_ke' => 10, 'time_start' => '13.20', 'time_end' => '13.55', 'label' => 'Jam Ke-8', 'is_break' => 0, 'break_type' => 'none'],
        ];

        foreach ($defaultDays as $d) {
            if (empty($result[$d])) {
                $result[$d] = $defaultSlots;
            }
        }

        return $result;
    }

    /**
     * Simpan / Perbarui Jam Pelajaran (Time Slot)
     */
    public function saveSlot(): void {
        $this->requireRole($this->manageRoles);

        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $day = trim($_POST['day'] ?? 'Senin');
        $jamKe = (int)($_POST['jam_ke'] ?? 1);
        $timeStart = trim($_POST['time_start'] ?? '07.15');
        $timeEnd = trim($_POST['time_end'] ?? '07.55');
        $label = trim($_POST['label'] ?? '');
        $isBreak = !empty($_POST['is_break']) ? 1 : 0;
        $breakType = trim($_POST['break_type'] ?? 'none');
        $applyAllDays = !empty($_POST['apply_all_days']);
        $viewMode = $_POST['view_mode'] ?? 'mingguan';

        // Standardize format: allow both 07:15 or 07.15
        $timeStart = str_replace(':', '.', $timeStart);
        $timeEnd = str_replace(':', '.', $timeEnd);

        $daysToUpdate = $applyAllDays ? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] : [$day];

        foreach ($daysToUpdate as $d) {
            $existing = Database::fetch(
                "SELECT * FROM schedule_time_slots WHERE unit_id = ? AND academic_year_id = ? AND day = ? AND jam_ke = ?",
                [$unitId, $taId, $d, $jamKe]
            );

            if ($existing) {
                Database::execute(
                    "UPDATE schedule_time_slots 
                     SET time_start = ?, time_end = ?, label = ?, is_break = ?, break_type = ? 
                     WHERE id = ?",
                    [$timeStart, $timeEnd, $label ?: null, $isBreak, $breakType, $existing['id']]
                );
            } else {
                Database::execute(
                    "INSERT INTO schedule_time_slots 
                     (unit_id, academic_year_id, day, jam_ke, time_start, time_end, label, is_break, break_type) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$unitId, $taId, $d, $jamKe, $timeStart, $timeEnd, $label ?: null, $isBreak, $breakType]
                );
            }

            // Sync waktu pada class_schedules yang sudah ada di slot ini
            Database::execute(
                "UPDATE class_schedules 
                 SET time_start = ?, time_end = ? 
                 WHERE unit_id = ? AND academic_year_id = ? AND day = ? AND jam_ke = ?",
                [$timeStart, $timeEnd, $unitId, $taId, $d, $jamKe]
            );
        }

        AuditLog::log('UPDATE_SCHEDULE_TIME_SLOT', 'schedule_time_slots', null, null, [
            'jam_ke' => $jamKe,
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
            'days' => $daysToUpdate
        ]);

        $msg = "Waktu Jam Ke-{$jamKe} ({$timeStart} - {$timeEnd}) berhasil diperbarui" . ($applyAllDays ? " untuk semua hari (Senin - Jum'at)." : " untuk hari {$day}.");
        $this->setFlash('success', $msg);
        $this->redirect(Auth::baseUrl() . '/jadwal/kelas?view_mode=' . urlencode($viewMode) . '&day=' . urlencode($day));
    }

    /**
     * Hapus Jam Pelajaran (Slot Waktu)
     */
    public function deleteSlot(): void {
        $this->requireRole($this->manageRoles);

        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $day = trim($_POST['day'] ?? 'Senin');
        $jamKe = (int)($_POST['jam_ke'] ?? -1);
        $viewMode = $_POST['view_mode'] ?? 'mingguan';

        if ($jamKe >= 0) {
            Database::execute(
                "DELETE FROM schedule_time_slots WHERE unit_id = ? AND academic_year_id = ? AND day = ? AND jam_ke = ?",
                [$unitId, $taId, $day, $jamKe]
            );
            // Hapus juga jadwal kelas pada slot ini jika ada
            Database::execute(
                "DELETE FROM class_schedules WHERE unit_id = ? AND academic_year_id = ? AND day = ? AND jam_ke = ?",
                [$unitId, $taId, $day, $jamKe]
            );

            AuditLog::log('DELETE_SCHEDULE_TIME_SLOT', 'schedule_time_slots', null, null, ['day' => $day, 'jam_ke' => $jamKe]);
            $this->setFlash('success', "Slot Jam Ke-{$jamKe} pada hari {$day} berhasil dihapus.");
        }

        $this->redirect(Auth::baseUrl() . '/jadwal/kelas?view_mode=' . urlencode($viewMode) . '&day=' . urlencode($day));
    }

    /**
     * Salin Slot Jam Pelajaran dari Suatu Hari ke Hari Lainnya
     */
    public function copySlots(): void {
        $this->requireRole($this->manageRoles);

        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $sourceDay = trim($_POST['source_day'] ?? 'Senin');
        $targetDays = $_POST['target_days'] ?? [];
        $viewMode = $_POST['view_mode'] ?? 'mingguan';

        if (empty($targetDays) || !is_array($targetDays)) {
            $this->setFlash('error', 'Silakan pilih hari tujuan untuk menyalin jam pelajaran.');
            $this->redirect(Auth::baseUrl() . '/jadwal/kelas');
            return;
        }

        $sourceSlots = Database::fetchAll(
            "SELECT * FROM schedule_time_slots WHERE unit_id = ? AND academic_year_id = ? AND day = ? ORDER BY jam_ke ASC",
            [$unitId, $taId, $sourceDay]
        );

        if (empty($sourceSlots)) {
            $this->setFlash('error', "Tidak ada slot waktu pada hari {$sourceDay} untuk disalin.");
            $this->redirect(Auth::baseUrl() . '/jadwal/kelas');
            return;
        }

        foreach ($targetDays as $tDay) {
            if ($tDay === $sourceDay) continue;

            // Hapus slot hari tujuan
            Database::execute(
                "DELETE FROM schedule_time_slots WHERE unit_id = ? AND academic_year_id = ? AND day = ?",
                [$unitId, $taId, $tDay]
            );

            // Masukkan slot dari source
            foreach ($sourceSlots as $sl) {
                Database::execute(
                    "INSERT INTO schedule_time_slots (unit_id, academic_year_id, day, jam_ke, time_start, time_end, label, is_break, break_type) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$unitId, $taId, $tDay, $sl['jam_ke'], $sl['time_start'], $sl['time_end'], $sl['label'], $sl['is_break'], $sl['break_type']]
                );

                // Update juga time_start & time_end di class_schedules
                Database::execute(
                    "UPDATE class_schedules SET time_start = ?, time_end = ? WHERE unit_id = ? AND academic_year_id = ? AND day = ? AND jam_ke = ?",
                    [$sl['time_start'], $sl['time_end'], $unitId, $taId, $tDay, $sl['jam_ke']]
                );
            }
        }

        $this->setFlash('success', "Slot waktu dari hari {$sourceDay} berhasil disalin ke: " . implode(', ', $targetDays));
        $this->redirect(Auth::baseUrl() . '/jadwal/kelas?view_mode=' . urlencode($viewMode) . '&day=' . urlencode($sourceDay));
    }

    /**
     * Kembalikan Jam ke Standar Sekolah
     */
    public function resetSlots(): void {
        $this->requireRole($this->manageRoles);

        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $viewMode = $_POST['view_mode'] ?? 'mingguan';
        $day = $_POST['day'] ?? 'Senin';

        // Hapus slot waktu yang ada
        Database::execute("DELETE FROM schedule_time_slots WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId]);

        // Masukkan slot default
        $defaultSlots = [
            'Senin' => [
                ['jam_ke' => 0, 'time_start' => '07.00', 'time_end' => '07.45', 'label' => 'Upacara Bendera / Ikrar Pagi', 'is_break' => 1, 'break_type' => 'upacara'],
                ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Sholat Dhuha', 'is_break' => 1, 'break_type' => 'istirahat'],
                ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 7, 'time_start' => '11.10', 'time_end' => '11.45', 'label' => 'Jam Ke-6', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 8, 'time_start' => '11.45', 'time_end' => '12.45', 'label' => 'Istirahat II, Sholat Dzuhur & Makan', 'is_break' => 1, 'break_type' => 'sholat'],
                ['jam_ke' => 9, 'time_start' => '12.45', 'time_end' => '13.20', 'label' => 'Jam Ke-7', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 10, 'time_start' => '13.20', 'time_end' => '13.55', 'label' => 'Jam Ke-8', 'is_break' => 0, 'break_type' => 'none'],
            ],
            'Selasa' => [
                ['jam_ke' => 0, 'time_start' => '07.15', 'time_end' => '07.45', 'label' => 'Tahsin Pagi & Doa', 'is_break' => 1, 'break_type' => 'upacara'],
                ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Sholat Dhuha', 'is_break' => 1, 'break_type' => 'istirahat'],
                ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 7, 'time_start' => '11.10', 'time_end' => '11.45', 'label' => 'Jam Ke-6', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 8, 'time_start' => '11.45', 'time_end' => '12.45', 'label' => 'Istirahat II, Sholat Dzuhur & Makan', 'is_break' => 1, 'break_type' => 'sholat'],
                ['jam_ke' => 9, 'time_start' => '12.45', 'time_end' => '13.20', 'label' => 'Jam Ke-7', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 10, 'time_start' => '13.20', 'time_end' => '13.55', 'label' => 'Jam Ke-8', 'is_break' => 0, 'break_type' => 'none'],
            ],
            'Rabu' => [
                ['jam_ke' => 0, 'time_start' => '07.15', 'time_end' => '07.45', 'label' => 'Literasi & Al-Ma\'tsurat', 'is_break' => 1, 'break_type' => 'upacara'],
                ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Sholat Dhuha', 'is_break' => 1, 'break_type' => 'istirahat'],
                ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 7, 'time_start' => '11.10', 'time_end' => '11.45', 'label' => 'Jam Ke-6', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 8, 'time_start' => '11.45', 'time_end' => '12.45', 'label' => 'Istirahat II, Sholat Dzuhur & Makan', 'is_break' => 1, 'break_type' => 'sholat'],
                ['jam_ke' => 9, 'time_start' => '12.45', 'time_end' => '13.20', 'label' => 'Jam Ke-7', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 10, 'time_start' => '13.20', 'time_end' => '13.55', 'label' => 'Jam Ke-8', 'is_break' => 0, 'break_type' => 'none'],
            ],
            'Kamis' => [
                ['jam_ke' => 0, 'time_start' => '07.15', 'time_end' => '07.45', 'label' => 'Kultum Siswa & Asmaul Husna', 'is_break' => 1, 'break_type' => 'upacara'],
                ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Sholat Dhuha', 'is_break' => 1, 'break_type' => 'istirahat'],
                ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 7, 'time_start' => '11.10', 'time_end' => '11.45', 'label' => 'Jam Ke-6', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 8, 'time_start' => '11.45', 'time_end' => '12.45', 'label' => 'Istirahat II, Sholat Dzuhur & Makan', 'is_break' => 1, 'break_type' => 'sholat'],
                ['jam_ke' => 9, 'time_start' => '12.45', 'time_end' => '13.20', 'label' => 'Jam Ke-7', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 10, 'time_start' => '13.20', 'time_end' => '13.55', 'label' => 'Jam Ke-8', 'is_break' => 0, 'break_type' => 'none'],
            ],
            'Jumat' => [
                ['jam_ke' => 0, 'time_start' => '07.15', 'time_end' => '07.45', 'label' => 'Senam Sehat / Dhuha Bersama', 'is_break' => 1, 'break_type' => 'upacara'],
                ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Snack', 'is_break' => 1, 'break_type' => 'istirahat'],
                ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
                ['jam_ke' => 8, 'time_start' => '11.10', 'time_end' => '13.00', 'label' => 'Sholat Jum\'at & Keputrian', 'is_break' => 1, 'break_type' => 'sholat'],
            ],
        ];

        foreach ($defaultSlots as $d => $slots) {
            foreach ($slots as $sl) {
                Database::execute(
                    "INSERT INTO schedule_time_slots (unit_id, academic_year_id, day, jam_ke, time_start, time_end, label, is_break, break_type) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$unitId, $taId, $d, $sl['jam_ke'], $sl['time_start'], $sl['time_end'], $sl['label'], $sl['is_break'], $sl['break_type']]
                );
            }
        }

        $this->setFlash('success', 'Jam pelajaran berhasil direset ke standar sekolah.');
        $this->redirect(Auth::baseUrl() . '/jadwal/kelas?view_mode=' . urlencode($viewMode) . '&day=' . urlencode($day));
    }
}
