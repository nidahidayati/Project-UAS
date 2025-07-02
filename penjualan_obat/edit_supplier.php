<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php'; // Untuk menampilkan pesan

$supplier_id = null; // Inisialisasi variabel
$supplier_name = '';
$contact_person = '';
$phone_number = '';
$address = '';

// Ambil ID dari URL (saat user klik "Edit")
if (isset($_GET['id'])) {
    $supplier_id = $conn->real_escape_string($_GET['id']);
    // Query untuk mengambil data pemasok berdasarkan ID
    $sql = "SELECT * FROM suppliers WHERE supplier_id = '$supplier_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $supplier_name = $row['supplier_name'];
        $contact_person = $row['contact_person'];
        $phone_number = $row['phone_number'];
        $address = $row['address'];
    } else {
        $_SESSION['message'] = "Pemasok tidak ditemukan.";
        $_SESSION['message_type'] = "danger";
        header("Location: suppliers.php");
        exit();
    }
}

// Proses saat form disubmit (saat user klik "Update")
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_id = $conn->real_escape_string($_POST['supplier_id']);
    $supplier_name = $_POST['supplier_name'];
    $contact_person = $_POST['contact_person'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];

    $supplier_name_safe = $conn->real_escape_string($supplier_name);
    $contact_person_safe = $conn->real_escape_string($contact_person);
    $phone_number_safe = $conn->real_escape_string($phone_number);
    $address_safe = $conn->real_escape_string($address);

    // Query UPDATE ke tabel 'suppliers'
    $sql = "UPDATE suppliers SET 
                supplier_name = '$supplier_name_safe', 
                contact_person = '$contact_person_safe', 
                phone_number = '$phone_number_safe', 
                address = '$address_safe',
                updated_at = CURRENT_TIMESTAMP 
            WHERE supplier_id = '$supplier_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Pemasok '<b>" . htmlspecialchars($supplier_name) . "</b>' berhasil diubah.";
        $_SESSION['message_type'] = "success";
        header("Location: suppliers.php");
        exit();
    } else {
        $_SESSION['message'] = "Error mengubah pemasok: " . $conn->error;
        $_SESSION['message_type'] = "danger";
        header("Location: suppliers.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pemasok - Penjualan Obat</title>
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
                            <li><a class="dropdown-item" href="suppliers.php">Manajemen Pemasok</a></li>
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
        <h2>Edit Pemasok</h2>
        <form action="" method="post">
            <input type="hidden" name="supplier_id" value="<?php echo htmlspecialchars($supplier_id); ?>">

            <div class="mb-3">
                <label for="supplier_name" class="form-label">Nama Pemasok</label>
                <input type="text" class="form-control" id="supplier_name" name="supplier_name" value="<?php echo htmlspecialchars($supplier_name); ?>" required>
            </div>
            <div class="mb-3">
                <label for="contact_person" class="form-label">Kontak Person</label>
                <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($contact_person); ?>">
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">No. Telepon</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Alamat</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Pemasok</button>
            <a href="suppliers.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>