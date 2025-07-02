<?php
// Pastikan session PHP sudah dimulai di setiap halaman yang menggunakan file ini.
// session_start() harus dipanggil sebelum mengakses $_SESSION.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah variabel session 'user_id' sudah ada atau belum.
// Jika 'user_id' tidak ada, berarti pengguna belum login.
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, arahkan (redirect) pengguna ke halaman login.
    header("Location: login.php");
    exit(); // Penting: Hentikan eksekusi script setelah melakukan redirect.
}

// Bagian opsional: Anda bisa menambahkan logika di sini untuk memeriksa peran (role) pengguna.
// Misalnya, jika halaman ini hanya boleh diakses oleh Administrator (role_id = 1).
// Untuk contoh ini, kita asumsikan role_id 1 adalah Administrator.
// if (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 1) {
//     // Jika pengguna bukan Administrator, arahkan ke dashboard atau halaman lain
//     // yang berhak diakses (misalnya, 'unauthorized.php' atau kembali ke 'dashboard.php').
//     header("Location: dashboard.php"); // Contoh: kembalikan ke dashboard
//     exit();
// }

// Jika kode sampai di sini, berarti pengguna sudah login dan (jika ada) memenuhi kriteria peran.
// Halaman yang memanggil 'auth_check.php' bisa melanjutkan eksekusinya.
?>