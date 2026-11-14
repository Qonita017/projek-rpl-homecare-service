import os
import subprocess

html_content = """<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background-color: #F8FAFC;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 2750px;
    height: 1950px;
  }
  svg {
    width: 2750px;
    height: 1950px;
  }
</style>
</head>
<body>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2750 1950" width="2750" height="1950">
  <defs>
    <!-- Arrowhead markers -->
    <marker id="arrow-include" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
      <path d="M 0 1.5 L 9 5 L 0 8.5" fill="none" stroke="#2563EB" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </marker>
    <marker id="arrow-extend" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
      <path d="M 0 1.5 L 9 5 L 0 8.5" fill="none" stroke="#D97706" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </marker>
    <marker id="arrow-ai" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
      <path d="M 0 1.5 L 9 5 L 0 8.5" fill="none" stroke="#D97706" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </marker>

    <!-- Filters for drop shadows -->
    <filter id="shadow-box" x="-2%" y="-1%" width="104%" height="103%" filterUnits="userSpaceOnUse">
      <feDropShadow dx="0" dy="4" stdDeviation="8" flood-color="#0F172A" flood-opacity="0.06"/>
    </filter>
    <filter id="shadow-oval" x="-5%" y="-10%" width="110%" height="125%" filterUnits="userSpaceOnUse">
      <feDropShadow dx="0" dy="2" stdDeviation="4" flood-color="#0F172A" flood-opacity="0.05"/>
    </filter>
    <filter id="shadow-actor" x="-20%" y="-10%" width="140%" height="130%">
      <feDropShadow dx="0" dy="3" stdDeviation="4" flood-color="#0F172A" flood-opacity="0.08"/>
    </filter>
  </defs>

  <!-- Background Canvas -->
  <rect width="2750" height="1950" fill="#F8FAFC"/>

  <!-- Top Title Banner -->
  <rect x="50" y="30" width="2650" height="100" rx="14" fill="#1E293B" filter="url(#shadow-box)"/>
  <text x="1375" y="70" text-anchor="middle" fill="#FFFFFF" font-size="28" font-weight="800" letter-spacing="1">
    USE CASE DIAGRAM — SISTEM INFORMASI HOMECARE SERVICE
  </text>
  <text x="1375" y="104" text-anchor="middle" fill="#94A3B8" font-size="15" font-weight="500">
    Mata Kuliah: Rekayasa Perangkat Lunak (TIKB2503021) | Mahasiswa: Qonita Goniyun Ulum | Dosen Pengampu: Yudhistira Arie Wijaya, M.Kom., CDS.
  </text>

  <!-- ================= SYSTEM BOUNDARY ================= -->
  <rect x="420" y="160" width="1860" height="1740" rx="20" fill="#FFFFFF" stroke="#0284C7" stroke-width="3" filter="url(#shadow-box)"/>
  
  <!-- System Boundary Header Tag -->
  <rect x="420" y="160" width="580" height="46" rx="16" fill="#0284C7"/>
  <text x="445" y="191" fill="#FFFFFF" font-size="18" font-weight="700" letter-spacing="0.5">
    🏢 BATAS SISTEM: APLIKASI WEB HOMECARE SERVICE
  </text>


  <!-- ================= ASSOCIATION LINES ================= -->

  <!-- --- 1. ADMIN ASSOCIATIONS (Actor Center: 210, 420) --- -->
  <!-- To Master Data (760, 260) -->
  <line x1="210" y1="420" x2="760" y2="260" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Penugasan Teknisi (760, 370) -->
  <line x1="210" y1="420" x2="760" y2="370" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Verifikasi Transfer (760, 480) -->
  <line x1="210" y1="420" x2="760" y2="480" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Analitik Omset (760, 590) -->
  <line x1="210" y1="420" x2="760" y2="590" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Moderasi Ulasan (760, 700) -->
  <line x1="210" y1="420" x2="760" y2="700" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Login Sistem (1320, 320) -->
  <line x1="210" y1="420" x2="1320" y2="320" stroke="#CBD5E1" stroke-width="1.5"/>
  <!-- To Kelola Profil (1320, 480) -->
  <line x1="210" y1="420" x2="1320" y2="480" stroke="#CBD5E1" stroke-width="1.5"/>
  <!-- To Konfigurasi AI (1880, 1150) -->
  <path d="M 210 420 Q 350 200 1100 210 T 1880 1150" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="6,4"/>

  <!-- --- 2. TEKNISI ASSOCIATIONS (Actor Center: 210, 960) --- -->
  <!-- To Pantau Tugas (760, 830) -->
  <line x1="210" y1="960" x2="760" y2="830" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Update Status (760, 940) -->
  <line x1="210" y1="960" x2="760" y2="940" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Input Laporan Fisik (760, 1050) -->
  <line x1="210" y1="960" x2="760" y2="1050" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Konfirmasi Selesai (760, 1160) -->
  <line x1="210" y1="960" x2="760" y2="1160" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Login Sistem (1320, 320) -->
  <line x1="210" y1="960" x2="1320" y2="320" stroke="#CBD5E1" stroke-width="1.5"/>
  <!-- To Kelola Profil (1320, 480) -->
  <line x1="210" y1="960" x2="1320" y2="480" stroke="#CBD5E1" stroke-width="1.5"/>

  <!-- --- 3. CUSTOMER ASSOCIATIONS (Actor Center: 210, 1500) --- -->
  <!-- To Registrasi (760, 1280) -->
  <line x1="210" y1="1500" x2="760" y2="1280" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Buat Pesanan (760, 1390) -->
  <line x1="210" y1="1500" x2="760" y2="1390" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Lacak Status (760, 1500) -->
  <line x1="210" y1="1500" x2="760" y2="1500" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Pembayaran (760, 1610) -->
  <line x1="210" y1="1500" x2="760" y2="1610" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Ulasan (1320, 1150) -->
  <line x1="210" y1="1500" x2="1320" y2="1150" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Riwayat & Kwitansi (1320, 1320) -->
  <line x1="210" y1="1500" x2="1320" y2="1320" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Notifikasi (1320, 1500) -->
  <line x1="210" y1="1500" x2="1320" y2="1500" stroke="#94A3B8" stroke-width="1.8"/>
  <!-- To Login Sistem (1320, 320) -->
  <line x1="210" y1="1500" x2="1320" y2="320" stroke="#CBD5E1" stroke-width="1.5"/>
  <!-- To AI Diagnosis (1880, 450) -->
  <path d="M 210 1500 Q 800 1780 1400 1700 T 1880 450" fill="none" stroke="#F59E0B" stroke-width="1.8"/>
  <!-- To AI Chatbot (1880, 750) -->
  <path d="M 210 1500 Q 1100 1850 1600 1600 T 1880 750" fill="none" stroke="#F59E0B" stroke-width="1.8"/>

  <!-- --- 4. AI ENGINE ASSOCIATIONS (Actor Center: 2520, 850) --- -->
  <!-- AI Actor connects to UC-01 AI Diagnosis (1880, 450) -->
  <line x1="2520" y1="850" x2="1880" y2="450" stroke="#D97706" stroke-width="2.5"/>
  <!-- AI Actor connects to UC-02 AI Chatbot (1880, 750) -->
  <line x1="2520" y1="850" x2="1880" y2="750" stroke="#D97706" stroke-width="2.5"/>
  <!-- AI Actor connects to UC-20 Konfigurasi AI (1880, 1150) -->
  <line x1="2520" y1="850" x2="1880" y2="1150" stroke="#D97706" stroke-width="2.5"/>


  <!-- ================= STEREOTYPE RELATIONSHIPS (Include / Extend) ================= -->

  <!-- 1. <<include>> Buat Pesanan (760, 1390) -> Login Sistem (1320, 320) -->
  <path d="M 940 1370 Q 1200 900 1280 370" fill="none" stroke="#2563EB" stroke-width="2" stroke-dasharray="8,5" marker-end="url(#arrow-include)"/>
  <rect x="1100" y="850" width="96" height="22" rx="4" fill="#EFF6FF" stroke="#BFDBFE" stroke-width="1"/>
  <text x="1148" y="866" text-anchor="middle" fill="#1D4ED8" font-size="11" font-weight="700">&lt;&lt;include&gt;&gt;</text>

  <!-- 2. <<extend>> AI Smart Diagnosis (1880, 450) -> Buat Pesanan (760, 1390) via CTA Button -->
  <path d="M 1700 480 Q 1300 850 945 1360" fill="none" stroke="#D97706" stroke-width="2.2" stroke-dasharray="8,5" marker-end="url(#arrow-extend)"/>
  <rect x="1350" y="770" width="90" height="22" rx="4" fill="#FFFBEB" stroke="#FDE68A" stroke-width="1"/>
  <text x="1395" y="786" text-anchor="middle" fill="#B45309" font-size="11" font-weight="700">&lt;&lt;extend&gt;&gt;</text>

  <!-- 3. <<extend>> Unggah Bukti Transfer (1320, 880) -> Melakukan Pembayaran (760, 1610) -->
  <path d="M 1200 910 Q 1020 1200 940 1580" fill="none" stroke="#D97706" stroke-width="2" stroke-dasharray="8,5" marker-end="url(#arrow-extend)"/>
  <rect x="1040" y="1250" width="90" height="22" rx="4" fill="#FFFBEB" stroke="#FDE68A" stroke-width="1"/>
  <text x="1085" y="1266" text-anchor="middle" fill="#B45309" font-size="11" font-weight="700">&lt;&lt;extend&gt;&gt;</text>

  <!-- 4. <<include>> Input Laporan Fisik (760, 1050) -> Konfirmasi Selesai (760, 1160) -->
  <path d="M 760 1095 L 760 1115" fill="none" stroke="#2563EB" stroke-width="2" stroke-dasharray="8,5" marker-end="url(#arrow-include)"/>
  <rect x="785" y="1092" width="96" height="22" rx="4" fill="#EFF6FF" stroke="#BFDBFE" stroke-width="1"/>
  <text x="833" y="1108" text-anchor="middle" fill="#1D4ED8" font-size="11" font-weight="700">&lt;&lt;include&gt;&gt;</text>

  <!-- 5. <<include>> Lacak Status (760, 1500) -> Buat Pesanan (760, 1390) -->
  <path d="M 760 1455 L 760 1435" fill="none" stroke="#2563EB" stroke-width="2" stroke-dasharray="8,5" marker-end="url(#arrow-include)"/>
  <rect x="785" y="1432" width="96" height="22" rx="4" fill="#EFF6FF" stroke="#BFDBFE" stroke-width="1"/>
  <text x="833" y="1448" text-anchor="middle" fill="#1D4ED8" font-size="11" font-weight="700">&lt;&lt;include&gt;&gt;</text>

  <!-- 6. <<include>> Ulasan (1320, 1150) -> Login Sistem (1320, 320) -->
  <path d="M 1320 1105 L 1320 375" fill="none" stroke="#2563EB" stroke-width="2" stroke-dasharray="8,5" marker-end="url(#arrow-include)"/>
  <rect x="1340" y="650" width="96" height="22" rx="4" fill="#EFF6FF" stroke="#BFDBFE" stroke-width="1"/>
  <text x="1388" y="666" text-anchor="middle" fill="#1D4ED8" font-size="11" font-weight="700">&lt;&lt;include&gt;&gt;</text>


  <!-- ================= USE CASES (OVALS) ================= -->

  <!-- ====== COLUMN A: ADMIN, TEKNISI, CUSTOMER PRIMARY (X: 760) ====== -->

  <!-- --- ADMIN USE CASES (Purple Violet Theme) --- -->
  <!-- UC-16: Kelola Master Data -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="260" rx="175" ry="40" fill="#F3E8FF" stroke="#7C3AED" stroke-width="2.5"/>
    <text x="760" y="254" text-anchor="middle" fill="#5B21B6" font-size="13" font-weight="800">🗃️ UC-16: Kelola Master Data</text>
    <text x="760" y="274" text-anchor="middle" fill="#5B21B6" font-size="13" font-weight="800">(Alat, Teknisi, &amp; Pelanggan)</text>
  </g>

  <!-- UC-17: Penugasan Teknisi -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="370" rx="175" ry="38" fill="#F5F3FF" stroke="#8B5CF6" stroke-width="2"/>
    <text x="760" y="364" text-anchor="middle" fill="#5B21B6" font-size="13" font-weight="700">👨‍🔧 UC-17: Validasi Order &amp;</text>
    <text x="760" y="384" text-anchor="middle" fill="#5B21B6" font-size="13" font-weight="700">Penugasan Teknisi Lapangan</text>
  </g>

  <!-- UC-18: Verifikasi Transfer Pembayaran -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="480" rx="175" ry="40" fill="#F3E8FF" stroke="#7C3AED" stroke-width="2.5"/>
    <text x="760" y="474" text-anchor="middle" fill="#5B21B6" font-size="13" font-weight="800">💰 UC-18: Verifikasi Pembayaran</text>
    <text x="760" y="494" text-anchor="middle" fill="#5B21B6" font-size="13" font-weight="800">Transfer &amp; Konfirmasi LUNAS</text>
  </g>

  <!-- UC-19: Analitik Omset & Statistik -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="590" rx="175" ry="40" fill="#F3E8FF" stroke="#7C3AED" stroke-width="2.5"/>
    <text x="760" y="584" text-anchor="middle" fill="#5B21B6" font-size="13" font-weight="800">📊 UC-19: Monitoring Laporan</text>
    <text x="760" y="604" text-anchor="middle" fill="#5B21B6" font-size="13" font-weight="800">Statistik &amp; Omset Bisnis Riil</text>
  </g>

  <!-- UC-21: Moderasi Ulasan -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="700" rx="175" ry="38" fill="#F5F3FF" stroke="#8B5CF6" stroke-width="2"/>
    <text x="760" y="694" text-anchor="middle" fill="#5B21B6" font-size="13" font-weight="700">🛡️ UC-21: Moderasi Ulasan &amp;</text>
    <text x="760" y="714" text-anchor="middle" fill="#5B21B6" font-size="13" font-weight="700">Testimoni Pengguna</text>
  </g>

  <!-- --- TEKNISI USE CASES (Green Emerald Theme) --- -->
  <!-- UC-12: Lihat Tugas Masuk -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="830" rx="175" ry="38" fill="#ECFDF5" stroke="#10B981" stroke-width="2"/>
    <text x="760" y="824" text-anchor="middle" fill="#065F46" font-size="13" font-weight="700">📋 UC-12: Memantau Penugasan</text>
    <text x="760" y="844" text-anchor="middle" fill="#065F46" font-size="13" font-weight="700">Pekerjaan Servis Masuk</text>
  </g>

  <!-- UC-13: Update Status Lapangan -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="940" rx="175" ry="38" fill="#ECFDF5" stroke="#10B981" stroke-width="2"/>
    <text x="760" y="934" text-anchor="middle" fill="#065F46" font-size="13" font-weight="700">🛵 UC-13: Update Status Lapangan</text>
    <text x="760" y="954" text-anchor="middle" fill="#065F46" font-size="13" font-weight="700">(Menuju Lokasi / Perbaikan)</text>
  </g>

  <!-- UC-14: Input Laporan Servis Fisik -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="1050" rx="175" ry="40" fill="#D1FAE5" stroke="#059669" stroke-width="2.5"/>
    <text x="760" y="1044" text-anchor="middle" fill="#065F46" font-size="13" font-weight="800">🛠️ UC-14: Input Laporan Fisik,</text>
    <text x="760" y="1064" text-anchor="middle" fill="#065F46" font-size="13" font-weight="800">Tindakan &amp; Suku Cadang</text>
  </g>

  <!-- UC-15: Konfirmasi Selesai -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="1160" rx="175" ry="38" fill="#ECFDF5" stroke="#10B981" stroke-width="2"/>
    <text x="760" y="1154" text-anchor="middle" fill="#065F46" font-size="13" font-weight="700">✅ UC-15: Konfirmasi Pekerjaan</text>
    <text x="760" y="1174" text-anchor="middle" fill="#065F46" font-size="13" font-weight="700">Servis Selesai &amp; Uji Fungsi</text>
  </g>

  <!-- --- CUSTOMER PRIMARY USE CASES (Blue Sky Theme) --- -->
  <!-- UC-03: Registrasi Akun -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="1280" rx="175" ry="38" fill="#F0F9FF" stroke="#0284C7" stroke-width="2"/>
    <text x="760" y="1285" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="700">UC-03: Registrasi Akun Baru</text>
  </g>

  <!-- UC-04: Buat Pesanan Servis -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="1390" rx="175" ry="42" fill="#E0F2FE" stroke="#0284C7" stroke-width="2.5"/>
    <text x="760" y="1384" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="800">📦 UC-04: Buat Pesanan Servis</text>
    <text x="760" y="1404" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="800">Peralatan (Booking Online)</text>
  </g>

  <!-- UC-05: Lacak Status Pengerjaan -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="1500" rx="175" ry="38" fill="#F0F9FF" stroke="#0284C7" stroke-width="2"/>
    <text x="760" y="1494" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="700">UC-05: Lacak Status Progres</text>
    <text x="760" y="1514" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="700">Pengerjaan Servis Real-Time</text>
  </g>

  <!-- UC-06: Pembayaran Servis -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="760" cy="1610" rx="175" ry="40" fill="#E0F2FE" stroke="#0284C7" stroke-width="2.5"/>
    <text x="760" y="1604" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="800">💳 UC-06: Melakukan Pembayaran</text>
    <text x="760" y="1624" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="800">(CASH / Transfer Bank)</text>
  </g>


  <!-- ====== COLUMN B: SHARED & TRANSACTIONAL (X: 1320) ====== -->

  <!-- UC-10: Login Sistem (Autentikasi Terpusat) -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="1320" cy="320" rx="175" ry="48" fill="#F1F5F9" stroke="#475569" stroke-width="3"/>
    <text x="1320" y="313" text-anchor="middle" fill="#0F172A" font-size="14" font-weight="800">🔐 UC-10: Login Sistem</text>
    <text x="1320" y="333" text-anchor="middle" fill="#475569" font-size="12" font-weight="600">(Autentikasi Sesi Multi-Role)</text>
  </g>

  <!-- UC-11: Kelola Profil & Password -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="1320" cy="480" rx="170" ry="38" fill="#F8FAFC" stroke="#64748B" stroke-width="2"/>
    <text x="1320" y="474" text-anchor="middle" fill="#334155" font-size="13" font-weight="700">⚙️ UC-11: Kelola Data Profil</text>
    <text x="1320" y="494" text-anchor="middle" fill="#334155" font-size="13" font-weight="700">&amp; Ubah Kata Sandi Akun</text>
  </g>

  <!-- UC-11B: Unggah Bukti Transfer -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="1320" cy="880" rx="170" ry="38" fill="#EFF6FF" stroke="#3B82F6" stroke-width="2"/>
    <text x="1320" y="874" text-anchor="middle" fill="#1D4ED8" font-size="13" font-weight="700">📤 UC-11B: Unggah File Bukti</text>
    <text x="1320" y="894" text-anchor="middle" fill="#1D4ED8" font-size="13" font-weight="700">Transfer Pembayaran Bank</text>
  </g>

  <!-- UC-07: Memberikan Ulasan & Rating -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="1320" cy="1150" rx="170" ry="40" fill="#F0F9FF" stroke="#0284C7" stroke-width="2"/>
    <text x="1320" y="1144" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="700">⭐ UC-07: Memberikan Ulasan &amp;</text>
    <text x="1320" y="1164" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="700">Rating Kinerja Teknisi</text>
  </g>

  <!-- UC-08: Riwayat Pesanan & Kwitansi -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="1320" cy="1320" rx="170" ry="40" fill="#F0F9FF" stroke="#0284C7" stroke-width="2"/>
    <text x="1320" y="1314" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="700">📄 UC-08: Melihat Riwayat Servis</text>
    <text x="1320" y="1334" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="700">&amp; Cetak Kwitansi Pembayaran</text>
  </g>

  <!-- UC-09: Notifikasi WhatsApp & In-App -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="1320" cy="1500" rx="170" ry="40" fill="#F0F9FF" stroke="#0284C7" stroke-width="2"/>
    <text x="1320" y="1494" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="700">🔔 UC-09: Menerima Notifikasi</text>
    <text x="1320" y="1514" text-anchor="middle" fill="#0369A1" font-size="13" font-weight="700">In-App &amp; WhatsApp (Fonnte)</text>
  </g>


  <!-- ====== COLUMN C: AI ENGINE SERVICES (X: 1880, NEAR AI ACTOR) ====== -->

  <!-- UC-01: AI Diagnosis (Amber Gold Theme) -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="1880" cy="450" rx="185" ry="50" fill="#FEF3C7" stroke="#D97706" stroke-width="3"/>
    <text x="1880" y="442" text-anchor="middle" fill="#92400E" font-size="14" font-weight="800">✨ UC-01: Konsultasi AI Smart</text>
    <text x="1880" y="464" text-anchor="middle" fill="#92400E" font-size="14" font-weight="800">Diagnosis Kerusakan Alat</text>
  </g>

  <!-- UC-02: AI Virtual Assistant (Amber Gold Theme) -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="1880" cy="750" rx="185" ry="50" fill="#FEF3C7" stroke="#D97706" stroke-width="3"/>
    <text x="1880" y="742" text-anchor="middle" fill="#92400E" font-size="14" font-weight="800">🤖 UC-02: Interaksi HomeCare AI</text>
    <text x="1880" y="764" text-anchor="middle" fill="#92400E" font-size="14" font-weight="800">Assistant (Live DB Context)</text>
  </g>

  <!-- UC-20: Konfigurasi AI Multi-Provider -->
  <g filter="url(#shadow-oval)">
    <ellipse cx="1880" cy="1150" rx="185" ry="50" fill="#FEF3C7" stroke="#D97706" stroke-width="3"/>
    <text x="1880" y="1142" text-anchor="middle" fill="#92400E" font-size="14" font-weight="800">⚡ UC-20: Konfigurasi AI Multi-</text>
    <text x="1880" y="1164" text-anchor="middle" fill="#92400E" font-size="14" font-weight="800">Provider &amp; Tes Koneksi API</text>
  </g>


  <!-- ================= LEFT ACTORS ================= -->

  <!-- ACTOR 1: ADMIN (Top Left - Center: 210, 420) -->
  <g id="actor-admin" filter="url(#shadow-actor)">
    <rect x="70" y="290" width="280" height="260" rx="16" fill="#FFFFFF" stroke="#7C3AED" stroke-width="2.5"/>
    <!-- Head -->
    <circle cx="210" cy="350" r="26" fill="#DDD6FE" stroke="#7C3AED" stroke-width="3"/>
    <path d="M 205 376 L 210 390 L 215 376 Z" fill="#7C3AED"/>
    <circle cx="202" cy="346" r="3" fill="#5B21B6"/>
    <circle cx="218" cy="346" r="3" fill="#5B21B6"/>
    <path d="M 202 358 Q 210 366 218 358" fill="none" stroke="#5B21B6" stroke-width="2.5" stroke-linecap="round"/>
    <!-- Body -->
    <line x1="210" y1="376" x2="210" y2="435" stroke="#7C3AED" stroke-width="3.5" stroke-linecap="round"/>
    <!-- Arms -->
    <line x1="160" y1="400" x2="260" y2="400" stroke="#7C3AED" stroke-width="3.5" stroke-linecap="round"/>
    <!-- Legs -->
    <line x1="210" y1="435" x2="170" y2="495" stroke="#7C3AED" stroke-width="3.5" stroke-linecap="round"/>
    <line x1="210" y1="435" x2="250" y2="495" stroke="#7C3AED" stroke-width="3.5" stroke-linecap="round"/>
    <!-- Label -->
    <rect x="90" y="505" width="240" height="32" rx="8" fill="#7C3AED"/>
    <text x="210" y="526" text-anchor="middle" fill="#FFFFFF" font-size="14" font-weight="800">&laquo;Actor&raquo; ADMINISTRATOR</text>
  </g>

  <!-- ACTOR 2: TEKNISI (Middle Left - Center: 210, 960) -->
  <g id="actor-teknisi" filter="url(#shadow-actor)">
    <rect x="70" y="830" width="280" height="260" rx="16" fill="#FFFFFF" stroke="#059669" stroke-width="2.5"/>
    <!-- Head -->
    <circle cx="210" cy="890" r="26" fill="#A7F3D0" stroke="#059669" stroke-width="3"/>
    <path d="M 182 878 Q 210 860 238 878" fill="none" stroke="#D97706" stroke-width="4" stroke-linecap="round"/>
    <circle cx="202" cy="886" r="3" fill="#065F46"/>
    <circle cx="218" cy="886" r="3" fill="#065F46"/>
    <path d="M 202 898 Q 210 906 218 898" fill="none" stroke="#065F46" stroke-width="2.5" stroke-linecap="round"/>
    <!-- Body -->
    <line x1="210" y1="916" x2="210" y2="975" stroke="#059669" stroke-width="3.5" stroke-linecap="round"/>
    <!-- Arms -->
    <line x1="160" y1="940" x2="260" y2="940" stroke="#059669" stroke-width="3.5" stroke-linecap="round"/>
    <!-- Legs -->
    <line x1="210" y1="975" x2="170" y2="1035" stroke="#059669" stroke-width="3.5" stroke-linecap="round"/>
    <line x1="210" y1="975" x2="250" y2="1035" stroke="#059669" stroke-width="3.5" stroke-linecap="round"/>
    <!-- Label -->
    <rect x="90" y="1045" width="240" height="32" rx="8" fill="#059669"/>
    <text x="210" y="1066" text-anchor="middle" fill="#FFFFFF" font-size="14" font-weight="800">&laquo;Actor&raquo; TEKNISI</text>
  </g>

  <!-- ACTOR 3: CUSTOMER (Bottom Left - Center: 210, 1500) -->
  <g id="actor-customer" filter="url(#shadow-actor)">
    <rect x="70" y="1370" width="280" height="260" rx="16" fill="#FFFFFF" stroke="#0284C7" stroke-width="2.5"/>
    <!-- Head -->
    <circle cx="210" cy="1430" r="26" fill="#BAE6FD" stroke="#0284C7" stroke-width="3"/>
    <circle cx="202" cy="1425" r="3" fill="#0284C7"/>
    <circle cx="218" cy="1425" r="3" fill="#0284C7"/>
    <path d="M 202 1438 Q 210 1446 218 1438" fill="none" stroke="#0284C7" stroke-width="2.5" stroke-linecap="round"/>
    <!-- Body -->
    <line x1="210" y1="1456" x2="210" y2="1515" stroke="#0284C7" stroke-width="3.5" stroke-linecap="round"/>
    <!-- Arms -->
    <line x1="160" y1="1480" x2="260" y2="1480" stroke="#0284C7" stroke-width="3.5" stroke-linecap="round"/>
    <!-- Legs -->
    <line x1="210" y1="1515" x2="170" y2="1575" stroke="#0284C7" stroke-width="3.5" stroke-linecap="round"/>
    <line x1="210" y1="1515" x2="250" y2="1575" stroke="#0284C7" stroke-width="3.5" stroke-linecap="round"/>
    <!-- Label -->
    <rect x="90" y="1585" width="240" height="32" rx="8" fill="#0284C7"/>
    <text x="210" y="1606" text-anchor="middle" fill="#FFFFFF" font-size="14" font-weight="800">&laquo;Actor&raquo; CUSTOMER</text>
  </g>


  <!-- ================= RIGHT ACTOR: AI ENGINE ================= -->
  <g id="actor-ai" filter="url(#shadow-actor)">
    <!-- Actor Box Backdrop -->
    <rect x="2360" y="660" width="320" height="380" rx="20" fill="#FFFFFF" stroke="#D97706" stroke-width="3"/>
    
    <!-- Header Tag -->
    <rect x="2380" y="685" width="280" height="28" rx="6" fill="#FEF3C7" stroke="#FDE68A" stroke-width="1"/>
    <text x="2520" y="704" text-anchor="middle" fill="#B45309" font-size="12" font-weight="800">&laquo;Secondary Actor / External AI&raquo;</text>
    
    <!-- Robot / AI Character Icon -->
    <!-- Head Box -->
    <rect x="2465" y="735" width="110" height="95" rx="18" fill="#FEF3C7" stroke="#D97706" stroke-width="3"/>
    <!-- Antennas -->
    <line x1="2520" y1="735" x2="2520" y2="720" stroke="#D97706" stroke-width="3" stroke-linecap="round"/>
    <circle cx="2520" cy="716" r="5" fill="#D97706"/>
    <!-- Ears -->
    <rect x="2455" y="768" width="10" height="30" rx="3" fill="#D97706"/>
    <rect x="2575" y="768" width="10" height="30" rx="3" fill="#D97706"/>
    <!-- Digital Eyes -->
    <rect x="2485" y="760" width="26" height="22" rx="6" fill="#1E293B"/>
    <circle cx="2498" cy="771" r="5" fill="#38BDF8"/>
    <rect x="2529" y="760" width="26" height="22" rx="6" fill="#1E293B"/>
    <circle cx="2542" cy="771" r="5" fill="#38BDF8"/>
    <!-- Digital Smile / Waveform -->
    <path d="M 2490 805 Q 2520 820 2550 805" fill="none" stroke="#D97706" stroke-width="3" stroke-linecap="round"/>

    <!-- Body & Neural Connections -->
    <line x1="2520" y1="830" x2="2520" y2="900" stroke="#D97706" stroke-width="4" stroke-linecap="round"/>
    <line x1="2450" y1="865" x2="2590" y2="865" stroke="#D97706" stroke-width="4" stroke-linecap="round"/>
    <line x1="2520" y1="900" x2="2470" y2="960" stroke="#D97706" stroke-width="4" stroke-linecap="round"/>
    <line x1="2520" y1="900" x2="2570" y2="960" stroke="#D97706" stroke-width="4" stroke-linecap="round"/>

    <!-- Actor Label Banner -->
    <rect x="2380" y="980" width="280" height="42" rx="10" fill="#D97706"/>
    <text x="2520" y="1006" text-anchor="middle" fill="#FFFFFF" font-size="15" font-weight="800">🤖 AI ENGINE (LLM)</text>
    <text x="2520" y="1045" text-anchor="middle" fill="#64748B" font-size="11" font-weight="600">Gemini / NVIDIA / Groq / OpenAI</text>
  </g>


  <!-- ================= LEGEND BOX (BOTTOM CENTER) ================= -->
  <g id="diagram-legend" filter="url(#shadow-box)">
    <rect x="450" y="1720" width="1800" height="160" rx="14" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="2"/>
    <text x="480" y="1750" fill="#0F172A" font-size="15" font-weight="800">📌 KETERANGAN &amp; LEGENDA SIMBOL USE CASE DIAGRAM</text>
    
    <!-- Row 1: Badges -->
    <rect x="480" y="1768" width="22" height="14" rx="4" fill="#F3E8FF" stroke="#7C3AED" stroke-width="1.5"/>
    <text x="510" y="1780" fill="#334155" font-size="12" font-weight="600">Use Case Administrator</text>

    <rect x="760" y="1768" width="22" height="14" rx="4" fill="#ECFDF5" stroke="#10B981" stroke-width="1.5"/>
    <text x="790" y="1780" fill="#334155" font-size="12" font-weight="600">Use Case Teknisi (Lapangan)</text>

    <rect x="1060" y="1768" width="22" height="14" rx="4" fill="#E0F2FE" stroke="#0284C7" stroke-width="1.5"/>
    <text x="1090" y="1780" fill="#334155" font-size="12" font-weight="600">Use Case Customer (Pelanggan)</text>

    <rect x="1390" y="1768" width="22" height="14" rx="4" fill="#FEF3C7" stroke="#D97706" stroke-width="1.5"/>
    <text x="1420" y="1780" fill="#334155" font-size="12" font-weight="600">Layanan Kecerdasan Artifisial (AI)</text>

    <rect x="1770" y="1768" width="22" height="14" rx="4" fill="#F1F5F9" stroke="#475569" stroke-width="1.5"/>
    <text x="1800" y="1780" fill="#334155" font-size="12" font-weight="600">Shared Authentication Sesi</text>

    <!-- Row 2: Relationships -->
    <line x1="480" y1="1815" x2="530" y2="1815" stroke="#94A3B8" stroke-width="2.5"/>
    <text x="540" y="1819" fill="#334155" font-size="11.5" font-weight="600">Asosiasi Aktor: Garis solid menghubungkan Aktor dengan Use Case.</text>

    <line x1="480" y1="1848" x2="530" y2="1848" stroke="#2563EB" stroke-width="2" stroke-dasharray="6,4" marker-end="url(#arrow-include)"/>
    <text x="540" y="1852" fill="#334155" font-size="11.5" font-weight="600">&lt;&lt;include&gt;&gt; Relasi Wajib: Eksekusi use case asal memicu use case tujuan secara mutlak.</text>

    <line x1="1260" y1="1848" x2="1310" y2="1848" stroke="#D97706" stroke-width="2" stroke-dasharray="6,4" marker-end="url(#arrow-extend)"/>
    <text x="1320" y="1852" fill="#334155" font-size="11.5" font-weight="600">&lt;&lt;extend&gt;&gt; Relasi Kondisional: Use case tujuan memperluas use case asal saat kondisi terpenuhi.</text>
  </g>

</svg>
</body>
</html>
"""

