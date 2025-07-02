<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

// Pastikan hanya Admin yang bisa mengakses halaman ini
if ($_SESSION['role_id'] != 1) { // role_id 1 = Admin
    $_SESSION['message'] = "Anda tidak memiliki akses ke halaman ini.";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php");
    exit();
}

// Query untuk mengambil daftar pembelian
$sql = "SELECT
            p.purchase_id,
            p.purchase_date,
            p.total_cost,
            s.supplier_name,
            u.username,
            p.invoice_number,
            p.created_at
        FROM
            purchases p
        JOIN
            suppliers s ON p.supplier_id = s.supplier_id
        JOIN
            users u ON p.user_id = u.user_id
        ORDER BY
            p.purchase_date DESC"; // Urutkan dari pembelian terbaru

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pembelian - Penjualan Obat</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Produk</a>
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
                        <a class="nav-link dropdown-toggle active" aria-current="page" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Admin
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
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
        <h2>Daftar Pembelian Barang</h2>

        <?php include 'includes/message.php'; // Tampilkan pesan ?>

        <a href="add_purchase.php" class="btn btn-primary mb-3">Catat Pembelian Baru</a>

        <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID Pembelian</th>
                        <th>Tanggal Pembelian</th>
                        <th>No. Faktur</th>
                        <th>Total Biaya</th>
                        <th>Pemasok</th>
                        <th>Dicatat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['purchase_id']; ?></td>
                        <td><?php echo $row['purchase_date']; ?></td>
                        <td><?php echo htmlspecialchars($row['invoice_number'] ?? '-'); ?></td>
                        <td>Rp <?php echo number_format($row['total_cost'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td>
                            <a href="view_purchase.php?id=<?php echo $row['purchase_id']; ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                            </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Belum ada catatan pembelian barang. Silakan catat pembelian baru.</div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>