<?php
// Jangan tampilkan error database langsung ke pengunjung website asli.
// Error_reporting(E_ALL); // Untuk saat belajar, boleh diaktifkan
// ini_set('display_errors', 1); // Untuk saat belajar, boleh diaktifkan

$host = "localhost"; // Alamat server database kamu, biasanya ini
$user = "root";      // Username database kamu, biasanya ini
$password = "";      // Password database kamu, kalau XAMPP/WAMP, biasanya kosong
$database = "penjualan_obat"; // Nama database yang kamu buat tadi

// Mencoba menghubungkan ke database
$conn = new mysqli($host, $user, $password, $database);

// Mengecek apakah koneksi berhasil atau ada masalah
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Kalau berhasil, tidak ada pesan apa-apa.
// Kamu bisa tambahkan echo "Koneksi berhasil!"; untuk uji coba, lalu hapus lagi.
?>