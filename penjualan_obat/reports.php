<?php
require_once 'config/auth_check.php';
require_once 'config/database.php';
require_once 'includes/message.php';

$report_data = [];
$start_date = '';
$end_date = '';
$report_type = 'daily'; // Default report type

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $report_type = $_POST['report_type'] ?? 'daily'; // daily, monthly, custom

    // Basic validation for dates
    if (!empty($start_date) && !empty($end_date) && strtotime($start_date) > strtotime($end_date)) {
        $_SESSION['message'] = "Tanggal mulai tidak boleh lebih besar dari tanggal akhir.";
        $_SESSION['message_type'] = "danger";
        header("Location: reports.php");
        exit();
    }

    // Adjust end date for daily report to include the entire day
    // For monthly, we use YEAR(transaction_date) and MONTH(transaction_date)
    if ($report_type == 'daily' && !empty($end_date)) {
        $end_date_query = date('Y-m-d 23:59:59', strtotime($end_date));
    } elseif ($report_type == 'monthly') {
        // For monthly, start_date and end_date would typically be 2024-01-01 and 2024-12-31
        // We'll group by year and month directly in SQL
    } else { // For custom range
        $end_date_query = !empty($end_date) ? date('Y-m-d 23:59:59', strtotime($end_date)) : date('Y-m-d 23:59:59');
    }

    $sql = "";

    // Query berdasarkan jenis laporan
    if ($report_type == 'daily') {
        $sql = "SELECT
                    DATE(transaction_date) AS report_period,
                    COUNT(transaction_id) AS total_transactions,
                    SUM(total_amount) AS total_sales
                FROM
                    transactions
                WHERE
                    transaction_date >= '$start_date 00:00:00'";
        if (!empty($end_date)) {
            $sql .= " AND transaction_date <= '$end_date_query'";
        }
        $sql .= " GROUP BY report_period
                  ORDER BY report_period DESC";
    } elseif ($report_type == 'monthly') {
        $sql = "SELECT
                    DATE_FORMAT(transaction_date, '%Y-%m') AS report_period,
                    COUNT(transaction_id) AS total_transactions,
                    SUM(total_amount) AS total_sales
                FROM
                    transactions
                WHERE
                    transaction_date >= '$start_date 00:00:00'";
        if (!empty($end_date)) {
            $sql .= " AND transaction_date <= '$end_date_query'"; // Still use adjusted end_date for overall range
        }
        $sql .= " GROUP BY report_period
                  ORDER BY report_period DESC";
    } elseif ($report_type == 'custom') {
        $sql = "SELECT
                    DATE(transaction_date) AS report_period,
                    COUNT(transaction_id) AS total_transactions,
                    SUM(total_amount) AS total_sales
                FROM
                    transactions
                WHERE
                    transaction_date >= '$start_date 00:00:00'";
        if (!empty($end_date)) {
            $sql .= " AND transaction_date <= '$end_date_query'";
        }
        $sql .= " GROUP BY report_period
                  ORDER BY report_period DESC"; // Group by day even in custom range
    }


    // Fallback if no start_date/end_date for specific types, or if just navigating to reports page
    if (empty($start_date) && empty($end_date) && $report_type == 'daily') {
         $sql = "SELECT
                    DATE(transaction_date) AS report_period,
                    COUNT(transaction_id) AS total_transactions,
                    SUM(total_amount) AS total_sales
                FROM
                    transactions
                WHERE
                    DATE(transaction_date) = CURDATE() -- Laporan hari ini secara default
                GROUP BY report_period";
    } elseif (empty($start_date) && empty($end_date) && $report_type == 'monthly') {
         $sql = "SELECT
                    DATE_FORMAT(transaction_date, '%Y-%m') AS report_period,
                    COUNT(transaction_id) AS total_transactions,
                    SUM(total_amount) AS total_sales
                FROM
                    transactions
                WHERE
                    YEAR(transaction_date) = YEAR(CURDATE()) AND MONTH(transaction_date) = MONTH(CURDATE()) -- Laporan bulan ini secara default
                GROUP BY report_period";
    }


    if (!empty($sql)) {
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
    }
} else {
    // Default view when page is first loaded (e.g., daily report for today)
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d');
    $report_type = 'daily';

    $sql = "SELECT
                DATE(transaction_date) AS report_period,
                COUNT(transaction_id) AS total_transactions,
                SUM(total_amount) AS total_sales
            FROM
                transactions
            WHERE
                DATE(transaction_date) = CURDATE()
            GROUP BY report_period";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
    }
}
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="reports.php">Laporan</a></li>
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="users.php">Manajemen Pengguna</a></li>
                            <li><a class="dropdown-item" href="categories.php">Manajemen Kategori</a></li>
                            <li><a class="dropdown-item" href="suppliers.php">Manajemen Pemasok</a></li>
                            <li><a class="dropdown-item" href="units.php">Manajemen Satuan</a></li>
                            <li><hr class="dropdown-divider"></li> <li><a class="dropdown-item" href="purchases.php">Manajemen Pembelian</a></li>
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
        <h2>Laporan Penjualan</h2>
        <?php include 'includes/message.php'; // Tampilkan pesan ?>

        <form action="" method="post" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="report_type" class="form-label">Jenis Laporan</label>
                    <select class="form-select" id="report_type" name="report_type">
                        <option value="daily" <?php echo ($report_type == 'daily') ? 'selected' : ''; ?>>Harian</option>
                        <option value="monthly" <?php echo ($report_type == 'monthly') ? 'selected' : ''; ?>>Bulanan</option>
                        <option value="custom" <?php echo ($report_type == 'custom') ? 'selected' : ''; ?>>Rentang Kustom</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Tampilkan Laporan</button>
                </div>
            </div>
        </form>

        <?php if (!empty($report_data)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total_sales = 0;
                        $grand_total_transactions = 0;
                        foreach ($report_data as $row): 
                            $grand_total_sales += $row['total_sales'];
                            $grand_total_transactions += $row['total_transactions'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['report_period']); ?></td>
                            <td><?php echo htmlspecialchars($row['total_transactions']); ?></td>
                            <td>Rp <?php echo number_format($row['total_sales'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="1">Total Keseluruhan</th>
                            <th><?php echo $grand_total_transactions; ?></th>
                            <th>Rp <?php echo number_format($grand_total_sales, 0, ',', '.'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Tidak ada data penjualan untuk periode yang dipilih.</div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // JavaScript untuk mengelola visibilitas input tanggal berdasarkan jenis laporan
        document.addEventListener('DOMContentLoaded', function() {
            const reportTypeSelect = document.getElementById('report_type');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            function toggleDateInputs() {
                if (reportTypeSelect.value === 'monthly') {
                    startDateInput.type = 'month';
                    endDateInput.type = 'month';
                    // Opsional: set value ke bulan dan tahun sekarang jika kosong
                    if (!startDateInput.value) startDateInput.value = new Date().toISOString().slice(0, 7);
                    if (!endDateInput.value) endDateInput.value = new Date().toISOString().slice(0, 7);
                } else { // daily atau custom
                    startDateInput.type = 'date';
                    endDateInput.type = 'date';
                    // Opsional: set value ke tanggal hari ini jika kosong
                    if (!startDateInput.value) startDateInput.value = new Date().toISOString().slice(0, 10);
                    if (!endDateInput.value) endDateInput.value = new Date().toISOString().slice(0, 10);
                }
            }

            reportTypeSelect.addEventListener('change', toggleDateInputs);
            toggleDateInputs(); // Panggil saat halaman pertama kali dimuat untuk inisialisasi
        });
    </script>
</body>
</html>