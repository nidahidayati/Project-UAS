<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

$user_id = null; // Inisialisasi variabel
$username = '';
$role_id = ''; // Default

// Ambil daftar role dari database
$sql_roles = "SELECT role_id, role_name FROM roles ORDER BY role_name ASC";
$result_roles = $conn->query($sql_roles);
$roles = [];
if ($result_roles->num_rows > 0) {
    while ($row = $result_roles->fetch_assoc()) {
        $roles[] = $row;
    }
}

// Ambil ID dari URL (saat user klik "Edit")
if (isset($_GET['id'])) {
    $user_id = $conn->real_escape_string($_GET['id']);
    // Query untuk mengambil data pengguna berdasarkan ID
    $sql = "SELECT user_id, username, role_id FROM users WHERE user_id = '$user_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
        $role_id = $row['role_id']; // Ambil role_id yang tersimpan
    } else {
        $_SESSION['message'] = "Pengguna tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header("Location: users.php");
        exit();
    }
} else {
    $_SESSION['message'] = "ID Pengguna tidak ditemukan.";
    $_SESSION['message_type'] = "danger";
    header("Location: users.php");
    exit();
}

// Proses saat form disubmit (saat user klik "Update")
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $conn->real_escape_string($_POST['user_id']);
    $username = $_POST['username'];
    $role_id = $_POST['role_id'];
    $password = $_POST['password']; // Password bisa kosong jika tidak diubah

    $username_safe = $conn->real_escape_string($username);
    $role_id_safe = $conn->real_escape_string($role_id);

    $sql_update = "UPDATE users SET
                        username = '$username_safe',
                        role_id = '$role_id_safe',
                        updated_at = CURRENT_TIMESTAMP";

    // Hanya update password jika password diisi di form
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_update .= ", password = '$hashed_password'";
    }

    $sql_update .= " WHERE user_id = '$user_id'";

    if ($conn->query($sql_update) === TRUE) {
        $_SESSION['message'] = "Pengguna '<b>" . htmlspecialchars($username) . "</b>' berhasil diubah.";
        $_SESSION['message_type'] = "success";
        header("Location: users.php");
        exit();
    } else {
        // Tangani error jika username sudah ada (contoh: UNIQUE constraint)
        if ($conn->errno == 1062) { // MySQL error code for Duplicate entry for key 'PRIMARY' or unique key
            $_SESSION['message'] = "Error: Username '<b>" . htmlspecialchars($username) . "</b>' sudah ada. Mohon gunakan username lain.";
        } else {
            $_SESSION['message'] = "Error mengubah pengguna: " . $conn->error;
        }
        $_SESSION['message_type'] = "danger";
        // Tidak redirect agar user bisa melihat error di halaman yang sama
        // header("Location: edit_user.php?id=" . $user_id);
        // exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - Penjualan Obat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background-color: #f0f2f5; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Toko Obat Multi77</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="transactions.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php">Laporan</a></li>
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" aria-current="page" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="users.php">Manajemen Pengguna</a></li>
                            <li><a class="dropdown-item" href="categories.php">Manajemen Kategori</a></li>
                            <li><a class="dropdown-item" href="suppliers.php">Manajemen Pemasok</a></li>
                            <li><a class="dropdown-item" href="units.php">Manajemen Satuan</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Edit Pengguna</h2>
        <?php include 'includes/message.php'; // Tampilkan pesan error/sukses ?>
        <form action="" method="post">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password (Kosongkan jika tidak diubah)</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="mb-3">
                <label for="role_id" class="form-label">Role</label>
                <select class="form-select" id="role_id" name="role_id" required>
                    <option value="">Pilih Role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['role_id']; ?>" <?php echo ($role['role_id'] == $role_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Pengguna</button>
            <a href="users.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>