<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';

// Pastikan ID kategori dikirim melalui URL
if (isset($_GET['id'])) {
    $category_id = $conn->real_escape_string($_GET['id']); // Amankan ID

    // Query untuk menghapus data kategori
    $sql = "DELETE FROM categories WHERE category_id = '$category_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Kategori berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        // Jika gagal, bisa jadi karena ada produk yang masih menggunakan kategori ini
        $_SESSION['message'] = "Error menghapus kategori: " . $conn->error . ". Pastikan tidak ada produk yang terhubung dengan kategori ini.";
        $_SESSION['message_type'] = "danger";
    }
} else {
    $_SESSION['message'] = "ID Kategori tidak ditemukan untuk dihapus.";
    $_SESSION['message_type'] = "danger";
}

// Arahkan kembali ke halaman daftar kategori
header("Location: categories.php");
exit();
?>