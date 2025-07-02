<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php'; // Untuk menampilkan pesan

$unit_id = null; // Inisialisasi variabel
$unit_name = '';
$description = '';

// Ambil ID dari URL (saat user klik "Edit")
if (isset($_GET['id'])) {
    $unit_id = $conn->real_escape_string($_GET['id']);
    // Query untuk mengambil data satuan berdasarkan ID
    $sql = "SELECT * FROM units WHERE unit_id = '$unit_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // === PENTING: Pastikan nama kolom yang diambil adalah 'unit_name' ===
        $unit_name = $row['unit_name'];
        $description = $row['description'];
    } else {
        $_SESSION['message'] = "Satuan tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header("Location: units.php");
        exit();
    }
}

// Proses saat form disubmit (saat user klik "Update")
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // === PENTING: Ambil unit_id dari input hidden, dan unit_name dari form ===
    $unit_id = $conn->real_escape_string($_POST['unit_id']);
    $unit_name = $_POST['unit_name'];
    $description = $_POST['description'];

    $unit_name_safe = $conn->real_escape_string($unit_name);
    $description_safe = $conn->real_escape_string($description);

    // Query UPDATE ke tabel 'units'
    // === PENTING: Set 'unit_name' dan WHERE 'unit_id' ===
    $sql = "UPDATE units SET unit_name = '$unit_name_safe', description = '$description_safe', updated_at = CURRENT_TIMESTAMP WHERE unit_id = '$unit_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Satuan '<b>" . htmlspecialchars($unit_name) . "</b>' berhasil diubah.";
        $_SESSION['message_type'] = "success";
        header("Location: units.php");
        exit();
    } else {
        $_SESSION['message'] = "Error mengubah satuan: " . $conn->error;
        $_SESSION['message_type'] = "danger";
        header("Location: units.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Satuan - Penjualan Obat</title>
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
                            <li><a class="dropdown-item" href="suppliers.php">Manajemen Pemasok</a></li>
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
        <h2>Edit Satuan</h2>
        <form action="" method="post">
            <input type="hidden" name="unit_id" value="<?php echo htmlspecialchars($unit_id); ?>">

            <div class="mb-3">
                <label for="unit_name" class="form-label">Nama Satuan</label>
                <input type="text" class="form-control" id="unit_name" name="unit_name" value="<?php echo htmlspecialchars($unit_name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Satuan</button>
            <a href="units.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>