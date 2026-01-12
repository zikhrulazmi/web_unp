<?php
session_start();
include '../config/database.php';

// Cek apakah admin sudah login
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit;
}

// Pastikan tabel notifikasi logs ada
$create_logs = "CREATE TABLE IF NOT EXISTS notification_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notification_id INT NULL,
  user_id INT NOT NULL,
  email VARCHAR(191) NULL,
  success TINYINT(1) DEFAULT 0,
  error_message TEXT NULL,
  sent_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_logs);

// Ambil logs
$logs_q = mysqli_query($conn, "
  SELECT nl.*, u.nama, u.nim, u.email as user_email
  FROM notification_logs nl
  LEFT JOIN users u ON nl.user_id=u.id
  ORDER BY nl.sent_at DESC LIMIT 100
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log Notifikasi Email</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  body {
    background-color: #f5f7fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .navbar-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }
  .page-header {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
  }
  .table-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
  }
  .table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e0e0e0;
    color: #333;
    font-weight: 600;
    padding: 15px;
  }
  .table td {
    padding: 15px;
    border-color: #e0e0e0;
  }
  .badge-success {
    background-color: #28a745;
  }
  .badge-danger {
    background-color: #dc3545;
  }
  .badge-warning {
    background-color: #ffc107;
  }
  .back-link {
    display: inline-block;
    margin-bottom: 20px;
  }
  .back-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
  }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container-fluid">
    <a class="navbar-brand text-white" href="dashboard.php">
      <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>
  </div>
</nav>

<div class="container-fluid py-4">
  <div class="back-link">
    <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>

  <!-- Page Header -->
  <div class="page-header">
    <h2><i class="fas fa-envelope"></i> Log Notifikasi Email</h2>
    <p class="text-muted">Pantau pengiriman email notifikasi peminjaman</p>
  </div>

  <!-- Data Table -->
  <div class="table-container">
    <?php if(mysqli_num_rows($logs_q) > 0): ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th><i class="fas fa-user"></i> Nama User</th>
              <th><i class="fas fa-id-card"></i> NIM</th>
              <th><i class="fas fa-envelope"></i> Email Tujuan</th>
              <th><i class="fas fa-check-circle"></i> Status</th>
              <th><i class="fas fa-exclamation-circle"></i> Error</th>
              <th><i class="fas fa-calendar"></i> Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php while($l = mysqli_fetch_assoc($logs_q)): ?>
              <tr>
                <td><?php echo htmlspecialchars($l['nama'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($l['nim'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($l['email'] ?? '-'); ?></td>
                <td>
                  <?php if($l['success']): ?>
                    <span class="badge badge-success">Berhasil</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Gagal</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if($l['error_message']): ?>
                    <small class="text-danger"><?php echo htmlspecialchars($l['error_message']); ?></small>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?php echo date('d-m-Y H:i', strtotime($l['sent_at'])); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-muted text-center py-5">
        <i class="fas fa-inbox" style="font-size: 48px; color: #ddd;"></i>
        <p>Tidak ada log notifikasi</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
