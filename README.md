# HomeCare Service

Sistem penerimaan jasa perbaikan dan perawatan peralatan rumah tangga yang dilengkapi fitur AI untuk diagnosis awal.

## Struktur Folder (MVC)
- `app/` : Berisi core logic aplikasi (Models, Views, Controllers, Config, Core).
- `public/` : Web root yang dapat diakses publik (index.php, css, js, assets).
- `routes/` : Berisi definisi routing aplikasi (web.php).

## Cara Menjalankan
1. Pastikan Anda sudah menginstall XAMPP/MAMP/LAMP.
2. Pastikan apache running.
3. Buka browser dan akses URL project, contoh: `http://localhost/jasa peralatan runah tangga/`
4. Server akan otomatis melakukan routing dari file `.htaccess` ke `public/index.php` tanpa perlu menuliskan `/public` di URL.

## Teknologi
- PHP Native (MVC Architecture)
- Bootstrap 5
- MySQL
