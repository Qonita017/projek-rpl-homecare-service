<?php
class AdminAISettingController extends Controller {

    private array $presetModels = [
        'nvidia' => [
            [
                'id' => 'meta/llama-3.2-11b-vision-instruct',
                'name' => 'Llama 3.2 11B Vision Instruct (Rekomendasi Default - Cepat & Multimodal)',
                'badge' => 'Rekomendasi'
            ],
            [
                'id' => 'meta/llama-3.2-3b-instruct',
                'name' => 'Llama 3.2 3B Instruct (Ultra Cepat, Ringan & Responsif)',
                'badge' => 'Cepat'
            ],
            [
                'id' => 'meta/llama-3.2-1b-instruct',
                'name' => 'Llama 3.2 1B Instruct (Sangat Ringan & Hemat Kuota)',
                'badge' => 'Ringan'
            ],
            [
                'id' => 'nvidia/llama-3.1-nemotron-70b-instruct',
                'name' => 'NVIDIA Llama 3.1 Nemotron 70B (Optimalisasi Khusus NVIDIA)',
                'badge' => 'NVIDIA'
            ]
        ],
        'gemini' => [
            [
                'id' => 'gemini-3.5-flash',
                'name' => 'Gemini 3.5 Flash (Rekomendasi Google - Generasi Terbaru, Cepat & Cerdas)',
                'badge' => 'Rekomendasi'
            ],
            [
                'id' => 'gemini-3.5-flash-lite',
                'name' => 'Gemini 3.5 Flash-Lite (Super Cepat & Hemat Kuota)',
                'badge' => 'Cepat'
            ],
            [
                'id' => 'gemini-3.1-flash-lite',
                'name' => 'Gemini 3.1 Flash-Lite (Ringan & Stabil)',
                'badge' => 'Ringan'
            ],
            [
                'id' => 'gemini-3.1-pro-preview',
                'name' => 'Gemini 3.1 Pro Preview (Penalaran Mendalam & Analisis Kompleks)',
                'badge' => 'Pro'
            ]
        ],
        'openai' => [
            [
                'id' => 'gpt-4o-mini',
                'name' => 'GPT-4o Mini (Rekomendasi OpenAI - Cerdas, Hemat Biaya & Responsif)',
                'badge' => 'Rekomendasi'
            ],
            [
                'id' => 'gpt-4o',
                'name' => 'GPT-4o (Model Flagship Tercerdas)',
                'badge' => 'Flagship'
            ],
            [
                'id' => 'gpt-3.5-turbo',
                'name' => 'GPT-3.5 Turbo (Klasik & Ringan)',
                'badge' => 'Klasik'
            ],
            [
                'id' => 'gpt-4-turbo',
                'name' => 'GPT-4 Turbo (Kapasitas Lengkap)',
                'badge' => 'Lengkap'
            ]
        ],
        'groq' => [
            [
                'id' => 'llama-3.3-70b-versatile',
                'name' => 'Llama 3.3 70B Versatile (Flagship Llama 3.3 di Groq LPU)',
                'badge' => 'Rekomendasi'
            ],
            [
                'id' => 'llama-3.1-8b-instant',
                'name' => 'Llama 3.1 8B Instant (Kecepatan Ekstrem 500+ tok/s)',
                'badge' => 'Ultra Cepat'
            ],
            [
                'id' => 'mixtral-8x7b-32768',
                'name' => 'Mixtral 8x7B (Konteks Panjang 32k)',
                'badge' => '32k Token'
            ],
            [
                'id' => 'gemma2-9b-it',
                'name' => 'Gemma 2 9B IT (Google Gemma di Groq)',
                'badge' => 'Gemma'
            ]
        ]
    ];

