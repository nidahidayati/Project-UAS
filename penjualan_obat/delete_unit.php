<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php'; // Untuk menampilkan pesan

if (isset($_GET['id'])) {
    $unit_id = $conn->real_escape_string($_GET['id']);

    // Query DELETE dari tabel 'units'
    // === PENTING: Hapus berdasarkan 'unit_id' ===
    $sql = "DELETE FROM units WHERE unit_id = '$unit_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Satuan berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        // Beri pesan yang lebih informatif jika gagal karena foreign key (misal: produk masih pakai satuan ini)
        if ($conn->errno == 1451) { // MySQL error code for foreign key constraint fail
            $_SESSION['message'] = "Error menghapus satuan: Satuan ini masih digunakan oleh beberapa produk.";
        } else {
            $_SESSION['message'] = "Error menghapus satuan: " . $conn->error;
        }
        $_SESSION['message_type'] = "danger";
    }
} else {
    $_SESSION['message'] = "ID Satuan tidak ditemukan untuk dihapus.";
    $_SESSION['message_type'] = "danger";
}

// Redirect kembali ke halaman daftar satuan
header("Location: units.php");
exit();
?>