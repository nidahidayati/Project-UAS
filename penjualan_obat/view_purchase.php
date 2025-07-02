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

$purchase_id = null;
$purchase = null;
$purchase_items = [];

if (isset($_GET['id'])) {
    $purchase_id = $conn->real_escape_string($_GET['id']);

    // 1. Ambil detail pembelian utama
    $sql_purchase = "SELECT
                        p.purchase_id,
                        p.purchase_date,
                        p.total_cost,
                        s.supplier_name,
                        u.username,
                        r.role_name AS user_role,
                        p.invoice_number,
                        p.notes,
                        p.created_at
                    FROM
                        purchases p
                    JOIN
                        suppliers s ON p.supplier_id = s.supplier_id
                    JOIN
                        users u ON p.user_id = u.user_id
                    JOIN
                        roles r ON u.role_id = r.role_id
                    WHERE
                        p.purchase_id = '$purchase_id'";

    $result_purchase = $conn->query($sql_purchase);

    if ($result_purchase && $result_purchase->num_rows > 0) {
        $purchase = $result_purchase->fetch_assoc();

        // 2. Ambil detail item pembelian
        $sql_items = "SELECT
                        pi.quantity,
                        pi.purchase_price,
                        prod.product_name,
                        prod.product_code,
                        cat.category_name,
                        unit.unit_name
                    FROM
                        purchase_items pi
                    JOIN
                        products prod ON pi.product_id = prod.product_id
                    LEFT JOIN -- LEFT JOIN karena tidak semua produk mungkin punya kategori/supplier/unit di awal
                        categories cat ON prod.category_id = cat.category_id
                    LEFT JOIN
                        units unit ON prod.unit_id = unit.unit_id
                    WHERE
                        pi.purchase_id = '$purchase_id'";

        $result_items = $conn->query($sql_items);

        if ($result_items && $result_items->num_rows > 0) {
            while ($row = $result_items->fetch_assoc()) {
                $purchase_items[] = $row;
            }
        } else {
            $_SESSION['message'] = "Tidak ada detail item untuk pembelian ini.";
            $_SESSION['message_type'] = "warning";
        }

    } else {
        $_SESSION['message'] = "Pembelian tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header("Location: purchases.php"); // Redirect jika pembelian tidak ditemukan
        exit();
    }

} else {
    $_SESSION['message'] = "ID Pembelian tidak diberikan.";
    $_SESSION['message_type'] = "danger";
    header("Location: purchases.php"); // Redirect jika ID tidak ada di URL
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembelian #<?php echo htmlspecialchars($purchase_id); ?> - Penjualan Obat</title>
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
        <?php include 'includes/message.php'; // Tampilkan pesan ?>

        <?php if ($purchase): ?>
            <h2>Detail Pembelian #<?php echo htmlspecialchars($purchase['purchase_id']); ?></h2>
            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Tanggal Pembelian:</strong> <?php echo htmlspecialchars($purchase['purchase_date']); ?></p>
                    <p><strong>Nomor Faktur:</strong> <?php echo htmlspecialchars($purchase['invoice_number'] ?? '-'); ?></p>
                    <p><strong>Pemasok:</strong> <?php echo htmlspecialchars($purchase['supplier_name']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Dicatat Oleh:</strong> <?php echo htmlspecialchars($purchase['username']); ?> (<?php echo htmlspecialchars($purchase['user_role']); ?>)</p>
                    <p><strong>Total Biaya:</strong> Rp <?php echo number_format($purchase['total_cost'], 0, ',', '.'); ?></p>
                    <p><strong>Dibuat Pada:</strong> <?php echo htmlspecialchars($purchase['created_at']); ?></p>
                </div>
            </div>

            <?php if (!empty($purchase['notes'])): ?>
            <div class="mb-3">
                <p><strong>Catatan:</strong><br><?php echo nl2br(htmlspecialchars($purchase['notes'])); ?></p>
            </div>
            <?php endif; ?>

            <h3>Produk yang Diterima</h3>
            <?php if (!empty($purchase_items)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Kode Produk</th>
                                <th>Kategori</th>
                                <th>Kuantitas</th>
                                <th>Harga Beli (per unit)</th>
                                <th>Subtotal Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $calculated_total_items_cost = 0;
                            foreach ($purchase_items as $item):
                                $item_subtotal = $item['quantity'] * $item['purchase_price'];
                                $calculated_total_items_cost += $item_subtotal;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['product_code'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($item['category_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?> <?php echo htmlspecialchars($item['unit_name'] ?? ''); ?></td>
                                <td>Rp <?php echo number_format($item['purchase_price'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($item_subtotal, 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Total Biaya Item:</th>
                                <th>Rp <?php echo number_format($calculated_total_items_cost, 0, ',', '.'); ?></th>
                            </tr>
                            <?php if (round($calculated_total_items_cost, 2) != round($purchase['total_cost'], 2)): ?>
                            <tr>
                                <td colspan="6" class="text-danger">
                                    <small><em>Peringatan: Total biaya item tidak sesuai dengan total biaya pembelian yang tercatat. (Kemungkinan pembulatan atau biaya tambahan/kurang)</em></small>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">Tidak ada produk yang tercatat dalam pembelian ini.</div>
            <?php endif; ?>

            <a href="purchases.php" class="btn btn-secondary mt-3">Kembali ke Daftar Pembelian</a>
            <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>