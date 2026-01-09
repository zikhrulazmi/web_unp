<?php
session_start();
include '../config/database.php';

// Cek apakah admin sudah login
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit;
}

$id = $_SESSION['admin_id'];
$q = mysqli_query($conn, "SELECT * FROM admin WHERE id=$id");
$a = mysqli_fetch_assoc($q);

if(!$a){
  die("Admin tidak ditemukan");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin</title>
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
  .sidebar {
    min-height: 100vh;
    background: white;
    box-shadow: 2px 0 10px rgba(0,0,0,0.05);
  }
  .sidebar-item {
    padding: 15px 20px;
    border-left: 4px solid transparent;
    text-decoration: none;
    color: #555;
    display: block;
    transition: all 0.3s;
    border-radius: 0 8px 8px 0;
    margin: 5px 10px;
  }
  .sidebar-item:hover {
    background-color: #f0f2f5;
    border-left-color: #667eea;
    color: #667eea;
  }
  .sidebar-item.active {
    background-color: #f0f2f5;
    border-left-color: #667eea;
    color: #667eea;
    font-weight: 600;
  }
  .card-widget {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
  }
  .card-widget:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 25px rgba(0,0,0,0.15);
  }
  .welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
  }
  .welcome-card h2 {
    font-weight: 700;
    margin-bottom: 5px;
  }
  .welcome-card p {
    opacity: 0.9;
    margin-bottom: 0;
  }
  .menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
  }
  .menu-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    transition: all 0.3s;
    text-decoration: none;
    color: #333;
  }
  .menu-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 5px 30px rgba(0,0,0,0.15);
    color: #667eea;
  }
  .menu-icon {
    font-size: 40px;
    color: #667eea;
    margin-bottom: 15px;
  }
  .menu-card:hover .menu-icon {
    transform: scale(1.1);
  }
  .menu-title {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 10px;
  }
  .menu-desc {
    color: #999;
    font-size: 13px;
  }
  .footer-bar {
    background: white;
    border-top: 1px solid #e0e0e0;
    padding: 20px;
    margin-top: 40px;
    text-align: center;
    color: #999;
  }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container-fluid">
    <a class="navbar-brand text-white" href="#">
      <i class="fas fa-tools"></i> Admin Peminjaman Labor
    </a>
    <div class="navbar-nav ms-auto">
      <div class="nav-item dropdown">
        <a class="nav-link text-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
          <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($a['username']); ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="container-fluid py-5">
  <div class="welcome-card">
    <h2><i class="fas fa-wave-hand"></i> Selamat Datang, <?php echo htmlspecialchars($a['username']); ?>!</h2>
    <p>Kelola sistem peminjaman labor dengan mudah dan efisien</p>
  </div>

  <!-- Menu Cards -->
  <div class="menu-grid">
    <a href="peminjaman.php" class="menu-card">
      <div class="menu-icon">
        <i class="fas fa-list"></i>
      </div>
      <div class="menu-title">Data Peminjaman</div>
      <div class="menu-desc">Lihat dan kelola data peminjaman labor</div>
    </a>

    <a href="peralatan.php" class="menu-card">
      <div class="menu-icon">
        <i class="fas fa-tools"></i>
      </div>
      <div class="menu-title">Kelola Peralatan</div>
      <div class="menu-desc">Tambah dan kelola daftar peralatan</div>
    </a>

    <a href="notification_logs.php" class="menu-card">
      <div class="menu-icon">
        <i class="fas fa-envelope"></i>
      </div>
      <div class="menu-title">Log Notifikasi Email</div>
      <div class="menu-desc">Monitor pengiriman email notifikasi</div>
    </a>

    <a href="info_dashboard.php" class="menu-card">
      <div class="menu-icon">
        <i class="fas fa-edit"></i>
      </div>
      <div class="menu-title">Kelola Info Dashboard</div>
      <div class="menu-desc">Perbarui informasi di halaman utama</div>
    </a>
  </div>
</div>

<!-- Footer -->
<div class="footer-bar">
  <p>&copy; 2024 Sistem Peminjaman Labor. All rights reserved.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
