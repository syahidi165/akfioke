<?php
// app/Controllers/SurveyController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;

class SurveyController extends Controller {

    // --- 1. SURVEI KEPUASAN ORANG TUA (53 BUTIR LENGKAP) ---
    public function ortuForm(): void {
        $this->requireAuth();
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        // Cek template survey orang tua
        $template = Database::fetch("SELECT * FROM survey_templates WHERE code = 'SURVEY_ORTU_53' LIMIT 1");
        $questions = $template ? json_decode($template['questions_json'], true) : $this->getDefaultOrtuQuestions();

        // Cek apakah sudah pernah mengisi tahun ini
        $existing = Database::fetch(
            "SELECT * FROM survey_responses WHERE template_id = ? AND unit_id = ? AND academic_year_id = ? AND respondent_user_id = ?",
            [$template['id'] ?? 1, $unitId, $taId, $userId]
        );

        $this->view('survey/ortu', [
            'pageTitle' => 'Kuesioner Kepuasan Layanan Sekolah (Orang Tua Santri)',
            'questions' => $questions,
            'existing'  => $existing
        ]);
    }

    public function ortuSubmit(): void {
        $this->requireAuth();
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $answers = $_POST['ans'] ?? [];
        $suggestions = trim($_POST['suggestions'] ?? '');

        $totalScore = 0;
        $count = count($answers);
        foreach ($answers as $val) {
            $totalScore += (int)$val;
        }

        $maxScore = max(1, $count * 5);
        $scorePct = round(($totalScore / $maxScore) * 100, 2);

        $tpl = Database::fetch("SELECT id FROM survey_templates WHERE code = 'SURVEY_ORTU_53' LIMIT 1");
        $tplId = $tpl ? (int)$tpl['id'] : 1;

        Database::execute(
            "INSERT INTO survey_responses (template_id, unit_id, academic_year_id, respondent_user_id, answers_json, total_score, max_score, score_pct, suggestions) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$tplId, $unitId, $taId, $userId, json_encode($answers), $totalScore, $maxScore, $scorePct, $suggestions]
        );

        AuditLog::log('SUBMIT_SURVEY_ORTU', 'survey_responses', Database::lastInsertId(), null, ['score_pct' => $scorePct]);
        $this->setFlash('success', 'Jazakumullah Khairan! Terima kasih telah mengisi kuesioner evaluasi layanan sekolah.');
        $this->redirect(Auth::baseUrl() . '/survey/ortu');
    }

    // --- 2. SURVEI GURU TERHADAP KEPALA SEKOLAH ---
    public function guruKepsekForm(): void {
        $this->requireRole([
            'guru_mapel', 'guru_mapel_kbtk', 'guru_diniyah', 'guru_tahfidz', 'walas', 
            'koor_tahfidz', 'waka_kurikulum', 'waka_kesiswaan', 
            'guru_supervisor', 'tutor_sebaya', 'tutor_siswa', 'tata_usaha'
        ]);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $questions = [
            'A. Kepemimpinan & Visi Misi' => [
                1 => 'Kepala Sekolah mampu mengarahkan dan memotivasi guru dalam mencapai target yayasan',
                2 => 'Kepala Sekolah transparan dan adil dalam pembagian tugas serta kebijakan',
                3 => 'Kepala Sekolah tanggap terhadap keluhan atau kendala proses belajar mengajar'
            ],
            'B. Supervisi & Pembinaan' => [
                4 => 'Kepala Sekolah rutin memberikan bimbingan dan umpan balik supervisi yang membangun',
                5 => 'Kepala Sekolah mendukung peningkatan kompetensi guru dan pelatihan'
            ],
            'C. Keteladanan & Budaya Kerja' => [
                6 => 'Kepala Sekolah menjadi teladan dalam kedisiplinan dan akhlaq Islami',
                7 => 'Kepala Sekolah membangun suasana kekeluargaan dan kerjasama tim yang solid'
            ]
        ];

        $this->view('survey/guru_kepsek', [
            'pageTitle' => 'Survei Kepemimpinan Kepala Sekolah (Oleh Dewan Guru & Staf)',
            'questions' => $questions
        ]);
    }

