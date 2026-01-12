<?php
session_start();
include '../config/database.php';

// Cek apakah admin sudah login
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit;
}

// Pastikan kolom yang diperlukan ada di tabel peminjaman
$check_status_col = mysqli_query($conn, "SHOW COLUMNS FROM peminjaman LIKE 'status'");
if($check_status_col && mysqli_num_rows($check_status_col) == 0){
  mysqli_query($conn, "ALTER TABLE peminjaman ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Menunggu'");
}

$check_ket_col = mysqli_query($conn, "SHOW COLUMNS FROM peminjaman LIKE 'keterangan'");
if($check_ket_col && mysqli_num_rows($check_ket_col) == 0){
  mysqli_query($conn, "ALTER TABLE peminjaman ADD COLUMN keterangan VARCHAR(255) NULL");
}

$message = '';
$message_type = '';

// Pastikan tabel notifikasi ada
$create_notif = "CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(191) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_notif);

// Pastikan tabel log notifikasi ada
$create_log = "CREATE TABLE IF NOT EXISTS notification_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notification_id INT NULL,
  user_id INT NOT NULL,
  email VARCHAR(191) NULL,
  success TINYINT(1) DEFAULT 0,
  error_message TEXT NULL,
  sent_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_log);

// Pastikan tabel peralatan ada
$create_alat = "CREATE TABLE IF NOT EXISTS peralatan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(191) NOT NULL,
  deskripsi TEXT NULL,
  stok INT DEFAULT 1,
  status VARCHAR(20) DEFAULT 'Tersedia',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_alat);

// Load mailer wrapper
require_once __DIR__ . '/../config/mailer.php';

// Setujui peminjaman
if(isset($_POST['approve'])){
  $id = mysqli_real_escape_string($conn, $_POST['peminjaman_id']);
  $update = mysqli_query($conn, "UPDATE peminjaman SET status='Disetujui' WHERE id='$id'");

  if($update){
    // Ambil detail peminjaman dan user
    $p_q = mysqli_query($conn, "SELECT p.*, u.email, u.nama AS user_nama, l.nama AS labor_nama, bl.nama AS barang_nama FROM peminjaman p JOIN users u ON p.user_id=u.id LEFT JOIN labor l ON p.labor_id=l.id LEFT JOIN barang_labor bl ON p.barang_labor_id=bl.id WHERE p.id='$id'");
    $p = mysqli_fetch_assoc($p_q);

    // Tentukan penanggung jawab labor hari ini (rotasi berdasarkan hari ke- tahun)
    $admins_q = mysqli_query($conn, "SELECT * FROM admin ORDER BY id ASC");
    $admins = [];
    while($ad = mysqli_fetch_assoc($admins_q)) $admins[] = $ad;
    $penanggung_nama = 'Staf Labor';
    if(count($admins) > 0){
      $idx = (int)date('z') % count($admins);
      $penanggung_nama = $admins[$idx]['username'];
    }

    // Siapkan pesan untuk notifikasi/email
    $labor_display = isset($p['labor_nama']) ? $p['labor_nama'] : 'Labor';
    $barang_display = isset($p['barang_nama']) ? $p['barang_nama'] : '-';
    $keperluan = isset($p['tanggung_jawab']) ? $p['tanggung_jawab'] : '';
    $judul_notif = 'Peminjaman Disetujui';
    $pesan = "Halo " . ($p['user_nama'] ?? '') . ",\n\nPeminjaman Anda telah disetujui.\n\nDetail:\n- Labor: $labor_display\n- Peralatan: $barang_display\n- Keperluan: $keperluan\n- Penanggung Jawab Labor " . $labor_display . " hari ini: $penanggung_nama\n\nSilakan hubungi penanggung jawab untuk koordinasi lebih lanjut.";

    // Simpan notifikasi ke database
    $notif_id = null;
    if(isset($p['user_id'])){
      $u_id = (int)$p['user_id'];
      $ins = mysqli_query($conn, "INSERT INTO notifications (user_id, title, message) VALUES ('$u_id', '".mysqli_real_escape_string($conn,$judul_notif)."', '".mysqli_real_escape_string($conn,$pesan)."')");
      if($ins) $notif_id = mysqli_insert_id($conn);
    }

    // Kirim email (jika tersedia) menggunakan wrapper send_mail()
    if(!empty($p['email'])){
      $to = $p['email'];
      $subject = $judul_notif;
      $sent = send_mail($to, $subject, $pesan);
      // simpan log pengiriman
      $email_esc = mysqli_real_escape_string($conn, $to);
      $err_msg = $sent ? '' : 'send_failed';
      mysqli_query($conn, "INSERT INTO notification_logs (notification_id, user_id, email, success, error_message) VALUES (".($notif_id ? intval($notif_id) : 'NULL').", '".intval($u_id)."', '$email_esc', ".($sent ? 1 : 0).", '".mysqli_real_escape_string($conn,$err_msg)."')");
    } else {
      // tidak ada email, catat log bahwa tidak ada email
      if(isset($u_id)){
        mysqli_query($conn, "INSERT INTO notification_logs (notification_id, user_id, email, success, error_message) VALUES (".($notif_id ? intval($notif_id) : 'NULL').", '".intval($u_id)."', NULL, 0, 'no_email')");
      }
    }

    $message = "Peminjaman berhasil disetujui! Notifikasi telah dikirim ke user.";
    $message_type = "success";
  } else {
    $message = "Gagal menyetujui peminjaman!";
    $message_type = "danger";
  }
}

