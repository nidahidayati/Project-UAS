<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

// Hanya admin atau staf yang berwenang yang bisa melihat laporan
// Sesuaikan role_id jika Anda memiliki role lain yang diizinkan melihat laporan
if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2) { // Misal: 1=Admin, 2=Manajer/Owner
    $_SESSION['message'] = "Anda tidak memiliki akses untuk melihat laporan penjualan.";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php");
    exit();
}

$start_date = $_GET['start_date'] ?? date('Y-m-01'); // Default awal bulan
$end_date = $_GET['end_date'] ?? date('Y-m-d');     // Default hari ini

$sales_summary = [
    'total_transactions' => 0,
    'total_sales_amount' => 0,
    'total_profit_amount' => 0, // Akan dihitung jika ada product_purchase_price
];
$sales_data = [];

// Query untuk ringkasan penjualan
$sql_summary = "SELECT
                    COUNT(t.transaction_id) AS total_transactions,
                    SUM(t.total_amount) AS total_sales_amount
                FROM
                    transactions t
                WHERE
                    DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";

$result_summary = $conn->query($sql_summary);
if ($result_summary && $result_summary->num_rows > 0) {
    $summary_row = $result_summary->fetch_assoc();
    $sales_summary['total_transactions'] = $summary_row['total_transactions'] ?? 0;
    $sales_summary['total_sales_amount'] = $summary_row['total_sales_amount'] ?? 0;
}

// Query untuk detail transaksi (per item untuk menghitung profit)
// Untuk menghitung profit, kita perlu 'product_purchase_price' di tabel 'products'
// Jika Anda belum punya kolom 'product_purchase_price' di tabel 'products', profit akan dihitung 0 atau error.
// Jika kolom 'product_purchase_price' belum ada, silakan tambahkan ke tabel products:
// ALTER TABLE products ADD COLUMN product_purchase_price DECIMAL(10,2) DEFAULT 0.00;
// Kemudian update data produk yang sudah ada dengan harga beli.
$sql_detail = "SELECT
                    t.transaction_id,
                    t.transaction_date,
                    t.total_amount,
                    u.username AS cashier_name,
                    GROUP_CONCAT(
                        CONCAT(ti.quantity, 'x ', p.product_name, ' (@Rp ', ti.selling_price, ') (Subtotal: Rp ', ti.quantity * ti.selling_price, ')')
                        ORDER BY p.product_name ASC
                        SEPARATOR '<br>'
                    ) AS products_summary,
                    SUM(ti.quantity * (ti.selling_price - p.product_purchase_price)) AS item_profit_sum
                FROM
                    transactions t
                JOIN
                    users u ON t.user_id = u.user_id
                JOIN
                    transaction_items ti ON t.transaction_id = ti.transaction_id
                JOIN
                    products p ON ti.product_id = p.product_id
                WHERE
                    DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'
                GROUP BY
                    t.transaction_id
                ORDER BY
                    t.transaction_date ASC";

$result_detail = $conn->query($sql_detail);
$total_calculated_profit = 0; // Untuk total profit dari semua item
if ($result_detail && $result_detail->num_rows > 0) {
    while ($row = $result_detail->fetch_assoc()) {
        $sales_data[] = $row;
        $total_calculated_profit += ($row['item_profit_sum'] ?? 0);
    }
}
$sales_summary['total_profit_amount'] = $total_calculated_profit;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Penjualan Obat</title>
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">Transaksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="reports.php">Laporan</a> </li>
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
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
        <h2>Laporan Penjualan</h2>
        <?php include 'includes/message.php'; // Tampilkan pesan ?>

        <form action="" method="get" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter Laporan</button>
                </div>
            </div>
        </form>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Transaksi</h5>
                        <p class="card-text fs-3"><?php echo number_format($sales_summary['total_transactions'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Penjualan</h5>
                        <p class="card-text fs-3">Rp <?php echo number_format($sales_summary['total_sales_amount'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Estimasi Profit</h5>
                        <p class="card-text fs-3">Rp <?php echo number_format($sales_summary['total_profit_amount'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h3>Detail Transaksi</h3>
        <?php if (!empty($sales_data)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Tanggal & Waktu</th>
                            <th>Kasir</th>
                            <th>Produk Terjual</th>
                            <th>Total Penjualan</th>
                            <th>Estimasi Profit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales_data as $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($data['transaction_date']); ?></td>
                            <td><?php echo htmlspecialchars($data['cashier_name']); ?></td>
                            <td><?php echo $data['products_summary']; ?></td>
                            <td>Rp <?php echo number_format($data['total_amount'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($data['item_profit_sum'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="view_transaction.php?id=<?php echo $data['transaction_id']; ?>" class="btn btn-info btn-sm">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Tidak ada transaksi dalam rentang tanggal ini.</div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>