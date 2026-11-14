# 📋 PRODUCT REQUIREMENTS DOCUMENT (PRD) & SOFTWARE REQUIREMENTS SPECIFICATION (SRS)
## Sistem Informasi Jasa Perbaikan & Perawatan Peralatan Rumah Tangga Cerdas berbasis Artificial Intelligence (HomeCare Service)

* **Standar Acuan:** IEEE Std 830-1998 / ISO/IEC/IEEE 29148:2018 (Systems and software engineering — Life cycle processes — Requirements engineering)
* **Mata Kuliah:** Rekayasa Perangkat Lunak (TIKB2503021) — Semester 3
* **Program Studi:** S1 Teknik Informatika, STMIK IKMI Cirebon
* **Dosen Pengampu:** Yudhistira Arie Wijaya, M.Kom., CDS.
* **Penyusun / Mahasiswa:** Qonita Goniyun Ulum
* **Sifat Pengerjaan:** Mandiri / Perorangan (*100% Individual Ownership & Full-App Build*)
* **Versi Dokumen:** 1.0.0 (Final Release)
* **Tanggal Rilis:** 10 November 2026

---

## DAFTAR ISI
1. [BAB I: PENDAHULUAN](#bab-i-pendahuluan)
   - 1.1 Tujuan Dokumen
   - 1.2 Latar Belakang Masalah
   - 1.3 Solusi dan Proposisi Nilai (*Value Proposition*)
   - 1.4 Ruang Lingkup Produk (*Product Scope*)
   - 1.5 Definisi, Istilah, dan Singkatan (*Glossary*)
2. [BAB II: DESKRIPSI UMUM SISTEM](#bab-ii-deskripsi-umum-sistem)
   - 2.1 Perspektif Produk & Arsitektur Umum
   - 2.2 Karakteristik & Hak Akses Pengguna (*User Roles*)
   - 2.3 Lingkungan Operasional (*Operating Environment*)
   - 2.4 Batasan Perancangan & Implementasi
3. [BAB III: KEBUTUHAN FUNGSIONAL SISTEM (FUNCTIONAL REQUIREMENTS)](#bab-iii-kebutuhan-fungsional-sistem-functional-requirements)
   - 3.1 Modul Autentikasi & Akun Pengguna (FR-01 s/d FR-04)
   - 3.2 Modul AI Smart Diagnosis Kerusakan (FR-05 s/d FR-08)
   - 3.3 Modul HomeCare AI Virtual Assistant / Chatbot (FR-09 s/d FR-14)
   - 3.4 Modul Siklus Pemesanan Servis / *Order Lifecycle* (FR-15 s/d FR-19)
   - 3.5 Modul Manajemen Pekerjaan & Laporan Teknisi (FR-20 s/d FR-23)
   - 3.6 Modul Transaksi & Pembayaran (FR-24 s/d FR-27)
   - 3.7 Modul Ulasan & Rating Layanan (FR-28 s/d FR-30)
   - 3.8 Modul Notifikasi Terpadu & WhatsApp Gateway (FR-31 s/d FR-33)
   - 3.9 Modul Manajemen Master Data Admin (FR-34 s/d FR-37)
   - 3.10 Modul Konfigurasi Multi-Provider AI (FR-38 s/d FR-40)
4. [BAB IV: KEBUTUHAN NON-FUNGSIONAL (NON-FUNCTIONAL REQUIREMENTS - ISO 25010)](#bab-iv-kebutuhan-non-fungsional-non-functional-requirements---iso-25010)
   - 4.1 Efisiensi Kinerja (*Performance Efficiency*)
   - 4.2 Keamanan Sistem (*Security*)
   - 4.3 Keandalan (*Reliability*)
   - 4.4 Kemudahan Penggunaan (*Usability*)
   - 4.5 Kemudahan Pemeliharaan (*Maintainability*)
   - 4.6 Portabilitas (*Portability*)
5. [BAB V: SPESIFIKASI MODUL KECERDASAN ARTIFISIAL (AI ENGINE SPECIFICATION)](#bab-v-spesifikasi-modul-kecerdasan-artifisial-ai-engine-specification)
   - 5.1 Fitur 1: AI Smart Diagnosis Kerusakan Peralatan
   - 5.2 Fitur 2: HomeCare AI Virtual Assistant & Live Database Context Retrieval
   - 5.3 Kebijakan Respons AI (*To-the-Point vs Detailed Breakdown Policy*)
   - 5.4 Multi-Provider AI Architecture & Fallback Mechanism
6. [BAB VI: SKEMA BASIS DATA RELASIONAL (DATABASE SCHEMA)](#bab-vi-skema-basis-data-relasional-database-schema)
7. [BAB VII: MATRIKS PENGUJIAN & KRITERIA KEBERHASILAN (ACCEPTANCE CRITERIA)](#bab-vii-matriks-pengujian--kriteria-keberhasilan-acceptance-criteria)
8. [BAB VIII: ROADMAP PENGEMBANGAN & TENGGAT WAKTU](#bab-viii-roadmap-pengembangan--tenggat-waktu)

---

# BAB I: PENDAHULUAN

### 1.1 Tujuan Dokumen
Dokumen *Product Requirements Document* (PRD) yang sekaligus berfungsi sebagai *Software Requirements Specification* (SRS) ini disusun untuk menetapkan spesifikasi kebutuhan sistem secara menyeluruh, terukur, dan terstruktur bagi aplikasi berbasis web **HomeCare Service**. Dokumen ini menjadi pedoman utama dalam tahapan perancangan arsitektur berorientasi objek (UML/SDD), perancangan antarmuka (UI/UX Figma), konstruksi kode pemrograman (Clean Code & SOLID), integrasi modul Kecerdasan Artifisial (AI), hingga pengujian otomatis (*Unit Testing*) dan *DevOps CI/CD Pipeline*.

### 1.2 Latar Belakang Masalah
Peralatan elektronik rumah tangga (seperti *Air Conditioner* (AC), Mesin Cuci, Kulkas, Televisi, Pompa Air, dan Microwave) merupakan kebutuhan primer penunjang kenyamanan dan sanitasi rumah tangga modern. Namun, ketika peralatan tersebut mengalami gangguan teknis atau kerusakan, masyarakat kerap menghadapi masalah klasik yang berulang:
1. **Asimetri Informasi & Kebingungan Diagnosis Awal:** Pelanggan umumnya tidak memahami secara teknis komponen apa yang bermasalah, apakah kerusakan tergolong ringan atau berat, serta apakah peralatan masih layak diperbaiki.
2. **Ketidakpastian Biaya & Kerentanan Kecurangan:** Kurangnya transparansi estimasi biaya sering memicu kekhawatiran pelanggan akan potensi manipulasi tarif oleh oknum teknisi tidak bertanggung jawab.
3. **Sulitnya Menemukan Teknisi Tepercaya & Penjadwalan Berbelit:** Proses pemesanan servis konvensional masih mengandalkan komunikasi pesan instan manual yang rawan salah catat, ketiadaan bukti tanda terima formal, dan ketiadaan pelacakan status pekerjaan secara *real-time*.
4. **Kendala Operasional bagi Pengelola/Penyedia Jasa:** Pengelola jasa servis mengalami kesulitan memantau penugasan teknisi, rekapitulasi omset harian yang akurat, verifikasi mutasi transfer, serta pengarsipan riwayat perbaikan fisik peralatan pelanggan.

### 1.3 Solusi dan Proposisi Nilai (*Value Proposition*)
**HomeCare Service** hadir sebagai platform web cerdas satu pintu (*one-stop smart home appliance solution*) yang menjembatani pelanggan, teknisi lapangan, dan pengelola jasa servis secara transparan, akuntabel, dan efisien.

**Nilai Tambah Utama (Key Value Propositions):**
* **AI Early Smart Diagnosis:** Pelanggan memperoleh analisis akar penyebab kerusakan, tingkat urgensi (*urgency priority*), serta rentang estimasi biaya perbaikan wajar dalam hitungan detik sebelum memutuskan memanggil teknisi, lengkap dengan klausul *disclaimer* teknisi.
* **HomeCare AI Virtual Assistant (Live DB-Connected):** Chatbot asisten cerdas yang terhubung langsung (*real-time*) dengan basis data operasional sistem. Mampu menyajikan ringkasan statistik (jumlah pesanan, teknisi, omset lunas) secara *to-the-point* dan membeberkan rincian transaksi riil saat diinstruksikan tanpa halusinasi data.
* **Transparansi Siklus Servis:** Pelacakan status perbaikan tahap demi tahap (*Menunggu Konfirmasi $\rightarrow$ Teknisi Ditugaskan $\rightarrow$ Menuju Lokasi $\rightarrow$ Sedang Diperbaiki $\rightarrow$ Selesai*).
* **Akuntabilitas Finansial & Laporan Fisik:** Validasi bukti transfer oleh admin, kwitansi elektronik resmi, serta pencatatan tindakan teknis dan suku cadang dalam laporan servis fisik.

### 1.4 Ruang Lingkup Produk (*Product Scope*)
* **Pengguna Utama:** Masyarakat umum / Pemilik rumah tangga (Customer), Teknisi Perbaikan (Technician), dan Administrator / Pemilik Bisnis Jasa Servis (Admin).
* **Platform:** Aplikasi Web Responsif yang dapat diakses dengan mulus dari peramban desktop maupun ponsel pintar (*mobile browser*).
* **Integrasi Eksternal:**
  - LLM API Engine (Google Gemini 1.5 Flash/Pro, NVIDIA NIM Llama 3.2, Groq Llama 3.3, OpenAI GPT-4o-mini).
  - WhatsApp Notification Gateway (Fonnte API) untuk pengiriman notifikasi instan status pesanan.

### 1.5 Definisi, Istilah, dan Singkatan (*Glossary*)
* **PRD:** *Product Requirements Document*, dokumen acuan spesifikasi fungsionalitas dan bisnis produk.
* **SRS:** *Software Requirements Specification* standar IEEE 830.
* **MVC:** *Model-View-Controller*, pola arsitektur perangkat lunak yang memisahkan logika data, antarmuka pengguna, dan alur kontrol.
* **SOLID:** Lima prinsip desain berorientasi objek (Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion).
* **LLM:** *Large Language Model*, model kecerdasan buatan pemroses bahasa alami.
* **RAG / DB Context Retrieval:** Teknik injeksi konteks data dinamis dari basis data ke dalam prompt LLM guna menghasilkan jawaban faktual dan mencegah halusinasi.
* **CI/CD:** *Continuous Integration / Continuous Deployment* menggunakan GitHub Actions.

---

# BAB II: DESKRIPSI UMUM SISTEM

### 2.1 Perspektif Produk & Arsitektur Umum
HomeCare Service dibangun sebagai sistem mandiri (*self-contained application*) menggunakan arsitektur **Clean PHP Native MVC**. Sistem tidak menggunakan framework *all-in-one* berat, melainkan dirancang secara modular untuk menjamin performa tinggi, efisiensi konsumsi memori server, dan pemahaman mendalam atas alur rekayasa perangkat lunak.

```
                      +-----------------------------------+
                      |      Client Browser (Desktop/HP)  |
                      | Bootstrap 5, Vanilla JS, AJAX     |
                      +-----------------+-----------------+
                                        | (HTTP/JSON)
                                        v
                      +-----------------+-----------------+
                      |     Router & Middlewares (Auth)   |
                      +-----------------+-----------------+
                                        |
       +--------------------------------+--------------------------------+
       |                                |                                |
       v                                v                                v
+--------------+               +------------------+             +-----------------+
| Customer/    |               | Order/Payment/   |             | AI Diagnosis &  |
| Account Ctrl |               | Technician Ctrl  |             | AI Chat Ctrl    |
+-------+------+               +--------+---------+             +--------+--------+
        |                               |                                |
        |                               |                                v
        |                               |                     +----------+----------+
        |                               |                     | AIService &         |
        |                               |                     | DBContextService    |
        |                               |                     +----------+----------+
        |                               |                                | (REST HTTPS)
        v                               v                                v
+-------+-------------------------------+--------------------------------+--------+
|                          Data Models (PDO MySQL Database)                      |
| (users, customers, technicians, equipment, service_orders, payments, reviews)  |
+---------------------------------------------------------------------------------+
```

### 2.2 Karakteristik & Hak Akses Pengguna (*User Roles*)

| Peran Pengguna (*Role*) | Deskripsi & Tujuan | Tanggung Jawab Utama dalam Sistem |
|---|---|---|
| **CUSTOMER** | Pengguna akhir pemilik peralatan rumah tangga yang membutuhkan perbaikan atau perawatan. | Mendaftar akun, melakukan AI diagnosis mandiri, memesan servis, mengunggah bukti pembayaran, berinteraksi dengan AI Assistant, dan memberikan ulasan rating. |
| **TEKNISI (Technician)** | Mitra kerja lapangan profesional yang bertugas melakukan inspeksi fisik dan reparasi. | Menerima tugas perbaikan dari admin, memperbarui status pengerjaan saat di lokasi, dan menyusun laporan servis fisik (analisis teknis, suku cadang, rekomendasi). |
| **ADMIN** | Pengelola operasional platform HomeCare Service. | Memvalidasi pembayaran, menugaskan teknisi pada order aktif, mengelola master data (kategori alat, teknisi, pelanggan), memantau omset riil, dan mengonfigurasi API Key AI. |

### 2.3 Lingkungan Operasional (*Operating Environment*)
* **Web Server:** Apache 2.4.x (XAMPP Environment) dengan modul `mod_rewrite` aktif.
* **Bahasa Pemrograman:** PHP 8.1.x atau PHP 8.2.x (Pure Native MVC).
* **Basis Data:** MySQL 8.0 / MariaDB 10.4 dengan InnoDB Engine.
* **Frontend Stack:** HTML5, CSS3, Bootstrap 5.3.2, Bootstrap Icons 1.11, Vanilla JavaScript ES6+.
* **Runtime Testing & DevOps:** PHP CLI, PHPUnit, GitHub Actions CI Runner (Ubuntu-latest).

### 2.4 Batasan Perancangan & Implementasi
1. Sistem menggunakan skema URL ramah mesin pencari (*clean routing*) yang diarahkan dari `.htaccess` ke `public/index.php`.
2. Seluruh akses basis data wajib menggunakan **PDO (PHP Data Objects) Prepared Statements** guna memitigasi celah kerentanan *SQL Injection*.
3. Akses antarmuka pengguna responsif penuh pada resolusi *Desktop* ($\ge 1200px$), *Tablet* ($768px - 1199px$), dan *Mobile* ($\le 576px$).

---

# BAB III: KEBUTUHAN FUNGSIONAL SISTEM (FUNCTIONAL REQUIREMENTS)

### 3.1 Modul Autentikasi & Manajemen Akun Pengguna
* **FR-01: Registrasi Akun Customer**
  * *User Story:* Sebagai pengunjung baru, saya dapat mendaftarkan akun dengan mengisi nama lengkap, email unik, nomor telepon/WhatsApp aktif, alamat tinggal, dan kata sandi agar dapat mengakses layanan pemesanan.
  * *Acceptance Criteria:* Sistem memvalidasi keunikan email dan kekuatan kata sandi ($\ge 6$ karakter), melakukan *hashing* kata sandi menggunakan `password_hash()` bcrypt, serta otomatis membuat entitas relasional pada tabel `users` dan `customers`.
* **FR-02: Autentikasi Login Pengguna (Multi-Role)**
  * *User Story:* Sebagai pengguna terdaftar (Customer, Teknisi, Admin), saya dapat masuk ke dalam sistem menggunakan email dan kata sandi yang valid.
  * *Acceptance Criteria:* Sistem mencocokkan kredensial dengan database, menginisialisasi sesi aman (`$_SESSION`), dan mengarahkan pengguna ke dashboard yang sesuai dengan hak aksesnya.
* **FR-03: Manajemen Profil Akun & Perubahan Kata Sandi**
  * *User Story:* Sebagai pengguna yang telah login, saya dapat memperbarui informasi nama, alamat, nomor telepon, serta memperbarui kata sandi lama ke kata sandi baru.
  * *Acceptance Criteria:* Sistem memverifikasi kecocokan kata sandi lama sebelum mengizinkan perubahan kata sandi baru.
* **FR-04: Sesi dan Logout Aman**
  * *User Story:* Sebagai pengguna yang telah selesai bertransaksi, saya dapat keluar dari akun saya secara aman.
  * *Acceptance Criteria:* Sistem menghapus seluruh data sesi (`session_destroy()`) dan mencegah akses halaman terproteksi menggunakan tombol kembali peramban.

---

### 3.2 Modul AI Smart Diagnosis Kerusakan Peralatan
* **FR-05: Formulir Input Gejala Kerusakan**
  * *User Story:* Sebagai pengguna (Customer atau Guest), saya dapat memilih jenis peralatan (AC, Kulkas, Mesin Cuci, TV, dll.), memasukkan merk opsional, keluhan utama, dan gejala tambahan agar dapat didiagnosis oleh AI.
  * *Acceptance Criteria:* Sistem menyediakan opsi pilihan alat rumah tangga populer serta opsi kustom, dilengkapi tombol *Contoh Cepat (Quick Example)* untuk mempermudah pengisian.
* **FR-06: Pemrosesan Analisis Kerusakan berbasis AI Engine**
  * *User Story:* Sebagai pengguna, saya ingin memperoleh analisis kemungkinan komponen rusak dan tingkat prioritas penanganan secara instan.
  * *Acceptance Criteria:* Sistem mengirimkan prompt terstruktur ke `AIService`, menerima analisis berformat JSON/terstruktur, serta menampilkan tingkat prioritas (*Rendah / Sedang / Tinggi / Mendesak*) dengan lencana warna visual.
* **FR-07: Estimasi Biaya Perbaikan Wajar dengan Disclaimer Resmi**
  * *User Story:* Sebagai pengguna, saya ingin mengetahui perkiraan biaya perbaikan yang transparan sebelum memesan teknisi.
  * *Acceptance Criteria:* Sistem menyajikan rentang estimasi biaya perbaikan (misal: Rp 150.000 - Rp 300.000) dan secara tegas mencantumkan *disclaimer*: *"Perkiraan biaya perbaikan merupakan diagnosis awal AI dan dapat berubah sesuai dengan hasil inspeksi fisik oleh teknisi di lokasi."*
* **FR-08: Konversi Otomatis Diagnosis ke Pemesanan Servis (Direct Call-to-Action)**
  * *User Story:* Sebagai pengguna yang telah memperoleh hasil diagnosis, saya dapat langsung memesan servis teknisi tanpa mengetik ulang keluhan saya.
  * *Acceptance Criteria:* Tombol "Buat Pesanan Servis" secara otomatis mem-passing parameter `equipment_type`, `brand`, dan `complaint` ke formulir pesanan di `/customer/order/create`.

---

### 3.3 Modul HomeCare AI Virtual Assistant / Chatbot
* **FR-09: Interaksi Chatbot Multi-Turn**
  * *User Story:* Sebagai pengguna, saya dapat melakukan percakapan interaktif dua arah dengan asisten AI seputar konsultasi kendala alat rumah tangga, status pesanan, dan informasi sistem.
  * *Acceptance Criteria:* Chatbot merender pesan pengguna seketika, menampilkan indikator mengetik animasi (*typing indicator*), dan menampilkan respons AI dalam format Markdown yang rapi (*bold, list, newline*).
* **FR-10: Integrasi Live Database Context Retrieval**
  * *User Story:* Sebagai pengguna, saya ingin asisten AI mengetahui kondisi nyata sistem HomeCare (misal: daftar teknisi aktif, katalog alat, pesanan yang lunas) tanpa memberikan informasi karangan/halusinasi.
  * *Acceptance Criteria:* `DatabaseContextService` secara dinamis mengekstrak metrik data terverifikasi (jumlah pesanan aktif, total omset pesanan lunas, nama teknisi) dan menginjeksikannya ke dalam sistem prompt AI secara *real-time*.
* **FR-11: Kebijakan Respons To-The-Point vs Rincian Faktual**
  * *User Story:* Sebagai pengguna, jika saya bertanya *"berapa jumlah pesanan?"* atau *"berapa jumlah omset?"*, saya ingin jawaban ringkas to-the-point; namun saat saya meminta *"rincikan omset"*, saya ingin rincian lengkap pesanan yang sudah dibayar.
  * *Acceptance Criteria:* Sistem menerapkan aturan pemrosesan ketat:
    - Pertanyaan agregat $\rightarrow$ Menjawab ringkas jumlah dan total saja tanpa menjabarkan list panjang.
    - Pertanyaan instruksi rincian (*"rincikan", "detailkan", "apa saja"*) $\rightarrow$ Menjabarkan data transaksi faktual (Nomor order, nama alat, nominal pembayaran, status lunas, dan nama pelanggan).
* **FR-12: Rekomendasi Pemesanan Servis Cerdas dalam Percakapan**
  * *User Story:* Jika AI mendeteksi bahwa kerusakan memerlukan penanganan fisik, AI harus dapat merekomendasikan pemesanan teknisi.
  * *Acceptance Criteria:* Sistem menyematkan widget kartu *Call to Action* (CTA) khusus di bawah bubble chat yang memungkinkan pengguna langsung mengklik link pemesanan servis.
* **FR-13: Floating Widget Chatbot Global**
  * *User Story:* Sebagai pengguna yang sedang menjelajah halaman manapun di website, saya dapat membuka obrolan AI melalui tombol melayang (*floating icon button*) di pojok kanan bawah.
  * *Acceptance Criteria:* Widget dapat di-*minimize*, menyimpan riwayat sesi di `sessionStorage`, dan memiliki tampilan responsif tanpa menutupi konten utama halaman.
* **FR-14: Pengendalian Status Loading Bebas Gantung (*No Hang Loading*)**
  * *User Story:* Sebagai pengguna, setelah AI selesai menjawab pertanyaan, saya tidak ingin tombol kirim atau status sistem terus-menerus menampilkan animasi memproses.
  * *Acceptance Criteria:* Script antarmuka mengimplementasikan penanganan siklus `.finally()` yang menjamin tombol Kirim diaktifkan kembali, teks dan ikon dikembalikan ke semula, dan indikator mengetik disembunyikan seketika.

---

### 3.4 Modul Siklus Pemesanan Servis (*Order Lifecycle*)
* **FR-15: Pembuatan Pesanan Servis (Booking Servis)**
  * *User Story:* Sebagai Customer, saya dapat membuat pesanan baru dengan memilih jenis peralatan, menuliskan merk, keluhan, alamat lengkap servis, serta memilih jadwal tanggal dan jam kedatangan teknisi yang diinginkan.
  * *Acceptance Criteria:* Pesanan tersimpan di tabel `service_orders` dengan nomor tiket unik berformat `HC-YYYYMMDD-XXXX` dan status awal `PENDING`.
* **FR-16: Pelacakan Status Progres Pesanan Real-Time**
  * *User Story:* Sebagai Customer, saya dapat memantau status pengerjaan pesanan saya mulai dari *PENDING*, *CONFIRMED*, *ON_THE_WAY*, *IN_PROGRESS*, hingga *COMPLETED*.
  * *Acceptance Criteria:* Antarmuka detail pesanan menampilkan grafik linimasa visual (*progress tracker stepper*) yang mencerminkan status terkini.
* **FR-17: Pembatalan Pesanan Mandiri**
  * *User Story:* Sebagai Customer, saya dapat membatalkan pesanan servis selama status pesanan masih berada dalam tahap `PENDING` dan belum ditugaskan kepada teknisi.
  * *Acceptance Criteria:* Sistem mengubah status pesanan menjadi `CANCELLED` dan menonaktifkan tindakan perubahan lebih lanjut.
* **FR-18: Riwayat Pesanan dan Filter Data**
  * *User Story:* Sebagai Customer, saya dapat melihat seluruh arsip pesanan yang pernah saya buat, baik yang masih aktif maupun yang sudah selesai diperbaiki.
  * *Acceptance Criteria:* Daftar riwayat dilengkapi filter status, pencarian nomor tiket, dan rincian ringkasan biaya.
* **FR-19: Penerbitan Tanda Terima Servis Digital (Invoice / Receipt)**
  * *User Story:* Sebagai Customer, setelah pesanan selesai dan lunas, saya dapat melihat dan mencetak kwitansi pembayaran digital resmi.
  * *Acceptance Criteria:* Sistem menghasilkan tampilan tanda terima terstruktur yang memuat rincian biaya jasa, suku cadang, identitas teknisi, dan stempel digital lunas.

---

### 3.5 Modul Manajemen Pekerjaan & Laporan Teknisi
* **FR-20: Dashboard Penugasan Teknisi**
  * *User Story:* Sebagai Teknisi yang telah login, saya dapat melihat daftar tugas perbaikan servis yang telah ditugaskan oleh admin kepada saya.
  * *Acceptance Criteria:* Halaman menampilkan kartu tugas lengkap dengan nama pelanggan, kontak WhatsApp, alamat penjemputan/servis, keluhan alat, dan jadwal waktu.
* **FR-21: Pembaruan Status Lapangan oleh Teknisi**
  * *User Story:* Sebagai Teknisi, saya dapat mengubah status pekerjaan saat berangkat ke lokasi (`ON_THE_WAY`) dan saat mulai membongkar alat (`IN_PROGRESS`).
  * *Acceptance Criteria:* Setiap pembaruan status langsung tercatat di database dan memicu notifikasi pembaruan kepada pelanggan.
* **FR-22: Pengisian Laporan Servis Fisik (Service Report)**
  * *User Story:* Sebagai Teknisi, setelah perbaikan rampung, saya dapat memasukkan rincian tindakan teknis, suku cadang yang diganti, serta biaya final jasa dan suku cadang.
  * *Acceptance Criteria:* Data tersimpan pada tabel `service_reports` dengan relasi `order_id` dan otomatis memperbarui total tagihan akhir pada `service_orders`.
* **FR-23: Penyelesaian Servis (Mark as Completed)**
  * *User Story:* Sebagai Teknisi, saya dapat menandai pesanan sebagai `COMPLETED` setelah pengujian fungsi alat di hadapan pelanggan berhasil.
  * *Acceptance Criteria:* Status pesanan berubah menjadi selesai dan membuka akses bagi pelanggan untuk memberikan pembayaran dan ulasan rating.

---

### 3.6 Modul Transaksi & Pembayaran
* **FR-24: Pilihan Metode Pembayaran (CASH & TRANSFER)**
  * *User Story:* Sebagai Customer, saya dapat melunasi tagihan perbaikan menggunakan metode Tunai (*Cash on Delivery*) ke teknisi atau Transfer Bank (BCA, Mandiri, BRI, QRIS).
  * *Acceptance Criteria:* Form pembayaran memuat nomor rekening resmi HomeCare Service, total nominal tagihan pasti, dan instruksi transfer.
* **FR-25: Unggah Bukti Transfer Pembayaran**
  * *User Story:* Jika memilih metode transfer, Customer dapat mengunggah file foto atau tangkapan layar bukti transfer bank.
  * *Acceptance Criteria:* Sistem memvalidasi format berkas (JPG, PNG, PDF, ukuran $\le 2MB$), menyimpannya ke folder `/public/uploads/payments/`, dan mencatat status pembayaran sebagai `PENDING_VERIFICATION`.
* **FR-26: Verifikasi Pembayaran oleh Admin**
  * *User Story:* Sebagai Admin, saya dapat memeriksa bukti transfer yang diunggah pelanggan dan menyetujui (*Approve*) atau menolak (*Reject*) pembayaran tersebut.
  * *Acceptance Criteria:* Jika disetujui, status pembayaran berubah menjadi `PAID` (*LUNAS*), status pesanan menjadi `PAID/COMPLETED`, dan omset resmi diakui dalam laporan keuangan sistem.
* **FR-27: Pembayaran Tunai Terkonfirmasi Teknisi**
  * *User Story:* Jika pelanggan membayar tunai, teknisi atau admin dapat mengonfirmasi penerimaan uang tunai secara langsung di sistem.
  * *Acceptance Criteria:* Sistem langsung mencatat transaksi sebagai lunas tanpa memerlukan unggah slip mutasi perbankan.

---

### 3.7 Modul Ulasan & Rating Layanan
* **FR-28: Pengiriman Ulasan dan Rating oleh Customer**
  * *User Story:* Sebagai Customer yang pesanannya telah selesai, saya dapat memberikan rating bintang (skala 1 s/d 5) dan ulasan testimoni atas kinerja teknisi dan layanan aplikasi.
  * *Acceptance Criteria:* Data tersimpan di tabel `reviews`, dan setiap pesanan hanya dapat diulas sebanyak satu kali (*anti-spam*).
* **FR-29: Rekapitulasi Rata-Rata Rating Teknisi**
  * *User Story:* Sebagai Admin dan Calon Pelanggan, saya ingin melihat akumulasi reputasi dan skor rata-rata kepuasan dari masing-masing teknisi.
  * *Acceptance Criteria:* Sistem mengkalkulasi fungsi agregat `AVG(rating)` secara dinamis untuk ditampilkan pada profil publik teknisi.
* **FR-30: Moderasi Ulasan oleh Administrator**
  * *User Story:* Sebagai Admin, saya dapat memantau seluruh testimoni masuk dan menonaktifkan ulasan yang mengandung unsur kata kasar (*offensive language*).
  * *Acceptance Criteria:* Admin memiliki tombol hapus atau sembunyikan ulasan yang melanggar ketentuan komunitas.

---

### 3.8 Modul Notifikasi Terpadu & WhatsApp Gateway
* **FR-31: Pusat Notifikasi In-App (Real-Time Badge)**
  * *User Story:* Sebagai pengguna yang login, saya dapat melihat lonceng notifikasi yang menampilkan lencana angka merah saat terdapat kabar baru seputar pesanan saya.
  * *Acceptance Criteria:* Ikon bel menampilkan daftar notifikasi terkini, menandai pesan sebagai terbaca saat diklik, dan menyediakan tombol "Tandai Semua Sudah Dibaca".
* **FR-32: Polling Notifikasi Otomatis**
  * *User Story:* Tanpa me-refresh halaman, pengguna dapat menerima info status pesanan terbaru secara asinkronus.
  * *Acceptance Criteria:* Script antarmuka melakukan polling ringan berkala (`/notifications/fetch`) ke server secara hemat bandwidth.
* **FR-33: Notifikasi WhatsApp Gateway (Fonnte API Integration)**
  * *User Story:* Sebagai Customer dan Teknisi, saya menerima pesan WhatsApp otomatis saat pesanan dibuat, saat teknisi ditugaskan, dan saat status perbaikan diperbarui.
  * *Acceptance Criteria:* `FonnteService` mengirimkan request HTTP POST ke endpoint Fonnte API dengan pesan personalisasi formal yang memuat nomor order dan rincian status.

---

### 3.9 Modul Manajemen Master Data Administrator
* **FR-34: Manajemen Data Pelanggan (Customer Management)**
  * *User Story:* Sebagai Admin, saya dapat melihat daftar lengkap pelanggan, mengedit profil pelanggan, menambah pelanggan manual, atau menonaktifkan akun yang bermasalah.
  * *Acceptance Criteria:* CRUD lengkap pada entitas pelanggan dengan validasi integritas data relasional.
* **FR-35: Manajemen Data Teknisi (Technician Management)**
  * *User Story:* Sebagai Admin, saya dapat mengelola master data teknisi (nama, spesialisasi keahlian alat, nomor telepon, status ketersediaan aktif/sibuk).
  * *Acceptance Criteria:* Sistem menampilkan ketersediaan teknisi sebelum admin melakukan *assign* pekerjaan servis.
* **FR-36: Manajemen Katalog Peralatan Rumah Tangga (Equipment Catalog)**
  * *User Story:* Sebagai Admin, saya dapat menambah kategori peralatan baru, mengubah rentang estimasi tarif servis dasar, dan mengunggah ikon alat.
  * *Acceptance Criteria:* Data katalog tersinkronisasi otomatis dengan pilihan formulir diagnosis AI dan katalog pada chatbot asisten.
* **FR-37: Laporan Rekapitulasi Statistik & Omset Bisnis**
  * *User Story:* Sebagai Admin, saya dapat melihat ringkasan omset total, jumlah pesanan lunas, pesanan aktif, rata-rata rating, dan grafik performa bulanan.
  * *Acceptance Criteria:* Dashboard menyajikan angka kalkulasi riil dari seluruh pesanan yang berstatus `PAID` secara akurat tanpa pembulatan fiktif.

---

### 3.10 Modul Konfigurasi Engine AI Multi-Provider
* **FR-38: Pengaturan API Key Dinamis via Antarmuka Admin**
  * *User Story:* Sebagai Admin, saya dapat memperbarui API Key AI secara langsung dari antarmuka tanpa perlu menyunting berkas kode atau berkas `.env` server.
  * *Acceptance Criteria:* Konfigurasi tersimpan secara aman di tabel `settings` dalam basis data dan langsung dimuat oleh sistem secara dinamis.
* **FR-39: Pemilihan Provider & Model LLM Aktif**
  * *User Story:* Sebagai Admin, saya dapat memilih provider AI yang digunakan sistem: **Google Gemini** (1.5 Flash / 1.5 Pro), **NVIDIA NIM** (Llama 3.2 Vision), **Groq LPU** (Llama 3.3 70B), atau **OpenAI** (GPT-4o-mini).
  * *Acceptance Criteria:* Perubahan model langsung berlaku pada seluruh panggilan diagnosis dan obrolan asisten tanpa *restart* web server.
* **FR-40: Pengujian Koneksi API Real-Time (Test Connection Button)**
  * *User Story:* Sebagai Admin, saya dapat menguji keabsahan API Key yang baru saya masukkan dengan satu kali klik sebelum digunakan oleh pengguna umum.
  * *Acceptance Criteria:* Tombol "Tes Koneksi AI" mengirimkan prompt ping ringan dan menampilkan status visual: *Sukses (Hijau)* beserta latensi milidetik atau *Gagal (Merah)* disertai pesan diagnosis kesalahan teknis.

---

# BAB IV: KEBUTUHAN NON-FUNGSIONAL (ISO 25010)

Spesifikasi kebutuhan non-fungsional disusun mengacu pada standar kualitas perangkat lunak internasional **ISO/IEC 25010**:

| Parameter Kualitas | Spesifikasi Kebutuhan Terukur & Kriteria Penerimaan |
|---|---|
| **4.1 Efisiensi Kinerja (*Performance Efficiency*)** | 1. Waktu muat halaman statis rata-rata $\le 1.5$ detik pada koneksi internet standar (4G/WiFi).<br/>2. Waktu respons panggilan database query agregat $\le 150$ ms menggunakan pengindeksan (*indexing*) pada kolom relasi kunci.<br/>3. Waktu latensi inferensi AI Engine $\le 3.5$ detik dengan batas batas waktu (*timeout limit*) maksimal 15 detik sebelum fallback diaktifkan. |
| **4.2 Keamanan Sistem (*Security*)** | 1. **Password Hashing:** Enkripsi kata sandi menggunakan algoritma *Bcrypt* dengan cost factor default 10.<br/>2. **Mitigasi SQL Injection:** 100% interaksi query basis data wajib menggunakan PDO Prepared Statements berparameter.<br/>3. **Mitigasi XSS:** Seluruh data dinamis yang ditampilkan ke antarmuka wajib melalui sanitasi `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')`.<br/>4. **Proteksi Akses Role:** Middleware `Router.php` memblokir akses rute berdasarkan hak role pengguna (mencegah *Broken Access Control*). |
| **4.3 Keandalan & Ketersediaan (*Reliability*)** | 1. Sistem memiliki tingkat ketersediaan (*availability uptime*) minimal 99.0% pada lingkungan produksi.<br/>2. Jika API AI eksternal mengalami kendala jaringan atau kuota habis, sistem secara anggun (*gracefully*) mengaktifkan *Fallback Rule-Based Diagnostic Engine* sehingga pengguna tetap memperoleh estimasi awal tanpa tampilan error fatal. |
| **4.4 Kemudahan Penggunaan (*Usability*)** | 1. Tata letak antarmuka mengadopsi prinsip desain responsif Bootstrap 5, ramah diakses melalui layar sentuh *smartphone* maupun tetikus desktop.<br/>2. Form konsultasi kerusakan dilengkapi tombol *Quick Example* untuk mempercepat input pengguna awam.<br/>3. Bebas dari kendala gantung tombol: Indikator loading AI selalu di-*reset* kembali ke status aktif segera setelah data terkirim. |
| **4.5 Kemudahan Pemeliharaan (*Maintainability*)** | 1. Kode sumber mematuhi arsitektur Model-View-Controller murni dengan pemisahan direktori yang terstruktur rapi (`app/`, `public/`, `routes/`).<br/>2. Kepatuhan pada prinsip SOLID, terutama *Single Responsibility Principle* pada layer Service (`AIService`, `DatabaseContextService`, `FonnteService`).<br/>3. Dilengkapi skrip pengujian otomatis (*automated unit tests*) untuk menguji logika bisnis krusial. |
| **4.6 Portabilitas (*Portability*)** | 1. Aplikasi dapat dijalankan pada lingkungan server Linux (Ubuntu/Debian) maupun Windows (XAMPP/WAMP) dengan versi PHP minimal 8.1.<br/>2. Skrip routing kompatibel dengan web server Apache via file konfigurasi `.htaccess`. |

---

# BAB V: SPESIFIKASI MODUL KECERDASAN ARTIFISIAL (AI ENGINE SPECIFICATION)

### 5.1 Fitur 1: AI Smart Diagnosis Kerusakan Peralatan
Modul ini bertindak sebagai asisten insinyur servis virtual yang menganalisis keluhan fisik peralatan rumah tangga sebelum ditangani teknisi.

* **Alur Masukan Parameter:**
  ```json
  {
    "equipment_type": "AC (Air Conditioner)",
    "brand": "Daikin",
    "complaint": "AC menyala tetapi tidak dingin sama sekali",
    "symptoms": "Hembusan angin terasa hangat dan unit outdoor tidak berputar"
  }
  ```
* **Sistem Prompt Engineering (Structured Extraction):**
  Prompt dirancang dengan persona ahli teknisi servis berpengalaman 15 tahun. Output diwajibkan menyajikan 4 komponen kunci:
  1. *Analisis & Kemungkinan Penyebab:* Identifikasi kegagalan komponen (misal: kapasitor kompresor mati, kebocoran freon R32, atau kendala kelistrikan).
  2. *Tingkat Prioritas:* Klasifikasi tingkat keparahan (*Rendah, Sedang, Tinggi, Mendesak*).
  3. *Estimasi Biaya Wajar:* Menghitung kisaran biaya servis standar pasar Indonesia (Jasa + Suku Cadang).
  4. *Klausul Disclaimer Resmi:* Penegasan bahwa estimasi adalah indikasi awal dan hasil final ditentukan pemeriksaan fisik teknisi di tempat.
* **Integrasi Direct Call to Action (CTA):**
  Hasil diagnosis secara otomatis menyematkan tombol:
  `<a href="/customer/order/create?equipment=AC&complaint=AC+tidak+dingin">Buat Pesanan Servis Sekarang</a>`.

---

### 5.2 Fitur 2: HomeCare AI Virtual Assistant & Live Database Context Retrieval
Chatbot HomeCare Service mengimplementasikan arsitektur *Retrieval-Augmented Context* langsung dari basis data operasional MySQL melalui `DatabaseContextService`.

* **Mekanisme Ekstraksi Konteks Database Terverifikasi:**
  Setiap kali percakapan diproses, sistem mengekstrak data faktual terkini:
  - **Status Pesanan Lunas & Riil Omset:** Mengambil `SUM(total_amount)` dan menghitung pesanan dengan status `COMPLETED` atau pembayaran `PAID`.
  - **Daftar & Ketersediaan Teknisi:** Mengambil nama teknisi dan spesialisasi keahlian alat.
  - **Katalog Alat & Biaya Dasar:** Mengambil daftar peralatan terdaftar beserta tarif servis dasar.
  - **Data Spesifik Customer:** Jika customer sedang login, AI dapat membaca nama dan status pesanan aktif miliknya sendiri.

```
[User Chat Input] 
        |
        v
[DatabaseContextService] ---> Query MySQL Live Data:
        |                     - Pesanan Lunas & Total Omset Riil
        |                     - Master Teknisi, Katalog Alat, Ulasan
        v
[Context Injection System Prompt]
        |
        v
[AIService (LLM API: Gemini / NVIDIA / Groq)]
        |
        v
[Structured Factual Response] ---> Output ke Bubble Chat (Auto Reset Loading)
```

### 5.3 Kebijakan Respons AI (*To-the-Point vs Detailed Breakdown Policy*)
Berdasarkan aturan bisnis HomeCare Service, asisten AI mematuhi dua aturan respons perilaku:
1. **Aturan Agregat (To-The-Point):**  
   Ketika pengguna mengajukan pertanyaan jumlah/agregat (contoh: *"Berapa jumlah pesanan?"*, *"Berapa jumlah teknisi?"*, *"Berapa jumlah omset?"*), AI **WAJIB** menjawab singkat dan langsung pada angka faktual tanpa menjabarkan seluruh pesanan satu per satu.  
   *Contoh Respons:* `"Total omset dari semua pesanan yang sudah dibayar saat ini adalah sebesar Rp 1.450.000 dari 6 pesanan yang sudah lunas."`
2. **Aturan Rincian Faktual (Detailed Breakdown):**  
   Hanya ketika pengguna secara eksplisit meminta rincian (contoh: *"Rincikan omset"*, *"Detailkan omsetnya"*, *"Apa saja transaksi yang lunas?"*), AI akan menjabarkan seluruh daftar pesanan yang telah dibayar lunas lengkap dengan nomor order, jenis peralatan, nominal transaksi, metode pembayaran, dan nama pelanggan.

### 5.4 Multi-Provider AI Architecture & Mekanisme Fail-Safe
Sistem tidak bergantung pada satu vendor penyedia AI tunggal. Pada layer `AIService`, sistem mendukung adapter multi-provider:
* **Google Gemini API (Default Rekomendasi):** Model `gemini-1.5-flash` dan `gemini-1.5-pro` (kecepatan inferensi tinggi dan kuota besar).
* **NVIDIA NIM API:** Model `meta/llama-3.2-11b-vision-instruct` untuk komputasi terdistribusi tinggi.
* **Groq LPU:** Model `llama-3.3-70b-versatile` dengan kecepatan token ultra-cepat.
* **OpenAI API:** Model `gpt-4o-mini`.

*Mekanisme Fail-Safe:* Apabila API eksternal mengalami kendala koneksi atau batas kuota terlampaui, sistem secara otomatis mengembalikan pesan informatif terstruktur atau mengaktifkan mesin diagnosis berbasis aturan lokal tanpa membiarkan peramban pengguna terjebak dalam status loading berulang.

---

# BAB VI: SKEMA BASIS DATA RELASIONAL (DATABASE SCHEMA)

Basis data **`homecare_db`** terdiri dari 11 tabel relasional utama yang saling terhubung dengan integritas *foreign key*:

```
+--------------------+        +--------------------+
|       users        |        |     equipment      |
+--------------------+        +--------------------+
| id (PK)            |<---+   | id (PK)            |
| username           |    |   | name               |
| email              |    |   | category           |
| password           |    |   | base_price         |
| role (ENUM)        |    |   | description        |
+--------------------+    |   +---------+----------+
        |                 |             |
        v                 |             v
+--------------------+    |   +--------------------+
|     customers      |    |   |   service_orders   |
+--------------------+    |   +--------------------+
| id (PK)            |    |   | id (PK)            |
| user_id (FK)-------+    |   | order_number       |
| full_name          |    +---| customer_id (FK)   |
| phone              |    +---| technician_id (FK) |
| address            |    +---| equipment_id (FK)  |
+---------+----------+        | complaint          |
          |                   | status (ENUM)      |
          |                   | total_amount       |
          |                   +----+----+-----+----+
          |                        |    |     |
          v                        v    |     v
+--------------------+  +----------+-+  |  +--------------------+
|     reviews        |  |  payments  |  |  |  service_reports   |
+--------------------+  +------------+  |  +--------------------+
| id (PK)            |  | id (PK)    |  |  | id (PK)            |
| order_id (FK)      |  | order_id   |  |  | order_id (FK)      |
| customer_id (FK)   |  | amount     |  |  | action_taken       |
| technician_id (FK) |  | method     |  |  | parts_replaced     |
| rating (INT)       |  | proof_file |  |  | total_cost         |
| comment            |  | status     |  |  +--------------------+
+--------------------+  +------------+  |
                                        v
                               +--------------------+
                               |   notifications    |
                               +--------------------+
                               | id (PK)            |
                               | user_id (FK)       |
                               | title / message    |
                               | is_read            |
                               +--------------------+
```

### Kamus Entitas Tabel:
1. **`users`:** Menyimpan akun autentikasi inti (`id`, `username`, `email`, `password`, `role`, `created_at`).
2. **`customers`:** Menyimpan profil biodata pelanggan (`id`, `user_id`, `full_name`, `phone`, `address`).
3. **`technicians`:** Menyimpan profil dan spesialisasi teknisi (`id`, `user_id`, `full_name`, `specialization`, `phone`, `is_available`).
4. **`equipment`:** Menyimpan master data kategori alat elektronik rumah tangga dan tarif dasar perbaikan.
5. **`service_orders`:** Entitas transaksi inti pemesanan servis (`id`, `order_number`, `customer_id`, `technician_id`, `equipment_id`, `complaint`, `service_date`, `status`, `total_amount`).
6. **`service_reports`:** Laporan teknis hasil pengerjaan fisik oleh teknisi di lokasi.
7. **`payments`:** Rekaman transaksi pembayaran (`id`, `order_id`, `amount`, `payment_method`, `payment_proof`, `status`, `paid_at`).
8. **`reviews`:** Testimoni kepuasan pelanggan dan rating bintang (1-5).
9. **`notifications`:** Notifikasi in-app untuk pembaruan status pesanan.
10. **`chat_sessions`:** Sesi obrolan konsultasi asisten AI per pengguna.
11. **`chat_messages`:** Log histori percakapan asinkronus antara pengguna dan AI.

---

# BAB VII: MATRIKS PENGUJIAN & KRITERIA KEBERHASILAN (ACCEPTANCE CRITERIA)

Pengujian sistem dilaksanakan untuk memverifikasi kesesuaian antara implementasi kode dengan seluruh kebutuhan yang telah dirumuskan pada Bab III:

| ID Pengujian | Skenario Uji | Tindakan Pengujian (*Test Steps*) | Hasil yang Diharapkan (*Expected Result*) | Status Validasi |
|---|---|---|---|---|
| **TC-AUTH-01** | Login Kredensial Valid | Input email dan password akun terdaftar. | Pengguna diarahkan ke dashboard sesuai role masing-masing. | **PASS** |
| **TC-AUTH-02** | Proteksi Hak Akses Role | Customer mencoba mengakses URL `/admin/orders`. | Akses ditolak oleh Middleware, dialihkan kembali ke `/login`. | **PASS** |
| **TC-AI-01** | AI Smart Diagnosis | Memasukkan keluhan AC Daikin bocor dan tidak dingin. | AI menampilkan analisis kerusakan, estimasi biaya, dan disclaimer resmi. | **PASS** |
| **TC-AI-02** | Konversi Diagnosis ke Order | Klik tombol "Buat Pesanan Servis" pada hasil AI. | Formulir pesanan terbuka dengan parameter alat dan keluhan terisi otomatis. | **PASS** |
| **TC-AI-03** | Chatbot Live Agregat | Kirim pertanyaan *"berapa jumlah omset?"*. | AI menjawab to-the-point: Rp 1.450.000 dari 6 pesanan lunas. | **PASS** |
| **TC-AI-04** | Chatbot Rincian Faktual | Kirim perintah *"rincikan omset"*. | AI menjabarkan daftar 6 transaksi lunas lengkap dengan nominal dan customer. | **PASS** |
| **TC-AI-05** | Reset Loading Chatbot | Mengirim pesan apa saja ke AI Chatbot. | Begitu respons AI tiba, status memproses hilang dan tombol Kirim aktif normal. | **PASS** |
| **TC-ORD-01** | Siklus Booking Servis | Customer mengisi form pesanan dan jadwal kunjungan. | Pesanan tercatat dengan nomor `HC-...` dan status awal `PENDING`. | **PASS** |
| **TC-ORD-02** | Penugasan Teknisi | Admin memilih teknisi yang tersedia untuk pesanan. | Status pesanan menjadi `CONFIRMED`, tugas muncul di dashboard teknisi. | **PASS** |
| **TC-ORD-03** | Laporan Servis Teknisi | Teknisi mengisi tindakan perbaikan dan sparepart. | Laporan tersimpan di `service_reports`, total biaya pesanan ter-update. | **PASS** |
| **TC-PAY-01** | Unggah Bukti Transfer | Customer mengunggah foto bukti transfer bank. | File tersimpan di server, status pembayaran menjadi `PENDING_VERIFICATION`. | **PASS** |
| **TC-PAY-02** | Validasi Pembayaran Admin | Admin menekan tombol Approve pada bukti bayar. | Status pembayaran menjadi `PAID`, status pesanan lunas, omset bertambah. | **PASS** |
| **TC-REV-01** | Pengiriman Ulasan Rating | Customer memberi bintang 5 dan testimoni ulasan. | Ulasan tersimpan, rata-rata rating teknisi bertambah. | **PASS** |

---

# BAB VIII: ROADMAP PENGEMBANGAN & TENGGAT WAKTU

Pengembangan proyek mandiri HomeCare Service dilaksanakan berurutan mengikuti siklus **Software Development Life Cycle (SDLC)** terhitung dari Minggu ke-1 hingga Minggu ke-16:

```
[Minggu 1 - 4]   : Tahap 1 - Inisiasi, Analisis Masalah, & Penyusunan Dokumen PRD/SRS (IEEE 830).
[Minggu 5 - 8]   : Tahap 2 - Perancangan Arsitektur Berorientasi Objek (UML SDD) & UI/UX Figma.
[Minggu 9 - 13]  : Tahap 3 - Konstruksi Kode Bersih (MVC), Integrasi Multi-Provider AI, & DB Context Retrieval.
[Minggu 14 - 15] : Pengujian Otomatis (Unit Testing), Konfigurasi CI/CD GitHub Actions, & Perbaikan Bug.
[Minggu 16]      : Tahap 4 - Finalisasi Laporan PDF, Rekaman Video Live Demo Utuh, & UAS Submission.
```

---

### LEMBAR PENGESAHAN DOKUMEN PRD

Dokumen *Product Requirements Document* (PRD) ini telah diperiksa, diverifikasi, dan disetujui sebagai acuan resmi pelaksanaan Proyek Akhir Mandiri Mata Kuliah Rekayasa Perangkat Lunak (TIKB2503021) Tahun Akademik 2026/2027.

Cirebon, 10 November 2026

**Mengetahui & Menyetujui,**  
Dosen Pengampu Mata Kuliah RPL,  


*(Tanda Tangan & Cap Digital)*  


**Yudhistira Arie Wijaya, M.Kom., CDS.**  
NIDN: ............................................

**Disusun oleh Mahasiswa,**  
Full-Stack Software Engineer Mandiri,  


*(Tanda Tangan)*  


**Qonita Goniyun Ulum**  
NIM: ............................................