    public function guruKepsekSubmit(): void {
        $this->requireRole([
            'guru_mapel', 'guru_mapel_kbtk', 'guru_diniyah', 'guru_tahfidz', 'walas', 
            'koor_tahfidz', 'waka_kurikulum', 'waka_kesiswaan', 
            'guru_supervisor', 'tutor_sebaya', 'tutor_siswa', 'tata_usaha'
        ]);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $answers = $_POST['ans'] ?? [];
        $suggestions = trim($_POST['suggestions'] ?? '');

        $totalScore = array_sum(array_map('intval', $answers));
        $maxScore = count($answers) * 5;
        $scorePct = round(($totalScore / max(1, $maxScore)) * 100, 2);

        Database::execute(
            "INSERT INTO survey_responses (template_id, unit_id, academic_year_id, respondent_user_id, answers_json, total_score, max_score, score_pct, suggestions) 
             VALUES (2, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$unitId, $taId, $userId, json_encode($answers), $totalScore, $maxScore, $scorePct, $suggestions]
        );

        $this->setFlash('success', 'Survei kepemimpinan kepala sekolah berhasil dikirim.');
        $this->redirect(Auth::baseUrl() . '/survey/guru-kepsek');
    }

    // --- 3. SURVEI SISWA ---
    public function siswaForm(): void {
        $this->requireRole('santri');
        $this->view('survey/siswa', [
            'pageTitle' => 'Kuesioner Pengalaman Belajar Santri'
        ]);
    }

    public function siswaSubmit(): void {
        $this->requireRole('santri');
        $this->setFlash('success', 'Terima kasih telah mengisi kuesioner.');
        $this->redirect(Auth::baseUrl() . '/dashboard');
    }

