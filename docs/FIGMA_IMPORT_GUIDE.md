# 🎨 Panduan Lengkap Import Desain Website ke Figma (HomeCare Service)

Dokumen ini memandu Anda untuk **langsung mengimpor seluruh desain website HomeCare Service ke dalam Figma** sebagai **vektor layer yang dapat diedit sepenuhnya (fully editable vector frames, text, colors, & components)**.

---

## 📁 Lokasi Berkas Vektor Siap Impor (`.svg`)

Seluruh berkas desain vektor siap pakai telah diekspor dan tersimpan di direktori:
`c:\xampp\htdocs\jasa peralatan runah tangga\docs\figma_import\`

| No | Nama Berkas | Resolusi / Ukuran | Cakupan Layar Antarmuka |
|:--:|---|:---:|---|
| **★** | **`HomeCare_Service_Master_All_Frames.svg`** | **3060 × 2100 px** | **MASTER ALL-IN-ONE:** Memuat 4 artboard utama sekaligus dalam 1 canvas raksasa. |
| 1 | `01_Landing_Page_Katalog.svg` | 1440 × 960 px | Hero Section, Banner Solusi AI, dan Grid Katalog Tarif Dasar 4 Peralatan. |
| 2 | `02_AI_Smart_Diagnosis.svg` | 1440 × 960 px | Form Gejala Kerusakan & Hasil 5 Kartu Diagnosa AI (Analisis, Penyebab, Panduan, Rekomendasi, Estimasi Biaya). |
| 3 | `03_HomeCare_AI_Assistant_Chatbot.svg` | 1440 × 960 px | Antarmuka AI Chatbot, Sidebar Riwayat Chat, Balon Percakapan Live DB, dan Quick Prompt Chips. |
| 4 | `04_Order_Tracking_Timeline_EReceipt.svg` | 1440 × 960 px | Pelacakan Linimasa 6 Tahapan Status Servis, Detail Teknisi, dan Kuitansi Elektronik (*E-Receipt*) Berstempel LUNAS. |
| 5 | `05_Admin_Analytics_Dispatcher.svg` | 1440 × 960 px | 4 Kartu Metrik Finansial Omset Bisnis dan Tabel Penugasan Teknisi (*Order Dispatcher Modal*). |

---

## 🚀 3 Cara Cepat Mengimpor ke Figma:

### Cara 1: Drag-and-Drop Langsung (Paling Cepat & Direkomendasikan) ⚡
1. Buka aplikasi **Figma** (Desktop App atau di web browser [figma.com](https://figma.com)).
2. Buat file baru (**New Design File**).
3. Buka folder Windows Explorer ke:
   ```
   c:\xampp\htdocs\jasa peralatan runah tangga\docs\figma_import\
   ```
4. **Tarik (Drag)** file `HomeCare_Service_Master_All_Frames.svg` (atau file modul yang diinginkan) lalu **Lepaskan (Drop)** langsung ke tengah canvas Figma Anda.
5. **Selesai!** Figma akan otomatis mengonversi seluruh elemen menjadi:
   - Frame halaman berukuran 1440 × 960 px.
   - Kotak teks yang dapat diedit langsung (*Editable Text Layers*).
   - Bentuk vektor persegi bersudut tumpul (*Rounded Rectangles with border-radius*).
   - Grup layer yang rapi dengan nama layer semantik (`#Hero_Section`, `#Card_1`, `#Chat_Main_Area`, dll.).

---

### Cara 2: Melalui Menu Figma (File -> Place Image) 📂
1. Di dalam Figma, klik menu logo Figma (kiri atas) $\rightarrow$ Pilih **File** $\rightarrow$ **Place image...** (atau tekan tombol pintas `Ctrl + Shift + K`).
2. Masuk ke folder `docs/figma_import/`.
3. Pilih salah satu atau seluruh berkas `.svg`.
4. Klik pada area kosong canvas untuk meletakkan frame desain.

---

### Cara 3: Menggunakan Plugin "HTML to Design" di Figma 🌐
Jika Anda ingin mengimpor halaman website lokal secara otomatis menggunakan plugin Figma:
1. Buka file [`docs/figma_import/import_guide_and_preview.html`](file:///c:/xampp/htdocs/jasa%20peralatan%20runah%20tangga/docs/figma_import/import_guide_and_preview.html) di browser Anda.
2. Di Figma, buka menu **Plugins** $\rightarrow$ cari **HTML to Design** (by divRIOTS) atau **Builder.io**.
3. Masukkan URL atau drag file HTML ke dalam plugin untuk me-render struktur CSS & Auto-Layout secara otomatis.

---

## 🛠️ Apa Saja yang Dapat Anda Modifikasi di Figma Setelah Diimpor?

1. **Ubah Teks & Nilai:** Anda dapat mengklik dua kali (*double click*) pada teks mana pun untuk mengubah nama alat, nominal biaya servis, data pelanggan, atau pesan chat AI.
2. **Kustomisasi Warna:** Seluruh warna menggunakan token resmi website (Primary Blue `#1565C0`, Accent Amber `#F59E0B`, Success Green `#10B981`) yang dapat langsung dijadikan *Color Styles* di panel kanan Figma.
3. **Ubah Menjadi Figma Components:** Klik kanan pada tombol atau kartu $\rightarrow$ pilih **Create Component** (`Ctrl + Alt + K`) untuk menjadikannya komponen atomik yang dapat digunakan berulang kali.
4. **Hubungkan Menjadi Prototipe Interaktif:** Pindah ke tab **Prototype** di sisi kanan Figma untuk menambahkan interaksi tombol (contoh: dari tombol *Mulai Diagnosis AI* ditarik panah interaksi ke frame *AI Smart Diagnosis* dengan transisi *Smart Animate*).

---

*© 2026 HomeCare Service — Qonita Goniyun Ulum (Proyek Akhir Rekayasa Perangkat Lunak)*