    public function index(): void {
        $settingModel = $this->model('Setting');

        $activeProvider = $settingModel->get('ai_provider', defined('AI_PROVIDER') ? AI_PROVIDER : 'nvidia');
        $activeApiKey   = $settingModel->get('ai_api_key', defined('AI_API_KEY') ? AI_API_KEY : '');
        $activeModel    = $settingModel->get('ai_model', defined('AI_MODEL') ? AI_MODEL : 'meta/llama-3.2-11b-vision-instruct');
        $temperature    = $settingModel->get('ai_temperature', '0.3');
        $maxTokens      = $settingModel->get('ai_max_tokens', '900');

        $data = [
            'title'           => 'Konfigurasi AI Chatbot & Model - Admin HomeCare',
            'active_provider' => strtolower($activeProvider),
            'active_api_key'  => $activeApiKey,
            'active_model'    => $activeModel,
            'temperature'     => $temperature,
            'max_tokens'      => $maxTokens,
            'preset_models'   => $this->presetModels,
            'provider_keys'   => [
                'nvidia' => $settingModel->get('ai_api_key_nvidia', ($activeProvider === 'nvidia' ? $activeApiKey : '')),
                'gemini' => $settingModel->get('ai_api_key_gemini', ($activeProvider === 'gemini' ? $activeApiKey : '')),
                'openai' => $settingModel->get('ai_api_key_openai', ($activeProvider === 'openai' ? $activeApiKey : '')),
                'groq'   => $settingModel->get('ai_api_key_groq', ($activeProvider === 'groq' ? $activeApiKey : ''))
            ]
        ];

        $this->view('admin/ai_setting/index', $data);
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/ai-setting");
            exit;
        }

        $provider    = strtolower(trim($_POST['provider'] ?? 'nvidia'));
        $apiKey      = trim($_POST['api_key'] ?? '');
        $modelChoice = trim($_POST['model_choice'] ?? '');
        $customModel = trim($_POST['custom_model'] ?? '');
        $temperature = trim($_POST['temperature'] ?? '0.3');
        $maxTokens   = trim($_POST['max_tokens'] ?? '900');

        // Tentukan model yang dipakai
        $selectedModel = ($modelChoice === 'custom' && !empty($customModel)) ? $customModel : $modelChoice;

        if ($provider === 'gemini') {
            if (empty($selectedModel) || str_contains($selectedModel, '1.5') || str_contains($selectedModel, '2.0') || str_contains($selectedModel, '2.5')) {
                $selectedModel = 'gemini-3.5-flash';
            }
        } elseif (empty($selectedModel)) {
            $selectedModel = match($provider) {
                'openai' => 'gpt-4o-mini',
                'groq'   => 'llama-3.3-70b-versatile',
                default  => 'meta/llama-3.2-11b-vision-instruct',
            };
        }

        $settingModel = $this->model('Setting');

        // Simpan ke database system_settings
        $settingModel->set('ai_provider', $provider);
        $settingModel->set('ai_api_key', $apiKey);
        $settingModel->set('ai_model', $selectedModel);
        $settingModel->set('ai_temperature', $temperature);
        $settingModel->set('ai_max_tokens', $maxTokens);

        // Simpan juga key spesifik provider agar tidak hilang saat beralih provider
        if (!empty($apiKey)) {
            $settingModel->set("ai_api_key_{$provider}", $apiKey);
        }

        // Sinkronisasi ke .env file agar sinkron
        $settingModel->updateEnv([
            'AI_PROVIDER' => $provider,
            'AI_API_KEY'  => $apiKey,
            'AI_MODEL'    => $selectedModel
        ]);

