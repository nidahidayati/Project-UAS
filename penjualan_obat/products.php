<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

// Pastikan hanya Admin atau Staf yang bisa mengakses halaman ini
// Sesuaikan role_id jika Anda memiliki role lain yang diizinkan melihat produk
if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2) { // Misal: 1=Admin, 2=Manajer/Owner/Staff
    $_SESSION['message'] = "Anda tidak memiliki akses untuk melihat produk.";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php");
    exit();
}

// Ambil filter status dari URL, defaultnya 'active'
$status_filter = $_GET['status'] ?? 'active';
$where_clause = ""; // Variabel untuk menyimpan kondisi WHERE

if ($status_filter == 'active') {
    $where_clause = " WHERE p.is_active = 1"; // Hanya tampilkan yang aktif
} elseif ($status_filter == 'inactive') {
    $where_clause = " WHERE p.is_active = 0"; // Hanya tampilkan yang tidak aktif
}
// Jika $status_filter adalah 'all', maka $where_clause tetap kosong, menampilkan semua

// Query untuk mengambil daftar produk
$sql = "SELECT p.*, c.category_name, u.unit_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN units u ON p.unit_id = u.unit_id
        $where_clause
        ORDER BY p.product_name ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Penjualan Obat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background-color: #f0f2f5; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .table img { max-width: 50px; height: auto; }
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
                        <a class="nav-link active" aria-current="page" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">Transaksi</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Laporan
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                            <li><a class="dropdown-item" href="sales_report.php">Laporan Penjualan</a></li>
                            </ul>
                    </li>
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarAdminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Admin
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarAdminDropdown">
                            <li><a class="dropdown-item" href="users.php">Manajemen Pengguna</a></li>
                            <li><a class="dropdown-item" href="categories.php">Manajemen Kategori</a></li>
                            <li><a class="dropdown-item" href="suppliers.php">Manajemen Pemasok</a></li>
                            <li><a class="dropdown-item" href="units.php">Manajemen Satuan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="purchases.php">Manajemen Pembelian</a></li>
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
        <h2>Manajemen Produk</h2>
        <?php include 'includes/message.php'; // Tampilkan pesan ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="add_product.php" class="btn btn-primary">Tambah Produk Baru</a>
            <div class="btn-group" role="group" aria-label="Filter produk">
                <a href="products.php?status=active" class="btn <?php echo ($status_filter == 'active') ? 'btn-primary' : 'btn-outline-primary'; ?>">Aktif</a>
                <a href="products.php?status=inactive" class="btn <?php echo ($status_filter == 'inactive') ? 'btn-primary' : 'btn-outline-primary'; ?>">Tidak Aktif</a>
                <a href="products.php?status=all" class="btn <?php echo ($status_filter == 'all') ? 'btn-primary' : 'btn-outline-primary'; ?>">Semua</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID Produk</th>
                        <th>Nama Produk</th>
                        <th>Kode Produk</th>
                        <th>Stok</th>
                        <th>Harga Jual</th>
                        <th>Harga Beli</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Status</th> <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_stock']); ?></td>
                                <td>Rp <?php echo number_format($row['product_price'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($row['product_purchase_price'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['unit_name']); ?></td>
                                <td>
                                    <?php
                                    if ($row['is_active'] == 1) {
                                        echo '<span class="badge bg-success">Aktif</span>';
                                    } else {
                                        echo '<span class="badge bg-danger">Tidak Aktif</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete_product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus atau menonaktifkan produk ini? Tindakan ini tidak dapat dibatalkan untuk penghapusan permanen.');">Hapus</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="10" class="text-center">Belum ada produk.</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
