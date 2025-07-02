<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

// Pastikan hanya Admin yang bisa mengakses halaman ini
if ($_SESSION['role_id'] != 1) { // role_id 1 = Admin
    $_SESSION['message'] = "Anda tidak memiliki akses untuk menghapus produk.";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $product_id = $conn->real_escape_string($_GET['id']);

    // --- Cek Keterkaitan Produk dengan Transaksi dan Pembelian ---
    // 1. Cek di transaction_items
    $sql_check_transactions = "SELECT COUNT(*) FROM transaction_items WHERE product_id = '$product_id'";
    $result_transactions = $conn->query($sql_check_transactions);
    $count_transactions = 0;
    if ($result_transactions && $result_transactions->num_rows > 0) {
        $count_transactions = $result_transactions->fetch_row()[0];
    }

    // 2. Cek di purchase_items
    $sql_check_purchases = "SELECT COUNT(*) FROM purchase_items WHERE product_id = '$product_id'";
    $result_purchases = $conn->query($sql_check_purchases);
    $count_purchases = 0;
    if ($result_purchases && $result_purchases->num_rows > 0) {
        $count_purchases = $result_purchases->fetch_row()[0];
    }

    // Ambil nama produk untuk pesan feedback
    $product_name_query = "SELECT product_name FROM products WHERE product_id = '$product_id'";
    $product_name_result = $conn->query($product_name_query);
    $product_name = ($product_name_result && $product_name_result->num_rows > 0) ? $product_name_result->fetch_assoc()['product_name'] : "Produk";


    // --- Logika Hapus atau Nonaktifkan ---
    if ($count_transactions > 0 || $count_purchases > 0) {
        // Jika produk sudah ada di transaksi atau pembelian, ubah statusnya menjadi tidak aktif (is_active = 0)
        $sql_update_status = "UPDATE products SET
                                is_active = 0,
                                updated_by = '" . $_SESSION['user_id'] . "',
                                updated_at = NOW()
                              WHERE product_id = '$product_id'";

        if ($conn->query($sql_update_status) === TRUE) {
            $_SESSION['message'] = "Produk <b>" . htmlspecialchars($product_name) . "</b> tidak dapat dihapus permanen karena sudah tercatat dalam transaksi/pembelian. Statusnya telah diubah menjadi **Tidak Aktif**.";
            $_SESSION['message_type'] = "warning";
        } else {
            $_SESSION['message'] = "Error saat menonaktifkan produk: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
    } else {
        // Jika produk BELUM pernah digunakan dalam transaksi atau pembelian, hapus permanen
        $sql_delete = "DELETE FROM products WHERE product_id = '$product_id'";
        if ($conn->query($sql_delete) === TRUE) {
            $_SESSION['message'] = "Produk <b>" . htmlspecialchars($product_name) . "</b> berhasil dihapus secara permanen.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error saat menghapus produk: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
    }
} else {
    $_SESSION['message'] = "ID Produk tidak diberikan.";
    $_SESSION['message_type'] = "danger";
}

// Redirect kembali ke halaman daftar produk
header("Location: products.php");
exit();
?>