<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

// Pastikan hanya Admin yang bisa mengakses halaman ini
if ($_SESSION['role_id'] != 1) { // role_id 1 = Admin
    $_SESSION['message'] = "Anda tidak memiliki akses untuk menambah produk.";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php");
    exit();
}

// Inisialisasi variabel untuk menghindari error Undefined Index jika form belum di-submit
$product_name = $product_code = $product_description = '';
$product_stock = 0;
$product_price = 0.00;
$product_purchase_price = 0.00;
$category_id = $unit_id = '';
$current_user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $product_name = $_POST['product_name'] ?? '';
    $product_code = $_POST['product_code'] ?? '';
    $product_description = $_POST['product_description'] ?? '';
    $product_stock = $_POST['product_stock'] ?? 0;
    $product_price = $_POST['product_price'] ?? 0.00;
    $product_purchase_price = $_POST['product_purchase_price'] ?? 0.00;
    $category_id = $_POST['category_id'] ?? '';
    $unit_id = $_POST['unit_id'] ?? '';

    // Validasi sederhana
    // Pastikan nilai numerik diubah ke tipe yang sesuai (int/float) sebelum perbandingan
    $product_stock = (int)$product_stock;
    $product_price = (float)$product_price;
    $product_purchase_price = (float)$product_purchase_price;
    $category_id = (int)$category_id; // Cast ke int karena ini FK
    $unit_id = (int)$unit_id; // Cast ke int karena ini FK

    if (empty($product_name) || empty($product_code) || $product_stock < 0 || $product_price <= 0 || $product_purchase_price <= 0 || $category_id <= 0 || $unit_id <= 0) {
        $_SESSION['message'] = "Semua field wajib diisi dan nilai harus valid.";
        $_SESSION['message_type'] = "danger";
    } else {
        // --- Menggunakan Prepared Statements untuk keamanan dan keandalan ---
        $sql = "INSERT INTO products (
                    product_name, product_code, product_description,
                    product_stock, product_price, product_purchase_price,
                    category_id, unit_id, created_by, updated_by, created_at, updated_at, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)";

        // Persiapan statement
        $stmt = $conn->prepare($sql);

        if ($stmt === FALSE) {
            $_SESSION['message'] = "Error saat menyiapkan statement: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        } else {
            // Bind parameter
            // s = string, i = integer, d = double (float)
            // Urutan dan Tipe Data:
            // 1. product_name (s)
            // 2. product_code (s)
            // 3. product_description (s)
            // 4. product_stock (i)
            // 5. product_price (d)
            // 6. product_purchase_price (d)
            // 7. category_id (i)
            // 8. unit_id (i)
            // 9. created_by (i)
            // 10. updated_by (i)
            // Total 10 parameter, string tipe data "sssiddiiii"
            $stmt->bind_param("sssiddiiii",
                $product_name,
                $product_code,
                $product_description,
                $product_stock,
                $product_price,
                $product_purchase_price,
                $category_id,
                $unit_id,
                $current_user_id, // created_by
                $current_user_id  // updated_by (nilai yang sama dengan created_by saat pertama kali dibuat)
            );

            // Eksekusi statement
            if ($stmt->execute()) {
                $_SESSION['message'] = "Produk baru berhasil ditambahkan.";
                $_SESSION['message_type'] = "success";
                header("Location: products.php"); // Redirect ke daftar produk
                exit();
            } else {
                $_SESSION['message'] = "Error saat menambahkan produk: " . $stmt->error;
                $_SESSION['message_type'] = "danger";
            }

            // Tutup statement
            $stmt->close();
        }
    }
}

// Query untuk mengambil kategori
$sql_categories = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);

// Query untuk mengambil satuan
$sql_units = "SELECT unit_id, unit_name FROM units ORDER BY unit_name ASC";
$result_units = $conn->query($sql_units);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru - Penjualan Obat</title>
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
        <h2>Tambah Produk Baru</h2>
        <?php include 'includes/message.php'; ?>

        <form action="add_product.php" method="POST">
            <div class="mb-3">
                <label for="product_name" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="product_code" class="form-label">Kode Produk</label>
                <input type="text" class="form-control" id="product_code" name="product_code" value="<?php echo htmlspecialchars($product_code); ?>" required>
            </div>
            <div class="mb-3">
                <label for="product_description" class="form-label">Deskripsi (Opsional)</label>
                <textarea class="form-control" id="product_description" name="product_description" rows="3"><?php echo htmlspecialchars($product_description); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="product_stock" class="form-label">Stok</label>
                <input type="number" class="form-control" id="product_stock" name="product_stock" value="<?php echo htmlspecialchars($product_stock); ?>" required min="0">
            </div>
            <div class="mb-3">
                <label for="product_price" class="form-label">Harga Jual</label>
                <input type="number" step="0.01" class="form-control" id="product_price" name="product_price" value="<?php echo htmlspecialchars($product_price); ?>" required min="0">
            </div>
            <div class="mb-3">
                <label for="product_purchase_price" class="form-label">Harga Beli</label>
                <input type="number" step="0.01" class="form-control" id="product_purchase_price" name="product_purchase_price" value="<?php echo htmlspecialchars($product_purchase_price); ?>" required min="0">
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Kategori</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Pilih Kategori</option>
                    <?php
                    if ($result_categories && $result_categories->num_rows > 0) {
                        while ($category = $result_categories->fetch_assoc()) {
                            $selected = ($category['category_id'] == $category_id) ? 'selected' : '';
                            echo "<option value='{$category['category_id']}' {$selected}>" . htmlspecialchars($category['category_name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="unit_id" class="form-label">Satuan</label>
                <select class="form-select" id="unit_id" name="unit_id" required>
                    <option value="">Pilih Satuan</option>
                    <?php
                    if ($result_units && $result_units->num_rows > 0) {
                        while ($unit = $result_units->fetch_assoc()) {
                            $selected = ($unit['unit_id'] == $unit_id) ? 'selected' : '';
                            echo "<option value='{$unit['unit_id']}' {$selected}>" . htmlspecialchars($unit['unit_name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Produk</button>
            <a href="products.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>