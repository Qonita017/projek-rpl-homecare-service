# 🎨 Panduan Lengkap Import Desain Website ke Figma (HomeCare Service)

Dokumen ini memandu Anda untuk **mengimpor seluruh desain website HomeCare Service ke dalam Figma 100% tanpa error**, lengkap dengan layer vektor, teks yang dapat diedit (*editable text*), warna, dan komponen.

---

## 💡 Mengapa Sebelumnya Gagal Saat Diupload ke Figma?
Ketika Anda berada di **Halaman Utama (Dashboard Figma / `figma.com/files`)** dan menekan tombol **"Import"**:
- Sistem Figma **hanya menerima berkas berformat `.sketch` dan `.fig`**.
- Mengunggah berkas gambar/SVG langsung ke tombol Import Dashboard akan memunculkan pesan error: *"File format not supported. Only .fig and .sketch files can be imported here."*

**Solusi:** Kami telah membuatkan berkas **`.sketch` resmi (`HomeCare_Service_Figma.sketch`)** yang diterima langsung oleh tombol Import Figma, serta menyediakan **Alat Salin 1-Klik (`salin_ke_figma.html`)** untuk kemudahan instan!

---

## 📁 Berkas Siap Pakai di Folder `docs/figma_import/`

Seluruh berkas berada di folder:
`c:\xampp\htdocs\jasa peralatan runah tangga\docs\figma_import\`

| Berkas | Format | Kegunaan & Cara Pakai |
|---|:---:|---|
| **`HomeCare_Service_Figma.sketch`** | `.sketch` | **Khusus Tombol "Import" di Dashboard Figma**. Langsung diterima 100% dan dikonversi menjadi project Figma native. |
| **`salin_ke_figma.html`** | `.html` | **Alat Salin 1-Klik**: Buka di browser, klik tombol "Salin", lalu tekan `Ctrl + V` di kanvas Figma. |
| **`HomeCare_Service_Master_All_Frames.svg`** | `.svg` | Master All-in-One 4 Artboard (Landing, AI Diagnosis, Chatbot, Admin) untuk di-drag ke kanvas yang sudah terbuka. |
| `01_Landing_Page_Katalog.svg` | `.svg` | Artboard 1: Landing Page & Katalog Peralatan |
| `02_AI_Smart_Diagnosis.svg` | `.svg` | Artboard 2: AI Smart Diagnosis Kerusakan (5 Kartu Hasil) |
| `03_HomeCare_AI_Assistant_Chatbot.svg` | `.svg` | Artboard 3: AI Chatbot Assistant & Live DB |
| `04_Order_Tracking_Timeline_EReceipt.svg` | `.svg` | Artboard 4: Tracking Order & Kuitansi Elektronik |
| `05_Admin_Analytics_Dispatcher.svg` | `.svg` | Artboard 5: Admin Analytics & Order Dispatcher |

---

## 🚀 3 Pilihan Cara Mengimpor ke Figma:

### Pilihan 1: Import Berkas `.sketch` di Dashboard Figma (Resmi & Paling Rapi) ⭐⭐⭐
1. Buka [Figma](https://www.figma.com/files) di browser atau aplikasi Figma Desktop.
2. Di pojok kanan atas Dashboard / Beranda, klik tombol **"Import file"** (atau ikon panah ke bawah dekat tombol *+ Design file*).
3. Pilih berkas:
   ```
   c:\xampp\htdocs\jasa peralatan runah tangga\docs\figma_import\HomeCare_Service_Figma.sketch
   ```
4. Tunggu beberapa detik hingga proses konversi selesai.
5. Klik **Done** / Buka file yang baru terbentuk.
6. **Selesai!** Seluruh 4 Artboard otomatis tersusun rapi di kanvas dengan layer vektor terstruktur.

---

### Pilihan 2: Gunakan Alat Salin 1-Klik (`salin_ke_figma.html`) ⚡ Paling Cepat
1. Buka berkas berikut dengan klik dua kali (otomatis terbuka di Google Chrome / Browser Anda):
   ```
   c:\xampp\htdocs\jasa peralatan runah tangga\docs\figma_import\salin_ke_figma.html
   ```
2. Di halaman web tersebut, klik tombol hijau:
   👉 **"📋 Salin Master Desain (Semua Halaman)"** (atau pilih per halaman yang diinginkan).
3. Buka tab atau aplikasi **Figma**, buka canvas file desain Anda.
4. Tekan pintasan keyboard:
   👉 **`Ctrl + V`** (atau `Cmd + V` di Mac).
5. **Selesai!** Seluruh frame dan vektor langsung menempel (*paste*) di kanvas Figma Anda secara instan.

---

### Pilihan 3: Drag-and-Drop ke Dalam Canvas yang Sedang Dibuka 🎯
1. Buat file baru di Figma (**New Design File**).
2. Buka folder `c:\xampp\htdocs\jasa peralatan runah tangga\docs\figma_import\` di Windows File Explorer.
3. Tarik (*Drag*) berkas `HomeCare_Service_Master_All_Frames.svg` (atau file `.sketch`) langsung ke tengah lembar kerja / canvas Figma.
4. Lepaskan (*Drop*). Elemen akan otomatis ter-render menjadi frame dan grup layer lengkap.

---

## 🛠️ Fitur & Kelebihan Desain yang Diimpor
- **Fully Editable Text:** Nama modul, estimasi tarif Rp, pesan chat, dan status teknisi dapat diubah dengan *double-click*.
- **Desain Presisi Sesuai Website Asli:** Menggunakan token warna resmi (Primary `#1565C0`, Accent `#F59E0B`, Success `#10B981`, Slate Background `#F8FAFC`).
- **Komponen Interaktif:** Dapat langsung disambungkan antar-frame pada tab **Prototype** Figma untuk simulasi aplikasi berjalan (*Smart Animate*).

---
*© 2026 HomeCare Service — Qonita Goniyun Ulum (Proyek Akhir Rekayasa Perangkat Lunak)*
