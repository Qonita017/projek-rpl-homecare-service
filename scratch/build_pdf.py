import os
import sys
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.units import cm
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.pdfgen import canvas

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            super().showPage()
        super().save()

    def draw_page_decorations(self, page_count):
        self.saveState()
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#718096"))
        
        # Header (on pages after cover / first page)
        if self._pageNumber > 1:
            self.drawString(1.5 * cm, 28.5 * cm, "STMIK IKMI Cirebon | Rekayasa Perangkat Lunak (TIKB2503021) — Panduan Naskah Video")
            self.setStrokeColor(colors.HexColor("#CBD5E0"))
            self.setLineWidth(0.5)
            self.line(1.5 * cm, 28.3 * cm, 19.5 * cm, 28.3 * cm)

        # Footer (on all pages)
        footer_text = f"Halaman {self._pageNumber} dari {page_count}"
        self.drawRightString(19.5 * cm, 1.0 * cm, footer_text)
        self.drawString(1.5 * cm, 1.0 * cm, "HomeCare Service — Script Video Proyek Akhir (OBE)")
        self.setStrokeColor(colors.HexColor("#CBD5E0"))
        self.setLineWidth(0.5)
        self.line(1.5 * cm, 1.3 * cm, 19.5 * cm, 1.3 * cm)
        self.restoreState()

