<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

// Ambil daftar produk yang tersedia untuk dijual
// Kita hanya mengambil produk dengan stok > 0
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
    $customer_name = $_POST['customer_name'] ?? NULL;
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices_at_sale = $_POST['price_at_sale'] ?? []; // Harga produk saat transaksi, dikirim dari JS

    // Validasi minimal: harus ada produk yang dipilih
    if (empty($product_ids) || count($product_ids) == 0) {
        $_SESSION['message'] = "Tidak ada produk yang dipilih untuk transaksi.";
        $_SESSION['message_type'] = "danger";
        // Redirect kembali ke halaman ini atau tampilkan error di tempat
        header("Location: add_transaction.php");
        exit();
    }

    // Mulai transaksi database
    $conn->begin_transaction();
    $success = true;

    try {
        // 1. Hitung total amount transaksi
        $total_amount = 0;
        for ($i = 0; $i < count($product_ids); $i++) {
            $total_amount += ($quantities[$i] * $prices_at_sale[$i]);
        }

        // 2. Insert ke tabel `transactions`
        $user_id = $_SESSION['user_id']; // Ambil user_id dari sesi login
        $customer_name_safe = $conn->real_escape_string($customer_name);
        $total_amount_safe = $conn->real_escape_string($total_amount);

        $sql_insert_transaction = "INSERT INTO transactions (total_amount, user_id, customer_name) VALUES ('$total_amount_safe', '$user_id', " . ($customer_name_safe ? "'$customer_name_safe'" : "NULL") . ")";

        if ($conn->query($sql_insert_transaction) === TRUE) {
            $transaction_id = $conn->insert_id; // Dapatkan ID transaksi yang baru dibuat

            // 3. Insert ke tabel `transaction_items` dan update stok produk
            for ($i = 0; $i < count($product_ids); $i++) {
                $product_id = $conn->real_escape_string($product_ids[$i]);
                $quantity = $conn->real_escape_string($quantities[$i]);
                $price_at_sale = $conn->real_escape_string($prices_at_sale[$i]);

                // Insert item transaksi
                $sql_insert_item = "INSERT INTO transaction_items (transaction_id, product_id, quantity, price_at_sale) VALUES ('$transaction_id', '$product_id', '$quantity', '$price_at_sale')";
                if ($conn->query($sql_insert_item) !== TRUE) {
                    $success = false;
                    break;
                }

                // Update stok produk (kurangi stok)
                $sql_update_stock = "UPDATE products SET product_stock = product_stock - '$quantity' WHERE product_id = '$product_id'";
                if ($conn->query($sql_update_stock) !== TRUE) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                $conn->commit(); // Komit transaksi jika semua berhasil
                $_SESSION['message'] = "Transaksi berhasil dicatat dengan ID: <b>" . $transaction_id . "</b>.";
                $_SESSION['message_type'] = "success";
                header("Location: transactions.php");
                exit();
            } else {
                $conn->rollback(); // Rollback jika ada yang gagal
                $_SESSION['message'] = "Error saat memproses item transaksi atau memperbarui stok: " . $conn->error;
                $_SESSION['message_type'] = "danger";
            }

        } else {
            $conn->rollback(); // Rollback jika insert transaksi utama gagal
            $_SESSION['message'] = "Error saat membuat transaksi baru: " . $conn->error;
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
    <title>Buat Transaksi Baru - Penjualan Obat</title>
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
        <h2>Buat Transaksi Baru</h2>
        <?php include 'includes/message.php'; // Tampilkan pesan error/sukses ?>

        <form action="" method="post">
            <div class="mb-3">
                <label for="customer_name" class="form-label">Nama Pelanggan (Opsional)</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name">
            </div>

            <h3>Detail Produk</h3>
            <div id="product-list">
                <div class="product-item row g-3 align-items-center mb-3">
                    <div class="col-md-6">
                        <label for="product_select_0" class="form-label visually-hidden">Pilih Produk</label>
                        <select class="form-select product-select" id="product_select_0" name="product_id[]" data-index="0" required>
                            <option value="" data-price="0" data-stock="0">Pilih Produk</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['product_id']; ?>" 
                                        data-price="<?php echo $product['product_price']; ?>"
                                        data-stock="<?php echo $product['product_stock']; ?>">
                                    <?php echo htmlspecialchars($product['product_name']); ?> (Stok: <?php echo $product['product_stock']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="quantity_0" class="form-label visually-hidden">Kuantitas</label>
                        <input type="number" class="form-control quantity-input" id="quantity_0" name="quantity[]" min="1" value="1" required>
                        <small class="text-danger stock-warning" id="stock_warning_0" style="display:none;">Stok tidak cukup!</small>
                    </div>
                    <div class="col-md-3">
                        <label for="subtotal_0" class="form-label visually-hidden">Subtotal</label>
                        <input type="text" class="form-control subtotal-output" id="subtotal_0" value="Rp 0" readonly>
                        <input type="hidden" name="price_at_sale[]" class="price-at-sale-hidden" value="0">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-product" style="display:none;">X</button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-success mb-3" id="add-product-row">Tambah Produk</button>

            <div class="mb-3 mt-4">
                <label for="total_amount" class="form-label fs-4">Total Transaksi</label>
                <input type="text" class="form-control form-control-lg" id="total_amount" value="Rp 0" readonly>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">Simpan Transaksi</button>
            <a href="transactions.php" class="btn btn-secondary btn-lg">Batal</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productList = document.getElementById('product-list');
            const addProductRowButton = document.getElementById('add-product-row');
            const totalAmountInput = document.getElementById('total_amount');
            let productIndex = 0; // Untuk mengelola index dinamis dari setiap baris produk

            // Fungsi untuk menghitung ulang total keseluruhan
            function calculateTotal() {
                let grandTotal = 0;
                document.querySelectorAll('.subtotal-output').forEach(function(input) {
                    // Ambil nilai numerik dari string "Rp X"
                    const subtotalText = input.value.replace('Rp ', '').replace(/\./g, '');
                    grandTotal += parseFloat(subtotalText || 0);
                });
                totalAmountInput.value = 'Rp ' + formatRupiah(grandTotal);
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
                    <div class="col-md-6">
                        <label for="product_select_${productIndex}" class="form-label visually-hidden">Pilih Produk</label>
                        <select class="form-select product-select" id="product_select_${productIndex}" name="product_id[]" data-index="${productIndex}" required>
                            <option value="" data-price="0" data-stock="0">Pilih Produk</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['product_id']; ?>" 
                                        data-price="<?php echo $product['product_price']; ?>"
                                        data-stock="<?php echo $product['product_stock']; ?>">
                                    <?php echo htmlspecialchars($product['product_name']); ?> (Stok: <?php echo $product['product_stock']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="quantity_${productIndex}" class="form-label visually-hidden">Kuantitas</label>
                        <input type="number" class="form-control quantity-input" id="quantity_${productIndex}" name="quantity[]" min="1" value="1" required>
                        <small class="text-danger stock-warning" id="stock_warning_${productIndex}" style="display:none;">Stok tidak cukup!</small>
                    </div>
                    <div class="col-md-3">
                        <label for="subtotal_${productIndex}" class="form-label visually-hidden">Subtotal</label>
                        <input type="text" class="form-control subtotal-output" id="subtotal_${productIndex}" value="Rp 0" readonly>
                        <input type="hidden" name="price_at_sale[]" class="price-at-sale-hidden" value="0">
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
                const newRemoveButton = newRow.querySelector('.remove-product');

                newProductSelect.addEventListener('change', updateSubtotalAndStock);
                newQuantityInput.addEventListener('input', updateSubtotalAndStock);
                newRemoveButton.addEventListener('click', removeProductRow);

                updateSubtotalAndStock.call(newProductSelect); // Panggil sekali untuk inisialisasi baris baru
            }

            // Fungsi untuk memperbarui subtotal dan memeriksa stok
            function updateSubtotalAndStock() {
                const currentRow = this.closest('.product-item');
                const productSelect = currentRow.querySelector('.product-select');
                const quantityInput = currentRow.querySelector('.quantity-input');
                const subtotalOutput = currentRow.querySelector('.subtotal-output');
                const priceAtSaleHidden = currentRow.querySelector('.price-at-sale-hidden');
                const stockWarning = currentRow.querySelector('.stock-warning');

                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.dataset.price || 0);
                const availableStock = parseInt(selectedOption.dataset.stock || 0);
                let quantity = parseInt(quantityInput.value || 0);

                // Validasi kuantitas tidak boleh lebih dari stok tersedia
                if (quantity > availableStock && availableStock > 0) {
                    stockWarning.style.display = 'block';
                    stockWarning.textContent = `Stok tidak cukup! (Sisa: ${availableStock})`; // Tampilkan sisa stok
                    quantityInput.classList.add('is-invalid');
                    // Opsional: set quantity ke max stock
                    // quantity = availableStock;
                    // quantityInput.value = quantity;
                } else if (availableStock === 0 && productSelect.value !== "") { // Jika stok 0 dan produk sudah dipilih
                     stockWarning.textContent = 'Produk ini tidak ada stok!';
                     stockWarning.style.display = 'block';
                     quantityInput.classList.add('is-invalid');
                } else {
                    stockWarning.style.display = 'none';
                    quantityInput.classList.remove('is-invalid');
                }
                
                // Jangan biarkan kuantitas negatif atau nol jika produk sudah dipilih
                if (quantity < 1 && productSelect.value !== "") {
                    quantity = 1;
                    quantityInput.value = 1;
                } else if (quantity < 0) { // Hanya kuantitas negatif
                    quantity = 0;
                    quantityInput.value = 0;
                }


                const subtotal = price * quantity;
                subtotalOutput.value = 'Rp ' + formatRupiah(subtotal);
                priceAtSaleHidden.value = price; // Simpan harga saat ini ke hidden input

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
            // Dipicu saat memilih produk atau mengubah kuantitas
            productList.querySelector('.product-select').addEventListener('change', updateSubtotalAndStock);
            productList.querySelector('.quantity-input').addEventListener('input', updateSubtotalAndStock);
            
            // Event listener untuk tombol tambah produk
            addProductRowButton.addEventListener('click', addProductRow);

            // Inisialisasi total saat halaman dimuat
            // Panggil sekali untuk memastikan tampilan awal sudah benar
            updateSubtotalAndStock.call(productList.querySelector('.product-select')); 

            // Pastikan tombol remove disembunyikan jika hanya ada 1 baris saat load awal
            if (document.querySelectorAll('.product-item').length <= 1) {
                document.querySelectorAll('.remove-product').forEach(btn => btn.style.display = 'none');
            }
        });
    </script>
</body>
</html>