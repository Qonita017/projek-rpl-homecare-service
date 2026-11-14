<?php
require_once __DIR__ . '/DatabaseContextService.php';

class AIService {
    
    private $apiKey;
    private $provider;
    private $model;

    public function __construct($apiKey = null, $provider = null, $model = null) {
        $dbApiKey = null;
        $dbProvider = null;
        $dbModel = null;

        try {
            if (class_exists('Database')) {
                require_once __DIR__ . '/../Models/Setting.php';
                $settingModel = new Setting();
                $dbApiKey = $settingModel->get('ai_api_key');
                $dbProvider = $settingModel->get('ai_provider');
                $dbModel = $settingModel->get('ai_model');
            }
        } catch (\Throwable $e) {
            // Silently fallback to constants
        }

        $this->apiKey = $apiKey ?: ($dbApiKey ?: (defined('AI_API_KEY') ? AI_API_KEY : ''));
        $this->provider = strtolower($provider ?: ($dbProvider ?: (defined('AI_PROVIDER') ? AI_PROVIDER : 'nvidia')));
        $this->model = $model ?: ($dbModel ?: (defined('AI_MODEL') ? AI_MODEL : 'meta/llama-3.2-11b-vision-instruct'));
    }

    /**
     * Diagnose equipment problem using AI API or smart fallback engine
     */
    public function diagnose($equipmentType, $brand = '', $complaint = '', $symptoms = '') {
        $equipmentType = trim($equipmentType);
        $brand = trim($brand);
        $complaint = trim($complaint);
        $symptoms = trim($symptoms);

        // If API key is not configured, directly use smart fallback engine
        if (empty($this->apiKey)) {
            return $this->getMockDiagnosis($equipmentType, $brand, $complaint, $symptoms, 'API Key belum dikonfigurasi. Menggunakan Smart Diagnosis Engine.');
        }

        try {
            if ($this->provider === 'nvidia') {
                return $this->callNvidia($equipmentType, $brand, $complaint, $symptoms);
            } elseif ($this->provider === 'gemini') {
                return $this->callGemini($equipmentType, $brand, $complaint, $symptoms);
            } elseif ($this->provider === 'openai') {
                return $this->callOpenAI($equipmentType, $brand, $complaint, $symptoms);
            } elseif ($this->provider === 'groq') {
                return $this->callGroq($equipmentType, $brand, $complaint, $symptoms);
            } else {
                return $this->getMockDiagnosis($equipmentType, $brand, $complaint, $symptoms, 'Provider AI tidak didukung. Menggunakan Smart Diagnosis Engine.');
            }
        } catch (Exception $e) {
            // Gracefully catch timeout, connection error, or invalid payload and fallback
            return $this->getMockDiagnosis($equipmentType, $brand, $complaint, $symptoms, 'Gagal terhubung ke API AI (' . $e->getMessage() . '). Menggunakan Smart Diagnosis Engine.');
        }
    }

    /**
     * Google Gemini API Integration for Diagnosis
     */
    private function callGemini($equipmentType, $brand, $complaint, $symptoms) {
        $model = $this->model;
        if (empty($model) || str_contains($model, '1.5') || str_contains($model, '2.0') || str_contains($model, '2.5')) {
            $model = 'gemini-3.5-flash';
        }
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($model) . ":generateContent?key=" . urlencode($this->apiKey);

        $prompt = "Anda adalah asisten teknisi ahli peralatan rumah tangga di HomeCare Service. Berikan diagnosis awal ramah, mendalam, dan akurat dalam Bahasa Indonesia.\n\n"
            . "Peralatan: {$equipmentType}\n"
            . "Merk: " . ($brand ?: 'Tidak disebutkan') . "\n"
            . "Keluhan: {$complaint}\n"
            . "Gejala Tambahan: " . ($symptoms ?: 'Tidak ada') . "\n\n"
            . "ATURAN PENTING: Jangan berikan penjelasan dalam bentuk poin-poin atau bullet points. Berikan penjelasan lengkap dan menyeluruh dalam bentuk PARAGRAF yang jelas, mengalir, deskriptif, dan detail.\n\n"
            . "Berikan jawaban dengan format JSON valid persis seperti ini:\n"
            . "{\n"
            . "  \"penjelasan_analisis\": \"Paragraf narasi deskriptif yang menjelaskan analisis mendalam mengenai kondisi peralatan, kaitan gejala dengan kerusakan teknis, dan mekanisme terjadinya gangguan.\",\n"
            . "  \"kemungkinan_penyebab\": \"Paragraf penjelasan detail mengenai faktor-faktor teknis penyebab masalah serta komponen internal yang diduga mengalami aus atau kerusakan.\",\n"
            . "  \"tingkat_prioritas\": \"Sedang\",\n"
            . "  \"estimasi_biaya\": \"Rp 150.000 - Rp 250.000\",\n"
            . "  \"rincian_biaya\": \"Paragraf rincian perkiraan biaya yang mencakup estimasi jasa teknisi serta kisaran harga suku cadang jika memerlukan penggantian komponen.\",\n"
            . "  \"rekomendasi_tindakan\": \"Paragraf panduan tindakan awal yang aman dilakukan pengguna di rumah beserta hal-hal yang wajib dihindari.\",\n"
            . "  \"perlu_teknisi\": \"Paragraf penjelasan alasan mengapa pemeriksaan dan perbaikan langsung oleh teknisi profesional sangat disarankan.\",\n"
            . "  \"disclaimer\": \"Hasil ini merupakan diagnosis awal dari AI dan bukan diagnosis teknisi pasti. Pemeriksaan fisik langsung oleh teknisi tetap diperlukan.\"\n"
            . "}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json'
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("Timeout/Network: " . $curlError);
        }

        if ($httpCode !== 200 || empty($response)) {
            throw new Exception("HTTP " . $httpCode);
        }

        $json = json_decode($response, true);
        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $parsed = json_decode($text, true);

        if (!is_array($parsed) || (empty($parsed['kemungkinan_penyebab']) && empty($parsed['penjelasan_analisis']))) {
            throw new Exception("Format output API tidak valid");
        }