    // --- 4. REKAP SURVEI UNTUK KEPSEK & YAYASAN ---
    public function rekapKepsek(): void {
        $this->requireRole(['kepala_sekolah', 'deputi_pendidikan', 'direktur_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $ortuStats = Database::fetch(
            "SELECT COUNT(id) as total_respondents, AVG(score_pct) as avg_pct 
             FROM survey_responses WHERE unit_id = ? AND academic_year_id = ? AND template_id = 1",
            [$unitId, $taId]
        );

        $guruStats = Database::fetch(
            "SELECT COUNT(id) as total_respondents, AVG(score_pct) as avg_pct 
             FROM survey_responses WHERE unit_id = ? AND academic_year_id = ? AND template_id = 2",
            [$unitId, $taId]
        );

        $responses = Database::fetchAll(
            "SELECT r.*, st.title as template_title, u.name as unit_name 
             FROM survey_responses r 
             JOIN survey_templates st ON r.template_id = st.id 
             JOIN units u ON r.unit_id = u.id 
             WHERE r.unit_id = ? AND r.academic_year_id = ? 
             ORDER BY r.created_at DESC",
            [$unitId, $taId]
        );

        $this->view('survey/rekap_kepsek', [
            'pageTitle' => 'Rekapitulasi Hasil Survei Kepuasan Layanan',
            'ortuStats' => $ortuStats,
            'guruStats' => $guruStats,
            'responses' => $responses
        ]);
    }

    // Helper: 53 butir instrumen resmi SURVEY ORANGTUA.pdf
    private function getDefaultOrtuQuestions(): array {
        return [
            'A. Layanan Pembayaran (TU / Keuangan)' => [
                1 => 'Respon komunikasi petugas dalam melayani pembayaran',
                2 => 'Kecepatan layanan pembayaran di tempat',
                3 => 'Frekuensi pengingat kewajiban pembayaran',
                4 => 'Kemudahan proses pembayaran via transfer bank',
                5 => 'Kejelasan informasi biaya dan tagihan sekolah',
                6 => 'Pilihan metode pembayaran yang tersedia',
                7 => 'Kelengkapan dan ketepatan bukti pembayaran / kwitansi'
            ],
            'B. Layanan Kesehatan (UKS)' => [
                8 => 'Ketepatan penanganan pertama saat siswa sakit di sekolah',
                9 => 'Komunikasi cepat kepada orang tua terkait tindak lanjut kesehatan anak',
                10 => 'Keramahan dan kepedulian petugas kesehatan / UKS'
            ],
            'C. Sarana & Fasilitas Sekolah' => [
                11 => 'Ketersediaan & kondisi ruang kelas yang nyaman dan representatif',
                12 => 'Kondisi dan kelayakan tempat wudhu serta masjid sekolah',
                13 => 'Kenyamanan fasilitas kantin dan sanitasi makanan',
                14 => 'Ketersediaan sarana olahraga dan area bermain',
                15 => 'Kondisi dan kebersihan toilet sekolah'
            ],
            'D. Keamanan & Ketertiban' => [
                16 => 'Kesiapsiagaan petugas keamanan (Security) di gerbang sekolah',
                17 => 'Ketertiban pengaturan lalu lintas saat penjemputan & antar siswa',
                18 => 'Penerapan SOP keamanan penjemputan anak',
                19 => 'Rasa aman lingkungan sekolah dari ancaman pihak luar',
                20 => 'Ketegasan penegakan disiplin dan pencegahan perundungan (bullying)'
            ],
            'E. Kebersihan Lingkungan (Cleaning Service)' => [
                21 => 'Kebersihan koridor, lorong, dan selasar sekolah',
                22 => 'Kebersihan dan wangi toilet siswa secara berkala',
                23 => 'Kerapian taman dan keasrian halaman sekolah',
                24 => 'Ketersediaan tempat sampah yang memadai dan terpilah',
                25 => 'Ketanggapan petugas kebersihan dalam menangani area kotor'
            ],
            'F. Kurikulum & Akademik' => [
                26 => 'Kualitas materi pelajaran dan kesesuaian kurikulum',
                27 => 'Keterbukaan informasi silabus dan capaian pembelajaran',
                28 => 'Kesesuaian tugas rumah / PR yang diberikan guru',
                29 => 'Kemajuan pemahaman akademik anak di rumah',
                30 => 'Transparansi sistem penilaian dan ketepatan penerimaan rapor',
                31 => 'Program remedial dan bimbingan bagi siswa yang tertinggal'
            ],
            'G. Program Tahfidz & Keislaman' => [
                32 => 'Kemajuan hafalan Al-Qur\'an anak sesuai target',
                33 => 'Kualitas makharijul huruf dan tajwid hafalan anak',
                34 => 'Ketertiban mutaba\'ah hafalan harian/mingguan',
                35 => 'Pelaksanaan sholat dhuha dan sholat berjamaah di sekolah',
                36 => 'Penanaman adab dan akhlaq mulia dalam keseharian anak',
                37 => 'Penyelenggaraan tasmi\' hafalan dan sertifikasi tahfidz'
            ],
            'H. Komunikasi & Informasi Sekolah' => [
                38 => 'Ketepatan waktu penyampaian agenda dan pengumuman sekolah',
                39 => 'Kemudahan berkomunikasi dengan Wali Kelas via WhatsApp/grup',
                40 => 'Kejelasan penyampaian informasi perkembangan anak',
                41 => 'Respon cepat pihak sekolah dalam menanggapi pertanyaan orang tua',
                42 => 'Kualitas pertemuan orang tua (Parenting / POMG)',
                43 => 'Transparansi agenda kalender akademik'
            ],
            'I. Kepemimpinan & Manajemen Sekolah' => [
                44 => 'Kemudahan menemui pimpinan sekolah saat diperlukan',
                45 => 'Keterbukaan pimpinan sekolah menerima saran dan kritik membangun',
                46 => 'Inovasi program sekolah yang semakin berkembang',
                47 => 'Pencitraan dan nama baik sekolah di masyarakat',
                48 => 'Kesesuaian janji program dengan realisasi di lapangan'
            ],
            'J. Kepuasan Umum & Loyalitas' => [
                49 => 'Secara umum, saya puas menyekolahkan anak di Al Kahfi Batam',
                50 => 'Biaya pendidikan sebanding dengan mutu layanan yang diterima',
                51 => 'Saya bersedia merekomendasikan sekolah ini kepada kerabat / rekan',
                52 => 'Anak merasa senang dan antusias bersekolah setiap hari',
                53 => 'Nilai-nilai keislaman tertanam kuat dalam pribadi anak'
            ]
        ];
    }
}