def create_script_pdf(output_paths):
    target_primary = output_paths[0]
    doc = SimpleDocTemplate(
        target_primary,
        pagesize=A4,
        leftMargin=1.5 * cm,
        rightMargin=1.5 * cm,
        topMargin=1.8 * cm,
        bottomMargin=1.8 * cm
    )

    styles = getSampleStyleSheet()
    
    # Custom Palette
    c_primary = colors.HexColor("#1A365D")   # Deep Navy
    c_secondary = colors.HexColor("#2B6CB0") # Blue
    c_dark = colors.HexColor("#2D3748")      # Dark Slate
    c_gray = colors.HexColor("#4A5568")      # Neutral Gray
    c_light = colors.HexColor("#F7FAFC")     # Off white
    c_accent = colors.HexColor("#ED8936")    # Amber accent
    c_border = colors.HexColor("#E2E8F0")

    # Custom Styles
    style_title = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=18,
        leading=22,
        textColor=c_primary,
        alignment=1, # Center
        spaceAfter=6
    )
    style_subtitle = ParagraphStyle(
        'DocSubTitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=10,
        leading=14,
        textColor=c_gray,
        alignment=1,
        spaceAfter=12
    )
    style_h1 = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Heading1'],
        fontName='Helvetica-Bold',
        fontSize=13,
        leading=17,
        textColor=c_primary,
        spaceBefore=14,
        spaceAfter=6,
        keepWithNext=True
    )
    style_h2 = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Heading2'],
        fontName='Helvetica-Bold',
        fontSize=11,
        leading=15,
        textColor=c_secondary,
        spaceBefore=10,
        spaceAfter=4,
        keepWithNext=True
    )
    style_body = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=13,
        textColor=c_dark,
        spaceAfter=5
    )
    style_spoken = ParagraphStyle(
        'Spoken_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Oblique',
        fontSize=9,
        leading=13.5,
        textColor=colors.HexColor("#1A202C"),
        spaceAfter=4
    )
    style_meta_label = ParagraphStyle(
        'MetaLabel',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11,
        textColor=c_secondary
    )
    style_meta_val = ParagraphStyle(
        'MetaVal',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=11,
        textColor=c_dark
    )
    style_table_cell = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=11,
        textColor=c_dark
    )
    style_table_header = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11,
        textColor=colors.white
    )

    elements = []

    # ── Header Banner ──
    header_data = [
        [
            Paragraph("<b>PROGRAM STUDI S1 TEKNIK INFORMATIKA — STMIK IKMI CIREBON</b><br/>"
                      "MATA KULIAH: REKAYASA PERANGKAT LUNAK (TIKB2503021) — SEMESTER 3", style_meta_label),
            Paragraph("<b>DOSEN PENGAMPU:</b><br/>Yudhistira Arie Wijaya, M.Kom., CDS.", style_meta_val)
        ]
    ]
    t_header = Table(header_data, colWidths=[11.5 * cm, 6.5 * cm])
    t_header.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#EDF2F7")),
        ('BOX', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('TOPPADDING', (0,0), (-1,-1), 6),
        ('BOTTOMPADDING', (0,0), (-1,-1), 6),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
    ]))
    elements.append(t_header)
    elements.append(Spacer(1, 10))

    # ── Document Title ──
    elements.append(Paragraph("NASKAH LENGKAP SCRIPT VIDEO PRESENTASI PROYEK AKHIR", style_title))
    elements.append(Paragraph("<b>Aplikasi:</b> HomeCare Service (Sistem Cerdas Jasa Perbaikan & Perawatan Peralatan Rumah Tangga berbasis AI)<br/>"
                              "<b>Mahasiswa:</b> Qonita Goniyun Ulum &nbsp;|&nbsp; <b>Pengerjaan:</b> Mandiri (100% Individual Ownership)", style_subtitle))
    elements.append(HRFlowable(width="100%", thickness=1.5, color=c_primary, spaceBefore=0, spaceAfter=10))

    # ── BAB I: KETENTUAN DAN STANDAR KUALITAS ──
    elements.append(Paragraph("BAB I: KETENTUAN STANDAR VIDEO & RUBRIK PENILAIAN OBE", style_h1))
    elements.append(Paragraph(
        "Berdasarkan buku panduan proyek akhir, video presentasi merupakan instrumen utama untuk memverifikasi autentisitas "
        "pengerjaan mandiri, penguasaan pemodelan UML, dan kelancaran demonstrasi aplikasi web AI. Total nilai video berkontribusi <b>20%</b> "
        "dari total nilai proyek akhir. Berikut standar kualitas wajib untuk meraih <b>Skor 4 (Sangat Baik)</b>:", style_body
    ))

    req_data = [
        [Paragraph("<b>Aspek Standar</b>", style_table_header), Paragraph("<b>Ketentuan Wajib Sesuai Rubrik Penilaian OBE</b>", style_table_header)],
        [Paragraph("<b>Perekaman Wajah (Webcam)</b>", style_table_cell), Paragraph("Perekaman layar (screen recording) <b>WAJIB menampilkan wajah mahasiswa (webcam aktif di pojok layar)</b> secara jelas sepanjang video untuk memastikan keaslian pengerjaan mandiri (anti-joki).", style_table_cell)],
        [Paragraph("<b>Kualitas Audio & Visual</b>", style_table_cell), Paragraph("Resolusi minimal 720p (sangat direkomendasikan <b>1080p Full HD</b>). Audio jernih, tegas, tanpa kebisingan latar (noise).", style_table_cell)],
        [Paragraph("<b>Live Demo Nyata</b>", style_table_cell), Paragraph("Demo sistem dan pengujian AI <b>WAJIB dilakukan secara LANGSUNG (real-time live demo)</b> di browser, dilarang hanya menampilkan foto/screenshot.", style_table_cell)],
        [Paragraph("<b>Manajemen Waktu</b>", style_table_cell), Paragraph("Durasi video wajib pas sesuai alokasi tiap tahapan (toleransi ±1 menit). Durasi yang terlalu singkat (<50%) atau terlalu panjang akan memotong skor.", style_table_cell)],
        [Paragraph("<b>Platform & Pengumpulan</b>", style_table_cell), Paragraph("Diunggah ke YouTube (Unlisted/Public) atau Google Drive (akses publik). Tautan wajib dicantumkan rapi pada file <code>README.md</code> GitHub.", style_table_cell)]
    ]
    t_req = Table(req_data, colWidths=[5.0 * cm, 13.0 * cm])
    t_req.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_primary),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, c_light]),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
    ]))
    elements.append(t_req)
    elements.append(Spacer(1, 10))

    # ── BAB II: VIDEO 1 — PROGRES 1 (PRD) ──
    elements.append(Paragraph("BAB II: NASKAH VIDEO PROGRES 1 — PRD & KONSEP APLIKASI", style_h1))
    elements.append(Paragraph("<b>Target Durasi:</b> 3 – 5 Menit &nbsp;|&nbsp; <b>Bobot Nilai Video:</b> 20%", style_meta_label))
    elements.append(Paragraph("<b>Fokus Materi:</b> Identifikasi masalah nyata, nilai guna (*value proposition*), kebutuhan fungsional & non-fungsional (IEEE 830), serta peran strategis fitur AI yang ditanamkan.", style_body))

    v1_table = [
        [Paragraph("<b>Waktu</b>", style_table_header), Paragraph("<b>Tampilan Layar (Visual)</b>", style_table_header), Paragraph("<b>Naskah Narasi Lisan Lengkap (Kata-per-Kata)</b>", style_table_header)],
        [
            Paragraph("<b>00:00 - 00:45</b>", style_table_cell),
            Paragraph("Slide Judul PRD, Logo STMIK IKMI, Nama: Qonita Goniyun Ulum, NIM, Webcam aktif.", style_table_cell),
            Paragraph('"Assalamu’alaikum Warahmatullahi Wabarakatuh. Selamat pagi/siang kepada Bapak Yudhistira Arie Wijaya, M.Kom., CDS., selaku dosen pengampu mata kuliah Rekayasa Perangkat Lunak, serta rekan-rekan sekalian.<br/><br/>'
                      'Perkenalkan, nama saya <b>Qonita Goniyun Ulum</b>, mahasiswa S1 Teknik Informatika STMIK IKMI Cirebon. Pada kesempatan video progres pertama ini, saya akan mempresentasikan dokumen <i>Product Requirements Document</i> (PRD) dari aplikasi web mandiri yang saya rancang dan bangun secara utuh, yaitu <b>HomeCare Service — Sistem Cerdas Jasa Perbaikan dan Perawatan Peralatan Rumah Tangga Terintegrasi Artificial Intelligence</b>."', style_spoken)
        ],
        [
            Paragraph("<b>00:45 - 02:00</b>", style_table_cell),
            Paragraph("Slide Permasalahan Masyarakat & Solusi Value Proposition.", style_table_cell),
            Paragraph('"Latar belakang lahirnya HomeCare Service berakar dari permasalahan nyata yang sering dihadapi masyarakat. Ketika peralatan elektronik rumah tangga seperti AC, kulkas, atau mesin cuci mengalami kendala, masyarakat kerap kebingungan menentukan jenis kerusakannya, ragu mengenai estimasi biaya perbaikan, serta khawatir akan transparansi teknisi konvensional. Di sisi lain, penyedia jasa servis independen mengalami kesulitan dalam pencatatan pesanan, penjadwalan teknisi, dan pemantauan omset secara terstruktur.<br/><br/>'
                      'Sebagai solusinya, HomeCare Service hadir dengan proposisi nilai utama: <b>Platform digital satu pintu (one-stop home appliance solution)</b> yang tidak hanya mengelola siklus pesanan servis secara transparan dari hulu ke hilir, melainkan juga menanamkan kecerdasan buatan (AI) untuk memberikan diagnosis awal kerusakan secara instan dan asisten virtual cerdas yang terhubung langsung dengan basis data sistem."', style_spoken)
        ],
        [
            Paragraph("<b>02:00 - 03:30</b>", style_table_cell),
            Paragraph("Dokumen PRD (Tabel User Stories, Non-Fungsional, & Arsitektur AI).", style_table_cell),
            Paragraph('"Berdasarkan standar IEEE 830, kebutuhan sistem HomeCare Service dirumuskan ke dalam tiga pilar utama:<br/>'
                      '1. <b>Kebutuhan Fungsional:</b> Mencakup sistem multi-role (Customer, Teknisi, dan Admin). Customer dapat memesan servis, mengunggah bukti pembayaran, melacak status perbaikan secara real-time, dan memberikan ulasan. Teknisi dapat memperbarui laporan servis fisik, dan Admin memiliki dashboard analitik omset serta data master.<br/>'
                      '2. <b>Kebutuhan Non-Fungsional:</b> Aplikasi dibangun berbasis Web Responsive (Bootstrap 5), mengutamakan keamanan data melalui autentikasi sesi dan enkripsi sandi, serta responsivitas sistem di bawah 2 detik.<br/>'
                      '3. <b>Peran Strategis Fitur AI:</b> Fitur AI pada HomeCare Service bukan sekadar gimmick, melainkan modul inti: Pertama, <b>AI Diagnosis Kerusakan</b> yang menganalisis gejala kerusakan alat dan memberikan estimasi biaya wajar dengan klausul disclaimer teknisi. Kedua, <b>HomeCare AI Virtual Assistant</b> berbasis LLM dengan kapabilitas Database Context Retrieval untuk menjawab pertanyaan faktual seputar pesanan dan omset."', style_spoken)
        ],
        [
            Paragraph("<b>03:30 - 04:30</b>", style_table_cell),
            Paragraph("Slide Roadmap Pengembangan & Penutup.", style_table_cell),
            Paragraph('"Dengan tersusunnya PRD ini, seluruh acuan rekayasa perangkat lunak telah terdefinisi secara terukur. Pada tahap berikutnya, saya akan mentransformasikan kebutuhan ini ke dalam rancangan arsitektur berorientasi objek melalui pemodelan 4 diagram UML utama serta prototipe interaktif UI/UX di Figma.<br/><br/>'
                      'Demikian pemaparan Video Progres 1 ini saya sampaikan. Terima kasih atas perhatian Bapak Yudhistira dan rekan-rekan. Wassalamu’alaikum Warahmatullahi Wabarakatuh."', style_spoken)
        ]
    ]

    t_v1 = Table(v1_table, colWidths=[2.2 * cm, 4.3 * cm, 11.5 * cm])
    t_v1.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_secondary),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, c_light]),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))
    elements.append(t_v1)
    elements.append(PageBreak())

    # ── BAB III: VIDEO 2 — PROGRES 2 (UML & UI/UX SDD) ──
    elements.append(Paragraph("BAB III: NASKAH VIDEO PROGRES 2 — PEMODELAN UML & UI/UX (SDD)", style_h1))
    elements.append(Paragraph("<b>Target Durasi:</b> 5 – 7 Menit &nbsp;|&nbsp; <b>Bobot Nilai Video:</b> 30%", style_meta_label))
    elements.append(Paragraph("<b>Fokus Materi:</b> Membedah 4 Diagram UML Utama (Use Case, Activity, Class, Sequence) secara runut, kepatuhan prinsip SOLID, alur integrasi data AI, dan walkthrough prototipe interaktif Figma.", style_body))

    v2_table = [
        [Paragraph("<b>Waktu</b>", style_table_header), Paragraph("<b>Tampilan Layar (Visual)</b>", style_table_header), Paragraph("<b>Naskah Narasi Lisan Lengkap (Kata-per-Kata)</b>", style_table_header)],
        [
            Paragraph("<b>00:00 - 00:40</b>", style_table_cell),
            Paragraph("Cover SDD, Judul Pemodelan UML HomeCare Service, Nama & Webcam.", style_table_cell),
            Paragraph('"Assalamu’alaikum Warahmatullahi Wabarakatuh. Bertemu kembali dengan saya, <b>Qonita Goniyun Ulum</b>, dalam Video Progres ke-2 Mata Kuliah Rekayasa Perangkat Lunak.<br/><br/>'
                      'Pada tahap ini, saya menyajikan dokumen <i>Software Design Description</i> (SDD) yang membedah struktur statis dan perilaku dinamis sistem HomeCare Service melalui 4 Diagram UML utama yang mematuhi standar rekayasa perangkat lunak modern serta walkthrough prototipe interaktif UI/UX di Figma."', style_spoken)
        ],
        [
            Paragraph("<b>00:40 - 02:00</b>", style_table_cell),
            Paragraph("Diagram Use Case & Tabel Skenario Aktor (Customer, Teknisi, Admin).", style_table_cell),
            Paragraph('"Mari kita telaah <b>Use Case Diagram</b> sistem. Sistem ini mengidentifikasi 3 aktor utama:<br/>'
                      '• <b>Customer:</b> Melakukan registrasi/login, konsultasi AI Diagnosis Kerusakan, membuat pesanan servis, membayar pesanan, memberikan rating, serta berinteraksi dengan chatbot HomeCare AI.<br/>'
                      '• <b>Teknisi:</b> Mengelola pesanan servis yang ditugaskan, memperbarui status pengerjaan, dan menginput laporan servis fisik.<br/>'
                      '• <b>Admin:</b> Memvalidasi pembayaran, menetapkan teknisi, mengonfigurasi multi-provider AI, dan memantau analitik omset.<br/>'
                      'Seluruh use case transaksional mewarisi relasi include terhadap use case Autentikasi Pengguna."', style_spoken)
        ],
        [
            Paragraph("<b>02:00 - 03:15</b>", style_table_cell),
            Paragraph("Activity Diagram alur konsultasi AI dan pemesanan servis.", style_table_cell),
            Paragraph('"<b>Activity Diagram</b> ini menggambarkan alur kerja kolaboratif antara pengguna dan modul AI.<br/>'
                      'Alur dimulai saat Customer memilih kategori alat dan mengisi keluhan. Request diteruskan controller backend ke <code>AIService</code>. Sistem melakukan validasi token dan prompt engineering terstruktur ke AI Engine. Hasil berupa estimasi biaya, penyebab kerusakan, dan saran teknisi ditampilkan ke layar. Customer dapat secara langsung menekan tombol Call-to-Action \'Buat Pesanan Servis\' yang otomatis mem-passing data alat ke form pesanan."', style_spoken)
        ],
        [
            Paragraph("<b>03:15 - 04:30</b>", style_table_cell),
            Paragraph("Class Diagram terperinci (Model, View, Controller, Service).", style_table_cell),
            Paragraph('"Beralih ke struktur statis pada <b>Class Diagram</b>. Sistem mengadopsi pola arsitektur <b>Model-View-Controller (MVC)</b> yang mematuhi prinsip-prinsip <b>SOLID</b>:<br/>'
                      '• <i>Single Responsibility Principle (SRP):</i> Pemisahan tugas secara tegas antara Controller HTTP (<code>AIChatController</code>, <code>OrderController</code>), Model data (<code>ServiceOrder</code>, <code>Payment</code>), dan Service eksternal (<code>AIService</code>, <code>DatabaseContextService</code>).<br/>'
                      '• Modul AI diisolasi dalam <code>AIService</code> untuk mendukung multi-provider (Gemini, NVIDIA NIM, Groq, OpenAI). Kardinalitas dirancang presisi, di mana satu Customer memiliki banyak ServiceOrder, dan tiap ServiceOrder terhubung dengan satu Payment dan satu ServiceReport."', style_spoken)
        ],
        [
            Paragraph("<b>04:30 - 05:45</b>", style_table_cell),
            Paragraph("Sequence Diagram alur pertukaran pesan Chatbot AI.", style_table_cell),
            Paragraph('"Pada <b>Sequence Diagram</b>, kita membedah alur pertukaran pesan asinkronus pada <b>HomeCare AI Assistant</b>:<br/>'
                      '1. Customer mengirim pertanyaan via AJAX.<br/>'
                      '2. Router meneruskan ke <code>AIChatController</code> pada method <code>sendMessage()</code>.<br/>'
                      '3. Controller memanggil <code>DatabaseContextService</code> untuk menarik ringkasan metrik faktual database (pesanan lunas, teknisi, alat).<br/>'
                      '4. Konteks diinjeksikan bersama riwayat percakapan ke AI Service menuju LLM API endpoint.<br/>'
                      '5. Setelah LLM menghasilkan respons, controller menyimpan pesan ke tabel <code>chat_messages</code> dan mengirimkan JSON response.<br/>'
                      '6. Frontend merender bubble dan langsung menonaktifkan indikator loading."', style_spoken)
        ],
        [
            Paragraph("<b>05:45 - 07:00</b>", style_table_cell),
            Paragraph("Walkthrough interaktif Prototipe Figma & Penutup.", style_table_cell),
            Paragraph('"Selanjutnya kita tinjau prototipe interaktif di Figma. Desain mengusung konsep modern, bersih, dan intuitif dengan palet biru profesional yang mencerminkan keandalan servis.<br/>'
                      'Di sini terlihat flow interaksi: landing page, form diagnosis interaktif, floating chatbot di sudut kanan bawah yang responsif, hingga dashboard pelacakan pesanan. Seluruh komponen dirancang berbasis grid 12-kolom yang siap diimplementasikan pada Bootstrap 5.<br/><br/>'
                      'Perancangan SDD dan Figma ini memastikan tahap implementasi kode memiliki cetak biru yang presisi. Terima kasih, Wassalamu’alaikum Warahmatullahi Wabarakatuh."', style_spoken)
        ]
    ]

    t_v2 = Table(v2_table, colWidths=[2.2 * cm, 4.3 * cm, 11.5 * cm])
    t_v2.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_secondary),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, c_light]),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))
    elements.append(t_v2)
    elements.append(PageBreak())

    # ── BAB IV: VIDEO 3 — VIDEO FINAL (LIVE DEMO & CI/CD) ──
    elements.append(Paragraph("BAB IV: NASKAH VIDEO FINAL — LIVE DEMO SISTEM UTUH, TESTING & CI/CD", style_h1))
    elements.append(Paragraph("<b>Target Durasi:</b> 8 – 12 Menit &nbsp;|&nbsp; <b>Bobot Nilai Video:</b> 50% (Paling Menentukan!)", style_meta_label))
    elements.append(Paragraph("<b>Fokus Materi:</b> Elevator Pitch, arsitektur kode bersih, <b>Live Demo Real-Time</b> (bukan screenshot!), pengujian AI terintegrasi database, verifikasi Unit Test otomatis, status hijau CI/CD GitHub Actions, dan refleksi pembelajaran mandiri.", style_body))

    v3_table = [
        [Paragraph("<b>Waktu</b>", style_table_header), Paragraph("<b>Tampilan Layar (Visual)</b>", style_table_header), Paragraph("<b>Naskah Narasi Lisan Lengkap (Kata-per-Kata)</b>", style_table_header)],
        [
            Paragraph("<b>00:00 - 01:00</b>", style_table_cell),
            Paragraph("Slide Judul Video Final, Nama: Qonita Goniyun Ulum, NIM, Kelas, & Webcam aktif.", style_table_cell),
            Paragraph('"Assalamu’alaikum Warahmatullahi Wabarakatuh. Selamat pagi/siang kepada Bapak Yudhistira Arie Wijaya, M.Kom., CDS., dan seluruh pemirsa.<br/><br/>'
                      'Saya <b>Qonita Goniyun Ulum</b> mempersembahkan <b>Video Final Demonstrasi Sistem dan Pengujian Utuh Proyek Akhir Rekayasa Perangkat Lunak</b> dengan judul aplikasi: <b>HomeCare Service — Sistem Informasi Layanan Perbaikan Peralatan Rumah Tangga Cerdas Terintegrasi Artificial Intelligence</b>.<br/><br/>'
                      'Sebagai Full-Stack Software Engineer tunggal dalam proyek mandiri ini, saya telah menyelesaikan seluruh tahapan SDLC dari inisiasi PRD, pemodelan UML, konstruksi kode bersih berbasis arsitektur MVC, integrasi multi-provider AI, hingga pengujian otomatis dan pipeline DevOps CI/CD."', style_spoken)
        ],
        [
            Paragraph("<b>01:00 - 02:00</b>", style_table_cell),
            Paragraph("Buka VS Code (tampilkan folder MVC: Controllers, Models, Views, Services) & phpMyAdmin ERD.", style_table_cell),
            Paragraph('"Sebelum masuk ke live demo, mari kita lihat arsitektur kode di VS Code. Aplikasi ini dibangun dengan arsitektur <b>MVC murni (Model-View-Controller)</b>:<br/>'
                      '• Folder <code>app/Controllers</code> menangani HTTP request seperti <code>AIChatController</code>, <code>AIDiagnosisController</code>, dan <code>OrderController</code>.<br/>'
                      '• Folder <code>app/Models</code> merepresentasikan entitas database (<code>User</code>, <code>Customer</code>, <code>ServiceOrder</code>, <code>Payment</code>).<br/>'
                      '• Folder <code>app/Services</code> mengisolasi fungsionalitas kompleks (<code>AIService</code> dan <code>DatabaseContextService</code>).<br/>'
                      'Basis data relasional MySQL terdiri dari 11 tabel yang saling terhubung dengan integritas foreign key."', style_spoken)
        ],
        [
            Paragraph("<b>02:00 - 04:30</b>", style_table_cell),
            Paragraph("Live Demo Browser: Login Customer (Qonita Goniyun Ulum) -> Menu AI Diagnosis -> Booking Servis.", style_table_cell),
            Paragraph('"Sekarang kita mulai <b>Live Demo secara langsung dan real-time</b>.<br/>'
                      'Saat ini saya masuk sebagai Customer. Fitur unggulan pertama adalah <b>AI Diagnosis Kerusakan</b>. Mari kita simulasikan: Pengguna memiliki AC Daikin dengan keluhan: <i>\'AC menyala tetapi tidak dingin dan mengeluarkan hembusan angin bau apek\'</i>.<br/><br/>'
                      'Kita klik tombol <b>Analisis dengan AI</b>. Perhatikan: AI mengidentifikasi kerusakan pada freon atau filter evaporator kotor, menentukan tingkat prioritas penanganan, memberikan estimasi kisaran biaya perbaikan, serta klausul disclaimer resmi teknisi.<br/><br/>'
                      'Customer cukup klik tombol <b>\'Buat Pesanan Servis\'</b>, sistem otomatis mem-passing data alat ke formulir pesanan. Pengguna tinggal melengkapi alamat dan tanggal, lalu pesanan resmi tersimpan di sistem."', style_spoken)
        ],
        [
            Paragraph("<b>04:30 - 06:45</b>", style_table_cell),
            Paragraph("Live Demo Chatbot AI (/customer/ai-chat): Uji pertanyaan live omset, pesanan, rincian omset, dan katalog alat.", style_table_cell),
            Paragraph('"Berikutnya fitur inti kedua: <b>HomeCare AI Assistant</b> yang terintegrasi langsung dengan basis data live sistem. Mari kita buktikan kecerdasan sistem:<br/>'
                      '1. <i>Tanya: \'Berapa jumlah pesanan?\'</i> -> AI menjawab to-the-point: \'Jumlah pesanan saat ini adalah sebanyak 11 pesanan.\'<br/>'
                      '2. <i>Tanya: \'Berapa jumlah omset?\'</i> -> AI menjawab: \'Total omset dari semua pesanan yang sudah dibayar saat ini adalah sebesar Rp 1.450.000 dari 6 pesanan yang sudah lunas.\'<br/>'
                      '3. <i>Tanya: \'Rincikan omset\'</i> -> Lihat hasilnya! AI langsung membeberkan daftar lengkap 6 transaksi pesanan lunas beserta nomor order, alat, metode bayar, nominal, dan nama pelanggannya.<br/>'
                      '4. <i>Tanya: \'Peralatan apa saja yang dilayani?\'</i> -> AI menampilkan katalog alat dan estimasi biaya perbaikan.<br/>'
                      'Perhatikan: Begitu AI selesai menjawab, indikator memproses langsung hilang seketika dan tombol Kirim kembali aktif tanpa delay."', style_spoken)
        ],
        [
            Paragraph("<b>06:45 - 08:30</b>", style_table_cell),
            Paragraph("Live Demo Role Teknisi (Update Laporan Servis) & Admin (Validasi Bayar & Konfigurasi AI).", style_table_cell),
            Paragraph('"Beralih ke sisi operasional:<br/>'
                      '1. <b>Role Teknisi:</b> Teknisi dapat memantau tugas servis, memperbarui status pengerjaan saat di lokasi, dan mengunggah laporan servis fisik akhir.<br/>'
                      '2. <b>Role Admin:</b> Admin memiliki ringkasan analitik statistik pesanan, memvalidasi bukti transfer pembayaran menjadi status \'LUNAS\', serta manajemen master data.<br/>'
                      '3. <b>Konfigurasi AI:</b> Admin memiliki halaman khusus untuk mengatur API Key dan memilih provider AI aktif (Google Gemini, NVIDIA NIM, Groq, OpenAI) dengan fitur tes koneksi real-time."', style_spoken)
        ],
        [
            Paragraph("<b>08:30 - 10:00</b>", style_table_cell),
            Paragraph("Buka Terminal (eksekusi Unit Test) & Buka GitHub Tab Actions (Status Hijau Passing).", style_table_cell),
            Paragraph('"Kualitas perangkat lunak diuji melalui kehandalan kodenya. Di terminal, kita jalankan pengujian unit otomatis.<br/>'
                      '<i>(Jalankan test runner, tunjukkan hasil: All tests passed)</i>. Seluruh skenario fungsionalitas lulus 100%.<br/><br/>'
                      'Selanjutnya, kita verifikasi otomasi <b>CI/CD Pipeline pada GitHub Actions</b> via berkas <code>.github/workflows/ci.yml</code>. Pada tab Actions repositori GitHub saya, dapat kita lihat riwayat commit rapi dan seluruh pipeline build dan test berstatus <b>HIJAU (Passing)</b>. Ini membuktikan kode siap di-deploy secara reliabel."', style_spoken)
        ],
        [
            Paragraph("<b>10:00 - 12:00</b>", style_table_cell),
            Paragraph("Tampilan Webcam dominan -> Buka README.md GitHub -> Slide Penutup.", style_table_cell),
            Paragraph('"<b>Refleksi Pembelajaran Mandiri:</b> Pengerjaan proyek akhir ini secara mandiri melatih kompetensi saya mentransformasikan kebutuhan abstrak menjadi spesifikasi PRD, arsitektur SOLID, hingga implementasi kode web bersih. Tantangan terbesar menghubungkan LLM dengan database relasional teratasi dengan baik melalui perancangan <code>DatabaseContextService</code> tanpa halusinasi.<br/><br/>'
                      'Seluruh kode sumber, dokumentasi PRD/SDD, dan tautan ketiga video telah tersusun rapi di repositori publik GitHub saya: <code>github.com/[username]/proyek-rpl-homecare-service</code>.<br/><br/>'
                      'Terima kasih sebesar-besarnya kepada Bapak Yudhistira Arie Wijaya atas bimbingan selama satu semester ini. Wassalamu’alaikum Warahmatullahi Wabarakatuh."', style_spoken)
        ]
    ]

    t_v3 = Table(v3_table, colWidths=[2.2 * cm, 4.3 * cm, 11.5 * cm])
    t_v3.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_primary),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, c_light]),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 5),
        ('RIGHTPADDING', (0,0), (-1,-1), 5),
    ]))
    elements.append(t_v3)
    elements.append(PageBreak())

    # ── BAB V: PANDUAN TEKNIS REKAMAN OBS ──
    elements.append(Paragraph("BAB V: PANDUAN TEKNIS REKAMAN OBS STUDIO & TIPS SKOR MAKSIMAL", style_h1))
    elements.append(Paragraph(
        "Untuk memastikan video Anda memperoleh <b>Skor 4 (Maksimal)</b> pada rubrik penilaian OBE, ikuti konfigurasi teknis berikut:", style_body
    ))

    obs_tips = [
        [Paragraph("<b>Pengaturan OBS</b>", style_table_header), Paragraph("<b>Petunjuk Praktis Pelaksanaan</b>", style_table_header)],
        [Paragraph("<b>Resolusi Kanvas</b>", style_table_cell), Paragraph("Atur Base Canvas Resolution dan Output Resolution di angka <b>1920x1080 (1080p Full HD)</b> pada 30 fps atau 60 fps.", style_table_cell)],
        [Paragraph("<b>Webcam Overlay (PiP)</b>", style_table_cell), Paragraph("Tempatkan webcam di pojok kanan bawah atau kanan atas dengan ukuran proporsional (sekitar 15-20% layar). Pastikan wajah disinari lampu terang dan tidak menutupi tombol navigasi sistem.", style_table_cell)],
        [Paragraph("<b>Audio Filter Mic</b>", style_table_cell), Paragraph("Klik kanan pada Mic/Aux di OBS -> Filters -> Tambahkan <b>Noise Suppression (mode RNNoise)</b> agar suara kipas/lingkungan hilang, serta tambahkan <b>Compressor</b> agar volume suara stabil.", style_table_cell)],
        [Paragraph("<b>Urutan Tab Demo Browser</b>", style_table_cell), Paragraph("Siapkan tab browser secara berurutan: Tab 1 (Customer Diagnosis), Tab 2 (Chatbot AI /customer/ai-chat), Tab 3 (Admin Dashboard & AI Config), Tab 4 (VS Code), Tab 5 (GitHub Actions).", style_table_cell)],
        [Paragraph("<b>Gaya Penyampaian</b>", style_table_cell), Paragraph("Bicaralah dengan intonasi mantap, percaya diri, dan alami. Hindari membaca teks kata demi kata secara kaku agar dinilai menguasai sistem secara mendalam.", style_table_cell)]
    ]
    t_obs = Table(obs_tips, colWidths=[5.0 * cm, 13.0 * cm])
    t_obs.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_secondary),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, c_light]),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
    ]))
    elements.append(t_obs)

    # Build document
    doc.build(elements, canvasmaker=NumberedCanvas)
    print(f"Primary PDF generated successfully at: {target_primary}")

    # Copy to additional destinations
    for extra_dest in output_paths[1:]:
        try:
            with open(target_primary, 'rb') as src, open(extra_dest, 'wb') as dst:
                dst.write(src.read())
            print(f"Copied PDF successfully to: {extra_dest}")
        except Exception as e:
            print(f"Failed to copy to {extra_dest}: {e}")

if __name__ == '__main__':
    paths = [
        r"C:\xampp\htdocs\jasa peralatan runah tangga\Naskah_Script_Video_Proyek_Akhir_HomeCare_Service.pdf",
        r"D:\A KULIAH\Semester 3\Perangkat Rekayasa Lunak\Naskah_Script_Video_Proyek_Akhir_HomeCare_Service.pdf"
    ]
    create_script_pdf(paths)