// Tolak peminjaman
if(isset($_POST['reject'])){
  $id = mysqli_real_escape_string($conn, $_POST['peminjaman_id']);
  $alasan = mysqli_real_escape_string($conn, $_POST['alasan_penolakan']);
  
  if(empty($alasan)){
    $message = "Alasan penolakan harus diisi!";
    $message_type = "warning";
  } else {
    $update = mysqli_query($conn, "UPDATE peminjaman SET status='Ditolak', keterangan='$alasan' WHERE id='$id'");
    
    if($update){
      $message = "Peminjaman berhasil ditolak!";
      $message_type = "success";
    } else {
      $message = "Gagal menolak peminjaman!";
      $message_type = "danger";
    }
  }
}

// Ambil data peminjaman dengan info labor dan barang
$q = mysqli_query($conn, "
  SELECT p.*, u.nama, u.nim, l.nama as labor_nama, bl.nama as barang_nama
  FROM peminjaman p
  JOIN users u ON p.user_id=u.id
  LEFT JOIN labor l ON p.labor_id=l.id
  LEFT JOIN barang_labor bl ON p.barang_labor_id=bl.id
  ORDER BY p.tanggal DESC
");

// Hitung status dengan error checking
$pending_query = mysqli_query($conn, "SELECT id FROM peminjaman WHERE status='Menunggu'");
$pending = ($pending_query) ? mysqli_num_rows($pending_query) : 0;

$approved_query = mysqli_query($conn, "SELECT id FROM peminjaman WHERE status='Disetujui'");
$approved = ($approved_query) ? mysqli_num_rows($approved_query) : 0;

$rejected_query = mysqli_query($conn, "SELECT id FROM peminjaman WHERE status='Ditolak'");
$rejected = ($rejected_query) ? mysqli_num_rows($rejected_query) : 0;

// Get admin info
$id = $_SESSION['admin_id'];
$admin_q = mysqli_query($conn, "SELECT * FROM admin WHERE id=$id");
$a = mysqli_fetch_assoc($admin_q);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Peminjaman</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }
  
  body {
    background: linear-gradient(135deg, #0d4d4d 0%, #1a6b6b 50%, #0d4d4d 100%);
    min-height: 100vh;
    font-family: 'Cooper Black', 'Arial Black', sans-serif;
    overflow-x: hidden;
    position: relative;
  }
  
  body::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: 
      linear-gradient(45deg, transparent 48%, rgba(255,255,255,0.03) 49%, rgba(255,255,255,0.03) 51%, transparent 52%),
      linear-gradient(-45deg, transparent 48%, rgba(255,255,255,0.03) 49%, rgba(255,255,255,0.03) 51%, transparent 52%);
    background-size: 80px 80px;
    opacity: 0.5;
    pointer-events: none;
  }
  
  /* Navbar */
  .navbar-custom {
    background: linear-gradient(135deg, #40e0d0 0%, #20b2aa 100%);
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    padding: 15px 30px;
    border-bottom: 3px solid rgba(0,0,0,0.2);
  }
  
  .navbar-brand {
    font-weight: 900;
    font-size: 22px;
    color: white !important;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    letter-spacing: 0.5px;
  }
  
  .nav-link {
    color: white !important;
    font-weight: 700;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
  }
  
  .dropdown-menu {
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  }
  
  /* Sidebar */
  .sidebar {
    position: fixed;
    top: 70px;
    left: 0;
    width: 280px;
    height: calc(100vh - 70px);
    background: linear-gradient(180deg, #40e0d0 0%, #20b2aa 100%);
    box-shadow: 4px 0 20px rgba(0,0,0,0.3);
    overflow-y: auto;
    transition: all 0.3s;
    z-index: 999;
    border-right: 3px solid rgba(0,0,0,0.2);
  }
  
  .sidebar-header {
    padding: 25px 20px;
    background: rgba(0,0,0,0.15);
    border-bottom: 2px solid rgba(255,255,255,0.2);
  }
  
  .sidebar-header h5 {
    margin: 0;
    font-weight: 900;
    color: white;
    font-size: 20px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    letter-spacing: 0.5px;
  }
  
  .sidebar-header p {
    margin: 5px 0 0 0;
    font-size: 13px;
    color: rgba(255,255,255,0.9);
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }
  
  .sidebar-menu {
    padding: 20px 0;
  }
  
  .sidebar-item {
    padding: 15px 25px;
    text-decoration: none;
    color: white;
    display: flex;
    align-items: center;
    transition: all 0.3s;
    border-left: 4px solid transparent;
    margin: 5px 0;
    font-weight: 700;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
  }
  
  .sidebar-item i {
    width: 25px;
    font-size: 18px;
    margin-right: 15px;
    color: white;
  }
  
  .sidebar-item span {
    font-weight: 700;
    font-size: 15px;
  }
  
  .sidebar-item:hover {
    background-color: rgba(255,255,255,0.2);
    border-left-color: #ffe600;
    color: #ffe600;
  }
  
  .sidebar-item.active {
    background-color: rgba(255,255,255,0.25);
    border-left-color: #ffe600;
    color: #ffe600;
    font-weight: 900;
  }
  
  /* Toggle button */
  .sidebar-toggle {
    position: fixed;
    top: 80px;
    left: 20px;
    z-index: 1001;
    background: linear-gradient(180deg, #48d1cc 0%, #20b2aa 100%);
    border: 3px solid rgba(255,255,255,0.3);
    border-radius: 12px;
    padding: 10px 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    cursor: pointer;
    transition: all 0.3s;
  }
  
  .sidebar-toggle:hover {
    background: linear-gradient(180deg, #5fe0d5 0%, #30c2b9 100%);
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
  }
  
  .sidebar-toggle i {
    font-size: 18px;
    color: white;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }
  
  /* Sidebar collapsed state */
  body.sidebar-collapsed .sidebar {
    left: -280px;
  }
  
  body.sidebar-collapsed .main-content {
    margin-left: 0;
  }
  
  body.sidebar-collapsed .sidebar-toggle {
    left: 20px;
  }
  
  body:not(.sidebar-collapsed) .sidebar-toggle {
    left: 300px;
  }
  
  /* Main Content */
  .main-content {
    margin-left: 280px;
    margin-top: 70px;
    padding: 40px;
    min-height: calc(100vh - 70px);
    transition: all 0.3s;
    position: relative;
    z-index: 1;
  }
  
  .page-header {
    background: linear-gradient(180deg, #40e0d0 0%, #20b2aa 100%);
    color: white;
    border-radius: 20px;
    padding: 35px;
    margin-bottom: 30px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    border: 3px solid rgba(0,0,0,0.2);
  }
  
  .page-header h2 {
    font-weight: 900;
    margin-bottom: 8px;
    font-size: 32px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    letter-spacing: 1px;
  }
  
  .page-header p {
    opacity: 0.95;
    margin-bottom: 0;
    font-size: 16px;
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }
  
  /* Statistics */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
  }
  
  .stat-card {
    background: linear-gradient(180deg, #48d1cc 0%, #20b2aa 100%);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.3);
    transition: all 0.3s;
    border: 3px solid rgba(0,0,0,0.2);
    color: white;
  }
  
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 35px rgba(0,0,0,0.4);
  }
  
  .stat-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.25);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    border: 2px solid rgba(255,255,255,0.3);
  }
  
  .stat-icon i {
    font-size: 28px;
    color: white;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
  }
  
  .stat-title {
    font-size: 14px;
    color: rgba(255,255,255,0.9);
    margin-bottom: 8px;
    font-weight: 700;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }
  
  .stat-value {
    font-size: 36px;
    font-weight: 900;
    color: white;
    margin-bottom: 5px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
  }
  
  .stat-desc {
    font-size: 13px;
    color: rgba(255,255,255,0.85);
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }
  
  /* Table Container */
  .table-container {
    background: linear-gradient(180deg, #48d1cc 0%, #20b2aa 100%);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.3);
    border: 3px solid rgba(0,0,0,0.2);
  }
  
  .table-responsive {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  }
  
  .table {
    margin-bottom: 0;
  }
  
  .table th {
    background: linear-gradient(135deg, #40e0d0 0%, #20b2aa 100%);
    color: white;
    border: none;
    padding: 18px 15px;
    font-weight: 900;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    letter-spacing: 0.5px;
  }
  
  .table td {
    padding: 15px;
    border-color: #e0e0e0;
    vertical-align: middle;
  }
  
  .table tbody tr:hover {
    background-color: #f8f9fa;
  }
  
  /* Badges */
  .badge {
    padding: 8px 15px;
    font-weight: 700;
    font-size: 13px;
    border-radius: 8px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
  }
  
  .badge-pending {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #000;
  }
  
  .badge-approved {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
  }
  
  .badge-rejected {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }
  
  /* Buttons */
  .btn-approve, .btn-reject {
    padding: 8px 15px;
    font-size: 13px;
    border-radius: 8px;
    font-weight: 700;
    border: none;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
  }
  
  .btn-approve {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    margin-right: 5px;
  }
  
  .btn-approve:hover {
    background: linear-gradient(135deg, #218838 0%, #1ba87d 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    color: white;
  }
  
  .btn-reject {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }
  
  .btn-reject:hover {
    background: linear-gradient(135deg, #c82333 0%, #a71d2a 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    color: white;
  }
  
  /* Modal */
  .modal-header {
    background: linear-gradient(135deg, #40e0d0 0%, #20b2aa 100%);
    color: white;
    border-bottom: 3px solid rgba(0,0,0,0.2);
  }
  
  .modal-title {
    font-weight: 900;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }
  
  .modal-body {
    padding: 25px;
  }
  
  .modal-footer {
    border-top: 2px solid #e0e0e0;
    padding: 20px;
  }
  
  .form-label {
    color: #333;
    font-weight: 700;
    margin-bottom: 8px;
  }
  
  .form-control {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px;
  }
  
  .form-control:focus {
    border-color: #40e0d0;
    box-shadow: 0 0 0 0.2rem rgba(64, 224, 208, 0.25);
  }
  
  .alert {
    border-radius: 12px;
    border: none;
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }
  
  .alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
  }
  
  .alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
  }
  
  .empty-message {
    text-align: center;
    padding: 60px 20px;
    color: rgba(255,255,255,0.9);
  }
  
  .empty-message i {
    font-size: 64px;
    color: rgba(255,255,255,0.5);
    margin-bottom: 20px;
  }
  
  .empty-message p {
    font-size: 18px;
    font-weight: 700;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }
  
  /* Mobile responsive */
  @media (max-width: 768px) {
    body:not(.sidebar-collapsed) .sidebar-toggle {
      left: 300px;
    }
    
    .main-content {
      padding: 20px;
    }
    
    .page-header h2 {
      font-size: 24px;
    }
    
    .stats-grid {
      grid-template-columns: 1fr;
    }
    
    .table-container {
      padding: 15px;
    }
    
    .btn-approve, .btn-reject {
      display: block;
      width: 100%;
      margin: 5px 0;
    }
  }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-custom">
  <div class="container-fluid">
    <a class="navbar-brand text-white" href="dashboard.php">
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

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
  <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h5><i class="fas fa-gauge-high"></i> Dashboard</h5>
    <p>Panel Administrasi</p>
  </div>
  
  <div class="sidebar-menu">
    <a href="dashboard.php" class="sidebar-item">
      <i class="fas fa-home"></i>
      <span>Beranda</span>
    </a>
    
    <a href="peminjaman.php" class="sidebar-item active">
      <i class="fas fa-list"></i>
      <span>Data Peminjaman</span>
    </a>
    
    <a href="peralatan.php" class="sidebar-item">
      <i class="fas fa-tools"></i>
      <span>Kelola Peralatan</span>
    </a>
    
    <a href="notification_logs.php" class="sidebar-item">
      <i class="fas fa-envelope"></i>
      <span>Log Notifikasi Email</span>
    </a>
    
    <a href="info_dashboard.php" class="sidebar-item">
      <i class="fas fa-edit"></i>
      <span>Kelola Info Dashboard</span>
    </a>
    
    <div style="border-top: 2px solid rgba(255,255,255,0.2); margin: 20px 0;"></div>
    
    <a href="logout.php" class="sidebar-item">
      <i class="fas fa-sign-out-alt"></i>
      <span>Logout</span>
    </a>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <!-- Message Alert -->
  <?php if($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
      <i class="fas fa-info-circle"></i> <?php echo $message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="page-header">
    <h2><i class="fas fa-list"></i> Data Peminjaman Labor</h2>
    <p>Kelola dan persetujuan peminjaman dari mahasiswa</p>
  </div>

  <!-- Statistics -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon