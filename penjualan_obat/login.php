<?php
// Mulai session PHP
// Ini penting untuk menyimpan status login pengguna
session_start();

// Termasuk (include) file koneksi database kita
// Pastikan path-nya benar sesuai lokasi file database.php
require_once 'config/database.php';

$error_message = ""; // Variabel untuk menyimpan pesan error jika ada

// Cek apakah form login sudah dikirim (dengan metode POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil username dari form dan amankan dari serangan SQL Injection
    // real_escape_string membersihkan karakter khusus yang bisa berbahaya
    $username = $conn->real_escape_string($_POST['username']);
    // Password tidak perlu diamankan dengan real_escape_string dulu
    // karena akan langsung diverifikasi dengan hash
    $password = $_POST['password'];

    // SQL Query untuk mencari user berdasarkan username yang dimasukkan
    // Kita ambil user_id, username, password (hash), dan role_id
    $sql = "SELECT user_id, username, password, role_id FROM users WHERE username = '$username'";
    $result = $conn->query($sql); // Jalankan query ke database

    // Cek apakah ada baris data yang ditemukan (username ada di database)
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Ambil data user dalam bentuk array asosiatif

        // Verifikasi password yang dimasukkan dengan hash password yang tersimpan di database
        // password_verify() membandingkan password asli dengan hash
        if (password_verify($password, $user['password'])) {
            // Jika password cocok (login berhasil):

            // Simpan informasi user ke dalam SESSION
            // Informasi ini akan dibawa ke halaman lain selama user login
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id']; // Simpan ID peran pengguna

            // Arahkan (redirect) pengguna ke halaman dashboard
            header("Location: dashboard.php");
            exit(); // Penting: hentikan eksekusi script setelah redirect
        } else {
            // Jika password tidak cocok
            $error_message = "Username atau password salah.";
        }
    } else {
        // Jika username tidak ditemukan di database
        $error_message = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Toko Obat Multi77</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        /* Gaya CSS tambahan untuk mengatur posisi form di tengah halaman */
        body {
            display: flex; /* Menggunakan flexbox untuk tata letak */
            justify-content: center; /* Menengahkan secara horizontal */
            align-items: center; /* Menengahkan secara vertikal */
            min-height: 100vh; /* Tinggi minimal 100% dari tinggi viewport */
            background-color: #f8f9fa; /* Warna latar belakang */
        }
        .login-container {
            background-color: white; /* Warna latar belakang kontainer login */
            padding: 30px; /* Jarak padding di dalam kontainer */
            border-radius: 8px; /* Sudut membulat */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Efek bayangan */
            width: 100%; /* Lebar kontainer 100% */
            max-width: 400px; /* Lebar maksimal kontainer */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center mb-4">Login Aplikasi</h2>

        <?php
        // Jika ada pesan error (misal: username/password salah), tampilkan di sini
        if (!empty($error_message)):
        ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>