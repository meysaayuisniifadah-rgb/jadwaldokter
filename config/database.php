<?php
/**
 * File Konfigurasi Database MySQL
 * Sistem Informasi Penjadwalan Praktik Dokter
 */

$host     = "localhost";
$username = "root";
$password = ""; // Default password XAMPP adalah kosong
$database = "db_jadwaldokter";

// Membuat koneksi ke database MySQL
$conn = mysqli_connect($host, $username, $password, $database);

// Periksa apakah koneksi berhasil
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set character set ke utf8mb4 agar mendukung format karakter modern
mysqli_set_charset($conn, "utf8mb4");
?>