        return $this->formatDiagnosisResult($parsed, 'Gemini AI (' . $this->model . ')');
    }

    /**
     * OpenAI API Integration for Diagnosis
     */
    private function callOpenAI($equipmentType, $brand, $complaint, $symptoms) {
        $url = "https://api.openai.com/v1/chat/completions";

        $prompt = "Peralatan: {$equipmentType}, Merk: {$brand}, Keluhan: {$complaint}, Gejala: {$symptoms}. Berikan diagnosis awal peralatan rumah tangga dalam format JSON. PENTING: Jangan gunakan format poin-poin/bullets, tetapi tuliskan penjelasan dalam bentuk paragraf yang jelas, mengalir, dan detail. Format JSON wajib memiliki key: penjelasan_analisis (string paragraf naratif), kemungkinan_penyebab (string paragraf detail), tingkat_prioritas (string: Rendah/Sedang/Tinggi/Mendesak), estimasi_biaya (string kisaran biaya dalam rupiah misalnya: Rp 150.000 - Rp 250.000), rincian_biaya (string paragraf detail rincian jasa dan suku cadang), rekomendasi_tindakan (string paragraf panduan aman), perlu_teknisi (string paragraf saran teknisi), disclaimer (string).";

        $payload = [
            'model' => $this->model ?: 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Anda adalah asisten AI diagnosis peralatan rumah tangga dalam Bahasa Indonesia yang menghasilkan output JSON valid dengan narasi penjelasan berbentuk paragraf detail tanpa bullet points.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.2
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            throw new Exception("OpenAI HTTP " . $httpCode . " / " . $curlError);
        }

        $json = json_decode($response, true);
        $content = $json['choices'][0]['message']['content'] ?? '';
        $parsed = json_decode($content, true);

        if (!is_array($parsed) || (empty($parsed['kemungkinan_penyebab']) && empty($parsed['penjelasan_analisis']))) {
            throw new Exception("Format output OpenAI tidak valid");
        }

        return $this->formatDiagnosisResult($parsed, 'OpenAI (' . $this->model . ')');
    }

    /**
     * NVIDIA Cloud NIM API Integration for Equipment Damage Diagnosis & Repair Recommendation
     */
    private function callNvidia($equipmentType, $brand, $complaint, $symptoms) {
        $url = "https://integrate.api.nvidia.com/v1/chat/completions";

        $prompt = "Peralatan: {$equipmentType}\n"
            . "Merk: " . ($brand ?: 'Tidak disebutkan') . "\n"
            . "Keluhan: {$complaint}\n"
            . "Gejala Tambahan: " . ($symptoms ?: 'Tidak ada');

        $systemPrompt = "Anda adalah asisten teknisi ahli peralatan rumah tangga di HomeCare Service.\n"
            . "Tugas Anda adalah mendiagnosis kemungkinan penyebab kerusakan, memberikan penjelasan analisis mendalam, serta rekomendasi panduan perbaikan yang aman bagi pengguna.\n"
            . "ATURAN PENTING: Seluruh penjelasan diagnosis dan rekomendasi tindakan HARUS dituliskan dalam bentuk PARAGRAF yang jelas, mengalir, dan detail (BUKAN berupa daftar poin/bullet points).\n"
            . "Berikan jawaban HANYA berupa objek JSON valid tanpa teks lain atau markdown codeblocks. Gunakan format JSON persis seperti berikut:\n"
            . "{\n"
            . "  \"penjelasan_analisis\": \"Paragraf narasi deskriptif yang menjelaskan analisis mendalam mengenai kondisi peralatan, hubungan antara keluhan dan kerusakan teknis, serta mekanisme terjadinya gangguan.\",\n"
            . "  \"kemungkinan_penyebab\": \"Paragraf penjelasan detail tentang faktor teknis penyebab kerusakan serta komponen internal yang diduga bermasalah.\",\n"
            . "  \"tingkat_prioritas\": \"Sedang\",\n"
            . "  \"estimasi_biaya\": \"Rp 150.000 - Rp 250.000\",\n"
            . "  \"rincian_biaya\": \"Paragraf penjelasan rincian estimasi biaya yang mencakup estimasi jasa teknisi serta kisaran harga suku cadang jika memerlukan penggantian komponen.\",\n"
            . "  \"rekomendasi_tindakan\": \"Paragraf langkah penanganan awal, pemeriksaan ringan yang aman, dan tindakan pencegahan bagi pengguna.\",\n"
            . "  \"perlu_teknisi\": \"Paragraf penjelasan rekomendasi pemeriksaan langsung oleh teknisi profesional.\",\n"
            . "  \"disclaimer\": \"Hasil ini merupakan diagnosis awal dari AI dan bukan diagnosis teknisi pasti. Pemeriksaan fisik oleh teknisi tetap diperlukan.\"\n"
            . "}";

        $payload = [
            'model' => $this->model ?: 'meta/llama-3.2-11b-vision-instruct',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.2,
            'max_tokens' => 1000
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200 || empty($response)) {
            throw new Exception("NVIDIA API HTTP " . $httpCode . ($curlError ? " / " . $curlError : ""));
        }

        $json = json_decode($response, true);
        $content = $json['choices'][0]['message']['content'] ?? '';

        // Clean markdown JSON codeblocks if present
        $cleanContent = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content));
        $parsed = json_decode($cleanContent, true);

        if (!is_array($parsed) || (empty($parsed['kemungkinan_penyebab']) && empty($parsed['penjelasan_analisis']))) {
            throw new Exception("Format output NVIDIA API tidak valid");
        }

        return $this->formatDiagnosisResult($parsed, 'NVIDIA AI (' . $this->model . ')');
    }

    /**
     * Smart Rule-based Contextual Mock Diagnostic Engine (Fallback)
     */
    public function getMockDiagnosis($equipmentType, $brand = '', $complaint = '', $symptoms = '', $notice = null) {
        $textSearch = strtolower($equipmentType . ' ' . $brand . ' ' . $complaint . ' ' . $symptoms);

        $analysis = '';
        $causes = '';
        $priority = 'Sedang';
        $estimatedCost = 'Rp 100.000 - Rp 250.000';
        $costDetail = 'Perkiraan biaya mencakup jasa pengecekan teknisi sekitar Rp 80.000 - Rp 120.000 serta estimasi perbaikan suku cadang ringan jika diperlukan.';
        $recommendations = '';
        $technicianAdvice = 'Pemeriksaan langsung oleh teknisi profesional sangat disarankan guna mendiagnosis komponen secara akurat dan menjamin keselamatan operasional perangkat.';

        // Category Analysis
        if (strpos($textSearch, 'ac') !== false || strpos($textSearch, 'air conditioner') !== false) {
            if (strpos($textSearch, 'tidak dingin') !== false || strpos($textSearch, 'kurang dingin') !== false || strpos($textSearch, 'air') !== false || strpos($textSearch, 'bocor') !== false) {
                $analysis = "Berdasarkan gejala yang dialami, AC yang menyala namun kurang dingin serta disertai tetesan air menunjukkan adanya hambatan signifikan pada sirkulasi udara dan sistem drainase unit indoor. Ketika saringan filter udara atau kisi-kisi evaporator tertutup debu pekat, penyerapan panas ruangan tidak berlangsung optimal sehingga permukaan evaporator mengalami pendinginan berlebih (frosting) yang kemudian mencair dan meluap dari bak talang air karena saluran pipa pembuangan tersumbat lumut atau kotoran.";
                $causes = "Penyebab utama dari kendala ini adalah akumulasi kotoran dan debu pekat pada filter udara serta koil pendingin indoor, penyumbatan lendir lumut pada pipa pembuangan air kondensasi, serta potensi penurunan tekanan gas refrigerant (freon) akibat kebocoran mikro pada sambungan pipa tembaga atau nepel. Selain itu, sirip kondensor pada unit outdoor yang kotor juga dapat menghambat pelepasan panas sehingga kompresor cepat panas dan memutus siklus pendinginan secara prematur.";
                $priority = 'Sedang';
                $estimatedCost = 'Rp 120.000 - Rp 250.000';
                $costDetail = 'Perkiraan biaya mencakup jasa servis cuci AC dan pembersihan talang drainase sekitar Rp 120.000 - Rp 150.000, serta penanganan perbaikan kebocoran nepel atau pengisian ulang freon berkisar Rp 80.000 - Rp 100.000 jika ditemukan penurunan tekanan.';
                $recommendations = "Sebagai langkah penanganan awal yang aman, segera matikan unit AC dan bersihkan saringan filter udara pada panel indoor menggunakan air mengalir lalu keringkan sebelum dipasang kembali. Pastikan jalur selang pembuangan di luar ruangan berada dalam posisi menurun bebas dan tidak tertekuk ataupun terendam air. Hindari membongkar penutup modul kelistrikan indoor atau mengisi freon secara mandiri demi keamanan dan keselamatan perangkat.";
                $technicianAdvice = "Sangat disarankan untuk menjadwalkan servis cuci AC berkala (steam cleaning) secara menyeluruh serta pengecekan tekanan freon dan arus kompresor oleh teknisi profesional guna memastikan kinerja pendinginan kembali optimal.";
            } elseif (strpos($textSearch, 'mati') !== false || strpos($textSearch, 'tidak menyala') !== false || strpos($textSearch, 'konslet') !== false) {
                $analysis = "Unit AC yang mati total atau tidak merespons perintah menyala umumnya mengindikasikan adanya gangguan pada suplai daya listrik, proteksi sirkuit kelistrikan, atau kegagalan pada modul pengendali elektronik (PCB). Sistem proteksi internal secara otomatis memutus arus jika mendeteksi adanya lonjakan tegangan, komponen motor yang macet, atau sirkuit pendek untuk mencegah kerusakan fatal lebih lanjut.";
                $causes = "Penyebab paling sering mencakup saklar pemutus daya listrik (MCB) yang turun karena kelebihan beban, sekring pada modul PCB indoor yang putus, kapasitor motor kompresor/fan yang mengalami kerusakan fisik atau penurunan kapasitas, serta kerusakan pada sensor penerima sinyal remote (receiver board).";
                $priority = 'Tinggi';
                $estimatedCost = 'Rp 150.000 - Rp 350.000';
                $costDetail = 'Perkiraan biaya mencakup jasa inspeksi kelistrikan multitester sekitar Rp 120.000 - Rp 150.000, serta estimasi penggantian kapasitor kompresor atau perbaikan modul PCB berkisar Rp 100.000 - Rp 200.000.';
                $recommendations = "Periksa apakah saklar MCB atau stop kontak AC berada dalam kondisi normal dan tidak longgar. Cobalah mencabut steker listrik AC selama 5 hingga 10 menit untuk melakukan reset daya kelistrikan, kemudian hubungkan kembali dan tes dengan menekan tombol manual (power switch) pada bodi unit indoor. Hindari membuka casing modul elektronik jika tidak memiliki alat pelindung dan keahlian kelistrikan.";
                $technicianAdvice = "Pemeriksaan teknisi berpengalaman sangat diperlukan untuk melakukan pengukuran tegangan multitester pada modul PCB, pengetesan relay kelistrikan, dan penggantian kapasitor jika ditemukan komponen yang rusak.";
            } else {
                $analysis = "Kendala operasional pada unit AC seperti timbulnya suara berisik, hembusan bau tidak sedap, atau fluktuasi hembusan angin umumnya bersumber dari keausan mekanis kipas blower atau penumpukan jamur dan kotoran pada komponen internal yang sudah lama tidak dibersihkan.";
                $causes = "Kondisi ini umumnya dipicu oleh bearing motor fan indoor atau outdoor yang mulai aus dan membutuhkan pelumasan, bilah kipas blower yang tidak seimbang akibat lapisan debu tebal, atau sensor termostat (thermistor suhu) yang mulai menurun akurasi pembacaannya.";
                $priority = 'Sedang';
                $estimatedCost = 'Rp 120.000 - Rp 220.000';
                $costDetail = 'Perkiraan biaya mencakup jasa servis pembersihan fan blower dan pelumasan bearing motor sekitar Rp 120.000 - Rp 150.000, serta kalibrasi sensor thermistor pendingin jika diperlukan.';
                $recommendations = "Bersihkan kisi-kisi ventilasi luar dan hindari mengatur pengaturan suhu terlalu ekstrem (di bawah 18°C) secara terus-menerus. Gunakan mode Fan atau Dry selama beberapa saat untuk membantu mengeringkan sisa kelembapan pada koil evaporator setelah pemakaian.";
                $technicianAdvice = "Inspeksi langsung oleh teknisi disarankan untuk memeriksa keseimbangan motor blower, pelumasan bearing, serta kalibrasi sensor pendingin agar AC bekerja senyap dan tahan lama.";
            }
        } elseif (strpos($textSearch, 'mesin cuci') !== false || strpos($textSearch, 'washing') !== false) {
            if (strpos($textSearch, 'tidak berputar') !== false || strpos($textSearch, 'pengering') !== false || strpos($textSearch, 'spin') !== false) {
                $analysis = "Mesin cuci yang tidak berputar atau sistem pengeringnya macet menandakan terganggunya transfer tenaga dari motor penggerak ke tabung cuci atau terpicunya mekanisme sensor keselamatan mesin. Pada sebagian besar mesin cuci otomatis maupun dua tabung, modul elektronik akan menghentikan putaran motor jika mendeteksi pintu tidak terkunci rapat, beban cucian tidak seimbang, atau komponen transmisi kehilangan daya cengkeram.";
                $causes = "Faktor penyebab yang dominan meliputi tali karet transmisi (fanbelt) yang sudah aus, kendur, atau putus; kapasitor motor starter yang kehilangan kapasitas microfarad; saklar sensor switch pintu (door lock safety) yang kotor atau patah; serta kemungkinan beban pakaian yang dimasukkan melebihi batas kapasitas maksimal mesin.";
                $priority = 'Tinggi';
                $estimatedCost = 'Rp 120.000 - Rp 280.000';
                $costDetail = 'Perkiraan biaya mencakup jasa pembongkaran dan servis mekanisme penggerak sekitar Rp 120.000 - Rp 150.000, ditambah penggantian komponen seperti tali karet fanbelt atau kapasitor motor spin berkisar Rp 50.000 - Rp 130.000.';
                $recommendations = "Kurangi jumlah pakaian di dalam tabung untuk meringankan beban kerja motor dan ratakan susunan cucian agar seimbang. Pastikan penutup atau pintu mesin cuci tertutup rapat sempurna hingga terdengar bunyi klik pengaman. Selalu cabut kabel daya dari stop kontak sebelum memeriksa bagian dalam tabung mesin.";
                $technicianAdvice = "Disarankan memanggil teknisi untuk melakukan penggantian tali fanbelt, pemeriksaan kapasitor motor dengan alat kapasitansi meter, atau perbaikan switch sensor pengunci pintu.";
            } else {
                $analysis = "Masalah aliran air pada mesin cuci biasanya berkaitan erat dengan sistem katup solenoid pengisian dan saluran katup pembuangan (drain valve). Ketika sirkulasi air terhambat, mesin cuci akan menolak melanjutkan proses siklus cuci atau mengalami error waktu pengisian/pembuangan.";
                $causes = "Penyebab utama adalah tersumbatnya saringan inlet valve oleh lumut atau endapan pasir dari pipa air rumah tangga, katup solenoid pengisi air yang terbakar atau macet, serta saluran pipa drainase bawah yang tersumbat oleh koin, kancing, atau serat benang pakaian yang menumpuk.";
                $priority = 'Sedang';
                $estimatedCost = 'Rp 100.000 - Rp 220.000';
                $costDetail = 'Perkiraan biaya mencakup jasa pembersihan saluran air dan saringan sekitar Rp 100.000 - Rp 120.000, serta estimasi penggantian katup solenoid inlet atau seal drainase berkisar Rp 60.000 - Rp 100.000.';
                $recommendations = "Lepas selang air masuk pada bagian belakang mesin dan bersihkan filter saringan kawat kecil menggunakan sikat lembut di bawah air mengalir. Periksa pipa saluran pembuangan di bagian belakang dan pastikan tidak terlipat atau tersumbat benda keras.";
                $technicianAdvice = "Pemeriksaan teknisi direkomendasikan jika katup solenoid elektrik atau motor drainase pembuangan perlu diganti untuk mengembalikan fungsi otomatis mesin cuci.";
            }
        } elseif (strpos($textSearch, 'kulkas') !== false || strpos($textSearch, 'lemari es') !== false || strpos($textSearch, 'refrigerator') !== false) {
            $analysis = "Kulkas yang mengalami penurunan suhu dingin atau tidak mampu membekukan es secara optimal menunjukkan adanya disrupsi pada siklus termodinamika pendinginan atau kebocoran isolasi termal kabinet. Kulkas bekerja dengan menyerap panas dari dalam kabinet dan membuangnya melalui kondensor di bagian belakang atau samping bodi; jika proses ini terhambat, suhu dalam kabinet akan terus meningkat dan membebani kompresor.";
            $causes = "Penyebab yang sering terjadi antara lain karet gasket pintu yang mengeras, robek, atau kotor sehingga udara hangat dari luar terus masuk; penumpukan debu tebal pada koil kondensor dan motor fan pendingin; gangguan pada sistem defrost otomatis (seperti timer defrost, bimetal defrost, atau elemen heater pemanas); serta kemungkinan sirkulasi gas freon yang berkurang akibat kebocoran mikro.";
            $priority = 'Tinggi';
            $estimatedCost = 'Rp 150.000 - Rp 350.000';
            $costDetail = 'Perkiraan biaya mencakup jasa pemeriksaan teknisi dan sistem sirkulasi pendingin sekitar Rp 150.000 - Rp 200.000, serta estimasi perbaikan komponen defrost (timer/bimetal/fuse heater) atau relay start kompresor berkisar Rp 100.000 - Rp 150.000.';
            $recommendations = "Bersihkan karet lis pintu kulkas dengan kain basah hangat dan pastikan pintu menutup rapat tanpa ada celah udara. Berikan jarak ventilasi minimal 10 sampai 15 cm antara bagian belakang serta samping kulkas dengan dinding ruangan. Hindari memasukkan makanan atau cairan yang masih bersuhu panas ke dalam kulkas.";
            $technicianAdvice = "Penanganan teknisi sangat dianjurkan untuk memeriksa sistem kelistrikan defrost bimetal, relay start kompresor, serta pengecekan sirkulasi freon demi mencegah kerusakan kompresor yang lebih berat.";
        } elseif (strpos($textSearch, 'tv') !== false || strpos($textSearch, 'televisi') !== false) {
            $analysis = "Kerusakan pada televisi seperti layar gelap namun suara tetap terdengar, layar bergaris, atau TV tidak menyala biasanya bersumber dari degradasi rangkaian pencahayaan latar (backlight panel) atau gangguan pada modul regulator pencatu daya (power supply board). Rangkaian proteksi TV modern akan secara otomatis mematikan layar jika mendeteksi adanya ketidakstabilan voltase pada deretan lampu LED backlight.";
            $causes = "Penyebab paling umum adalah lampu strip LED backlight panel yang sudah putus atau melemah akibat usia pakai, komponen elco/kapasitor pada power board yang mengalami penurunan kapasitas tegangan, atau kabel pita konektor LVDS antara mainboard dan panel T-Con yang longgar atau berdebu.";
            $priority = 'Sedang';
            $estimatedCost = 'Rp 200.000 - Rp 450.000';
            $costDetail = 'Perkiraan biaya mencakup jasa inspeksi dan perbaikan modul sirkuit TV sekitar Rp 200.000 - Rp 250.000, serta estimasi penggantian deretan strip backlight LED atau komponen regulator daya berkisar Rp 100.000 - Rp 200.000 tergantung ukuran layar TV.';
            $recommendations = "Pastikan TV terhubung pada colokan listrik yang stabil dan periksa kembali sambungan kabel HDMI, Set Top Box, atau antena. Hindari mengetuk, menekan, atau menyemprotkan cairan pembersih secara langsung ke permukaan panel kaca layar TV.";
            $technicianAdvice = "Perbaikan TV memerlukan teknisi spesialis untuk melakukan pembongkaran panel secara presisi di lingkungan bebas debu serta pengujian modul menggunakan voltmeter dan backlight tester.";
        } elseif (strpos($textSearch, 'kompor') !== false) {
            $analysis = "Kendala pada kompor gas atau kompor listrik seperti api merah, pemantik tidak memercik, atau kompor mati saat knop dilepas berkaitan erat dengan kebersihan spuyer aliran gas, keausan elektroda pemantik, atau sensor pengaman termokopel (thermocouple).";
            $causes = "Penyebab yang umum antara lain kerak sisa masakan atau jelaga menyumbat lubang kuningan burner kepala spuyer, baterai atau kabel modul pemantik aus, serta termokopel yang kotor sehingga tidak dapat menahan magnet katup gas terbuka.";
            $priority = 'Sedang';
            $estimatedCost = 'Rp 85.000 - Rp 175.000';
            $costDetail = 'Perkiraan biaya mencakup jasa servis pembersihan burner spuyer dan kalibrasi pemantik sekitar Rp 85.000, serta estimasi penggantian suku cadang pemantik atau katup pengaman berkisar Rp 50.000 - Rp 90.000.';
            $recommendations = "Pastikan regulator tabung gas terpasang kencang dan tidak berbau gas menyengat. Bersihkan burner cap kuningan dengan sikat halus kering dan hindari penggunaan air yang membasahi pemantik.";
            $technicianAdvice = "Pemeriksaan teknisi disarankan untuk memeriksa kerapatan katup gas dan melakukan kalibrasi spuyer agar nyala api kembali biru dan aman.";
        } elseif (strpos($textSearch, 'pompa') !== false) {
            $analysis = "Pompa air yang mendengung namun tidak berputar, air tidak naik, atau saklar otomatis berbunyi cetak-cetek terus menerus mengindikasikan gangguan mekanis pada impeller, kebocoran seal penahan tekanan, atau penurunan nilai kapasitansi starter motor.";
            $causes = "Penyebab utama meliputi seal mekanikal yang aus menyebabkan angin palsu masuk ke ruang pompa, kapasitor dinamo motor melemah, atau switch otomatis (pressure switch) yang kotor dan kehilangan elastisitas pegas.";
            $priority = 'Sedang';
            $estimatedCost = 'Rp 120.000 - Rp 250.000';
            $costDetail = 'Perkiraan biaya mencakup jasa servis dan pengetesan pompa sekitar Rp 120.000 - Rp 150.000, serta estimasi penggantian mechanical seal, kapasitor pompa, atau pressure switch otomatis berkisar Rp 60.000 - Rp 100.000.';
            $recommendations = "Matikan saklar pompa jika dinamo mendengung tanpa berputar untuk mencegah gulungan tembaga terbakar. Periksa ketersediaan air pada sumber sumur/tandon.";
            $technicianAdvice = "Teknisi profesional diperlukan untuk melakukan pembongkaran ruang impeller, penggantian seal mekanis, dan penyetelan tekanan switch otomatis.";
        } else {
            // General Appliances
            $analysis = "Kendala operasional pada peralatan rumah tangga umumnya dipengaruhi oleh penurunan kinerja elemen pemanas atau motor penggerak, keausan kabel transmisi daya, atau akumulasi kotoran sisa pemakaian pada bagian mekanis. Kondisi operasional yang dipaksakan saat komponen internal mengalami degradasi dapat memicu panas berlebih dan berisiko merusak modul proteksi alat.";
            $causes = "Faktor penyebab mencakup penurunan ketahanan isolasi komponen kelistrikan internal, koneksi kabel atau kontak saklar daya yang longgar dan berkarat, serta timbunan kerak, debu, atau sisa residu yang menghambat pergerakan mekanis perangkat.";
            $priority = 'Sedang';
            $estimatedCost = 'Rp 80.000 - Rp 220.000';
            $costDetail = 'Perkiraan biaya mencakup jasa pemeriksaan kelistrikan dan uji fungsi alat sekitar Rp 80.000 - Rp 120.000, serta estimasi penggantian suku cadang ringan (seperti elemen pemanas, termostat, atau saklar) berkisar Rp 40.000 - Rp 100.000.';
            $recommendations = "Segera matikan alat dan cabut steker dari colokan listrik setelah digunakan atau saat terdeteksi kelainan fungsi. Bersihkan permukaan luar perangkat secara rutin dan jangan membongkar casing internal sendiri jika tidak memiliki peralatan uji kelistrikan yang memadai.";
            $technicianAdvice = "Pemeriksaan langsung oleh teknisi profesional sangat disarankan guna menguji kelayakan komponen, memastikan keamanan isolasi listrik, dan melakukan penggantian suku cadang yang presisi.";
        }

        $data = [
            'penjelasan_analisis' => $analysis,
            'kemungkinan_penyebab' => $causes,
            'tingkat_prioritas' => $priority,
            'estimasi_biaya' => $estimatedCost,
            'rincian_biaya' => $costDetail,
            'rekomendasi_tindakan' => $recommendations,
            'perlu_teknisi' => $technicianAdvice,
            'disclaimer' => 'Hasil ini merupakan diagnosis awal dari AI dan bukan diagnosis teknisi pasti. Pemeriksaan fisik secara langsung oleh teknisi tetap diperlukan.'
        ];

        return $this->formatDiagnosisResult($data, 'Smart Diagnosis Engine' . ($notice ? ' (' . $notice . ')' : ''));
    }

    /**
     * Interactive AI Chat Assistant with Live Database Context & Full Knowledge Retrieval
     */
    public function chat(array $messagesContext, $customerId = null) {
        if ($customerId === null && isset($_SESSION['user_id'])) {
            $customerId = (int)$_SESSION['user_id'];
        }

        $dbContextService = new DatabaseContextService();
        $dbKnowledgePrompt = $dbContextService->buildKnowledgePrompt($customerId);
        $structuredData = $dbContextService->getFullContextData($customerId);

        if (empty($this->apiKey)) {
            return $this->getMockChatResponse($messagesContext, $structuredData);
        }

        $res = null;
        try {
            if ($this->provider === 'nvidia') {
                $res = $this->callNvidiaChat($messagesContext, $dbKnowledgePrompt);
            } elseif ($this->provider === 'gemini') {
                $res = $this->callGeminiChat($messagesContext, $dbKnowledgePrompt);
            } elseif ($this->provider === 'openai') {
                $res = $this->callOpenAIChat($messagesContext, $dbKnowledgePrompt);
            } elseif ($this->provider === 'groq') {
                $res = $this->callGroqChat($messagesContext, $dbKnowledgePrompt);
            } else {
                $res = $this->getMockChatResponse($messagesContext, $structuredData);
            }
        } catch (Exception $e) {
            $res = $this->getMockChatResponse($messagesContext, $structuredData);
        }

        // Post-processing guard: UNIVERSAL RULE - TO THE POINT TANPA RINCIAN UNTUK SEMUA PERTANYAAN
        $lastUser = '';
        $prevUser = '';
        $prevAI = '';
        $userCount = 0;
        foreach (array_reverse($messagesContext) as $m) {
            $sender = strtoupper($m['sender'] ?? ($m['role'] ?? ''));
            $content = strtolower($m['message'] ?? ($m['content'] ?? (is_string($m) ? $m : '')));
            if ($sender === 'USER') {
                $userCount++;
                if ($userCount === 1) {
                    $lastUser = $content;
                } elseif ($userCount === 2 && empty($prevUser)) {
                    $prevUser = $content;
                }
            } elseif (($sender === 'AI' || $sender === 'ASSISTANT') && empty($prevAI)) {
                $prevAI = $content;
            }
        }
        $prevContext = $prevUser . ' ' . $prevAI;

        $pStats = $structuredData['platform_stats'] ?? [];
        $totalCust = (int)($pStats['total_customers'] ?? 7);
        $totalTech = (int)($pStats['total_technicians'] ?? 6);
        $availTech = (int)($structuredData['technicians']['available_count'] ?? 5);
        $totalOrders = (int)($pStats['total_orders'] ?? 10);
        $pendingOrders = (int)($pStats['pending_orders'] ?? 0);
        $activeOrders = (int)($pStats['active_orders'] ?? 4);
        $completedOrders = (int)($pStats['completed_orders'] ?? 6);
        $cancelledOrders = (int)($pStats['cancelled_orders'] ?? 0);
        $totalEquip = (int)($pStats['total_equipment_items'] ?? 27);
        $totalOmset = (float)($pStats['total_omset'] ?? 1450000);
        $paidCount = (int)($pStats['paid_orders_count'] ?? 6);
        $omsetFormatted = "Rp " . number_format($totalOmset, 0, ',', '.');
        $totalReviews = (int)($pStats['total_reviews'] ?? 3);
        $avgRating = $pStats['average_rating'] ?? 5.0;
        $totalReports = (int)($pStats['total_reports'] ?? 6);
        $totalPayments = (int)($pStats['total_payments'] ?? 6);
        $totalNotifs = (int)($pStats['total_notifications'] ?? 22);
        $totalSessions = (int)($pStats['total_chat_sessions'] ?? 35);
        $totalUsers = (int)($pStats['total_users'] ?? 15);
        $totalAdmins = (int)($pStats['total_admins'] ?? 2);

        $isAskingDetail = (bool)preg_match('/\b(rincikan|rincian|rinciannya|detail|detailnya|daftar|siapa+|siapakah|siapa saja|siapa sajakah|siapaa saja|apa saja|apa sajakah|mana saja|list|tampilkan|tampilkan daftar|sebutkan|sebutkan nama)\w*/i', $lastUser);
        $isCountQuery = (bool)preg_match('/\b(berapa|jumlah|total|ada berapa|hitung|banyaknya)\b/i', $lastUser);

        // 1. Guard pertanyaan OMSET / PEMBAYARAN SUDAH DIBAYAR (Nominal saja tanpa rincian)
        $isOmsetQuery = (bool)preg_match('/\b(omset|omzet|pendapatan|pemasukan|keuangan|uang masuk|pembayaran yang sudah dibayar|semua pembayaran|total pembayaran)\w*/i', $lastUser);
        $isExplicitPaidDetails = (bool)preg_match('/\b(rincikan pesanan yang sudah dibayar|rincian pesanan sudah dibayar|rincikan omset|rincian omset|detail omset|daftar omset|apa saja omset|rincikan pembayaran|daftar pembayaran|rincikan pendapatan|rincian pendapatan)\w*/i', $lastUser)
            || ($isAskingDetail && (bool)preg_match('/\b(omset|omzet|pendapatan|pemasukan|keuangan|uang masuk|dibayar|lunas)\w*/i', $lastUser));

        if ($isOmsetQuery && !$isExplicitPaidDetails && isset($res['message'])) {
            $regexFormatted = preg_quote($omsetFormatted, '/');
            if (preg_match('/(Order #|Pelanggan:|Teknisi:|Keluhan:|Rincian Pesanan|\•\s*Order|1\.\s*Order|daftar pesanan)/i', $res['message']) || !preg_match('/' . $regexFormatted . '/i', $res['message'])) {
                $res['message'] = "Total omset dari semua pesanan yang sudah dibayar saat ini adalah sebesar {$omsetFormatted} dari {$paidCount} pesanan yang sudah lunas.";
            }
        }

        // 2. Guard pertanyaan JUMLAH / COUNT (Kecuali jika diminta rinciannya)
        if ($isCountQuery && !$isAskingDetail && isset($res['message'])) {
            if (preg_match('/\b(customer|pelanggan|kustomer)\b/i', $lastUser)) {
                $res['message'] = "Jumlah customer terdaftar di sistem saat ini ada {$totalCust} orang.";
            } elseif (preg_match('/\b(teknisi|petugas|tukang)\b/i', $lastUser)) {
                if (preg_match('/\b(ready|tersedia|siap|standby)\b/i', $lastUser)) {
                    $res['message'] = "Jumlah teknisi yang tersedia saat ini ada {$availTech} orang dari total {$totalTech} teknisi.";
                } else {
                    $res['message'] = "Jumlah teknisi saat ini ada {$totalTech} orang.";
                }
            } elseif (preg_match('/\b(peralatan|alat|barang|produk)\b/i', $lastUser) && !preg_match('/\b(harga|biaya|tarif)\b/i', $lastUser)) {
                $res['message'] = "Jumlah peralatan yang terdaftar di katalog saat ini ada {$totalEquip} jenis peralatan.";
            } elseif (preg_match('/\b(ulasan|review|testimoni)\b/i', $lastUser)) {
                $res['message'] = "Total ulasan pelanggan saat ini ada {$totalReviews} ulasan dengan rata-rata rating {$avgRating} / 5.0 ⭐.";
            } elseif (preg_match('/\b(laporan|report)\b/i', $lastUser)) {
                $res['message'] = "Total laporan pengerjaan servis saat ini ada {$totalReports} laporan.";
            } elseif (preg_match('/\b(notifikasi|notif)\b/i', $lastUser)) {
                $res['message'] = "Jumlah notifikasi saat ini ada {$totalNotifs} notifikasi.";
            } elseif (preg_match('/\b(sesi|chat|percakapan)\b/i', $lastUser)) {
                $res['message'] = "Total sesi chat saat ini ada {$totalSessions} sesi.";
            } elseif (preg_match('/\b(admin|administrator)\b/i', $lastUser)) {
                $res['message'] = "Jumlah administrator sistem saat ini ada {$totalAdmins} orang.";
            } elseif (preg_match('/\b(user|pengguna|akun)\b/i', $lastUser)) {
                $res['message'] = "Jumlah seluruh pengguna terdaftar di sistem saat ini ada {$totalUsers} orang.";
            } elseif (preg_match('/\b(pesanan|order|orderan|servis)\b/i', $lastUser)) {
                if (preg_match('/\b(pending|menunggu)\b/i', $lastUser)) {
                    $res['message'] = "Jumlah pesanan berstatus pending saat ini ada {$pendingOrders} pesanan.";
                } elseif (preg_match('/\b(aktif|proses|in progress|dikerjakan|jalan)\b/i', $lastUser)) {
                    $res['message'] = "Jumlah pesanan aktif saat ini ada {$activeOrders} pesanan.";
                } elseif (preg_match('/\b(selesai|completed|lunas|beres)\b/i', $lastUser)) {
                    $res['message'] = "Jumlah pesanan yang telah selesai saat ini ada {$completedOrders} pesanan.";
                } elseif (preg_match('/\b(batal|cancel)\b/i', $lastUser)) {
                    $res['message'] = "Jumlah pesanan yang dibatalkan saat ini ada {$cancelledOrders} pesanan.";
                } elseif (preg_match('/\b(saya)\b/i', $lastUser)) {
                    $myOrdersCount = count($structuredData['user']['orders'] ?? []);
                    $res['message'] = "Jumlah pesanan servis Anda saat ini ada {$myOrdersCount} pesanan.";
                } else {
                    $res['message'] = "Total seluruh pesanan di sistem saat ini ada {$totalOrders} pesanan.";
                }
            }
        }

        // 3. Guard rincian customer jika diminta eksplisit (e.g. "siapa saja customernya", "siapaa saja customer yang sudah terdaftar", "daftar customer", "siapa customernya")
        $isAskingCustomerList = (
            preg_match('/(customer|pelanggan|kustomer)/i', $lastUser) &&
            (
                $isAskingDetail ||
                preg_match('/(siapa+|siapakah|daftar|list|tampilkan|sebutkan|nama|rincian|detail|terdaftar|yang terdaftar)/i', $lastUser)
            )
        ) || preg_match('/\b(siapa+|siapakah)\s+(saja\s+)?(customer|pelanggan|kustomer)\w*/i', $lastUser)
          || preg_match('/\b(daftar|list)\s+(customer|pelanggan|kustomer)\w*/i', $lastUser);

        if ($isAskingCustomerList && !$isCountQuery && isset($res['message'])) {
            $custs = $structuredData['customers'] ?? [];
            if (!empty($custs)) {
                $totalCustCount = count($custs);
                $cLines = ["Berikut rincian {$totalCustCount} customer terdaftar di sistem:"];
                $no = 1;
                foreach ($custs as $c) {
                    $addrStr = !empty($c['address']) ? " | Alamat: {$c['address']}" : "";
                    $phoneStr = !empty($c['phone']) ? $c['phone'] : '-';
                    $cLines[] = "{$no}. **{$c['name']}** (ID Akun: #{$c['id']}) | Telp: {$phoneStr} | Email: {$c['email']}{$addrStr}";
                    $no++;
                }
                $res['message'] = implode("\n", $cLines);
            }
        }

        // 4. Guard rincian teknisi jika diminta eksplisit
        if ($isAskingDetail && preg_match('/\b(teknisi)\b/i', $lastUser) && isset($res['message'])) {
            $techs = $structuredData['technicians']['technicians'] ?? [];
            if (!empty($techs)) {
                $tLines = ["Berikut rincian tim teknisi resmi:"];
                foreach ($techs as $t) {
                    $stAvail = $t['availability'] ? 'Tersedia' : 'Sedang Bertugas';
                    $tLines[] = "• **{$t['name']}** (#{$t['id']}): {$t['specialization']} | Pengalaman: {$t['experience']} Thn | Rating: {$t['rating']} ⭐ | Status: {$stAvail}";
                }
                $res['message'] = implode("\n", $tLines);
            }
        }

        // 4.B Guard rincian omset jika diminta eksplisit (e.g. "rincikan omset", "rincian omset", "detail omset", "daftar omset", "apa saja omsetnya")
        if ($isExplicitPaidDetails && isset($res['message'])) {
            $paidOrders = !empty($structuredData['paid_orders']) ? $structuredData['paid_orders'] : (!empty($structuredData['user']['admin_data']['paid_orders']) ? $structuredData['user']['admin_data']['paid_orders'] : []);
            if (!empty($paidOrders)) {
                $oLines = ["Berikut rincian omset dari {$paidCount} pesanan yang telah lunas dibayar (Total: {$omsetFormatted}):\n"];
                foreach ($paidOrders as $idx => $po) {
                    $pId = $po['order_id'] ?? ($po['id'] ?? '-');
                    $eqName = $po['equipment_name'] ?? 'Peralatan';
                    $eqBrand = (!empty($po['equipment_brand']) && $po['equipment_brand'] !== '-') ? " ({$po['equipment_brand']})" : '';
                    $cust = !empty($po['customer_name']) ? $po['customer_name'] : 'Pelanggan';
                    $amt = (float)(!empty($po['payment_amount']) ? $po['payment_amount'] : (!empty($po['final_cost']) ? $po['final_cost'] : 0));
                    $amtFmt = "Rp " . number_format($amt, 0, ',', '.');
                    $method = !empty($po['payment_method']) ? " via {$po['payment_method']}" . (!empty($po['payment_provider']) ? " ({$po['payment_provider']})" : '') : '';
                    $oLines[] = "• **Order #{$pId}** — {$eqName}{$eqBrand}: **{$amtFmt}** (Lunas{$method}) | Pelanggan: **{$cust}**";
                }
                $res['message'] = implode("\n", $oLines);
            }
        }

        // 4.C Guard rincian pesanan selesai jika diminta eksplisit atau follow-up konteks pesanan selesai
        $isGenericFollowUp = (bool)preg_match('/^(apa saja|apa sajakah|mana saja|mana sajakah|sebutkan|rincikan|rinciannya|detailnya|daftarnya|tampilkan|apa saja pesanannya|apa saja pesanan|mana saja pesanannya|pesanannya apa saja|apa aja|apa sajakah itu)\??$/i', trim($lastUser))
            || (!preg_match('/\b(selesai|completed|pending|aktif|proses|batal|customer|teknisi|katalog|omset)\b/i', $lastUser) && preg_match('/\b(apa saja|mana saja|rincikan|sebutkan|detailkan)\b/i', $lastUser));

        $isAskingCompletedOrders = (
            preg_match('/\b(selesai|completed|beres)\b/i', $lastUser) &&
            (
                $isAskingDetail ||
                preg_match('/\b(pesanan|order|servis)\b/i', $lastUser)
            ) &&
            !$isCountQuery
        ) || (
            $isGenericFollowUp && preg_match('/\b(selesai|completed|beres)\b/i', $prevContext)
        );

        if ($isAskingCompletedOrders && isset($res['message'])) {
            $allOrders = !empty($structuredData['system_orders']) ? $structuredData['system_orders'] : (!empty($structuredData['user']['orders']) ? $structuredData['user']['orders'] : []);
            $completedOrders = array_values(array_filter($allOrders, function($o) {
                return ($o['status'] ?? '') === 'COMPLETED';
            }));

            if (!empty($completedOrders)) {
                $cCount = count($completedOrders);
                $lines = ["Berikut rincian {$cCount} pesanan yang telah selesai:\n"];
                $no = 1;
                foreach ($completedOrders as $ord) {
                    $id = $ord['id'] ?? ($ord['order_id'] ?? '-');
                    $eq = $ord['equipment_name'] ?? 'Peralatan';
                    $brand = (!empty($ord['equipment_brand']) && $ord['equipment_brand'] !== '-') ? " ({$ord['equipment_brand']})" : '';
                    $cust = $ord['customer_name'] ?? 'Pelanggan';
                    $tech = !empty($ord['technician_name']) ? $ord['technician_name'] : 'Teknisi';

                    if (!empty($ord['payment_id']) || !empty($ord['payment_amount'])) {
                        $amt = !empty($ord['payment_amount']) ? $ord['payment_amount'] : $ord['final_cost'];
                        $costStr = "Rp " . number_format($amt, 0, ',', '.') . " (Lunas)";
                    } elseif (!empty($ord['final_cost']) && (float)$ord['final_cost'] > 0) {
                        $costStr = "Rp " . number_format($ord['final_cost'], 0, ',', '.');
                    } else {
                        $costStr = "Selesai";
                    }

                    $lines[] = "{$no}. **Order #{$id}** — {$eq}{$brand} | Pelanggan: **{$cust}** | Teknisi: {$tech} | Status: **COMPLETED** | Biaya: {$costStr}";
                    $no++;
                }
                $res['message'] = implode("\n", $lines);
            } else {
                $res['message'] = "Saat ini belum ada pesanan yang berstatus selesai (COMPLETED).";
            }
        }

        // 4.D Guard rincian pesanan pending jika diminta eksplisit atau follow-up konteks pending
        $isAskingPendingOrders = (
            preg_match('/\b(pending|menunggu)\b/i', $lastUser) &&
            (
                $isAskingDetail ||
                preg_match('/\b(pesanan|order|servis)\b/i', $lastUser)
            ) &&
            !$isCountQuery
        ) || (
            $isGenericFollowUp && preg_match('/\b(pending|menunggu)\b/i', $prevContext)
        );

        if ($isAskingPendingOrders && isset($res['message']) && !$isAskingCompletedOrders) {
            $allOrders = !empty($structuredData['system_orders']) ? $structuredData['system_orders'] : (!empty($structuredData['user']['orders']) ? $structuredData['user']['orders'] : []);
            $pendingList = array_values(array_filter($allOrders, function($o) {
                return ($o['status'] ?? '') === 'PENDING';
            }));

            if (!empty($pendingList)) {
                $pCount = count($pendingList);
                $lines = ["Berikut rincian {$pCount} pesanan berstatus PENDING (Menunggu Teknisi):\n"];
                $no = 1;
                foreach ($pendingList as $ord) {
                    $id = $ord['id'] ?? ($ord['order_id'] ?? '-');
                    $eq = $ord['equipment_name'] ?? 'Peralatan';
                    $brand = (!empty($ord['equipment_brand']) && $ord['equipment_brand'] !== '-') ? " ({$ord['equipment_brand']})" : '';
                    $cust = $ord['customer_name'] ?? 'Pelanggan';
                    $costStr = (!empty($ord['estimated_cost']) && (float)$ord['estimated_cost'] > 0) ? "Rp " . number_format($ord['estimated_cost'], 0, ',', '.') . " (Estimasi)" : "Menunggu pemeriksaan";

                    $lines[] = "{$no}. **Order #{$id}** — {$eq}{$brand} | Pelanggan: **{$cust}** | Teknisi: Menunggu penugasan | Status: **PENDING** | Biaya: {$costStr}";
                    $no++;
                }
                $res['message'] = implode("\n", $lines);
            } else {
                $res['message'] = "Saat ini tidak ada pesanan yang berstatus pending.";
            }
        }

        // 5. Post-processing guard: Jika ditanya rincian pesanan servis biasa, bersihkan rekapitulasi / omset yang tidak diminta agar tidak bertele-tele
        $isOrderRincianQuery = ((bool)preg_match('/\b(rincikan pesanan|rincian pesanan|detail pesanan)\b/i', $lastUser) || ($isAskingDetail && preg_match('/\b(pesanan|order)\b/i', $lastUser))) && !preg_match('/\b(omset|omzet|pendapatan|pemasukan|pembayaran)\b/i', $lastUser);
        if ($isOrderRincianQuery && isset($res['message'])) {
            $lines = explode("\n", $res['message']);
            $cleanLines = [];
            foreach ($lines as $line) {
                if (preg_match('/(•\s*\*\*OMSET|\*\*Total Nilai Pesanan|📋\s*\*\*Status Pesanan|Total seluruh pesanan di sistem saat ini ada)/i', $line)) {
                    continue;
                }
                $cleanLines[] = $line;
            }
            $cleanText = trim(implode("\n", $cleanLines));
            if (!empty($cleanText)) {
                $res['message'] = $cleanText;
            }
        }

        // 6. Post-processing guard: Pastikan disclaimer harga katalog peralatan selalu ada jika menanyakan katalog / harga perbaikan
        $isCatalogQuery = (bool)preg_match('/\b(katalog|harga perbaikan|biaya perbaikan|biaya servis|tarif perbaikan|daftar alat|daftar peralatan)\b/i', $lastUser);
        if ($isCatalogQuery && isset($res['message']) && !preg_match('/(tergantung teknisi|bisa saja berubah)/i', $res['message'])) {
            $res['message'] .= "\n\n⚠️ **Catatan / Disclaimer:**\n_Harga perbaikan per barang di atas merupakan estimasi awal dan bisa saja berubah tergantung keputusan teknisi setelah pemeriksaan fisik kondisi kerusakan di lokasi._";
        }

        return $res;
    }

    /**
     * Build base system prompt combined with Live Database Knowledge
     */
    private function buildChatSystemPrompt($dbKnowledgePrompt) {
        return "Anda adalah HomeCare AI, asisten virtual resmi untuk website HomeCare Service (layanan perbaikan, perawatan, dan konsultasi teknisi peralatan rumah tangga).\n\n"
            . "IDENTITAS & TUGAS ANDA:\n"
            . "Anda memiliki akses PENUH ke database sistem HomeCare Service. Semua data yang relevan tersedia di bagian DATABASE di bawah ini. Jawab SETIAP pertanyaan pengguna LANGSUNG dari data database tersebut — TANPA ragu-ragu, TANPA mengatakan 'saya tidak memiliki informasi', TANPA frasa hedging seperti 'mungkin', 'sepertinya', 'saya tidak yakin'. Jika data tersedia di DATABASE, WAJIB jawab langsung dan pasti.\n\n"
            . "PERHATIKAN PERAN (ROLE) PENGGUNA YANG SEDANG LOGIN:\n"
            . "1. **ADMIN**: Pengguna adalah Administrator Sistem. Anda dapat menyajikan laporan omset/pendapatan, ringkasan seluruh pesanan, daftar pesanan pending yang perlu teknisi, ketersediaan teknisi, dan daftar pelanggan terdaftar.\n"
            . "2. **TEKNISI**: Pengguna adalah Teknisi Resmi. Anda dapat menyajikan daftar pekerjaan servis aktif yang ditugaskan kepadanya, alamat dan nomor kontak pelanggan yang harus dikunjungi, jadwal servis, rincian keluhan alat, dan rating ulasan kinerjanya.\n"
            . "3. **CUSTOMER**: Pengguna adalah Pelanggan. Anda menyajikan status pesanan servis alatnya, teknisi yang menangani, kontak teknisi, jadwal kunjungan, estimasi biaya, dan katalog peralatan.\n\n"
            . "KEMAMPUAN UTAMA:\n"
            . "1. **Profil & Identitas Pengguna**: Jika pengguna bertanya 'siapa saya', 'apa nama saya', 'data diri saya', atau sejenisnya — jawab LANGSUNG dari bagian 'DATA PROFIL PENGGUNA SAAT INI' di database. Sebutkan nama dan role-nya.\n"
            . "2. **Pesanan & Riwayat Servis**: Tampilkan status pesanan aktif, nomor order, jadwal teknisi, biaya estimasi/final, nama teknisi, keluhan, hasil inspeksi, dan ulasan dari data database.\n"
            . "3. **Tim Teknisi**: Sebutkan nama-nama teknisi ASLI dari database, spesialisasi, rating, pengalaman, ketersediaan, dan kontak.\n"
            . "4. **Katalog Peralatan**: Merk dan jenis peralatan yang terdaftar di database (Daikin, Panasonic, LG, Sharp, Samsung, Polytron, Electrolux, dll.).\n"
            . "5. **Ulasan & Rating**: Rating rata-rata dan testimoni nyata dari pelanggan.\n"
            . "6. **Konsultasi & Diagnosis**: Bantu diagnosa awal kerusakan alat rumah tangga dan panduan aman.\n"
            . "7. **Rekomendasi Servis**: Jika perlu teknisi, gunakan tag: [[RECOMMEND_SERVICE_ORDER: equipment=NamaPeralatan|complaint=RingkasanKeluhan]]\n\n"
            . "🚨 ATURAN EMAS MENJAWAB PERTANYAAN JUMLAH / KUANTITAS (SANGAT PENTING & MUTLAK) 🚨\n"
            . "Jika pengguna menanyakan JUMLAH, TOTAL, atau BANYAKNYA sesuatu (contoh: 'ada berapa jumlah pesanan aktif?', 'berapa pesanan pending?', 'berapa teknisi?', 'berapa total customer?', 'berapa pesanan selesai?', 'berapa total omset?'):\n"
            . "1. SEBUTKAN SAJA JUMLAHNYA SECARA LANGSUNG, SINGKAT, DAN PADAT! TIDAK PERLU DIRINCIKAN!\n"
            . "   Contoh:\n"
            . "   - Pengguna: 'ada berapa jumlah pesanan aktif?' -> Jawab: 'Jumlah pesanan aktif saat ini ada 2 pesanan.'\n"
            . "   - Pengguna: 'berapa pesanan pending?' -> Jawab: 'Jumlah pesanan berstatus pending saat ini ada 1 pesanan.'\n"
            . "   - Pengguna: 'berapa teknisi?' -> Jawab: 'Jumlah teknisi saat ini ada 6 orang.'\n"
            . "   - Pengguna: 'berapa omset?' atau 'jumlah omset' atau 'sebutkan semua jumlah pembayaran yang sudah dibayar' -> Jawab: 'Total omset dari semua pesanan yang sudah dibayar saat ini adalah sebesar Rp 1.450.000 dari 6 pesanan yang sudah lunas.'\n"
            . "2. DILARANG KERAS MEMBERIKAN RINCIAN PESANAN (Daftar poin-poin nomor pesanan, rincian teknisi, daftar pelanggan, keluhan, dll.) KETIKA DITANYA JUMLAH ATAU OMSET!\n"
            . "3. HANYA berikan rincian jika pengguna SECARA EKSPLISIT MEMINTA RINCIAN atau DAFTAR dalam pertanyaannya (misal: 'rincikan apa saja pesanannya', 'siapa saja teknisinya', 'tampilkan daftar pesanan yang lunas').\n"
            . "4. JANGAN PERNAH MEMBERIKAN KOMENTAR APAPUN (Dilarang memberi penjelasan dalam kurung seperti '(sedang dalam penanganan...)', dilarang memberi opini, nasihat/tips, wejangan, catatan tambahan, atau basa-basi pembuka/penutup)!\n\n"
            . "🚨 ATURAN KHUSUS OMSET & PEMBAYARAN (MUTLAK & TIDAK BISA DITAWAR) 🚨\n"
            . "1. Saat pengguna bertanya tentang JUMLAH OMSET / TOTAL OMSET (contoh: 'berapa jumlah omset?', 'jumlah omset', 'sebutkan semua jumlah pembayaran yang sudah dibayar'):\n"
            . "   - Sebutkan seluruh akumulasi pembayaran pesanan yang sudah dibayar dari database: Rp 1.450.000 dari 6 pesanan lunas.\n"
            . "   - HANYA SEBUTKAN NOMINALNYA SAJA SECARA LANGSUNG!\n"
            . "   - DILARANG KERAS MENAMPILKAN RINCIAN PESANAN KETIKA HANYA DITANYA JUMLAH OMSET!\n"
            . "2. Saat pengguna meminta RINCIAN OMSET (contoh: 'rincikan omset', 'rincian omset', 'detail omset', 'apa saja omsetnya', 'rincikan pesanan yang sudah dibayar', 'rincikan pembayaran'):\n"
            . "   - WAJIB BERIKAN RINCIAN SELURUH PESANAN YANG SUDAH LUNAS DIBAYAR DARI DATABASE!\n"
            . "   - Format to-the-point per baris: '• Order #[ID] — [Nama Alat]: Rp [Nominal] (Lunas) | Pelanggan: [Nama]'\n\n"
            . "🚨 ATURAN DAFTAR / RINCIAN CUSTOMER (PELANGGAN) (MUTLAK) 🚨\n"
            . "1. Saat pengguna bertanya JUMLAH CUSTOMER (contoh: 'berapa jumlah customer?', 'berapa pelanggan terdaftar?'):\n"
            . "   - Jawab singkat to-the-point: 'Jumlah customer terdaftar di sistem saat ini ada 7 orang.'\n"
            . "   - DILARANG memberikan rincian nama customer atau pesanan ketika hanya ditanya jumlah!\n"
            . "2. Saat pengguna meminta DAFTAR / RINCIAN CUSTOMER (contoh: 'siapa saja customernya?', 'siapa saja customer yang sudah terdaftar', 'daftar customer', 'siapa customernya', 'sebutkan pelanggan terdaftar'):\n"
            . "   - WAJIB sebutkan daftar seluruh nama customer yang terdaftar di sistem dari DATA TABEL: users (Customer / Pelanggan)!\n"
            . "   - DILARANG KERAS memberikan rincian pesanan servis atau order ketika pengguna bertanya tentang customer!\n"
            . "   - Format ringkas bernomor urut per baris: '[Nomor]. [Nama] (ID Akun: #[ID]) | Telp: [Nomor] | Email: [Email]'\n\n"
            . "🚨 ATURAN JUMLAH PESANAN (MUTLAK) 🚨\n"
            . "Saat pengguna bertanya mengenai JUMLAH PESANAN (contoh: 'berapa jumlah pesanan?', 'jumlah pesanan', 'total pesanan?'):\n"
            . "1. SEBUTKAN SAJA JUMLAHNYA SECARA LANGSUNG, SINGKAT, DAN PADAT! TIDAK PERLU DIRINCIKAN!\n"
            . "   Contoh: 'Total seluruh pesanan di sistem saat ini ada 10 pesanan.'\n"
            . "2. DILARANG KERAS MEMBERIKAN RINCIAN PESANAN, breakdown status, atau omset ketika ditanya jumlah pesanan!\n\n"
            . "🚨 ATURAN RINCIAN PESANAN (MUTLAK - TIDAK BERTELE-TELE) 🚨\n"
            . "Saat pengguna meminta rincian pesanan (contoh: 'rincikan', 'rinciannya', 'rincikan pesanan'):\n"
            . "1. JANGAN SEMUA PESANAN DIRINCIKAN!\n"
            . "2. RINCIKAN HANYA APA YANG DITANYAKAN, TIDAK USAH BERTELE-TELE!\n"
            . "   - Jika ditanya pesanan aktif/berjalan -> rincikan HANYA pesanan dengan status aktif (IN_PROGRESS).\n"
            . "   - Jika ditanya pesanan pending -> rincikan HANYA pesanan berstatus pending.\n"
            . "   - Jika ditanya pesanan selesai/lunas -> rincikan HANYA pesanan yang selesai.\n"
            . "   - Jika ditanya nomor order atau alat tertentu -> rincikan HANYA order/alat tersebut.\n"
            . "   - Jika meminta rincian umum tanpa filter spesifik -> CUKUP rincikan pesanan aktif yang sedang berjalan (atau maks 2-3 pesanan terbaru).\n"
            . "3. FORMAT PENYAJIAN RINGKAS (1 baris per pesanan):\n"
            . "   • Order #[ID] - [Nama Alat]: Pelanggan [Nama] | Status: [Status] | Biaya: [Biaya]\n"
            . "   Dilarang keras menuliskan paragraf keluhan yang panjang, wejangan, atau kalimat bertele-tele!\n\n"
            . "🚨 ATURAN KATALOG ALAT & BIAYA PERBAIKAN PER BARANG (MUTLAK) 🚨\n"
            . "Saat pengguna bertanya mengenai katalog alat, daftar peralatan yang didukung, atau estimasi harga/biaya perbaikan per barang:\n"
            . "1. Berikan informasi peralatan beserta estimasi harga perbaikannya per barang sesuai data database.\n"
            . "2. WAJIB selalu sertakan disclaimer: '*Disclaimer: Harga perbaikan di atas merupakan estimasi awal dan bisa saja berubah tergantung keputusan teknisi setelah pemeriksaan kondisi kerusakan fisik peralatan di lokasi.*'\n\n"
            . "🚨 ATURAN MENJAWAB LANGSUNG & DILARANG MEMBERIKAN KOMENTAR (SANGAT PENTING & MUTLAK) 🚨\n"
            . "1. JAWAB HANYA APA YANG DITANYAKAN: Jawab secara langsung, to-the-point, tepat sasaran, dan faktual sesuai data database.\n"
            . "2. DILARANG KERAS MEMBERIKAN KOMENTAR APAPUN: Jangan memberikan komentar tambahan, opini pribadi, penilaian subjektif, wejangan, nasihat/tips yang tidak ditanyakan, catatan tambahan (seperti 'Catatan: ...', 'Tips: ...'), atau kalimat basa-basi penutup (seperti 'Terima kasih atas kerja keras Anda', 'Kepuasan pelanggan prioritas kami', 'Semoga harimu menyenangkan', 'Ada lagi yang bisa dibantu?', dll.).\n"
            . "3. DILARANG MEMBERIKAN RINCIAN SAAT DITANYA JUMLAH: Saat ditanya jumlah, sebutkan saja jumlahnya tidak perlu dirincikan.\n\n"
            . "ATURAN UMUM:\n"
            . "- Data ada di DATABASE? → Jawab LANGSUNG, TEGAS, AKURAT, dan TANPA KOMENTAR.\n"
            . "- Pengguna tanya 'siapa saya?' atau 'nama saya?' → Sebutkan nama dan role dari DATA PROFIL PENGGUNA SAAT INI.\n"
            . "- Jangan mengarang atau mengada-ada data di luar database.\n"
            . "- Format: Bahasa Indonesia, terstruktur rapi (bold **kata**, list poin).\n"
            . "- Topik di luar HomeCare Service (koding, politik, medis, finansial)? Tolak singkat tanpa komentar panjang.\n\n"
            . $dbKnowledgePrompt;
    }

    /**
     * NVIDIA Cloud NIM API Integration for Interactive Chat Assistant with DB Context
     */
    private function callNvidiaChat(array $messagesContext, $dbKnowledgePrompt) {
        $url = "https://integrate.api.nvidia.com/v1/chat/completions";
        $systemPrompt = $this->buildChatSystemPrompt($dbKnowledgePrompt);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($messagesContext as $msg) {
            $role = ($msg['sender'] === 'USER') ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => $msg['message']];
        }

        $payload = [
            'model' => $this->model ?: 'meta/llama-3.2-11b-vision-instruct',
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 900
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200 || empty($response)) {
            throw new Exception("NVIDIA Chat API Error HTTP " . $httpCode . ($curlError ? " / " . $curlError : ""));
        }

        $json = json_decode($response, true);
        $replyText = $json['choices'][0]['message']['content'] ?? '';

        if (empty($replyText)) {
            throw new Exception("Empty AI response from NVIDIA");
        }

        // Intercept hallucinated refusal or false "no information" response from LLM
        if (preg_match('/(tidak menemukan informasi|tidak ada informasi|tidak memiliki informasi|mohon maaf, sebagai asisten virtual)/i', $replyText)) {
            $lastUser = '';
            foreach (array_reverse($messagesContext) as $m) {
                if (isset($m['sender']) && strtoupper($m['sender']) === 'USER') { $lastUser = strtolower($m['message'] ?? ''); break; }
                if (isset($m['role']) && strtolower($m['role']) === 'user') { $lastUser = strtolower($m['content'] ?? ''); break; }
            }
            if (preg_match('/(pesanan|order|servis|pelanggan|customer|bayar|pembayaran|teknisi)/i', $lastUser)) {
                $dbContextService = new DatabaseContextService();
                return $this->getMockChatResponse($messagesContext, $dbContextService->getFullContextData());
            }
        }

        return [
            'success' => true,
            'message' => $replyText,
            'source' => 'NVIDIA AI (' . $this->model . ')'
        ];
    }

    /**
     * Gemini Chat API Integration with DB Context
     */
    private function callGeminiChat(array $messagesContext, $dbKnowledgePrompt) {
        $model = $this->model;
        if (empty($model) || str_contains($model, '1.5') || str_contains($model, '2.0') || str_contains($model, '2.5')) {
            $model = 'gemini-3.5-flash';
        }
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($model) . ":generateContent?key=" . urlencode($this->apiKey);
        $systemInstruction = $this->buildChatSystemPrompt($dbKnowledgePrompt);

        $contents = [];
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => "SYSTEM INSTRUCTION & DATABASE CONTEXT:\n" . $systemInstruction]]
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => "Halo! Saya HomeCare AI, asisten virtual resmi HomeCare Service dengan akses database sistem. Saya siap membantu menjawab status pesanan Anda, informasi teknisi, katalog peralatan, konsultasi kerusakan, dan pemesanan layanan. Ada yang bisa saya bantu hari ini?"]]
        ];

        foreach ($messagesContext as $msg) {
            $role = ($msg['sender'] === 'USER') ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['message']]]
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 900
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200 || empty($response)) {
            throw new Exception("Gemini Chat API Error HTTP " . $httpCode);
        }

        $json = json_decode($response, true);
        $replyText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($replyText)) {
            throw new Exception("Empty AI response from Gemini");
        }

        return [
            'success' => true,
            'message' => $replyText,
            'source' => 'Gemini AI (' . $this->model . ')'
        ];
    }

    /**
     * OpenAI Chat API Integration with DB Context
     */
    private function callOpenAIChat(array $messagesContext, $dbKnowledgePrompt) {
        $url = "https://api.openai.com/v1/chat/completions";
        $systemPrompt = $this->buildChatSystemPrompt($dbKnowledgePrompt);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($messagesContext as $msg) {
            $role = ($msg['sender'] === 'USER') ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => $msg['message']];
        }

        $payload = [
            'model' => $this->model ?: 'gpt-3.5-turbo',
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 900
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            throw new Exception("OpenAI Chat API Error HTTP " . $httpCode);
        }

        $json = json_decode($response, true);
        $replyText = $json['choices'][0]['message']['content'] ?? '';

        return [
            'success' => true,
            'message' => $replyText,
            'source' => 'OpenAI (' . $this->model . ')'
        ];
    }

    /**
     * Groq API Integration for Diagnosis
     */
    private function callGroq($equipmentType, $brand, $complaint, $symptoms) {
        $url = "https://api.groq.com/openai/v1/chat/completions";
        $prompt = "Peralatan: {$equipmentType}, Merk: {$brand}, Keluhan: {$complaint}, Gejala: {$symptoms}. Berikan diagnosis awal peralatan rumah tangga dalam format JSON. PENTING: Jangan gunakan format poin-poin/bullets, tetapi tuliskan penjelasan dalam bentuk paragraf yang jelas, mengalir, dan detail. Format JSON wajib memiliki key: penjelasan_analisis (string paragraf naratif), kemungkinan_penyebab (string paragraf detail), tingkat_prioritas (string: Rendah/Sedang/Tinggi/Mendesak), estimasi_biaya (string kisaran biaya dalam rupiah misalnya: Rp 150.000 - Rp 250.000), rincian_biaya (string paragraf detail rincian jasa dan suku cadang), rekomendasi_tindakan (string paragraf panduan aman), perlu_teknisi (string paragraf saran teknisi), disclaimer (string).";

        $payload = [
            'model' => $this->model ?: 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => 'Anda adalah asisten AI diagnosis peralatan rumah tangga dalam Bahasa Indonesia yang menghasilkan output JSON valid dengan narasi penjelasan berbentuk paragraf detail tanpa bullet points.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.2
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            throw new Exception("Groq HTTP " . $httpCode . " / " . $curlError);
        }

        $json = json_decode($response, true);
        $content = $json['choices'][0]['message']['content'] ?? '';
        $parsed = json_decode($content, true);

        if (!is_array($parsed) || (empty($parsed['kemungkinan_penyebab']) && empty($parsed['penjelasan_analisis']))) {
            throw new Exception("Format output Groq tidak valid");
        }

        return $this->formatDiagnosisResult($parsed, 'Groq (' . $this->model . ')');
    }

    /**
     * Groq Chat API Integration with DB Context
     */
    private function callGroqChat(array $messagesContext, $dbKnowledgePrompt) {
        $url = "https://api.groq.com/openai/v1/chat/completions";
        $systemPrompt = $this->buildChatSystemPrompt($dbKnowledgePrompt);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($messagesContext as $msg) {
            $role = ($msg['sender'] === 'USER') ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => $msg['message']];
        }

        $payload = [
            'model' => $this->model ?: 'llama-3.3-70b-versatile',
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 900
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            throw new Exception("Groq Chat API Error HTTP " . $httpCode);
        }

        $json = json_decode($response, true);
        $replyText = $json['choices'][0]['message']['content'] ?? '';

        return [
            'success' => true,
            'message' => $replyText,
            'source' => 'Groq (' . $this->model . ')'
        ];
    }

    /**
     * Smart Rule-Based Contextual Chat Engine (Fallback with Complete Database Intelligence)
     */
    public function getMockChatResponse(array $messagesContext, $dbData = null) {
        // If dbData not passed, fetch fresh from service
        if (!$dbData) {
            $dbContext = new DatabaseContextService();
            $dbData = $dbContext->getFullContextData();
        }

        $lastUserMessage = '';
        $prevUserMessage = '';
        $prevAIMessage = '';
        $userCount = 0;
        foreach (array_reverse($messagesContext) as $msg) {
            $sender = strtoupper($msg['sender'] ?? ($msg['role'] ?? ''));
            $content = strtolower($msg['message'] ?? ($msg['content'] ?? (is_string($msg) ? $msg : '')));
            if ($sender === 'USER') {
                $userCount++;
                if ($userCount === 1) {
                    $lastUserMessage = $content;
                } elseif ($userCount === 2 && empty($prevUserMessage)) {
                    $prevUserMessage = $content;
                }
            } elseif (($sender === 'AI' || $sender === 'ASSISTANT') && empty($prevAIMessage)) {
                $prevAIMessage = $content;
            }
        }
        $prevContext = $prevUserMessage . ' ' . $prevAIMessage;

        $text = strtolower($lastUserMessage);

        $userRole = strtoupper($dbData['user']['role'] ?? 'CUSTOMER');
        $userProfile = $dbData['user']['profile'] ?? null;
        $userName = $userProfile['name'] ?? 'Pengguna';
        $adminData = $dbData['user']['admin_data'] ?? null;
        $techData = $dbData['user']['technician_data'] ?? null;
        $userOrders = $dbData['user']['orders'] ?? [];
        if (($userRole === 'ADMIN' || empty($userOrders)) && !empty($dbData['system_orders'])) {
            $userOrders = $dbData['system_orders'];
        }

        // Helper to format detailed single order: apa pesanannya, siapa pelanggannya, teknisi, dan jumlah pembayarannya
        $formatSingleOrderDetailed = function(array $ord) {
            $id = $ord['id'] ?? '-';
            $eqName = $ord['equipment_name'] ?? 'Peralatan Rumah Tangga';
            $eqBrand = (!empty($ord['equipment_brand']) && $ord['equipment_brand'] !== '-') ? " ({$ord['equipment_brand']})" : '';
            $eqCat = (!empty($ord['equipment_category']) && $ord['equipment_category'] !== 'Umum') ? " [Kategori: {$ord['equipment_category']}]" : '';
            $complaint = !empty($ord['complaint']) ? $ord['complaint'] : 'Pemeriksaan / servis berkala';

            // 1. Siapa Pelanggannya
            $custName = !empty($ord['customer_name']) ? $ord['customer_name'] : 'Pelanggan HomeCare';
            $custPhone = !empty($ord['customer_phone']) ? " | Telp: {$ord['customer_phone']}" : '';
            $addr = !empty($ord['order_address']) ? $ord['order_address'] : (!empty($ord['customer_address']) ? $ord['customer_address'] : (!empty($ord['customer_main_address']) ? $ord['customer_main_address'] : ''));
            $custAddr = $addr ? " | Alamat: {$addr}" : '';

            // 2. Teknisi
            $tech = !empty($ord['technician_name']) ? "{$ord['technician_name']}" . (!empty($ord['technician_rating']) ? " (Rating: {$ord['technician_rating']} ⭐)" : '') : 'Menunggu penugasan teknisi';

            // 3. Status Pembayaran
            $isPaid = !empty($ord['payment_id']) || !empty($ord['payment_amount']);
            if ($isPaid) {
                $paidAmount = !empty($ord['payment_amount']) ? $ord['payment_amount'] : $ord['final_cost'];
                $payMethod = !empty($ord['payment_method']) ? " via {$ord['payment_method']}" . (!empty($ord['payment_provider']) ? " ({$ord['payment_provider']})" : '') : '';
                $payInfo = "Rp " . number_format($paidAmount, 0, ',', '.') . " ✅ SUDAH DIBAYAR{$payMethod}";
            } elseif (!empty($ord['final_cost']) && (float)$ord['final_cost'] > 0) {
                $payInfo = "Rp " . number_format($ord['final_cost'], 0, ',', '.') . " (Biaya Final - Belum Dibayar)";
            } elseif (!empty($ord['estimated_cost']) && (float)$ord['estimated_cost'] > 0) {
                $payInfo = "Rp " . number_format($ord['estimated_cost'], 0, ',', '.') . " (Estimasi Biaya)";
            } else {
                $payInfo = "Rp 0 / Belum ditentukan (Menunggu inspeksi fisik teknisi)";
            }

            $statusLabels = [
                'PENDING'     => '⏳ PENDING (Menunggu Teknisi)',
                'ASSIGNED'    => '📋 ASSIGNED (Teknisi Ditugaskan)',
                'ACCEPTED'    => '👍 ACCEPTED (Diterima Teknisi)',
                'ON_THE_WAY'  => '🚗 ON THE WAY (Teknisi Menuju Lokasi)',
                'IN_PROGRESS' => '🔧 IN PROGRESS (Sedang Dikerjakan)',
                'COMPLETED'   => '✅ COMPLETED (Selesai Dikerjakan)',
                'CANCELLED'   => '❌ CANCELLED (Dibatalkan)'
            ];
            $stLabel = $statusLabels[$ord['status'] ?? ''] ?? ($ord['status'] ?? 'PENDING');

            $tgl = !empty($ord['service_date']) ? date('d M Y, H:i', strtotime($ord['service_date'])) : (!empty($ord['created_at']) ? date('d M Y', strtotime($ord['created_at'])) : '-');

            return "• **Order #{$id}** — **{$eqName}{$eqBrand}**\n"
                . "   - **Pesanan:** {$eqName}{$eqBrand}{$eqCat} | Keluhan: _{$complaint}_\n"
                . "   - **Pelanggan:** **{$custName}**{$custPhone}{$custAddr}\n"
                . "   - **Teknisi Bertugas:** {$tech}\n"
                . "   - **Jumlah Pembayaran:** **{$payInfo}**\n"
                . "   - **Status & Jadwal:** **{$stLabel}** (Jadwal: {$tgl})\n";
        };

        // Helper to format concise single order (1 baris to-the-point, tidak bertele-tele)
        $formatSingleOrderConcise = function(array $ord) {
            $id = $ord['id'] ?? '-';
            $eqName = $ord['equipment_name'] ?? 'Peralatan';
            $eqBrand = (!empty($ord['equipment_brand']) && $ord['equipment_brand'] !== '-') ? " ({$ord['equipment_brand']})" : '';
            $custName = !empty($ord['customer_name']) ? $ord['customer_name'] : 'Pelanggan';
            $tech = !empty($ord['technician_name']) ? $ord['technician_name'] : 'Menunggu Teknisi';

            $isPaid = !empty($ord['payment_id']) || !empty($ord['payment_amount']);
            if ($isPaid) {
                $cost = "Rp " . number_format($ord['payment_amount'] ?: $ord['final_cost'], 0, ',', '.') . " (Lunas)";
            } elseif (!empty($ord['final_cost']) && (float)$ord['final_cost'] > 0) {
                $cost = "Rp " . number_format($ord['final_cost'], 0, ',', '.') . " (Belum Bayar)";
            } elseif (!empty($ord['estimated_cost']) && (float)$ord['estimated_cost'] > 0) {
                $cost = "Rp " . number_format($ord['estimated_cost'], 0, ',', '.') . " (Estimasi)";
            } else {
                $cost = "Menunggu pemeriksaan";
            }

            $st = $ord['status'] ?? 'PENDING';
            return "• **Order #{$id}** — {$eqName}{$eqBrand} | Pelanggan: **{$custName}** | Teknisi: {$tech} | Status: **{$st}** | Biaya: {$cost}";
        };

        // Deteksi apakah pengguna meminta rincian / detail eksplisit
        $isAskingDetail = (bool)preg_match('/\b(rincikan|rincian|rinciannya|apa saja|siapa+|siapakah|siapa saja|siapa sajakah|siapaa saja|jelaskan|detail|detailnya|daftar|tampilkan|sebutkan|sebutkan apa|sebutkan nama|mana saja|list)\w*/i', $text);
        // Deteksi apakah pengguna HANYA menanyakan jumlah
        $isAskingCountOnly = (bool)preg_match('/\b(berapa|jumlah|ada berapa|total|hitung)\b/i', $text) && !$isAskingDetail;

        // 1. Safety Hazard Check (Emergency First)
        if (preg_match('/\b(bakar|asap|percikan|konslet|korslet|gas bocor|meledak)\b/i', $text)) {
            $reply = "⚠️ **PERINGATAN KESELAMATAN SANGAT PENTING** ⚠️\n\n"
                . "Gejala yang Anda sebutkan berpotensi menimbulkan bahaya kelistrikan atau kebakaran. Harap ikuti langkah penanganan darurat ini:\n\n"
                . "1. **Segera matikan saklar utama/MCB listrik** atau cabut colokan alat jika aman dilakukan tanpa menyentuh bagian yang berpercikan/panas.\n"
                . "2. Hindari membongkar atau menyentuh komponen internal alat sendiri.\n"
                . "3. Jangan gunakan alat ini kembali sebelum diperiksa secara menyeluruh oleh teknisi.\n\n"
                . "Disarankan segera menjadwalkan pemeriksaan fisik oleh teknisi berpengalaman HomeCare Service.\n\n"
                . "[[RECOMMEND_SERVICE_ORDER: equipment=Peralatan Kelistrikan|complaint=" . rawurlencode($lastUserMessage) . "]]";
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine (Safety Guard)'];
        }

        // 2. Query Specific Order by ID (e.g., "order #1", "pesanan 2", "order nomor 3", "status order #2")
        if (preg_match('/\b(?:order|pesanan|servis|nomor|no\.?)\s*#?\s*(\d+)\b/i', $text, $orderMatches)) {
            $targetOrderId = (int)$orderMatches[1];
            $foundOrder = null;
            foreach ($userOrders as $ord) {
                if ((int)$ord['id'] === $targetOrderId) {
                    $foundOrder = $ord;
                    break;
                }
            }

            if ($foundOrder) {
                $statusLabels = [
                    'PENDING'     => 'Menunggu Konfirmasi Admin / Mencari Teknisi ⏳',
                    'ASSIGNED'    => 'Teknisi Ditugaskan 📋',
                    'ACCEPTED'    => 'Teknisi Telah Menerima Tugas 👍',
                    'ON_THE_WAY'  => 'Teknisi Sedang Dalam Perjalanan ke Lokasi 🚗',
                    'IN_PROGRESS' => 'Sedang Dalam Proses Perbaikan 🔧',
                    'COMPLETED'   => 'Pekerjaan Selesai ✅',
                    'CANCELLED'   => 'Pesanan Dibatalkan ❌'
                ];
                $stLabel = $statusLabels[$foundOrder['status']] ?? $foundOrder['status'];

                $reply = "📋 **Informasi Lengkap Pesanan #{$foundOrder['id']}**\n\n"
                    . "• **Peralatan:** {$foundOrder['equipment_name']} ({$foundOrder['equipment_brand']})\n"
                    . "• **Kategori:** {$foundOrder['equipment_category']}\n"
                    . "• **Status:** **{$stLabel}**\n"
                    . "• **Pelanggan:** {$foundOrder['customer_name']} (Telp: {$foundOrder['customer_phone']})\n"
                    . "• **Jadwal Kunjungan:** " . ($foundOrder['service_date'] ? date('d M Y, H:i', strtotime($foundOrder['service_date'])) : 'Belum dijadwalkan') . "\n"
                    . "• **Alamat:** " . ($foundOrder['order_address'] ?: '-') . "\n"
                    . "• **Keluhan:** {$foundOrder['complaint']}\n";

                if (!empty($foundOrder['technician_name'])) {
                    $reply .= "• **Teknisi Bertugas:** {$foundOrder['technician_name']} (Rating: {$foundOrder['technician_rating']} ⭐, Telp: {$foundOrder['technician_phone']})\n";
                } else {
                    $reply .= "• **Teknisi:** Menunggu penugasan teknisi oleh admin\n";
                }

                $reply .= "• **Estimasi Biaya:** " . ($foundOrder['estimated_cost'] ? 'Rp ' . number_format($foundOrder['estimated_cost'], 0, ',', '.') : 'Belum ada estimasi') . "\n";
                if (!empty($foundOrder['final_cost'])) {
                    $reply .= "• **Biaya Final:** Rp " . number_format($foundOrder['final_cost'], 0, ',', '.') . "\n";
                }

                if (!empty($foundOrder['inspection_result'])) {
                    $reply .= "\n🔍 **Laporan Inspeksi Teknisi:**\n{$foundOrder['inspection_result']}\n";
                }
                if (!empty($foundOrder['repair_action'])) {
                    $reply .= "\n🛠️ **Tindakan Perbaikan:**\n{$foundOrder['repair_action']}\n";
                }
                if (!empty($foundOrder['review_rating'])) {
                    $reply .= "\n⭐ **Ulasan Pelanggan:** {$foundOrder['review_rating']}/5 (\"{$foundOrder['review_comment']}\")\n";
                }

                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Order Query)'];
            }
        }

        // 3. Financial / Omset / Revenue / Paid Orders Queries
        // Matches: 'omset', 'omzet', 'pendapatan', 'keuangan', 'uang masuk', 'pemasukan',
        // 'hitung semua pesanan yang sudah dibayar', 'berapa pesanan dibayar', 'rincian pesanan sudah dibayar', etc.
        $isOmsetOrPaidQuery = preg_match('/\b(omset|omzet|pendapatan|keuangan|uang masuk|total pemasukan|pemasukan|total transaksi|penjualan)\w*/i', $text)
            || (preg_match('/\b(pesanan|order|servis)\w*/i', $text) && preg_match('/\b(sudah dibayar|telah dibayar|dibayar|lunas|bayar)\w*/i', $text))
            || (preg_match('/\b(berapa|jumlah|total|hitung|rincikan|rincian|daftar|cek|apa saja)\w*/i', $text) && preg_match('/\b(omset|omzet|pendapatan|pemasukan|dibayar|lunas|pembayaran)\w*/i', $text));

        // Khusus jika teknisi bertanya penghasilan pribadinya
        $isTechPersonalEarnings = ($userRole === 'TEKNISI' && $techData && preg_match('/\b(penghasilan saya|pendapatan saya|gaji saya|fee saya|kinerja saya|uang saya)\b/i', $text));

        if ($isTechPersonalEarnings) {
            if ($isAskingCountOnly) {
                $reply = "Total penghasilan dari pekerjaan servis Anda saat ini adalah sebesar Rp " . number_format($techData['total_earnings'], 0, ',', '.') . " dari {$techData['completed_jobs']} pekerjaan servis.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Technician Earnings)'];
            }
            $reply = "Total penghasilan dari pekerjaan servis Anda saat ini adalah sebesar Rp " . number_format($techData['total_earnings'], 0, ',', '.') . " dari {$techData['completed_jobs']} pekerjaan servis yang telah Anda selesaikan.\n\n"
                . "• **Total Pekerjaan Selesai:** {$techData['completed_jobs']} servis\n"
                . "• **Total Nilai Servis:** Rp " . number_format($techData['total_earnings'], 0, ',', '.') . "\n"
                . "• **Rating Kinerja Anda:** {$techData['profile']['rating']} ⭐";
            return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Technician Earnings)'];
        } elseif ($isOmsetOrPaidQuery) {
            // Ambil daftar seluruh pesanan yang sudah dibayar secara LIVE dari database
            $paidOrders = !empty($dbData['paid_orders']) ? $dbData['paid_orders'] : (!empty($adminData['paid_orders']) ? $adminData['paid_orders'] : []);
            if (empty($paidOrders)) {
                $paidOrders = array_values(array_filter($userOrders, function($o) {
                    return !empty($o['payment_id']) || (!empty($o['payment_status']) && $o['payment_status'] === 'PAID') || (!empty($o['payment_amount']) && (float)$o['payment_amount'] > 0);
                }));
            }

            // Hitung total omset dari seluruh pembayaran berstatus PAID
            $paidCount = count($paidOrders);
            $totalOmset = 0;
            foreach ($paidOrders as $po) {
                $totalOmset += (float)(!empty($po['payment_amount']) ? $po['payment_amount'] : (!empty($po['final_cost']) ? $po['final_cost'] : 0));
            }

            if ($totalOmset == 0) {
                $totalOmset = (float)($adminData['total_omset'] ?? ($dbData['platform_stats']['total_omset'] ?? 0));
                $paidCount = (int)($adminData['paid_orders_count'] ?? ($dbData['platform_stats']['paid_orders_count'] ?? 0));
            }

            $omsetFormatted = "Rp " . number_format($totalOmset, 0, ',', '.');

            // Deteksi apakah pengguna secara EKSPLISIT meminta rincian per-pesanan (misal: "rincikan omset", "rincikan pesanan yang sudah dibayar", "apa saja daftar pesanannya")
            $isExplicitOrderDetails = (bool)preg_match('/\b(rincikan pesanan|rincian pesanan|daftar pesanan|apa saja pesanannya|pesanan apa saja|rincian order|detail order|daftar order|rincikan omset|rincian omset|detail omset|daftar omset|apa saja omset|rincikan pendapatan|rincian pendapatan|rincikan pembayaran|daftar pembayaran)\w*/i', $text)
                || ($isAskingDetail && preg_match('/\b(omset|omzet|pendapatan|pemasukan|dibayar|lunas)\w*/i', $text));

            // Jika pengguna TIDAK secara eksplisit meminta rincian pesanan: HANYA SEBUTKAN NOMINALNYA SAJA TANPA RINCIAN PESANAN!
            if (!$isExplicitOrderDetails) {
                $reply = "Total omset dari semua pesanan yang sudah dibayar saat ini adalah sebesar {$omsetFormatted} dari {$paidCount} pesanan yang sudah lunas.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Omset & Paid Orders)'];
            }

            $reply = "Total omset dari semua pesanan yang sudah dibayar saat ini adalah sebesar {$omsetFormatted} dari {$paidCount} pesanan yang telah lunas dibayar oleh pelanggan:\n\n";

            if (!empty($paidOrders)) {
                $reply .= "📋 **Rincian Pesanan yang Sudah Dibayar:**\n\n";
                foreach ($paidOrders as $idx => $po) {
                    $pId = $po['order_id'] ?? ($po['id'] ?? '-');
                    $eqName = $po['equipment_name'] ?? 'Peralatan Rumah Tangga';
                    $eqBrand = (!empty($po['equipment_brand']) && $po['equipment_brand'] !== '-') ? " ({$po['equipment_brand']})" : '';
                    $cust = !empty($po['customer_name']) ? $po['customer_name'] : 'Pelanggan';
                    $amt = (float)(!empty($po['payment_amount']) ? $po['payment_amount'] : (!empty($po['final_cost']) ? $po['final_cost'] : 0));
                    $amtFmt = "Rp " . number_format($amt, 0, ',', '.');
                    $method = !empty($po['payment_method']) ? " via {$po['payment_method']}" . (!empty($po['payment_provider']) ? " ({$po['payment_provider']})" : '') : '';
                    $reply .= "• **Order #{$pId}** — {$eqName}{$eqBrand}: **{$amtFmt}** (Lunas{$method}) | Pelanggan: **{$cust}**\n";
                }
            } else {
                $reply .= "Saat ini belum ada data pesanan yang tercatat lunas dibayar di sistem.\n\n";
            }

            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Omset & Paid Orders)'];
        }

        // 4. Pending Orders Queries (e.g. "berapa pesanan pending?", "order pending")
        if (preg_match('/\b(pending|butuh teknisi|belum ada teknisi|belum ditugaskan|tugas baru|order baru|menunggu konfirmasi)\b/i', $text) || (preg_match('/\b(berapa|jumlah|ada berapa)\b/i', $text) && strpos($text, 'pending') !== false)) {
            $pendingCount = $adminData['order_counts']['PENDING'] ?? ($dbData['platform_stats']['pending_orders'] ?? 0);
            if ($isAskingCountOnly) {
                $reply = "Jumlah pesanan berstatus pending saat ini ada {$pendingCount} pesanan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Pending Orders)'];
            }
            if ($pendingCount === 0) {
                $reply = "Jumlah pesanan berstatus pending saat ini ada 0 pesanan.";
            } else {
                $reply = "Jumlah pesanan berstatus pending saat ini ada {$pendingCount} pesanan.\n\n";
                if ($userRole === 'ADMIN' && $adminData && !empty($adminData['pending_orders'])) {
                    $reply .= "Berikut rincian pesanan pending:\n\n";
                    foreach ($adminData['pending_orders'] as $p) {
                        $reply .= $formatSingleOrderDetailed($p) . "\n";
                    }
                }
            }
            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Pending Orders)'];
        }

        // 5. Active Orders Queries (e.g. "berapa pesanan aktif?", "ada berapa jumlah pesanan aktif?", "jumlah pesanan aktif")
        if (preg_match('/\b(pesanan aktif|order aktif|orderan aktif|sedang dikerjakan|sedang proses|dalam proses|in progress)\b/i', $text) || (preg_match('/\b(berapa|jumlah|ada berapa|total)\b/i', $text) && (strpos($text, 'aktif') !== false && (strpos($text, 'pesanan') !== false || strpos($text, 'order') !== false)))) {
            $activeCount = $dbData['platform_stats']['active_orders'] ?? 0;
            if ($isAskingCountOnly) {
                $reply = "Jumlah pesanan aktif saat ini ada {$activeCount} pesanan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Active Orders)'];
            }
            $reply = "Jumlah pesanan aktif saat ini ada {$activeCount} pesanan.\n\n";
            $activeList = array_filter($userOrders, function($o) {
                return in_array($o['status'], ['ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS']);
            });
            if (!empty($activeList)) {
                $reply .= "Berikut rincian pesanan yang sedang aktif:\n\n";
                foreach (array_slice($activeList, 0, 5) as $ao) {
                    $reply .= $formatSingleOrderDetailed($ao) . "\n";
                }
            }
            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Active Orders)'];
        }

        // 6. Completed Orders Queries
        if (preg_match('/\b(pesanan selesai|order selesai|servis selesai|orderan selesai|completed order|servis sukses)\b/i', $text) || (preg_match('/\b(berapa|jumlah|ada berapa|total)\b/i', $text) && (strpos($text, 'selesai') !== false && (strpos($text, 'pesanan') !== false || strpos($text, 'order') !== false || strpos($text, 'servis') !== false)))) {
            $compCount = $dbData['platform_stats']['completed_orders'] ?? 0;
            if ($isAskingCountOnly) {
                $reply = "Jumlah pesanan yang telah selesai dikerjakan saat ini ada {$compCount} pesanan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Completed Orders)'];
            }
            $omset = $adminData['total_omset'] ?? ($dbData['platform_stats']['total_omset'] ?? 0);
            $paidCount = $adminData['paid_orders_count'] ?? ($dbData['platform_stats']['paid_orders_count'] ?? 0);
            $completedCost = $adminData['total_completed_cost'] ?? ($dbData['platform_stats']['total_completed_cost'] ?? 0);
            $reply = "Jumlah pesanan yang telah selesai dikerjakan saat ini ada {$compCount} pesanan.\n\n"
                . "• **OMSET (Sudah Dibayar):** Rp " . number_format($omset, 0, ',', '.') . " dari {$paidCount} pesanan\n"
                . "• **Total Nilai Pesanan Selesai:** Rp " . number_format($completedCost, 0, ',', '.') . "\n\n";
            $compList = array_filter($userOrders, function($o) {
                return $o['status'] === 'COMPLETED';
            });
            if (!empty($compList)) {
                $reply .= "Berikut rincian pesanan yang telah selesai dikerjakan:\n\n";
                foreach (array_slice($compList, 0, 5) as $co) {
                    $reply .= $formatSingleOrderDetailed($co) . "\n";
                }
            }
            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Completed Orders)'];
        }

        // 7. Total Customers / User List Queries (e.g. "berapa jumlah customer?", "siapa saja customernya", "siapa customer", "daftar customer")
        $isCustomerTerm = (bool)preg_match('/(customer|pelanggan|kustomer)/i', $text);
        $isAskingCustomerNames = $isCustomerTerm && (
            $isAskingDetail ||
            preg_match('/(siapa+|siapakah|daftar|list|tampilkan|sebutkan|nama|rincian|detail|terdaftar|yang terdaftar)/i', $text)
        ) && !$isAskingCountOnly;
        $isCustomerCountQuery = (strpos($text, 'pesan') === false && strpos($text, 'order') === false) && (
            $isCustomerTerm || (strpos($text, 'user') !== false && strpos($text, 'terdaftar') !== false)
        ) && (
            $isAskingCountOnly || preg_match('/\b(berapa|jumlah|ada berapa|total|banyaknya)\b/i', $text)
        );

        if ($isAskingCustomerNames || $isCustomerCountQuery) {
            $totalCust = $dbData['platform_stats']['total_customers'] ?? ($adminData['total_customers'] ?? 7);
            if ($isAskingCountOnly) {
                $reply = "Jumlah customer terdaftar di sistem saat ini ada {$totalCust} orang.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Customers)'];
            }
            $custs = !empty($dbData['customers']) ? $dbData['customers'] : (!empty($adminData['recent_customers']) ? $adminData['recent_customers'] : []);
            $totalCustCount = !empty($custs) ? count($custs) : $totalCust;
            $reply = "Berikut rincian {$totalCustCount} customer terdaftar di sistem:\n";
            if (!empty($custs)) {
                $no = 1;
                foreach ($custs as $c) {
                    $addrStr = !empty($c['address']) ? " | Alamat: {$c['address']}" : "";
                    $reply .= "{$no}. **{$c['name']}** (ID Akun: #{$c['id']}) | Telp: " . ($c['phone'] ?: '-') . " | Email: {$c['email']}{$addrStr}\n";
                    $no++;
                }
            }
            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Customers)'];
        }

        // 8. TEKNISI SPECIFIC: Active Jobs & Tasks Queries
        if (preg_match('/\b(pekerjaan saya|tugas saya|orderan saya|jadwal saya|job saya|tugas servis|pekerjaan aktif)\b/i', $text) || (strpos($text, 'pekerjaan') !== false && strpos($text, 'saya') !== false) || (strpos($text, 'tugas') !== false && strpos($text, 'saya') !== false)) {
            if ($userRole === 'TEKNISI') {
                $activeJobs = array_filter($userOrders, function($o) {
                    return in_array($o['status'], ['ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS']);
                });
                $activeCount = count($activeJobs);

                if ($isAskingCountOnly) {
                    $reply = "Jumlah pekerjaan aktif Anda saat ini ada {$activeCount} pekerjaan.";
                    return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Technician Jobs)'];
                }

                if (empty($activeJobs)) {
                    $reply = "Saat ini jumlah pekerjaan aktif Anda ada 0 pekerjaan.";
                } else {
                    $reply = "Saat ini jumlah pekerjaan aktif Anda ada {$activeCount} pekerjaan.\n\n"
                        . "Berikut rincian daftar pekerjaan aktif Anda:\n\n";

                    $statusBadges = [
                        'ASSIGNED'    => '📋 Ditugaskan',
                        'ACCEPTED'    => '👍 Diterima',
                        'ON_THE_WAY'  => '🚗 Dalam Perjalanan',
                        'IN_PROGRESS' => '🔧 Sedang Dikerjakan'
                    ];

                    foreach ($activeJobs as $job) {
                        $st = $statusBadges[$job['status']] ?? $job['status'];
                        $jadwal = $job['service_date'] ? date('d M Y, H:i', strtotime($job['service_date'])) : 'Hari ini';
                        $reply .= "• **Order #{$job['id']}** - {$job['equipment_name']} ({$job['equipment_brand']})\n"
                            . "   Status: **{$st}**\n"
                            . "   Pelanggan: **{$job['customer_name']}** (Telp: {$job['customer_phone']})\n"
                            . "   Alamat: {$job['order_address']}\n"
                            . "   Jadwal Kunjungan: {$jadwal}\n"
                            . "   Keluhan: _{$job['complaint']}_\n\n";
                    }
                }
                return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Technician Jobs)'];
            }
        }

        // 9. TEKNISI SPECIFIC: Customer Address & Contact Queries
        if (preg_match('/\b(alamat customer|lokasi customer|nomor customer|kontak customer|telepon customer|no customer|wa customer|alamat pelanggan|nomor pelanggan)\b/i', $text)) {
            if ($userRole === 'TEKNISI') {
                $activeJobs = array_filter($userOrders, function($o) {
                    return in_array($o['status'], ['ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS']);
                });

                if (empty($activeJobs)) {
                    $reply = "Saat ini Anda belum memiliki pekerjaan aktif.";
                } else {
                    $reply = "📍 **Daftar Alamat & Kontak Pelanggan Pekerjaan Aktif Anda:**\n\n";
                    foreach ($activeJobs as $job) {
                        $reply .= "• **Order #{$job['id']} ({$job['equipment_name']})**:\n"
                            . "   - Pelanggan: **{$job['customer_name']}**\n"
                            . "   - No. HP/WA: **{$job['customer_phone']}**\n"
                            . "   - Alamat Kunjungan: **{$job['order_address']}**\n"
                            . "   - Jadwal: " . ($job['service_date'] ? date('d M Y, H:i', strtotime($job['service_date'])) : 'Segera') . "\n\n";
                    }
                }
                return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Customer Contact)'];
            }
        }

        // 10. TEKNISI SPECIFIC: Rating & Performance Queries
        if (preg_match('/\b(rating saya|ulasan saya|review saya|bintang saya|reputasi saya|performa saya)\b/i', $text)) {
            if ($userRole === 'TEKNISI' && $techData) {
                $tProf = $techData['profile'];
                if ($isAskingCountOnly) {
                    $reply = "Rating kinerja Anda saat ini adalah {$tProf['rating']} / 5.0 ⭐ dari {$techData['completed_jobs']} pekerjaan servis.";
                    return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Technician Rating)'];
                }
                $reply = "Rating kinerja Anda saat ini adalah {$tProf['rating']} / 5.0 ⭐ dari {$techData['completed_jobs']} pekerjaan servis yang telah selesai.\n\n"
                    . "• **Rating Kinerja:** **{$tProf['rating']} / 5.0 ⭐**\n"
                    . "• **Total Servis Sukses:** {$techData['completed_jobs']} pekerjaan\n"
                    . "• **Pengalaman:** {$tProf['experience']} Tahun\n"
                    . "• **Spesialisasi:** {$tProf['specialization']}\n\n";

                if (!empty($techData['my_reviews'])) {
                    $reply .= "💬 **Ulasan Terbaru dari Pelanggan:**\n";
                    foreach ($techData['my_reviews'] as $rev) {
                        $stars = str_repeat('⭐', (int)$rev['rating']);
                        $reply .= "• **{$rev['customer_name']}** ({$stars}) - Servis {$rev['equipment_name']}\n"
                            . "   _\"{$rev['comment']}\"_\n\n";
                    }
                }
                return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Technician Rating)'];
            }
        }

        // 11. Orders Breakdown / All Orders Queries (e.g. "berapa total pesanan?", "data pesanan", "jelaskan pesanan", "semua pesanan", "rekap pesanan", "rincikan")
        if (strpos($text, 'saya') === false && (strpos($text, 'rincik') !== false || strpos($text, 'rincian') !== false || strpos($text, 'pesan') !== false || strpos($text, 'order') !== false || strpos($text, 'bayar') !== false || preg_match('/\b(semua pesanan|seluruh pesanan|rekap pesanan|status order sistem|ringkasan pesanan|rekapitulasi|total pesanan|total order|jumlah semua pesanan|jumlah seluruh order|data pesanan|info pesanan|informasi pesanan|daftar pesanan|rincian pesanan|detail pesanan|pesanan yang ada|order yang ada|daftar order|data order|data servis|pesanan servis|laporan pesanan|cek pesanan|riwayat pesanan)\b/i', $text) || preg_match('/\b(apa pesanannya|siapa pelanggannya|jumlah pembayarannya|berapa pembayarannya|pembayarannya berapa|siapa customer|pelanggannya siapa|apa saja ordernya|rincian pembayaran)\b/i', $text) || (preg_match('/\b(berapa|jumlah|ada berapa)\b/i', $text) && preg_match('/\b(pesanan|order|servis)\b/i', $text)))) {
            $totOrders = $dbData['platform_stats']['total_orders'] ?? count($userOrders);
            if ($isAskingCountOnly) {
                $reply = "Total seluruh pesanan di sistem saat ini ada {$totOrders} pesanan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Detailed Order Information)'];
            }

            // Filter cerdas: rincikan HANYA apa yang ditanyakan, tidak usah bertele-tele
            $filteredOrders = [];
            $filterLabel = "";

            $isGenericFollowUp = (bool)preg_match('/^(apa saja|apa sajakah|mana saja|mana sajakah|sebutkan|rincikan|rinciannya|detailnya|daftarnya|tampilkan|apa saja pesanannya|apa saja pesanan|mana saja pesanannya|pesanannya apa saja|apa aja|apa sajakah itu)\??$/i', trim($text))
                || (!preg_match('/\b(pending|aktif|proses|selesai|completed|batal|customer|teknisi|katalog|omset)\b/i', $text) && preg_match('/\b(apa saja|mana saja|rincikan|sebutkan)\b/i', $text));

            if (preg_match('/\b(selesai|completed|lunas|beres)\b/i', $text) || ($isGenericFollowUp && preg_match('/\b(selesai|completed|lunas|beres)\b/i', $prevContext))) {
                $filteredOrders = array_values(array_filter($userOrders, function($o) { return ($o['status'] ?? '') === 'COMPLETED'; }));
                $filterLabel = "yang telah selesai";
            } elseif (preg_match('/\b(pending|menunggu)\b/i', $text) || ($isGenericFollowUp && preg_match('/\b(pending|menunggu)\b/i', $prevContext))) {
                $filteredOrders = array_values(array_filter($userOrders, function($o) { return ($o['status'] ?? '') === 'PENDING'; }));
                $filterLabel = "berstatus PENDING (Menunggu Teknisi)";
            } elseif (preg_match('/\b(aktif|proses|in progress|sedang dikerjakan|jalan)\b/i', $text) || ($isGenericFollowUp && preg_match('/\b(aktif|in progress|sedang dikerjakan)\b/i', $prevContext))) {
                $filteredOrders = array_values(array_filter($userOrders, function($o) {
                    return in_array($o['status'] ?? '', ['IN_PROGRESS', 'ACCEPTED', 'ASSIGNED', 'ON_THE_WAY']);
                }));
                $filterLabel = "aktif yang sedang dikerjakan";
            } elseif (preg_match('/\b(ac|air conditioner)\b/i', $text)) {
                $filteredOrders = array_values(array_filter($userOrders, function($o) { return stripos($o['equipment_name'] ?? '', 'ac') !== false; }));
                $filterLabel = "peralatan AC";
            } elseif (preg_match('/\b(kulkas|lemari es)\b/i', $text)) {
                $filteredOrders = array_values(array_filter($userOrders, function($o) { return stripos($o['equipment_name'] ?? '', 'kulkas') !== false; }));
                $filterLabel = "peralatan Kulkas";
            } elseif (preg_match('/\b(mesin cuci)\b/i', $text)) {
                $filteredOrders = array_values(array_filter($userOrders, function($o) { return stripos($o['equipment_name'] ?? '', 'mesin cuci') !== false; }));
                $filterLabel = "peralatan Mesin Cuci";
            } elseif (preg_match('/\b(tv|televisi)\b/i', $text)) {
                $filteredOrders = array_values(array_filter($userOrders, function($o) { return stripos($o['equipment_name'] ?? '', 'tv') !== false || stripos($o['equipment_name'] ?? '', 'televisi') !== false; }));
                $filterLabel = "peralatan Televisi";
            } else {
                // Default jika hanya "rincikan pesanan" / "rinciannya" tanpa konteks status:
                $activeOnly = array_values(array_filter($userOrders, function($o) {
                    return in_array($o['status'] ?? '', ['IN_PROGRESS', 'ACCEPTED', 'ASSIGNED', 'ON_THE_WAY', 'PENDING']);
                }));
                if (!empty($activeOnly)) {
                    $filteredOrders = array_slice($activeOnly, 0, 5);
                    $filterLabel = "aktif saat ini";
                } else {
                    $filteredOrders = array_slice($userOrders, 0, 5);
                    $filterLabel = "terbaru";
                }
            }

            if (empty($filteredOrders)) {
                $reply = "Tidak ada data pesanan {$filterLabel}.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Detailed Order Information)'];
            }

            $countF = count($filteredOrders);
            $reply = "Berikut rincian {$countF} pesanan {$filterLabel}:\n\n";
            $no = 1;
            foreach (array_slice($filteredOrders, 0, 10) as $ord) {
                $rawFmt = $formatSingleOrderConcise($ord);
                $cleanFmt = preg_replace('/^•\s*/u', '', $rawFmt);
                $reply .= "{$no}. " . $cleanFmt . "\n";
                $no++;
            }
            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Detailed Order Information)'];
        }

        // 12. User Orders & Service History Queries (Customer / General)
        if (preg_match('/\b(pesanan saya|order saya|riwayat servis|status pesanan|status order|cek pesanan|jadwal servis|jadwal teknisi|riwayat pesanan|daftar pesanan|servis saya|orderan saya)\b/i', $text) || (strpos($text, 'pesanan') !== false && strpos($text, 'saya') !== false) || (strpos($text, 'order') !== false && strpos($text, 'saya') !== false)) {
            $userStats = $dbData['user']['stats'] ?? null;
            $myTotal = $userStats['total'] ?? count($userOrders);

            if ($isAskingCountOnly) {
                $reply = "Jumlah pesanan servis Anda saat ini ada {$myTotal} pesanan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (User Orders)'];
            }

            if (empty($userOrders) || $myTotal === 0) {
                $reply = "Saat ini jumlah pesanan servis Anda di kami ada 0 pesanan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context'];
            }

            $reply = "Berikut rincian pesanan servis Anda:\n\n";

            foreach (array_slice($userOrders, 0, 3) as $ord) {
                $reply .= $formatSingleOrderConcise($ord) . "\n";
            }

            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (User Orders)'];
        }

        // 13. Technicians / Team Queries (e.g. "berapa jumlah teknisi?", "teknisi di kami ada berapa?", "daftar teknisi")
        if (preg_match('/\b(teknisi|daftar teknisi|siapa saja teknisi|tim teknisi|spesialis|keahlian teknisi|rating teknisi|pengalaman teknisi|teknisi ready|teknisi tersedia|rekomendasi teknisi)\b/i', $text) || (strpos($text, 'siapa') !== false && strpos($text, 'teknisi') !== false) || (preg_match('/\b(berapa|jumlah|ada berapa|total)\b/i', $text) && strpos($text, 'teknisi') !== false)) {
            $techList = $dbData['technicians']['technicians'] ?? [];
            $totalTech = $dbData['platform_stats']['total_technicians'] ?? count($techList);
            $availCount = $dbData['technicians']['available_count'] ?? 0;
            $busyCount = $dbData['technicians']['busy_count'] ?? 0;

            // Check if asking for specific appliance technician (e.g. AC, Kulkas, Mesin Cuci, TV, Pompa)
            $filteredTechs = [];
            $filterName = '';

            if (preg_match('/\b(ac|air conditioner|pendingin)\b/i', $text)) {
                $filterName = 'AC & Pendingin';
                $filteredTechs = array_filter($techList, function($t) { return stripos($t['specialization'], 'AC') !== false || stripos($t['specialization'], 'Pendingin') !== false; });
            } elseif (preg_match('/\b(kulkas|freezer|lemari es)\b/i', $text)) {
                $filterName = 'Kulkas & Freezer';
                $filteredTechs = array_filter($techList, function($t) { return stripos($t['specialization'], 'Kulkas') !== false || stripos($t['specialization'], 'Freezer') !== false; });
            } elseif (preg_match('/\b(mesin cuci|pengering|dryer|cuci)\b/i', $text)) {
                $filterName = 'Mesin Cuci';
                $filteredTechs = array_filter($techList, function($t) { return stripos($t['specialization'], 'Mesin Cuci') !== false; });
            } elseif (preg_match('/\b(tv|televisi|audio|elektronik)\b/i', $text)) {
                $filterName = 'TV & Elektronik';
                $filteredTechs = array_filter($techList, function($t) { return stripos($t['specialization'], 'TV') !== false || stripos($t['specialization'], 'Televisi') !== false; });
            } elseif (preg_match('/\b(pompa|water heater|microwave|kompor)\b/i', $text)) {
                $filterName = 'Pompa, Water Heater & Dapur';
                $filteredTechs = array_filter($techList, function($t) { return stripos($t['specialization'], 'Pompa') !== false || stripos($t['specialization'], 'Water Heater') !== false; });
            }

            $displayTechs = !empty($filteredTechs) ? $filteredTechs : $techList;

            // Jika hanya menanyakan jumlah teknisi:
            if ($isAskingCountOnly) {
                if ($filterName) {
                    $fCount = count($displayTechs);
                    $reply = "Jumlah teknisi spesialisasi {$filterName} saat ini ada {$fCount} orang dari total {$totalTech} teknisi.";
                } elseif (preg_match('/\b(tersedia|ready|siap)\b/i', $text)) {
                    $reply = "Jumlah teknisi yang tersedia saat ini ada {$availCount} orang dari total {$totalTech} teknisi.";
                } else {
                    $reply = "Jumlah teknisi saat ini ada {$totalTech} orang.";
                }
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Technicians)'];
            }

            if ($filterName) {
                $fCount = count($displayTechs);
                $reply = "Saat ini **jumlah teknisi spesialisasi {$filterName} di kami ada {$fCount} orang** (dari total {$totalTech} teknisi resmi HomeCare Service).\n\n"
                    . "Berikut rincian teknisi spesialisasi {$filterName}:\n\n";
            } else {
                $reply = "Saat ini **teknisi di kami ada {$totalTech} orang** ({$availCount} orang bersertifikat siap bertugas/tersedia dan {$busyCount} orang sedang bertugas di lapangan).\n\n"
                    . "Berikut rincian tim teknisi kami:\n\n";
            }

            foreach ($displayTechs as $t) {
                $availBadge = $t['availability'] ? '🟢 Tersedia (Ready)' : '🟡 Sedang Bertugas';
                $reply .= "• **{$t['name']}** (ID: #{$t['id']})\n"
                    . "   - **Spesialisasi:** {$t['specialization']}\n"
                    . "   - **Pengalaman:** {$t['experience']} Tahun\n"
                    . "   - **Rating:** {$t['rating']} ⭐ ({$t['completed_jobs']} servis selesai)\n"
                    . "   - **Status:** {$availBadge}\n\n";
            }

            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Technicians)'];
        }

        // 14. Equipment Catalog & Supported Brands Queries (e.g. "katalog", "peralatan apa saja", "harga perbaikan")
        if (preg_match('/\b(katalog|peralatan apa saja|merk apa saja|brand apa saja|kategori apa saja|bisa servis apa saja|layanan apa saja|alat apa saja|daftar peralatan|dukungan alat|harga perbaikan|biaya perbaikan|biaya servis per barang|biaya perbaikan per barang|tarif perbaikan)\b/i', $text) || (strpos($text, 'katalog') !== false) || (strpos($text, 'merk') !== false && strpos($text, 'apa') !== false) || (preg_match('/\b(berapa|jumlah|ada berapa|total)\b/i', $text) && (strpos($text, 'peralatan') !== false || strpos($text, 'alat') !== false || strpos($text, 'barang') !== false))) {
            $byCat = $dbData['equipment']['by_category'] ?? [];
            $totalItems = $dbData['equipment']['total'] ?? 0;
            $catCount = count($byCat);

            $isAskingEquipCountOnly = $isAskingCountOnly && (strpos($text, 'harga') === false && strpos($text, 'biaya') === false && strpos($text, 'tarif') === false);
            if ($isAskingEquipCountOnly) {
                $reply = "Jumlah peralatan yang terdaftar di katalog saat ini ada {$totalItems} jenis peralatan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Equipment Catalog)'];
            }

            // Cek apakah pengguna menanyakan kategori alat spesifik (AC, Kulkas, Mesin Cuci, TV, dll.)
            $filteredCat = [];
            foreach ($byCat as $catName => $items) {
                $matchesCat = false;
                if (preg_match('/\b(ac|air conditioner)\b/i', $text) && stripos($catName, 'pendingin udara') !== false) $matchesCat = true;
                if (preg_match('/\b(kulkas|lemari es|chiller|freezer)\b/i', $text) && stripos($catName, 'lemari es') !== false) $matchesCat = true;
                if (preg_match('/\b(mesin cuci|pengering|laundry)\b/i', $text) && stripos($catName, 'mesin cuci') !== false) $matchesCat = true;
                if (preg_match('/\b(tv|televisi|oled|led)\b/i', $text) && stripos($catName, 'televisi') !== false) $matchesCat = true;
                if (preg_match('/\b(pompa|sanyo)\b/i', $text) && stripos($catName, 'pompa') !== false) $matchesCat = true;
                if (preg_match('/\b(water heater|pemanas)\b/i', $text) && stripos($catName, 'water heater') !== false) $matchesCat = true;
                if (preg_match('/\b(microwave|kompor|rice cooker|magic com|magic jar|dapur)\b/i', $text) && stripos($catName, 'dapur') !== false) $matchesCat = true;
                if (preg_match('/\b(dispenser|galon|minum)\b/i', $text) && stripos($catName, 'minum') !== false) $matchesCat = true;

                if ($matchesCat) {
                    $filteredCat[$catName] = $items;
                }
            }

            $displayCat = !empty($filteredCat) ? $filteredCat : $byCat;
            $headerText = !empty($filteredCat) ? "Berikut estimasi harga perbaikan untuk peralatan yang Anda tanyakan:" : "Berikut adalah **katalog peralatan rumah tangga yang didukung** beserta **estimasi harga perbaikan per barang**:";

            $reply = "{$headerText}\n\n";

            foreach ($displayCat as $categoryName => $items) {
                $reply .= "🏷️ **Kategori: {$categoryName}**\n";
                foreach ($items as $item) {
                    $priceFmt = !empty($item['repair_price']) ? 'Rp ' . number_format($item['repair_price'], 0, ',', '.') : 'Rp 100.000';
                    $brandStr = (!empty($item['brand']) && $item['brand'] !== '-') ? " ({$item['brand']})" : '';
                    $reply .= "   • **{$item['name']}{$brandStr}**: Estimasi Perbaikan **{$priceFmt}**\n";
                }
                $reply .= "\n";
            }

            $reply .= "⚠️ **Catatan / Disclaimer:**\n"
                . "_Harga perbaikan per barang di atas merupakan estimasi awal dan bisa saja berubah tergantung keputusan teknisi setelah pemeriksaan fisik kondisi kerusakan di lokasi._";

            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Equipment Catalog)'];
        }

        // 15. Reviews & Customer Ratings Queries (e.g. "berapa ulasan?", "rating layanan")
        if (preg_match('/\b(ulasan|review|testimoni|apa kata pelanggan|rating layanan|kepuasan pelanggan|bintang)\b/i', $text) || (preg_match('/\b(berapa|jumlah|ada berapa)\b/i', $text) && (strpos($text, 'ulasan') !== false || strpos($text, 'review') !== false || strpos($text, 'testimoni') !== false))) {
            $revData = $dbData['reviews'] ?? [];
            $avg = $revData['average_rating'] ?? 5.0;
            $total = $revData['total_reviews'] ?? 0;
            $recent = $revData['recent'] ?? [];

            if ($isAskingCountOnly) {
                $reply = "Jumlah ulasan pelanggan saat ini ada {$total} ulasan dengan rating rata-rata {$avg} / 5.0 ⭐.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Reviews)'];
            }

            $reply = "Saat ini **jumlah ulasan pelanggan yang masuk di kami ada {$total} ulasan** dengan rating rata-rata **{$avg} / 5.0 ⭐**.\n\n"
                . "Berikut rincian testimoni pelanggan terbaru:\n\n";

            if (!empty($recent)) {
                foreach (array_slice($recent, 0, 4) as $r) {
                    $stars = str_repeat('⭐', (int)$r['rating']);
                    $reply .= "• **{$r['customer_name']}** ({$stars})\n"
                        . "   Teknisi: **{$r['technician_name']}** | Peralatan: {$r['equipment_name']}\n"
                        . "   _\"{$r['comment']}\"_\n\n";
                }
            } else {
                $reply .= "Belum ada ulasan publik yang dipublikasikan.\n";
            }

            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Reviews)'];
        }

        // 15.A. Service Reports Queries (Tabel: service_reports - Customer, Teknisi, Admin)
        if (preg_match('/\b(laporan servis|laporan perbaikan|laporan teknisi|hasil perbaikan|hasil inspeksi|tindakan perbaikan|catatan teknisi|service report|service_reports)\b/i', $text)) {
            $reports = $dbData['service_reports'] ?? [];
            $totalReports = count($reports);

            if ($isAskingCountOnly) {
                $reply = "Jumlah laporan hasil servis tercatat saat ini ada {$totalReports} laporan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Service Reports)'];
            }

            if ($totalReports === 0) {
                $reply = "Saat ini belum ada laporan hasil servis (service_reports) yang tercatat di database.";
            } else {
                $reply = "Saat ini terdapat {$totalReports} laporan hasil servis (service_reports) tercatat di database sistem:\n\n";
                foreach (array_slice($reports, 0, 5) as $sr) {
                    $repDate = !empty($sr['completed_at']) ? date('d M Y, H:i', strtotime($sr['completed_at'])) : date('d M Y', strtotime($sr['created_at']));
                    $costFmt = "Rp " . number_format($sr['cost'], 0, ',', '.');
                    $reply .= "📋 **Laporan #{$sr['id']} (Order #{$sr['order_id']} — {$sr['equipment_name']} {$sr['equipment_brand']})**\n"
                        . "   • **Pelanggan:** {$sr['customer_name']} (Telp: {$sr['customer_phone']})\n"
                        . "   • **Teknisi Bertugas:** {$sr['technician_name']}\n"
                        . "   • **Biaya Pengerjaan:** {$costFmt}\n"
                        . "   • **Hasil Inspeksi:** _{$sr['inspection_result']}_\n"
                        . "   • **Tindakan Perbaikan:** {$sr['repair_action']}\n"
                        . "   • **Catatan/Garansi:** {$sr['notes']}\n"
                        . "   • **Waktu Selesai:** {$repDate}\n\n";
                }
            }
            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Service Reports)'];
        }

        // 15.B. Notifications Queries (Tabel: notifications - Customer, Teknisi, Admin)
        if (preg_match('/\b(notifikasi|pemberitahuan|pesan notifikasi|cek notif|ada notif|inbox notif|notifications)\b/i', $text)) {
            $notifs = $dbData['notifications'] ?? [];
            $totalNotif = count($notifs);

            if ($isAskingCountOnly) {
                $reply = "Jumlah notifikasi saat ini ada {$totalNotif} notifikasi.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Notifications)'];
            }

            if ($totalNotif === 0) {
                $reply = "Saat ini tidak ada notifikasi baru yang tersimpan di database sistem.";
            } else {
                $reply = "Saat ini terdapat {$totalNotif} notifikasi sistem (notifications) tercatat di database:\n\n";
                foreach (array_slice($notifs, 0, 5) as $n) {
                    $tglNotif = date('d M Y, H:i', strtotime($n['created_at']));
                    $msgClean = strip_tags(str_replace(['<br>', '</p>'], " ", $n['message']));
                    $reply .= "🔔 **Notifikasi #{$n['id']} [{$n['type']}]** ({$tglNotif})\n"
                        . "   • **Penerima:** {$n['user_name']}\n"
                        . "   • **Judul:** **{$n['title']}**\n"
                        . "   • **Pesan:** {$msgClean}\n\n";
                }
            }
            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Notifications)'];
        }

        // 15.C. Payment Records Queries (Tabel: payments - Customer, Teknisi, Admin)
        if (preg_match('/\b(daftar pembayaran|riwayat pembayaran|data pembayaran|tabel payment|tabel pembayaran)\b/i', $text) || (preg_match('/\b(berapa|jumlah|ada berapa)\b/i', $text) && (strpos($text, 'pembayaran') !== false || strpos($text, 'payment') !== false))) {
            $payments = $dbData['payments'] ?? [];
            $totalPay = count($payments);
            $totalOmset = $dbData['platform_stats']['total_omset'] ?? 0;

            if ($isAskingCountOnly) {
                $reply = "Jumlah data transaksi pembayaran tercatat saat ini ada {$totalPay} transaksi.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Payments)'];
            }

            $reply = "Saat ini terdapat **{$totalPay} data pembayaran (payments)** dengan total omset lunas (PAID) sebesar **Rp " . number_format($totalOmset, 0, ',', '.') . "**.\n\n";
            if (!empty($payments)) {
                $reply .= "Berikut rincian transaksi pembayaran:\n\n";
                foreach (array_slice($payments, 0, 5) as $p) {
                    $pDate = !empty($p['paid_at']) ? date('d M Y, H:i', strtotime($p['paid_at'])) : date('d M Y', strtotime($p['created_at']));
                    $pAmt = "Rp " . number_format($p['amount'], 0, ',', '.');
                    $pMet = !empty($p['payment_method']) ? "{$p['payment_method']} ({$p['payment_provider']})" : 'CASH';
                    $statusBadge = ($p['status'] === 'PAID') ? '✅ LUNAS (PAID)' : ($p['status'] === 'PENDING' ? '⏳ MENUNGGU VERIFIKASI' : '❌ DITOLAK');
                    $reply .= "💳 **Payment #{$p['id']} (Order #{$p['order_id']} — {$p['equipment_name']} {$p['equipment_brand']})**\n"
                        . "   • **Pelanggan:** {$p['customer_name']}\n"
                        . "   • **Nominal:** {$pAmt} ({$statusBadge})\n"
                        . "   • **Metode:** {$pMet}\n"
                        . "   • **Waktu:** {$pDate}\n\n";
                }
            }
            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Payments)'];
        }

        // 15.D. Users Table Queries (Tabel: users)
        if (preg_match('/\b(semua user|daftar user|data user|seluruh pengguna|daftar pengguna|tabel users)\b/i', $text)) {
            $allUsers = $dbData['users'] ?? [];
            $totU = count($allUsers);
            if ($isAskingCountOnly) {
                $reply = "Jumlah seluruh pengguna terdaftar di sistem saat ini ada {$totU} orang.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Users Table)'];
            }
            if ($userRole === 'ADMIN') {
                $reply = "👑 **Laporan Data Pengguna Sistem (Tabel: users)**\n"
                    . "Saat ini terdaftar **{$totU} pengguna** di database sistem:\n\n";
                foreach (array_slice($allUsers, 0, 10) as $u) {
                    $tglReg = date('d M Y', strtotime($u['created_at']));
                    $reply .= "• **#{$u['id']} {$u['name']}** [Role: **{$u['role']}**]\n"
                        . "   - Email: {$u['email']} | Telp: " . ($u['phone'] ?: '-') . "\n"
                        . "   - Alamat: " . ($u['address'] ?: '-') . " | Terdaftar: {$tglReg}\n";
                }
                return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Admin Users Table)'];
            } else {
                $reply = "Saat ini ada {$totU} pengguna terdaftar di sistem.";
                return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
            }
        }

        // 15.E. Chat Sessions & Messages Queries (Tabel: chat_sessions & chat_messages)
        if (preg_match('/\b(sesi chat|riwayat chat|pesan chat|statistik chat|tabel chat)\b/i', $text)) {
            $chatData = $dbData['chat_data'] ?? [];
            $totalSessions = $chatData['total_sessions'] ?? 0;
            $totalMessages = $chatData['total_messages'] ?? 0;
            $userMsg = $chatData['user_messages'] ?? 0;
            $aiMsg = $chatData['ai_messages'] ?? 0;

            if ($isAskingCountOnly) {
                $reply = "Total sesi chat saat ini ada {$totalSessions} sesi dengan total {$totalMessages} pesan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Chat Table)'];
            }

            if ($userRole === 'ADMIN') {
                $reply = "💬 **Laporan Konsultasi Chatbot (Tabel: chat_sessions & chat_messages)**\n\n"
                    . "• **Total Sesi Obrolan:** {$totalSessions} sesi percakapan\n"
                    . "• **Total Pesan:** {$totalMessages} pesan ({$userMsg} dari pengguna, {$aiMsg} dari AI)\n\n"
                    . "Berikut 5 sesi konsultasi terbaru:\n";
                if (!empty($chatData['recent_sessions'])) {
                    foreach (array_slice($chatData['recent_sessions'], 0, 5) as $cs) {
                        $tglCs = date('d M Y, H:i', strtotime($cs['updated_at']));
                        $reply .= "• Sesi #{$cs['id']}: \"{$cs['title']}\" (Pelanggan: {$cs['customer_name']}, Pesan: {$cs['message_count']} chat - {$tglCs})\n";
                    }
                }
                return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Admin Chat Table)'];
            } else {
                $reply = "Riwayat percakapan konsultasi Anda dengan asisten HomeCare tersimpan aman pada sesi obrolan saat ini.";
                return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
            }
        }

        // 16. General Quantity Catch-All Detector (Strictly to-the-point)
        if (preg_match('/\b(berapa|jumlah|total|ada berapa|banyaknya|hitung)\b/i', $text)) {
            $pStats = $dbData['platform_stats'] ?? [];
            if (preg_match('/\b(customer|pelanggan|kustomer)\b/i', $text)) {
                $tot = $pStats['total_customers'] ?? 7;
                $reply = "Jumlah customer terdaftar di sistem saat ini ada {$tot} orang.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(teknisi|petugas|tukang)\b/i', $text)) {
                if (preg_match('/\b(ready|tersedia|siap|standby)\b/i', $text)) {
                    $avail = (int)($dbData['technicians']['available_count'] ?? 5);
                    $tot = $pStats['total_technicians'] ?? 6;
                    $reply = "Jumlah teknisi yang tersedia saat ini ada {$avail} orang dari total {$tot} teknisi.";
                } else {
                    $tot = $pStats['total_technicians'] ?? 6;
                    $reply = "Jumlah teknisi saat ini ada {$tot} orang.";
                }
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(pending)\b/i', $text)) {
                $tot = $pStats['pending_orders'] ?? 0;
                $reply = "Jumlah pesanan berstatus pending saat ini ada {$tot} pesanan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(aktif|proses|jalan)\b/i', $text)) {
                $tot = $pStats['active_orders'] ?? 4;
                $reply = "Jumlah pesanan aktif saat ini ada {$tot} pesanan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(selesai|sukses|beres)\b/i', $text)) {
                $tot = $pStats['completed_orders'] ?? 6;
                $reply = "Jumlah pesanan yang telah selesai dikerjakan saat ini ada {$tot} pesanan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(pesanan|order|servis)\b/i', $text)) {
                $tot = $pStats['total_orders'] ?? 10;
                $reply = "Total seluruh pesanan di sistem saat ini ada {$tot} pesanan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(peralatan|alat|barang|produk)\b/i', $text)) {
                $tot = $pStats['total_equipment_items'] ?? 27;
                $reply = "Jumlah peralatan yang terdaftar di katalog saat ini ada {$tot} jenis peralatan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(ulasan|review|testimoni)\b/i', $text)) {
                $tot = $pStats['total_reviews'] ?? 3;
                $avg = $pStats['average_rating'] ?? 5.0;
                $reply = "Total ulasan pelanggan saat ini ada {$tot} ulasan dengan rata-rata rating {$avg} / 5.0 ⭐.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(laporan|report)\b/i', $text)) {
                $tot = $pStats['total_reports'] ?? 6;
                $reply = "Total laporan pengerjaan servis saat ini ada {$tot} laporan.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(notifikasi|notif)\b/i', $text)) {
                $tot = $pStats['total_notifications'] ?? 22;
                $reply = "Jumlah notifikasi saat ini ada {$tot} notifikasi.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(admin|administrator)\b/i', $text)) {
                $tot = $pStats['total_admins'] ?? 2;
                $reply = "Jumlah administrator sistem saat ini ada {$tot} orang.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            } elseif (preg_match('/\b(user|pengguna|akun)\b/i', $text)) {
                $tot = $pStats['total_users'] ?? 15;
                $reply = "Jumlah seluruh pengguna terdaftar di sistem saat ini ada {$tot} orang.";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (Quantity Fallback)'];
            }
        }

        // 17. User Profile / Account Information Queries
        // Catches: 'siapa saya', 'saya siapa', 'siapa aku', 'nama saya', 'profil saya', 'cek profil',
        //          'data diri saya', 'akun saya', 'email saya', 'nomor saya', 'alamat saya', etc.
        $isProfileQuery = preg_match('/\b(siapa saya|saya siapa|siapa aku|aku siapa|data (diri|akun|profil)|profil (saya|ku|aku)|cek profil|lihat profil|info (akun|profil|diri)|nama (saya|aku|ku)|alamat (saya|aku)|nomor (saya|aku|hp saya|telepon saya|wa saya)|email (saya|aku)|akun saya|identitas saya|tentang saya|tentang aku)\b/i', $text)
            || (strpos($text, 'profil') !== false && (strpos($text, 'saya') !== false || strpos($text, 'aku') !== false || strpos($text, 'ku') !== false))
            || (strpos($text, 'akun') !== false && strpos($text, 'saya') !== false)
            || (strpos($text, 'siapa') !== false && strpos($text, 'saya') !== false)
            || (strpos($text, 'siapa') !== false && strpos($text, 'aku') !== false);

        if ($isProfileQuery) {
            $userProf = $dbData['user']['profile'] ?? null;
            $userStats = $dbData['user']['stats'] ?? ['total' => 0, 'active' => 0, 'pending' => 0, 'completed' => 0];
            if ($userProf) {
                $roleLabel = ['CUSTOMER' => 'Pelanggan (Customer)', 'ADMIN' => 'Administrator', 'TEKNISI' => 'Teknisi'][$userProf['role']] ?? $userProf['role'];
                $reply = "👤 **Data Profil Akun Anda di Sistem HomeCare Service:**\n\n"
                    . "• **Nama Lengkap:** **" . ($userProf['name'] ?: '-') . "**\n"
                    . "• **Email:** " . ($userProf['email'] ?: '-') . "\n"
                    . "• **No. Telepon / WhatsApp:** " . ($userProf['phone'] ?: '_Belum diisi_') . "\n"
                    . "• **Alamat Utama:** " . ($userProf['address'] ?: '_Belum diisi_') . "\n"
                    . "• **Role Akun:** {$roleLabel}\n"
                    . "• **Statistik Pesanan:** Total **{$userStats['total']}** pesanan ({$userStats['active']} aktif, {$userStats['completed']} selesai)";
                return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (User Profile)'];
            } else {
                $reply = "Saya tidak dapat menemukan data profil Anda saat ini. Pastikan Anda sudah login ke akun HomeCare Service Anda.";
                return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
            }
        }

        // 18. Platform Overview / System Statistics Queries
        if (preg_match('/\b(statistik|tentang homecare|cakupan layanan)\b/i', $text)) {
            $pStats = $dbData['platform_stats'] ?? [];
            $reply = "📊 **Statistik & Informasi Platform HomeCare Service:**\n\n"
                . "• **Jumlah Teknisi Aktif:** {$pStats['total_technicians']} teknisi profesional\n"
                . "• **Jumlah Pelanggan Terdaftar:** {$pStats['total_customers']} pengguna\n"
                . "• **Total Pesanan Servis:** {$pStats['total_orders']} pesanan ({$pStats['completed_orders']} selesai, {$pStats['active_orders']} aktif, {$pStats['pending_orders']} pending)\n"
                . "• **Jumlah Peralatan yang Didukung:** {$pStats['total_equipment_items']} jenis perangkat rumah tangga\n"
                . "• **Total Omset Selesai:** Rp " . number_format($pStats['total_omset'] ?? $pStats['total_revenue'] ?? 0, 0, ',', '.') . " (pesanan sudah dibayar)\n"
                . "• **Jaminan:** Garansi servis resmi di setiap pengerjaan";
            return ['success' => true, 'message' => $reply, 'source' => 'Database Live Context (System Stats)'];
        }

        // 13. Greetings & Bot Identity (Role-Aware)
        // Note: 'siapa saya?' is handled in profile query (block #7) — NOT here
        if (preg_match('/^(halo|hai|hi|hey|pagi|siang|sore|malam|permisi|assalamualaikum|tes|test|selamat)\b/i', $text) || preg_match('/\b(siapa (kamu|anda|lo|lu)|kamu siapa|anda siapa|lo siapa|lu siapa)\b/i', $text) || strlen($text) < 4) {
            if ($userRole === 'ADMIN') {
                $greeting = "Halo Administrator **{$userName}**! 👑";
                $reply = "{$greeting} Saya **HomeCare AI**, asisten cerdas sistem HomeCare Service.\n\n"
                    . "Sebagai Admin, Anda dapat memantau data database secara langsung:\n"
                    . "• 📊 **Laporan omset & keuangan servis** (Ketik: *'cek omset'*)\n"
                    . "• ⏳ **Daftar pesanan PENDING butuh teknisi** (Ketik: *'pesanan pending'*)\n"
                    . "• 👥 **Jumlah & data pelanggan terdaftar** (Ketik: *'total customer'*)\n"
                    . "• 👨‍🔧 **Ketersediaan seluruh tim teknisi** (Ketik: *'status teknisi'*)\n"
                    . "• 📈 **Statistik ringkas platform** (Ketik: *'statistik'*)\n\n"
                    . "Silakan ketik pertanyaan Anda seputar data sistem yang ingin dicek!";
            } elseif ($userRole === 'TEKNISI') {
                $greeting = "Halo Pak/Ibu Teknisi **{$userName}**! 🔧";
                $reply = "{$greeting} Saya **HomeCare AI**, asisten kerja resmi Anda di HomeCare Service.\n\n"
                    . "Saya dapat membantu Anda mengakses database pekerjaan Anda:\n"
                    . "• 🛠️ **Daftar pekerjaan aktif Anda** (Ketik: *'pekerjaan saya'*)\n"
                    . "• 📍 **Alamat & nomor kontak pelanggan** (Ketik: *'alamat customer'*)\n"
                    . "• 📅 **Jadwal kunjungan servis** (Ketik: *'jadwal servis'*)\n"
                    . "• ⭐ **Laporan rating kinerja & ulasan pelanggan** (Ketik: *'rating saya'*)\n"
                    . "• 💰 **Total pekerjaan servis selesai & omset** (Ketik: *'pendapatan saya'*)\n\n"
                    . "Ada pekerjaan atau data pelanggan yang ingin Anda tanyakan?";
            } else {
                $greeting = $userProfile ? "Halo, **{$userName}**!" : "Halo!";
                $reply = "{$greeting} Saya **HomeCare AI**, asisten virtual resmi untuk layanan perbaikan dan perawatan peralatan rumah tangga di **HomeCare Service**. 😊\n\n"
                    . "Saya dapat membantu Anda:\n"
                    . "• 📦 **Cek status & riwayat pesanan servis Anda** (Ketik: *'pesanan saya'*)\n"
                    . "• 👨‍🔧 **Melihat daftar teknisi, spesialisasi & rating** (Ketik: *'daftar teknisi'*)\n"
                    . "• 📋 **Informasi katalog peralatan & merk yang didukung** (Ketik: *'katalog'*)\n"
                    . "• 🔍 **Konsultasi diagnosis kerusakan & tips perawatan**\n"
                    . "• 👤 **Melihat data profil & riwayat akun Anda** (Ketik: *'profil saya'*)\n\n"
                    . "Silakan tanyakan hal yang ingin Anda ketahui atau ceritakan kendala peralatan Anda!";
            }
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
        }

        // 10. Platform / Cost / Warranty / Booking Questions
        if (preg_match('/\b(biaya|harga|tarif|ongkos)\b/i', $text)) {
            $reply = "Biaya perbaikan di **HomeCare Service** transparan dan terjangkau per jenis peralatan:\n\n";
            $byCat = $dbData['equipment']['by_category'] ?? [];
            if (!empty($byCat)) {
                foreach (array_slice($byCat, 0, 4) as $categoryName => $items) {
                    $reply .= "• **{$categoryName}**:\n";
                    foreach (array_slice($items, 0, 2) as $item) {
                        $priceFmt = !empty($item['repair_price']) ? 'Rp ' . number_format($item['repair_price'], 0, ',', '.') : 'Rp 100.000';
                        $reply .= "   - {$item['name']} ({$item['brand']}): Estimasi **{$priceFmt}**\n";
                    }
                }
            }
            $reply .= "\n⚠️ **Catatan / Disclaimer:**\n"
                . "_Harga perbaikan di atas merupakan estimasi awal dan bisa saja berubah tergantung keputusan teknisi setelah pemeriksaan fisik kondisi kerusakan di lokasi._\n\n"
                . "Ketik *'katalog'* untuk melihat daftar lengkap seluruh peralatan dan biaya servis per barang!";
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
        }

        if (preg_match('/\b(garansi|klaim)\b/i', $text)) {
            $reply = "Setiap pekerjaan perbaikan melalui **HomeCare Service** dilindungi oleh garansi servis resmi! 🛡️\n\n"
                . "Jika kendala yang sama terjadi kembali dalam masa garansi, teknisi kami akan melakukan pengecekan ulang tanpa biaya tambahan sesuai syarat dan ketentuan yang berlaku.";
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
        }

        if (preg_match('/\b(cara pesan|cara order|booking|pesan servis|order servis|jadwal kunjungan)\b/i', $text) || (strpos($text, 'cara') !== false && strpos($text, 'pesan') !== false)) {
            $reply = "Untuk memesan layanan teknisi di HomeCare Service, langkahnya sangat mudah:\n\n"
                . "1. Buka menu **Order Servis** di dashboard akun Anda.\n"
                . "2. Pilih jenis peralatan rumah tangga dan jelaskan keluhan kerusakan.\n"
                . "3. Tentukan tanggal dan jam kunjungan teknisi ke lokasi Anda.\n"
                . "4. Teknisi kami akan datang sesuai jadwal untuk inspeksi dan perbaikan.";
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
        }

        // 11. Specific Appliance Troubleshooting (AC, Kulkas, Mesin Cuci, TV, dll.)
        if (preg_match('/\b(ac|air conditioner)\b/i', $text)) {
            if (preg_match('/\b(dingin|panas|kurang dingin|tidak dingin)\b/i', $text)) {
                $reply = "Masalah AC tidak dingin umumnya disebabkan oleh:\n\n"
                    . "• **Filter udara kotor/tersumbat debu pekat** (menghambat hembusan angin).\n"
                    . "• **Tekanan freon (refrigerant) berkurang** atau terjadi kebocoran halus pada sambungan pipa.\n"
                    . "• **Unit outdoor/kondensor kotor** sehingga pelepasan panas terhambat.\n\n"
                    . "💡 **Langkah Awal Aman:** Matikan AC, buka penutup indoor, dan bersihkan saringan filter udaranya. Jika hembusan tetap tidak dingin, disarankan memesan layanan servis cuci AC dan cek tekanan freon oleh teknisi.\n\n"
                    . "[[RECOMMEND_SERVICE_ORDER: equipment=Air Conditioner (AC)|complaint=AC kurang dingin / berangin saja]]";
            } elseif (preg_match('/\b(bocor|air|menetes|tetes)\b/i', $text)) {
                $reply = "Bocoran air pada unit indoor AC biasanya disebabkan oleh **saluran pipa drainase yang tersumbat lumut atau debu pekat**, atau bak talang indoor miring.\n\n"
                    . "💡 **Saran:** Hindari menusuk pipa dengan kawat keras sendiri. Servis cuci AC dan flushing saluran drainase oleh teknisi akan mengatasi kendala ini.\n\n"
                    . "[[RECOMMEND_SERVICE_ORDER: equipment=Air Conditioner (AC)|complaint=Unit indoor AC bocor air / drainase tersumbat]]";
            } else {
                $reply = "Untuk kendala AC Anda, apakah unit indoor masih dapat menyala normal, atau terdapat kode error tertentu di layar remote/indikator unit?";
            }
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
        }

        if (preg_match('/\b(kulkas|lemari es|freezer|chiller)\b/i', $text)) {
            if (preg_match('/\b(dingin|beku|kurang dingin|tidak dingin)\b/i', $text)) {
                $reply = "Kulkas yang kurang dingin atau tidak membekukan es biasanya dipengaruhi oleh:\n\n"
                    . "1. **Karet pintu kulkas (gasket) aus atau kendor** sehingga udara dingin keluar.\n"
                    . "2. **Defrost sensor / timer bimetal rusak** (pada kulkas 2 pintu no-frost).\n"
                    . "3. **Koil kondensor di belakang kotor** atau sirkulasi freon berkurang.\n\n"
                    . "💡 **Langkah Awal:** Bersihkan karet pintu kulkas dan pastikan ada jarak minimal 10 cm dari dinding ruangan.\n\n"
                    . "[[RECOMMEND_SERVICE_ORDER: equipment=Kulkas / Lemari Es|complaint=Kulkas tidak dingin / freezer tidak beku]]";
            } else {
                $reply = "Apakah kulkas Anda tipe 1 pintu atau 2 pintu? Ceritakan gejala yang dialami agar saya dapat membantu menganalisis lebih tepat.";
            }
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
        }

        if (preg_match('/\b(mesin cuci|cuci baju|pengering|dryer)\b/i', $text)) {
            if (preg_match('/\b(putar|berputar|mati|macet|bunyi)\b/i', $text)) {
                $reply = "Mesin cuci yang tidak mau berputar atau pengeringnya macet umumnya dipicu oleh:\n\n"
                    . "• **Tali karet fanbelt kendor atau terputus**.\n"
                    . "• **Kapasitor motor dinamo melemah**.\n"
                    . "• **Sensor pengunci pintu (door switch safety) bermasalah**.\n\n"
                    . "💡 **Tips:** Pastikan beban cucian seimbang dan pintu tertutup rapat sempurna.\n\n"
                    . "[[RECOMMEND_SERVICE_ORDER: equipment=Mesin Cuci|complaint=Mesin cuci tidak berputar / pengering macet]]";
            } else {
                $reply = "Mesin cuci Anda tipe Front Loading (bukaan depan) atau Top Loading (bukaan atas)? Jelaskan gejala yang terjadi seperti air tidak mengalir atau tabung bergetar keras.";
            }
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
        }

        if (preg_match('/\b(tv|televisi|led tv|smart tv)\b/i', $text)) {
            $reply = "Untuk kendala TV (layar gelap namun ada suara, layar bergaris, atau TV tidak menyala), kerusakan umumnya berkaitan dengan rangkaian **Backlight LED Panel** atau **Power Supply Board**.\n\n"
                . "💡 **Penting:** Pastikan sambungan listrik stabil. Pembongkaran panel TV memerlukan alat uji tegangan spesialis teknisi TV.\n\n"
                . "[[RECOMMEND_SERVICE_ORDER: equipment=Televisi (TV)|complaint=TV bermasalah pada layar / gambar / audio]]";
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
        }

        if (preg_match('/\b(dispenser|pompa air|sanyo|microwave|oven|kompor|kipas|kipas angin|setrika|water heater|rice cooker|magic com|magic jar|blender|vacuum)\b/i', $text)) {
            $reply = "Kendala pada peralatan tersebut umumnya berkaitan dengan elemen pemanas, switch saklar, atau dinamo penggerak.\n\n"
                . "Untuk mencegah kerusakan lebih lanjut dan menjamin keamanan listrik, kami menyarankan pengecekan fisik langsung oleh teknisi HomeCare Service.\n\n"
                . "[[RECOMMEND_SERVICE_ORDER: equipment=Peralatan Rumah Tangga|complaint=" . rawurlencode($lastUserMessage) . "]]";
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
        }

        // 12. General Repair Inquiries
        if (preg_match('/\b(rusak|servis|service|teknisi|reparasi|perbaikan|elektronik|peralatan|perabot|dinamo|kapasitor|freon|kompresor|termostat|bimetal)\b/i', $text)) {
            $reply = "Terima kasih telah berkonsultasi mengenai kendala peralatan rumah tangga Anda.\n\n"
                . "Untuk memastikan penyebab kerusakan dan solusi penanganan yang tepat dan aman, kami menyarankan pemeriksaan fisik langsung oleh teknisi berpengalaman HomeCare Service.\n\n"
                . "[[RECOMMEND_SERVICE_ORDER: equipment=Peralatan Rumah Tangga|complaint=" . rawurlencode($lastUserMessage) . "]]";
            return ['success' => true, 'message' => $reply, 'source' => 'Smart Database Chat Engine'];
        }

        // 13. Smart Data Catch-all for Orders (Guarantees no false out-of-scope refusals)
        if (preg_match('/\b(pesanan|order|orderan|servis|service|status pesanan|jadwal servis|tugas|pekerjaan)\b/i', $text) || (strpos($text, 'apa') !== false && strpos($text, 'pesan') !== false) || (strpos($text, 'jumlah') !== false && strpos($text, 'bayar') !== false) || (strpos($text, 'rincian') !== false && strpos($text, 'pesan') !== false)) {
            $totOrders = $dbData['platform_stats']['total_orders'] ?? count($userOrders);
            if ($isAskingCountOnly) {
                return ['success' => true, 'message' => "Total seluruh pesanan di sistem saat ini ada {$totOrders} pesanan.", 'source' => 'Database Live Context (Detailed Order Information)'];
            }

            // Tampilkan rincian ringkas (hanya yang relevan/aktif, tidak bertele-tele)
            $activeOnly = array_filter($userOrders, function($o) {
                return in_array($o['status'] ?? '', ['IN_PROGRESS', 'ACCEPTED', 'ASSIGNED', 'ON_THE_WAY', 'PENDING']);
            });
            $displayOrders = !empty($activeOnly) ? array_slice($activeOnly, 0, 3) : array_slice($userOrders, 0, 2);

            if (empty($displayOrders)) {
                $reply = "Saat ini belum ada data pesanan servis di database.";
            } else {
                $reply = "Berikut rincian pesanan yang sedang berjalan:\n\n";
                foreach ($displayOrders as $ord) {
                    $reply .= $formatSingleOrderConcise($ord) . "\n";
                }
            }

            return ['success' => true, 'message' => trim($reply), 'source' => 'Database Live Context (Detailed Order Information)'];
        }

        // 14. Strict Out-of-Scope Refusal (Only for truly irrelevant inquiries like politics, recipe, sports, coding)
        $reply = "Mohon maaf, sebagai asisten virtual resmi **HomeCare Service**, saya hanya dapat membantu pertanyaan seputar:\n\n"
            . "• **Data pesanan & riwayat servis** (rincian apa pesanannya, siapa pelanggannya, teknisi, dan jumlah pembayaran)\n"
            . "• **Daftar tim teknisi, spesialisasi, rating & ketersediaan**\n"
            . "• **Katalog peralatan & merk yang didukung di database**\n"
            . "• **Konsultasi, perawatan, dan perbaikan peralatan rumah tangga**\n\n"
            . "Silakan ajukan pertanyaan seputar data pesanan, info pelanggan, pembayaran, atau kendala peralatan rumah tangga Anda!";

        return [
            'success' => true,
            'message' => $reply,
            'source' => 'Smart Database Chat Engine (Domain Guard)'
        ];
    }

    /**
     * Format parsed diagnosis into standard structure + raw text for DB
     */
    private function formatDiagnosisResult(array $data, $source = 'AI Engine') {
        $priority = $data['tingkat_prioritas'] ?? 'Sedang';
        $technicianAdvice = $data['perlu_teknisi'] ?? 'Disarankan melakukan pemeriksaan oleh teknisi profesional.';
        $disclaimer = $data['disclaimer'] ?? 'Hasil ini merupakan diagnosis awal dari AI dan bukan diagnosis teknisi pasti. Pemeriksaan fisik oleh teknisi tetap diperlukan.';

        // Helper to format array or string into clean flowing paragraph text
        $formatParagraphText = function($val) {
            if (is_array($val)) {
                $cleaned = array_map(function($v) {
                    $t = trim($v, " \t\n\r\0\x0B-•*");
                    return rtrim($t, '.') . '.';
                }, array_filter($val));
                return implode(" ", $cleaned);
            }
            return trim((string)$val);
        };

        $analysisText = $formatParagraphText($data['penjelasan_analisis'] ?? '');
        $causesText = $formatParagraphText($data['kemungkinan_penyebab'] ?? '');
        $recommendationsText = $formatParagraphText($data['rekomendasi_tindakan'] ?? '');
        $technicianText = $formatParagraphText($technicianAdvice);
        $disclaimerText = $formatParagraphText($disclaimer);

        $estimasiBiaya = trim((string)($data['estimasi_biaya'] ?? ''));
        if (empty($estimasiBiaya)) {
            $estimasiBiaya = 'Rp 100.000 - Rp 250.000';
        }
        $rincianBiayaText = $formatParagraphText($data['rincian_biaya'] ?? 'Estimasi biaya mencakup jasa pemeriksaan teknisi dan kisaran penggantian komponen suku cadang standar.');

        if (empty($analysisText) && !empty($causesText)) {
            $analysisText = $causesText;
        }

        // Build neat narrative raw text for DB and order attachments without bullet points
        $rawText = "=== HASIL DIAGNOSIS AWAL AI ===\n";
        $rawText .= "Tingkat Prioritas: " . strtoupper($priority) . "\n\n";
        $rawText .= "Penjelasan & Analisis Kerusakan:\n";
        $rawText .= $analysisText . "\n\n";
        if (!empty($causesText) && $causesText !== $analysisText) {
            $rawText .= "Kemungkinan Penyebab Teknis:\n";
            $rawText .= $causesText . "\n\n";
        }
        $rawText .= "Estimasi Perkiraan Biaya:\n";
        $rawText .= $estimasiBiaya . "\n";
        if (!empty($rincianBiayaText)) {
            $rawText .= "Rincian Biaya: " . $rincianBiayaText . "\n\n";
        } else {
            $rawText .= "\n";
        }
        $rawText .= "Panduan & Rekomendasi Tindakan Awal:\n";
        $rawText .= $recommendationsText . "\n\n";
        $rawText .= "Saran Pemeriksaan Teknisi:\n";
        $rawText .= $technicianText . "\n\n";
        $rawText .= "Catatan / Disclaimer:\n";
        $rawText .= $disclaimerText;

        return [
            'success' => true,
            'source' => $source,
            'penjelasan_analisis' => $analysisText,
            'kemungkinan_penyebab' => $causesText,
            'tingkat_prioritas' => $priority,
            'estimasi_biaya' => $estimasiBiaya,
            'rincian_biaya' => $rincianBiayaText,
            'rekomendasi_tindakan' => $recommendationsText,
            'perlu_teknisi' => $technicianText,
            'disclaimer' => $disclaimerText,
            'raw_text' => $rawText
        ];
    }
}
