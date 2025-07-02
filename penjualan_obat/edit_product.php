<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

// Pastikan hanya Admin yang bisa mengakses halaman ini
if ($_SESSION['role_id'] != 1) { // role_id 1 = Admin
    $_SESSION['message'] = "Anda tidak memiliki akses untuk mengedit produk.";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php");
    exit();
}

$product_id = $_GET['id'] ?? null;
$product = null;

// Ambil data produk berdasarkan ID
if ($product_id) {
    $product_id_safe = $conn->real_escape_string($product_id);
    $sql = "SELECT * FROM products WHERE product_id = '$product_id_safe'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = "Produk tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header("Location: products.php");
        exit();
    }
} else {
    $_SESSION['message'] = "ID Produk tidak diberikan.";
    $_SESSION['message_type'] = "danger";
    header("Location: products.php");
    exit();
}

// Ambil daftar kategori dan satuan untuk dropdown
$categories = [];
$units = [];

$sql_categories = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);
if ($result_categories) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

$sql_units = "SELECT unit_id, unit_name FROM units ORDER BY unit_name ASC";
$result_units = $conn->query($sql_units);
if ($result_units) {
    while ($row = $result_units->fetch_assoc()) {
        $units[] = $row;
    }
}

// Proses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'] ?? '';
    $product_code = $_POST['product_code'] ?? '';
    $product_description = $_POST['product_description'] ?? '';
    $product_stock = $_POST['product_stock'] ?? 0;
    $product_price = $_POST['product_price'] ?? 0; // Harga jual
    $product_purchase_price = $_POST['product_purchase_price'] ?? 0; // Harga beli
    $category_id = $_POST['category_id'] ?? null;
    $unit_id = $_POST['unit_id'] ?? null;

    // Validasi input
    if (empty($product_name) || empty($product_code) || empty($product_price) || empty($product_purchase_price) || empty($category_id) || empty($unit_id)) {
        $_SESSION['message'] = "Semua kolom wajib diisi (kecuali deskripsi).";
        $_SESSION['message_type'] = "danger";
    } elseif (!is_numeric($product_stock) || $product_stock < 0 || !is_numeric($product_price) || $product_price <= 0 || !is_numeric($product_purchase_price) || $product_purchase_price < 0) {
        $_SESSION['message'] = "Stok, harga jual, dan harga beli harus angka valid. Harga jual harus lebih dari 0.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Escape string untuk mencegah SQL injection
        $product_name_safe = $conn->real_escape_string($product_name);
        $product_code_safe = $conn->real_escape_string($product_code);
        $product_description_safe = $conn->real_escape_string($product_description);
        $product_stock_safe = $conn->real_escape_string($product_stock);
        $product_price_safe = $conn->real_escape_string($product_price);
        $product_purchase_price_safe = $conn->real_escape_string($product_purchase_price);
        $category_id_safe = $conn->real_escape_string($category_id);
        $unit_id_safe = $conn->real_escape_string($unit_id);
        $current_user_id = $_SESSION['user_id'];

        // Query UPDATE
        $sql_update = "UPDATE products SET
                            product_name = '$product_name_safe',
                            product_code = '$product_code_safe',
                            product_description = '$product_description_safe',
                            product_stock = '$product_stock_safe',
                            product_price = '$product_price_safe',
                            product_purchase_price = '$product_purchase_price_safe',
                            category_id = '$category_id_safe',
                            unit_id = '$unit_id_safe',
                            updated_by = '$current_user_id',
                            updated_at = NOW()
                        WHERE product_id = '$product_id_safe'";

        if ($conn->query($sql_update) === TRUE) {
            $_SESSION['message'] = "Produk <b>" . htmlspecialchars($product_name) . "</b> berhasil diperbarui.";
            $_SESSION['message_type'] = "success";
            // Redirect kembali ke halaman produk setelah sukses
            header("Location: products.php");
            exit();
        } else {
            $_SESSION['message'] = "Error saat memperbarui produk: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
    }
    // Jika ada error, data produk akan tetap ditampilkan dari $_POST
    // atau jika tidak ada POST, dari $product yang diambil dari DB
    // agar form tidak kosong.
    $product = [
        'product_id' => $product_id, // Pastikan ID tetap ada
        'product_name' => $product_name,
        'product_code' => $product_code,
        'product_description' => $product_description,
        'product_stock' => $product_stock,
        'product_price' => $product_price,
        'product_purchase_price' => $product_purchase_price,
        'category_id' => $category_id,
        'unit_id' => $unit_id,
        'created_at' => $product['created_at'] ?? '', // Pertahankan created_at dari data awal
        'created_by' => $product['created_by'] ?? '',
        'updated_at' => $product['updated_at'] ?? '',
        'updated_by' => $product['updated_by'] ?? ''
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Penjualan Obat</title>
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="products.php">Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="transactions.php">Transaksi</a></li>
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
                    <li class="nav-item"><a class="nav-link" href="#">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Edit Produk: <?php echo htmlspecialchars($product['product_name'] ?? ''); ?></h2>
        <?php include 'includes/message.php'; // Tampilkan pesan ?>

        <?php if ($product): ?>
        <form action="" method="post">
            <div class="mb-3">
                <label for="product_name" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="product_code" class="form-label">Kode Produk</label>
                <input type="text" class="form-control" id="product_code" name="product_code" value="<?php echo htmlspecialchars($product['product_code'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="product_description" class="form-label">Deskripsi (Opsional)</label>
                <textarea class="form-control" id="product_description" name="product_description" rows="3"><?php echo htmlspecialchars($product['product_description'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="product_stock" class="form-label">Stok</label>
                <input type="number" class="form-control" id="product_stock" name="product_stock" value="<?php echo htmlspecialchars($product['product_stock'] ?? 0); ?>" min="0" required>
            </div>
            <div class="mb-3">
                <label for="product_price" class="form-label">Harga Jual (Rp)</label>
                <input type="number" class="form-control" id="product_price" name="product_price" value="<?php echo htmlspecialchars($product['product_price'] ?? 0); ?>" min="0.01" step="0.01" required>
            </div>
             <div class="mb-3">
                <label for="product_purchase_price" class="form-label">Harga Beli (Rp)</label>
                <input type="number" class="form-control" id="product_purchase_price" name="product_purchase_price" value="<?php echo htmlspecialchars($product['product_purchase_price'] ?? 0); ?>" min="0" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Kategori</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Pilih Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" <?php echo ($category['category_id'] == ($product['category_id'] ?? '') ? 'selected' : ''); ?>>
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="unit_id" class="form-label">Satuan</label>
                <select class="form-select" id="unit_id" name="unit_id" required>
                    <option value="">Pilih Satuan</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?php echo $unit['unit_id']; ?>" <?php echo ($unit['unit_id'] == ($product['unit_id'] ?? '') ? 'selected' : ''); ?>>
                            <?php echo htmlspecialchars($unit['unit_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="products.php" class="btn btn-secondary">Batal</a>
        </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>