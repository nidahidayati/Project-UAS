<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

if (isset($_GET['id'])) {
    $user_id = $conn->real_escape_string($_GET['id']);

    // Pastikan pengguna tidak menghapus dirinya sendiri jika itu adalah satu-satunya admin, dll.
    // Untuk saat ini, kita izinkan. Di aplikasi sungguhan, perlu validasi lebih ketat.
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['message'] = "Anda tidak bisa menghapus akun Anda sendiri saat ini.";
        $_SESSION['message_type'] = "danger";
        header("Location: users.php");
        exit();
    }

    // Query DELETE dari tabel 'users'
    $sql = "DELETE FROM users WHERE user_id = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Pengguna berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        // Beri pesan yang lebih informatif jika gagal karena foreign key (misal: user terkait dengan transaksi)
        if ($conn->errno == 1451) { // MySQL error code for foreign key constraint fail
            $_SESSION['message'] = "Error menghapus pengguna: Pengguna ini masih terhubung dengan data lain (misal: transaksi).";
        } else {
            $_SESSION['message'] = "Error menghapus pengguna: " . $conn->error;
        }
        $_SESSION['message_type'] = "danger";
    }
} else {
    $_SESSION['message'] = "ID Pengguna tidak ditemukan untuk dihapus.";
    $_SESSION['message_type'] = "danger";
}

// Redirect kembali ke halaman daftar pengguna
header("Location: users.php");
exit();
?>