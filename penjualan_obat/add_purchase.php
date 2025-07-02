<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

// Pastikan hanya Admin yang bisa mengakses halaman ini
if ($_SESSION['role_id'] != 1) { // role_id 1 = Admin
    $_SESSION['message'] = "Anda tidak memiliki akses untuk mencatat pembelian.";
    $_SESSION['message_type'] = "danger";
    header("Location: dashboard.php");
    exit();
}

// Ambil daftar pemasok yang tersedia
$sql_suppliers = "SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC";
$result_suppliers = $conn->query($sql_suppliers);
$suppliers = [];
if ($result_suppliers && $result_suppliers->num_rows > 0) {
    while ($row = $result_suppliers->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

// Ambil daftar produk yang tersedia (semua produk, tidak hanya yang > 0 stoknya)
$sql_products = "SELECT product_id, product_name, product_price, product_stock
                 FROM products
                 WHERE is_active = 1 -- Hanya ambil produk aktif
                 ORDER BY product_name ASC";
$result_products = $conn->query($sql_products);
$products = [];
if ($result_products && $result_products->num_rows > 0) {
    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_id = $_POST['supplier_id'] ?? null;
    $invoice_number = $_POST['invoice_number'] ?? null;
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $purchase_prices = $_POST['purchase_price'] ?? []; // Harga beli produk saat ini, dikirim dari JS

    // Validasi dasar
    if (empty($supplier_id)) {
        $_SESSION['message'] = "Pemasok harus dipilih.";
        $_SESSION['message_type'] = "danger";
        header("Location: add_purchase.php");
        exit();
    }
    if (empty($product_ids) || count($product_ids) == 0) {
        $_SESSION['message'] = "Tidak ada produk yang ditambahkan untuk pembelian.";
        $_SESSION['message_type'] = "danger";
        header("Location: add_purchase.php");
        exit();
    }

    // Mulai transaksi database
    $conn->begin_transaction();
    $success = true;

    try {
        // 1. Hitung total cost pembelian
        $total_cost = 0;
        for ($i = 0; $i < count($product_ids); $i++) {
            // Pastikan quantity dan purchase_price adalah angka
            $qty = (int)$quantities[$i];
            $price = (float)$purchase_prices[$i];
            $total_cost += ($qty * $price);
        }

        // 2. Insert ke tabel `purchases`
        $user_id = $_SESSION['user_id']; // Ambil user_id dari sesi login
        $supplier_id_safe = $conn->real_escape_string($supplier_id);
        $total_cost_safe = $conn->real_escape_string($total_cost);
        $invoice_number_safe = $conn->real_escape_string($invoice_number);

        $sql_insert_purchase = "INSERT INTO purchases (total_cost, supplier_id, invoice_number, user_id) VALUES ('$total_cost_safe', '$supplier_id_safe', " . ($invoice_number_safe ? "'$invoice_number_safe'" : "NULL") . ", '$user_id')";

        if ($conn->query($sql_insert_purchase) === TRUE) {
            $purchase_id = $conn->insert_id; // Dapatkan ID pembelian yang baru dibuat

            // 3. Insert ke tabel `purchase_items` dan update stok produk
            for ($i = 0; $i < count($product_ids); $i++) {
                $product_id = $conn->real_escape_string($product_ids[$i]);
                $quantity = $conn->real_escape_string($quantities[$i]);
                $purchase_price = $conn->real_escape_string($purchase_prices[$i]);

                // Insert item pembelian
                $sql_insert_item = "INSERT INTO purchase_items (purchase_id, product_id, quantity, purchase_price) VALUES ('$purchase_id', '$product_id', '$quantity', '$purchase_price')";
                if ($conn->query($sql_insert_item) !== TRUE) {
                    $success = false;
                    break;
                }

                // Update stok produk (tambah stok)
                $sql_update_stock = "UPDATE products SET product_stock = product_stock + '$quantity' WHERE product_id = '$product_id'";
                if ($conn->query($sql_update_stock) !== TRUE) {
                    $success = false;
                    break;
                }
                // Opsional: Anda juga bisa update product_purchase_price di tabel products jika harga belinya berubah
                // $sql_update_product_price = "UPDATE products SET product_purchase_price = '$purchase_price' WHERE product_id = '$product_id'";
                // if ($conn->query($sql_update_product_price) !== TRUE) {
                //     $success = false;
                //     break;
                // }
            }

            if ($success) {
                $conn->commit(); // Komit transaksi jika semua berhasil
                $_SESSION['message'] = "Pembelian berhasil dicatat dengan ID: <b>" . $purchase_id . "</b>.";
                $_SESSION['message_type'] = "success";
                header("Location: purchases.php");
                exit();
            } else {
                $conn->rollback(); // Rollback jika ada yang gagal
                $_SESSION['message'] = "Error saat memproses item pembelian atau memperbarui stok: " . $conn->error;
                $_SESSION['message_type'] = "danger";
            }

        } else {
            $conn->rollback(); // Rollback jika insert pembelian utama gagal
            $_SESSION['message'] = "Error saat mencatat pembelian baru: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }

    } catch (Exception $e) {
        $conn->rollback(); // Pastikan rollback jika ada exception
        $_SESSION['message'] = "Terjadi kesalahan: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Pembelian Baru - Penjualan Obat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background-color: #f0f2f5; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .product-item { display: flex; align-items: center; margin-bottom: 10px; }
        .product-item select, .product-item input { margin-right: 10px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Aplikasi Penjualan Obat</a>
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
        <h2>Catat Pembelian Baru</h2>
        <?php include 'includes/message.php'; // Tampilkan pesan error/sukses ?>

        <form action="" method="post">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="supplier_id" class="form-label">Pemasok</label>
                    <select class="form-select" id="supplier_id" name="supplier_id" required>
                        <option value="">Pilih Pemasok</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['supplier_id']; ?>">
                                <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="invoice_number" class="form-label">Nomor Faktur (Opsional)</label>
                    <input type="text" class="form-control" id="invoice_number" name="invoice_number">
                </div>
            </div>

            <h3>Detail Produk yang Dibeli</h3>
            <div id="product-list">
                <div class="product-item row g-3 align-items-center mb-3">
                    <div class="col-md-5">
                        <label for="product_select_0" class="form-label visually-hidden">Pilih Produk</label>
                        <select class="form-select product-select" id="product_select_0" name="product_id[]" data-index="0" required>
                            <option value="" data-price="0">Pilih Produk</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['product_id']; ?>"
                                        data-price="<?php echo $product['product_price']; ?>"> <?php echo htmlspecialchars($product['product_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="quantity_0" class="form-label visually-hidden">Kuantitas</label>
                        <input type="number" class="form-control quantity-input" id="quantity_0" name="quantity[]" min="1" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label for="purchase_price_0" class="form-label visually-hidden">Harga Beli</label>
                        <input type="number" class="form-control purchase-price-input" id="purchase_price_0" name="purchase_price[]" min="0" value="0" step="0.01" required>
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control subtotal-output" id="subtotal_0" value="Rp 0" readonly>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-product" style="display:none;">X</button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-success mb-3" id="add-product-row">Tambah Produk</button>

            <div class="mb-3 mt-4">
                <label for="total_cost" class="form-label fs-4">Total Biaya Pembelian</label>
                <input type="text" class="form-control form-control-lg" id="total_cost" value="Rp 0" readonly>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">Simpan Pembelian</button>
            <a href="purchases.php" class="btn btn-secondary btn-lg">Batal</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productList = document.getElementById('product-list');
            const addProductRowButton = document.getElementById('add-product-row');
            const totalCostInput = document.getElementById('total_cost');
            let productIndex = 0; // Untuk mengelola index dinamis dari setiap baris produk

            // Fungsi untuk menghitung ulang total keseluruhan
            function calculateTotal() {
                let grandTotal = 0;
                document.querySelectorAll('.subtotal-output').forEach(function(input) {
                    const subtotalText = input.value.replace('Rp ', '').replace(/\./g, '');
                    grandTotal += parseFloat(subtotalText || 0);
                });
                totalCostInput.value = 'Rp ' + formatRupiah(grandTotal);
            }

            // Fungsi untuk format angka ke Rupiah
            function formatRupiah(angka) {
                let number_string = angka.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return rupiah;
            }

            // Fungsi untuk menambahkan baris produk baru
            function addProductRow() {
                productIndex++;
                const newRow = document.createElement('div');
                newRow.classList.add('product-item', 'row', 'g-3', 'align-items-center', 'mb-3');
                newRow.innerHTML = `
                    <div class="col-md-5">
                        <label for="product_select_${productIndex}" class="form-label visually-hidden">Pilih Produk</label>
                        <select class="form-select product-select" id="product_select_${productIndex}" name="product_id[]" data-index="${productIndex}" required>
                            <option value="" data-price="0">Pilih Produk</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['product_id']; ?>"
                                        data-price="<?php echo $product['product_price']; ?>">
                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="quantity_${productIndex}" class="form-label visually-hidden">Kuantitas</label>
                        <input type="number" class="form-control quantity-input" id="quantity_${productIndex}" name="quantity[]" min="1" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label for="purchase_price_${productIndex}" class="form-label visually-hidden">Harga Beli</label>
                        <input type="number" class="form-control purchase-price-input" id="purchase_price_${productIndex}" name="purchase_price[]" min="0" value="0" step="0.01" required>
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control subtotal-output" id="subtotal_${productIndex}" value="Rp 0" readonly>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-product">X</button>
                    </div>
                `;
                productList.appendChild(newRow);

                // Pastikan tombol remove muncul setelah ada lebih dari 1 baris
                if (document.querySelectorAll('.product-item').length > 1) {
                    document.querySelectorAll('.remove-product').forEach(btn => btn.style.display = 'block');
                }

                // Tambahkan event listener untuk elemen baru
                const newProductSelect = newRow.querySelector('.product-select');
                const newQuantityInput = newRow.querySelector('.quantity-input');
                const newPurchasePriceInput = newRow.querySelector('.purchase-price-input');
                const newRemoveButton = newRow.querySelector('.remove-product');

                newProductSelect.addEventListener('change', updateSubtotal);
                newQuantityInput.addEventListener('input', updateSubtotal);
                newPurchasePriceInput.addEventListener('input', updateSubtotal);
                newRemoveButton.addEventListener('click', removeProductRow);

                updateSubtotal.call(newProductSelect); // Panggil sekali untuk inisialisasi baris baru
            }

            // Fungsi untuk memperbarui subtotal
            function updateSubtotal() {
                const currentRow = this.closest('.product-item');
                const productSelect = currentRow.querySelector('.product-select');
                const quantityInput = currentRow.querySelector('.quantity-input');
                const purchasePriceInput = currentRow.querySelector('.purchase-price-input');
                const subtotalOutput = currentRow.querySelector('.subtotal-output');

                const selectedOption = productSelect.options[productSelect.selectedIndex];
                // Jika produk baru dipilih, set harga beli default dari product_price
                if (this === productSelect && selectedOption.value !== "") {
                    const defaultPrice = parseFloat(selectedOption.dataset.price || 0);
                    purchasePriceInput.value = defaultPrice;
                }

                let quantity = parseInt(quantityInput.value || 0);
                let purchasePrice = parseFloat(purchasePriceInput.value || 0);

                if (quantity < 1) {
                    quantity = 1;
                    quantityInput.value = 1;
                }
                if (purchasePrice < 0) {
                    purchasePrice = 0;
                    purchasePriceInput.value = 0;
                }

                const subtotal = purchasePrice * quantity;
                subtotalOutput.value = 'Rp ' + formatRupiah(subtotal);

                calculateTotal();
            }

            // Fungsi untuk menghapus baris produk
            function removeProductRow() {
                this.closest('.product-item').remove();
                // Sembunyikan tombol remove jika hanya ada 1 baris
                if (document.querySelectorAll('.product-item').length <= 1) {
                    document.querySelectorAll('.remove-product').forEach(btn => btn.style.display = 'none');
                }
                calculateTotal(); // Hitung ulang total setelah menghapus
            }

            // Event listeners untuk baris produk pertama (default)
            productList.querySelector('.product-select').addEventListener('change', updateSubtotal);
            productList.querySelector('.quantity-input').addEventListener('input', updateSubtotal);
            productList.querySelector('.purchase-price-input').addEventListener('input', updateSubtotal);

            // Event listener untuk tombol tambah produk
            addProductRowButton.addEventListener('click', addProductRow);

            // Inisialisasi total saat halaman dimuat
            updateSubtotal.call(productList.querySelector('.product-select')); // Panggil untuk inisialisasi baris pertama

            // Pastikan tombol remove disembunyikan jika hanya ada 1 baris saat load awal
            if (document.querySelectorAll('.product-item').length <= 1) {
                document.querySelectorAll('.remove-product').forEach(btn => btn.style.display = 'none');
            }
        });
    </script>
</body>
</html>