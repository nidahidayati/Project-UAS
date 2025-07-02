<?php
// Pastikan session dimulai sebelum bisa dihancurkan
session_start();

// session_unset() akan menghapus semua variabel session
// Contoh: $_SESSION['user_id'], $_SESSION['username'], $_SESSION['role_id'] akan dihapus
session_unset();

// session_destroy() akan benar-benar menghancurkan session yang ada
// Ini akan mengakhiri sesi pengguna saat ini
session_destroy();

// Setelah session dihancurkan, arahkan (redirect) pengguna kembali ke halaman login
// Ini penting agar pengguna tidak bisa lagi mengakses halaman yang membutuhkan login
header("Location: login.php");
exit(); // Penting: Hentikan eksekusi script setelah melakukan redirect
?>