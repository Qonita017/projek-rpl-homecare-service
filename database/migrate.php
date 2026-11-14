<?php
// Script untuk melakukan setup dan migrasi database otomatis
$host = 'localhost';
$user = 'root';
$pass = ''; // Biarkan kosong jika default XAMPP
$dbname = 'homecare_db';

try {
    // 1. Buat koneksi tanpa memilih database untuk membuat DB
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 2. Buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    echo "Database `$dbname` siap digunakan.\n<br>";
    
    // 3. Gunakan database yang baru dibuat
    $pdo->exec("USE `$dbname`");
    
    // 4. Baca isi dari schema.sql
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    // 5. Eksekusi query
    $pdo->exec($sql);
    echo "Migrasi berhasil! Semua tabel telah sukses dibuat.\n<br>";
    echo "<br><a href='../'>Kembali ke Aplikasi</a>";

} catch (PDOException $e) {
    die("Terjadi kesalahan pada Database: " . $e->getMessage() . "\n");
}
