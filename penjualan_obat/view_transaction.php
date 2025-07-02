<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

$transaction_id = null;
$transaction = null;
$transaction_items = [];

if (isset($_GET['id'])) {
    $transaction_id = $conn->real_escape_string($_GET['id']);

    // 1. Ambil detail transaksi utama
    $sql_transaction = "SELECT 
                            t.transaction_id, 
                            t.transaction_date, 
                            t.total_amount, 
                            u.username, 
                            r.role_name AS user_role,
                            t.customer_name,
                            t.created_at
                        FROM 
                            transactions t
                        JOIN 
                            users u ON t.user_id = u.user_id
                        JOIN
                            roles r ON u.role_id = r.role_id
                        WHERE 
                            t.transaction_id = '$transaction_id'";

    $result_transaction = $conn->query($sql_transaction);

    if ($result_transaction && $result_transaction->num_rows > 0) {
        $transaction = $result_transaction->fetch_assoc();

        // 2. Ambil detail item transaksi
        $sql_items = "SELECT 
                        ti.quantity, 
                        ti.price_at_sale, 
                        p.product_name,
                        p.product_code,
                        c.category_name,
                        s.supplier_name,
                        un.unit_name
                    FROM 
                        transaction_items ti
                    JOIN 
                        products p ON ti.product_id = p.product_id
                    LEFT JOIN -- LEFT JOIN karena tidak semua produk mungkin punya kategori/supplier/unit di awal
                        categories c ON p.category_id = c.category_id
                    LEFT JOIN
                        suppliers s ON p.supplier_id = s.supplier_id
                    LEFT JOIN
                        units un ON p.unit_id = un.unit_id
                    WHERE 
                        ti.transaction_id = '$transaction_id'";

        $result_items = $conn->query($sql_items);

        if ($result_items && $result_items->num_rows > 0) {
            while ($row = $result_items->fetch_assoc()) {
                $transaction_items[] = $row;
            }
        } else {
            $_SESSION['message'] = "Tidak ada detail item untuk transaksi ini.";
            $_SESSION['message_type'] = "warning";
        }

    } else {
        $_SESSION['message'] = "Transaksi tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header("Location: transactions.php"); // Redirect jika transaksi tidak ditemukan
        exit();
    }

} else {
    $_SESSION['message'] = "ID Transaksi tidak diberikan.";
    $_SESSION['message_type'] = "danger";
    header("Location: transactions.php"); // Redirect jika ID tidak ada di URL
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi #<?php echo htmlspecialchars($transaction_id); ?> - Penjualan Obat</title>
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="transactions.php">Transaksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php">Laporan</a></li>
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
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
        <?php include 'includes/message.php'; // Tampilkan pesan ?>

        <?php if ($transaction): ?>
            <h2>Detail Transaksi #<?php echo htmlspecialchars($transaction['transaction_id']); ?></h2>
            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Tanggal Transaksi:</strong> <?php echo htmlspecialchars($transaction['transaction_date']); ?></p>
                    <p><strong>Total Pembayaran:</strong> Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></p>
                    <p><strong>Nama Pelanggan:</strong> <?php echo htmlspecialchars($transaction['customer_name'] ?? '-'); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Dicatat Oleh:</strong> <?php echo htmlspecialchars($transaction['username']); ?> (<?php echo htmlspecialchars($transaction['user_role']); ?>)</p>
                    <p><strong>Dibuat Pada:</strong> <?php echo htmlspecialchars($transaction['created_at']); ?></p>
                </div>
            </div>

            <h3>Produk yang Dibeli</h3>
            <?php if (!empty($transaction_items)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Kode Produk</th>
                                <th>Kategori</th>
                                <th>Kuantitas</th>
                                <th>Harga Jual (per unit)</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transaction_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['product_code'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($item['category_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?> <?php echo htmlspecialchars($item['unit_name'] ?? ''); ?></td>
                                <td>Rp <?php echo number_format($item['price_at_sale'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($item['quantity'] * $item['price_at_sale'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">Tidak ada produk yang tercatat dalam transaksi ini.</div>
            <?php endif; ?>

            <a href="transactions.php" class="btn btn-secondary mt-3">Kembali ke Daftar Transaksi</a>
            <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>