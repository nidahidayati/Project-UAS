<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';

$category_id = null;
$category_name = '';
$description = '';

// Cek apakah ada ID kategori di URL (saat pertama kali dibuka)
if (isset($_GET['id'])) {
    $category_id = $conn->real_escape_string($_GET['id']); // Amankan ID
    $sql = "SELECT * FROM categories WHERE category_id = '$category_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $category_name = $row['category_name'];
        $description = $row['description'];
    } else {
        // Jika ID tidak ditemukan, arahkan kembali ke daftar kategori
        $_SESSION['message'] = "Kategori tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header("Location: categories.php");
        exit();
    }
}

// Cek apakah form sudah disubmit (untuk menyimpan perubahan)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $conn->real_escape_string($_POST['category_id']);
    $category_name = $_POST['category_name'];
    $description = $_POST['description'];

    // === PENTING: Lakukan sanitasi/pengamanan data ===
    $category_name_safe = $conn->real_escape_string($category_name);
    $description_safe = $conn->real_escape_string($description);

    // Query untuk update data
    $sql = "UPDATE categories SET category_name = '$category_name_safe', description = '$description_safe', updated_at = CURRENT_TIMESTAMP WHERE category_id = '$category_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Kategori '<b>" . htmlspecialchars($category_name) . "</b>' berhasil diubah.";
        $_SESSION['message_type'] = "success";
        header("Location: categories.php");
        exit();
    } else {
        $_SESSION['message'] = "Error mengubah kategori: " . $conn->error;
        $_SESSION['message_type'] = "danger";
        header("Location: categories.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - Penjualan Obat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background-color: #f0f2f5; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
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
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">Transaksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Laporan</a>
                    </li>
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" aria-current="page" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Admin
                        </a>
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
                    <li class="nav-item">
                        <a class="nav-link" href="#">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Edit Kategori</h2>
        <form action="" method="post">
            <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category_id); ?>">

            <div class="mb-3">
                <label for="category_name" class="form-label">Nama Kategori</label>
                <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category_name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Kategori</button>
            <a href="categories.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>