html_path = r"C:\xampp\htdocs\jasa peralatan runah tangga\scratch\usecase.html"
with open(html_path, "w", encoding="utf-8") as f:
    f.write(html_content)

print(f"HTML SVG written to {html_path}")

chrome_path = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
dest1 = r"C:\xampp\htdocs\jasa peralatan runah tangga\usecase_homecare_service.png"
dest2 = r"D:\A KULIAH\Semester 3\Perangkat Rekayasa Lunak\usecase_homecare_service.png"
dest3 = r"C:\Users\Hype AMD\.gemini\antigravity\brain\1d0a8584-928e-4397-bea6-0d262cd1d11e\usecase_homecare_service.png"

cmd = [
    chrome_path,
    "--headless=new",
    "--disable-gpu",
    f"--screenshot={dest1}",
    "--window-size=2750,1950",
    html_path
]

res = subprocess.run(cmd, capture_output=True, text=True)
print(f"Chrome returncode: {res.returncode}")
print(f"Stderr: {res.stderr}")

if os.path.exists(dest1):
    print(f"PNG created successfully at: {dest1} (Size: {os.path.getsize(dest1)} bytes)")
    import shutil
    shutil.copy2(dest1, dest2)
    shutil.copy2(dest1, dest3)
    print(f"Copied PNG successfully to: {dest2} and {dest3}")
else:
    print("Failed to generate PNG.")
