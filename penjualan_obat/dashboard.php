<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php'; // Pastikan file ini ada

// Statistik untuk Dashboard
$today = date('Y-m-d'); // Tanggal hari ini

// 1. Total Penjualan Hari Ini
$sql_sales_today = "SELECT SUM(total_amount) AS total_sales FROM transactions WHERE DATE(transaction_date) = '$today'";
$result_sales_today = $conn->query($sql_sales_today);
$total_sales_today = ($result_sales_today && $result_sales_today->num_rows > 0) ? ($result_sales_today->fetch_assoc()['total_sales'] ?? 0) : 0;

// 2. Total Transaksi Hari Ini
$sql_transactions_today = "SELECT COUNT(transaction_id) AS total_transactions FROM transactions WHERE DATE(transaction_date) = '$today'";
$result_transactions_today = $conn->query($sql_transactions_today);
$total_transactions_today = ($result_transactions_today && $result_transactions_today->num_rows > 0) ? ($result_transactions_today->fetch_assoc()['total_transactions'] ?? 0) : 0;

// 3. Total Pembelian Hari Ini
$sql_purchases_today = "SELECT SUM(total_cost) AS total_purchases FROM purchases WHERE DATE(purchase_date) = '$today'";
$result_purchases_today = $conn->query($sql_purchases_today);
$total_purchases_today = ($result_purchases_today && $result_purchases_today->num_rows > 0) ? ($result_purchases_today->fetch_assoc()['total_purchases'] ?? 0) : 0;

// 4. Jumlah Produk Dengan Stok Sedikit (Low Stock Alert)
$low_stock_threshold = 10; // Anda bisa mengubah ambang batas ini
$sql_low_stock = "SELECT COUNT(product_id) AS low_stock_count FROM products WHERE product_stock <= '$low_stock_threshold'";
$result_low_stock = $conn->query($sql_low_stock);
$low_stock_count = ($result_low_stock && $result_low_stock->num_rows > 0) ? ($result_low_stock->fetch_assoc()['low_stock_count'] ?? 0) : 0;

// 5. Total Produk Aktif
$sql_total_products = "SELECT COUNT(product_id) AS total_products_count FROM products";
$result_total_products = $conn->query($sql_total_products);
$total_products_count = ($result_total_products && $result_total_products->num_rows > 0) ? ($result_total_products->fetch_assoc()['total_products_count'] ?? 0) : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Penjualan Obat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background-color: #f0f2f5; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .card-title { font-size: 1rem; }
        .card-text { font-weight: bold; }
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
                        <a class="nav-link active" aria-current="page" href="dashboard.php">Dashboard</a>
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
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): // Hanya tampilkan jika Admin ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
        <h2>Dashboard</h2>

        <?php include 'includes/message.php'; // Tampilkan pesan ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Total Penjualan Hari Ini</h5>
                        <p class="card-text fs-3">Rp <?php echo number_format($total_sales_today, 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Total Transaksi Hari Ini</h5>
                        <p class="card-text fs-3"><?php echo $total_transactions_today; ?> Transaksi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-dark">
                    <div class="card-body">
                        <h5 class="card-title">Total Pembelian Hari Ini</h5>
                        <p class="card-text fs-3">Rp <?php echo number_format($total_purchases_today, 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Produk Stok Sedikit (<= <?php echo $low_stock_threshold; ?>)</h5>
                        <p class="card-text fs-3"><?php echo $low_stock_count; ?> Produk</p>
                        <?php if ($low_stock_count > 0): ?>
                            <p class="card-text"><small><a href="products.php" class="text-white text-decoration-underline">Lihat daftar produk</a></small></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-secondary">
                    <div class="card-body">
                        <h5 class="card-title">Total Produk Aktif</h5>
                        <p class="card-text fs-3"><?php echo $total_products_count; ?> Produk</p>
                    </div>
                </div>
            </div>
        </div>

        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>