<?php
// File ini akan menampilkan pesan sukses atau error yang disimpan di session.
// Biasanya dipanggil di bagian atas halaman yang ingin menampilkan pesan.

// Cek apakah ada pesan yang disimpan di session
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; // Default type is info

    // Tampilkan alert Bootstrap
    echo '<div class="alert alert-' . $message_type . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($message); // Amankan pesan dari karakter berbahaya
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';

    // Hapus pesan dari session agar tidak muncul lagi setelah refresh
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>