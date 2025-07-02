<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php'; // Untuk menampilkan pesan

if (isset($_GET['id'])) {
    $supplier_id = $conn->real_escape_string($_GET['id']);

    // Query DELETE dari tabel 'suppliers'
    $sql = "DELETE FROM suppliers WHERE supplier_id = '$supplier_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Pemasok berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        // Beri pesan yang lebih informatif jika gagal karena foreign key (misal: produk masih pakai pemasok ini)
        if ($conn->errno == 1451) { // MySQL error code for foreign key constraint fail
            $_SESSION['message'] = "Error menghapus pemasok: Pemasok ini masih digunakan oleh beberapa produk atau transaksi.";
        } else {
            $_SESSION['message'] = "Error menghapus pemasok: " . $conn->error;
        }
        $_SESSION['message_type'] = "danger";
    }
} else {
    $_SESSION['message'] = "ID Pemasok tidak ditemukan untuk dihapus.";
    $_SESSION['message_type'] = "danger";
}

// Redirect kembali ke halaman daftar pemasok
header("Location: suppliers.php");
exit();
?>