        $_SESSION['flash_success'] = "Pengaturan AI Chatbot berhasil diperbarui! Provider saat ini: <strong>" . strtoupper($provider) . "</strong> dengan Model: <code>" . htmlspecialchars($selectedModel) . "</code>.";
        header("Location: " . BASE_URL . "/admin/ai-setting");
        exit;
    }

    /**
     * AJAX endpoint: Fetch daftar model dari API Key yang dimasukkan
     */
    public function fetchModels(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode tidak valid.']);
            exit;
        }

        $provider = strtolower(trim($_POST['provider'] ?? 'nvidia'));
        $apiKey   = trim($_POST['api_key'] ?? '');

        // Jika API Key kosong, coba ambil key tersimpan untuk provider ini
        if (empty($apiKey)) {
            $settingModel = $this->model('Setting');
            $activeProvider = $settingModel->get('ai_provider', 'nvidia');
            if ($provider === $activeProvider) {
                $apiKey = $settingModel->get('ai_api_key', defined('AI_API_KEY') ? AI_API_KEY : '');
            } else {
                $apiKey = $settingModel->get("ai_api_key_{$provider}", '');
            }
        }

        if (empty($apiKey)) {
            echo json_encode([
                'success' => false,
                'message' => 'API Key untuk provider ' . strtoupper($provider) . ' belum diisi. Masukkan API Key terlebih dahulu.',
                'fallback_models' => $this->presetModels[$provider] ?? []
            ]);
            exit;
        }

        // Validasi kesesuaian format prefix key
        $keyWarning = $this->validateKeyFormat($provider, $apiKey);
        if ($keyWarning) {
            echo json_encode([
                'success' => false,
                'message' => $keyWarning,
                'fallback_models' => $this->presetModels[$provider] ?? []
            ]);
            exit;
        }

        try {
            $models = [];
            switch ($provider) {
                case 'gemini':
                    $models = $this->fetchGeminiModels($apiKey);
                    break;
                case 'openai':
                    $models = $this->fetchOpenAIModels($apiKey);
                    break;
                case 'groq':
                    $models = $this->fetchGroqModels($apiKey);
                    break;
                case 'nvidia':
                default:
                    $models = $this->fetchNvidiaModels($apiKey);
                    break;
            }

            if (empty($models)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'API terhubung, namun model chat khusus belum terfilter. Menampilkan model rekomendasi standar.',
                    'source' => 'preset',
                    'count' => count($this->presetModels[$provider] ?? []),
                    'models' => $this->presetModels[$provider] ?? []
                ]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Berhasil mengambil ' . count($models) . ' model langsung dari API provider ' . strtoupper($provider) . '!',
                'source' => 'api',
                'count' => count($models),
                'models' => $models
            ]);
            exit;

        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mengambil model dari API: ' . $e->getMessage() . '. Menampilkan model rekomendasi siap pakai.',
                'fallback_models' => $this->presetModels[$provider] ?? []
            ]);
            exit;
        }
    }

    /**
     * AJAX endpoint: Tes koneksi chat singkat dengan API Key & Model
     */
    public function testConnection(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode tidak valid.']);
            exit;
        }

        $provider = strtolower(trim($_POST['provider'] ?? 'nvidia'));
        $apiKey   = trim($_POST['api_key'] ?? '');
        $model    = trim($_POST['model'] ?? '');

        // Jika API Key kosong, coba ambil key tersimpan untuk provider ini
        if (empty($apiKey)) {
            $settingModel = $this->model('Setting');
            $activeProvider = $settingModel->get('ai_provider', 'nvidia');
            if ($provider === $activeProvider) {
                $apiKey = $settingModel->get('ai_api_key', defined('AI_API_KEY') ? AI_API_KEY : '');
            } else {
                $apiKey = $settingModel->get("ai_api_key_{$provider}", '');
            }
        }

        if (empty($apiKey)) {
            echo json_encode([
                'success'    => false,
                'latency_ms' => 0,
                'provider'   => strtoupper($provider),
                'model'      => $model ?: '-',
                'message'    => 'API Key untuk provider ' . strtoupper($provider) . ' belum diisi. Masukkan API Key terlebih dahulu.'
            ]);
            exit;
        }

        // Validasi kesesuaian format prefix key
        $keyWarning = $this->validateKeyFormat($provider, $apiKey);
        if ($keyWarning) {
            echo json_encode([
                'success'    => false,
                'latency_ms' => 0,
                'provider'   => strtoupper($provider),
                'model'      => $model ?: '-',
                'message'    => $keyWarning
            ]);
            exit;
        }

        if (empty($model)) {
            $model = match($provider) {
                'gemini' => 'gemini-3.5-flash',
                'openai' => 'gpt-4o-mini',
                'groq'   => 'llama-3.3-70b-versatile',
                default  => 'meta/llama-3.2-11b-vision-instruct',
            };
        }

        $startTime = microtime(true);

        try {
            $reply = $this->executeQuickTestPing($provider, $apiKey, $model);
            $latencyMs = round((microtime(true) - $startTime) * 1000);

            echo json_encode([
                'success'    => true,
                'latency_ms' => $latencyMs,
                'provider'   => strtoupper($provider),
                'model'      => $model,
                'reply'      => $reply,
                'message'    => "Koneksi berhasil! Respon diterima dalam {$latencyMs} ms."
            ]);
            exit;
        } catch (\Throwable $e) {
            $latencyMs = round((microtime(true) - $startTime) * 1000);
            echo json_encode([
                'success'    => false,
                'latency_ms' => $latencyMs,
                'provider'   => strtoupper($provider),
                'model'      => $model,
                'message'    => 'Koneksi gagal: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Validasi format prefix API Key untuk mencegah tertukar antar provider
     */
    private function validateKeyFormat(string $provider, string $apiKey): ?string {
        if (empty($apiKey)) return null;

        $detected = 'Unknown';
        if (str_starts_with($apiKey, 'nvapi-')) {
            $detected = 'NVIDIA NIM';
        } elseif (str_starts_with($apiKey, 'gsk_')) {
            $detected = 'Groq Cloud';
        } elseif (str_starts_with($apiKey, 'sk-')) {
            $detected = 'OpenAI';
        } elseif (str_starts_with($apiKey, 'AIza') || str_starts_with($apiKey, 'AQ.')) {
            $detected = 'Google Gemini';
        }

        if ($provider === 'nvidia' && !str_starts_with($apiKey, 'nvapi-')) {
            return "Format API Key tidak sesuai untuk NVIDIA NIM (terdeteksi format {$detected}). API Key NVIDIA harus diawali dengan 'nvapi-'. Silakan periksa kembali key Anda.";
        }
        if ($provider === 'groq' && !str_starts_with($apiKey, 'gsk_')) {
            return "Format API Key tidak sesuai untuk Groq LPU (terdeteksi format {$detected}). API Key Groq harus diawali dengan 'gsk_'. Dapatkan key gratis di console.groq.com.";
        }
        if ($provider === 'openai' && !str_starts_with($apiKey, 'sk-')) {
            return "Format API Key tidak sesuai untuk OpenAI (terdeteksi format {$detected}). API Key OpenAI harus diawali dengan 'sk-'.";
        }
        if ($provider === 'gemini' && ($detected !== 'Google Gemini' && $detected !== 'Unknown')) {
            return "Format API Key tidak sesuai untuk Google Gemini (terdeteksi format {$detected}). API Key Gemini diawali dengan 'AIzaSy...' atau 'AQ.' dari aistudio.google.com.";
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Private Helpers: Provider API Fetchers & Ping
    // -------------------------------------------------------------------------

    private function fetchGeminiModels(string $apiKey): array {
        $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($apiKey);
        $response = $this->curlGet($url, [
            "x-goog-api-key: {$apiKey}"
        ], $apiKey);
        $data = json_decode($response, true);

        if (!isset($data['models']) || !is_array($data['models'])) {
            $errMsg = $data['error']['message'] ?? 'Respons Gemini tidak valid.';
            throw new Exception($errMsg);
        }

        $models = [];
        foreach ($data['models'] as $m) {
            $methods = $m['supportedGenerationMethods'] ?? [];
            if (!in_array('generateContent', $methods)) {
                continue;
            }
            $cleanId = str_replace('models/', '', $m['name'] ?? '');
            $displayName = $m['displayName'] ?? $cleanId;
            $models[] = [
                'id' => $cleanId,
                'name' => $displayName . " ({$cleanId})",
                'badge' => str_contains($cleanId, 'flash') ? 'Flash' : 'Pro'
            ];
        }

        return $models;
    }

    private function fetchNvidiaModels(string $apiKey): array {
        $url = "https://integrate.api.nvidia.com/v1/models";
        $response = $this->curlGet($url, ["Authorization: Bearer {$apiKey}"], $apiKey);
        $data = json_decode($response, true);

        if (!isset($data['data']) || !is_array($data['data'])) {
            $errMsg = $data['detail'] ?? ($data['error']['message'] ?? 'Respons NVIDIA NIM tidak valid.');
            throw new Exception($errMsg);
        }

        $models = [];
        foreach ($data['data'] as $m) {
            $id = $m['id'] ?? '';
            if (empty($id)) continue;
            // Prefer text / instruct / vision models
            if (!str_contains($id, 'instruct') && !str_contains($id, 'chat') && !str_contains($id, 'vision') && !str_contains($id, 'llama')) {
                continue;
            }
            $models[] = [
                'id' => $id,
                'name' => $id,
                'badge' => str_contains($id, 'llama') ? 'Llama' : 'NVIDIA'
            ];
        }

        return !empty($models) ? $models : $this->presetModels['nvidia'];
    }

    private function fetchOpenAIModels(string $apiKey): array {
        $url = "https://api.openai.com/v1/models";
        $response = $this->curlGet($url, ["Authorization: Bearer {$apiKey}"], $apiKey);
        $data = json_decode($response, true);

        if (!isset($data['data']) || !is_array($data['data'])) {
            $errMsg = $data['error']['message'] ?? 'Respons OpenAI tidak valid.';
            throw new Exception($errMsg);
        }

        $models = [];
        foreach ($data['data'] as $m) {
            $id = $m['id'] ?? '';
            if (empty($id)) continue;
            // Filter chat models (gpt, o1, o3)
            if (str_starts_with($id, 'gpt-') || str_starts_with($id, 'o1') || str_starts_with($id, 'o3') || str_starts_with($id, 'chatgpt-')) {
                $models[] = [
                    'id' => $id,
                    'name' => $id,
                    'badge' => str_contains($id, 'mini') ? 'Mini' : (str_contains($id, '4o') ? '4o' : 'GPT')
                ];
            }
        }

        usort($models, function($a, $b) {
            return strcmp($a['id'], $b['id']);
        });

        return $models;
    }

    private function fetchGroqModels(string $apiKey): array {
        $url = "https://api.groq.com/openai/v1/models";
        $response = $this->curlGet($url, ["Authorization: Bearer {$apiKey}"], $apiKey);
        $data = json_decode($response, true);

        if (!isset($data['data']) || !is_array($data['data'])) {
            $errMsg = $data['error']['message'] ?? 'Respons Groq tidak valid.';
            throw new Exception($errMsg);
        }

        $models = [];
        foreach ($data['data'] as $m) {
            $id = $m['id'] ?? '';
            if (empty($id)) continue;
            if (str_contains($id, 'whisper')) continue; // skip audio
            $models[] = [
                'id' => $id,
                'name' => $id,
                'badge' => 'Groq LPU'
            ];
        }

        return $models;
    }

    private function executeQuickTestPing(string $provider, string $apiKey, string $model): string {
        $testPrompt = "Balas dalam satu kalimat singkat: 'Halo! Koneksi AI HomeCare Service aktif dan siap melayani.'";

        if ($provider === 'gemini') {
            $cleanModel = str_replace('models/', '', $model);
            if (empty($cleanModel) || str_contains($cleanModel, '1.5') || str_contains($cleanModel, '2.0') || str_contains($cleanModel, '2.5')) {
                $cleanModel = 'gemini-3.5-flash';
                $model = 'gemini-3.5-flash';
            }
            $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($cleanModel) . ":generateContent?key=" . urlencode($apiKey);
            $payload = [
                'contents' => [
                    ['parts' => [['text' => $testPrompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 300
                ]
            ];
            $headers = [
                'Content-Type: application/json',
                "x-goog-api-key: {$apiKey}"
            ];
            $res = $this->curlPost($url, json_encode($payload), $headers, $apiKey, $model);
            $json = json_decode($res, true);
            if (isset($json['error'])) {
                $errMsg = $json['error']['message'] ?? 'Gemini Error';
                $reason = $json['error']['details'][0]['reason'] ?? '';
                if ($reason === 'ACCESS_TOKEN_TYPE_UNSUPPORTED' || str_contains($errMsg, 'API key not valid')) {
                    throw new Exception("API Key Google Gemini ditolak oleh server Google (UNAUTHENTICATED). Pastikan API Key dibuat di Google AI Studio (aistudio.google.com/app/apikey) dan layanan Generative Language API telah diaktifkan.");
                }
                throw new Exception("Gemini Error: " . $errMsg);
            }
            
            $parts = $json['candidates'][0]['content']['parts'] ?? [];
            $replyText = '';
            foreach ($parts as $p) {
                if (!empty($p['text'])) {
                    $replyText .= $p['text'] . ' ';
                }
            }
            $replyText = trim($replyText);
            return !empty($replyText) ? $replyText : 'Koneksi Gemini berhasil terhubung!';

        } elseif ($provider === 'openai' || $provider === 'groq') {
            $url = ($provider === 'groq') 
                ? "https://api.groq.com/openai/v1/chat/completions" 
                : "https://api.openai.com/v1/chat/completions";

            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $testPrompt]
                ],
                'max_tokens' => 60,
                'temperature' => 0.2
            ];
            $res = $this->curlPost($url, json_encode($payload), [
                'Content-Type: application/json',
                "Authorization: Bearer {$apiKey}"
            ], $apiKey, $model);
            $json = json_decode($res, true);
            if (isset($json['error'])) {
                $errMsg = $json['error']['message'] ?? 'API Error';
                if ($provider === 'groq' && str_contains($errMsg, 'Invalid API Key')) {
                    throw new Exception("API Key Groq tidak valid. Pastikan key diawali dengan 'gsk_' dan disalin dari console.groq.com.");
                }
                throw new Exception(strtoupper($provider) . " Error: " . $errMsg);
            }
            return $json['choices'][0]['message']['content'] ?? 'Respons kosong';

        } else { // nvidia
            $url = "https://integrate.api.nvidia.com/v1/chat/completions";
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $testPrompt]
                ],
                'max_tokens' => 60,
                'temperature' => 0.2
            ];
            $res = $this->curlPost($url, json_encode($payload), [
                'Content-Type: application/json',
                "Authorization: Bearer {$apiKey}"
            ], $apiKey, $model);
            $json = json_decode($res, true);
            if (isset($json['error'])) {
                throw new Exception("NVIDIA Error: " . ($json['error']['message'] ?? ''));
            }
            return $json['choices'][0]['message']['content'] ?? 'Respons kosong dari NVIDIA NIM';
        }
    }

    private function curlGet(string $url, array $headers = [], string $apiKey = '', string $model = ''): string {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            throw new Exception("Koneksi gagal (Network Timeout/Error): " . $err);
        }
        if ($httpCode >= 400) {
            $this->handleHttpErrorResponse($httpCode, (string)$response, $model);
        }
        return (string)$response;
    }

    private function curlPost(string $url, string $payload, array $headers = [], string $apiKey = '', string $model = ''): string {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            throw new Exception("Koneksi gagal (Network Timeout/Error): " . $err);
        }
        if ($httpCode >= 400) {
            $this->handleHttpErrorResponse($httpCode, (string)$response, $model);
        }
        return (string)$response;
    }

    private function handleHttpErrorResponse(int $httpCode, string $response, string $model): void {
        $parsed = json_decode($response, true);
        $rawMsg = $parsed['error']['message'] ?? ($parsed['detail'] ?? ($parsed['title'] ?? "HTTP Status {$httpCode}"));

        // Terjemahan pesan error yang jelas dan solutif bagi pengguna
        if (str_contains($response, 'Invalid unsecured/JWS') || str_contains($response, 'Invalid JSON object')) {
            throw new Exception("API Key NVIDIA tidak valid atau formatnya keliru. Pastikan Anda memasukkan token nvapi-... resmi dari build.nvidia.com.");
        }
        if (str_contains($response, 'ACCESS_TOKEN_TYPE_UNSUPPORTED') || (str_contains($response, 'API key not valid') && $httpCode === 401)) {
            throw new Exception("API Key Google Gemini ditolak oleh server Google (UNAUTHENTICATED). Pastikan API Key dibuat di Google AI Studio (aistudio.google.com/app/apikey) dan Generative Language API aktif pada project Anda.");
        }
        if (str_contains($response, 'Not found for account') || ($httpCode === 404 && str_contains($response, 'Function'))) {
            throw new Exception("Model '{$model}' tidak tersedia atau memerlukan otorisasi khusus pada akun NVIDIA ini (HTTP 404). Silakan gunakan model lain atau provider Google Gemini / Groq.");
        }
        if ($httpCode === 410 || str_contains($response, 'reached its end of life')) {
            throw new Exception("Model '{$model}' sudah tidak didukung lagi (End-Of-Life) oleh NVIDIA. Silakan pilih model terbaru.");
        }
        if ($httpCode === 429) {
            throw new Exception("Batas kuota panggilan terlampaui (Rate Limit / Quota Exceeded - HTTP 429). Mohon tunggu sejenak sebelum mencoba kembali.");
        }

        throw new Exception($rawMsg);
    }
}


