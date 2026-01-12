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
  
  .welcome-card {
    background: linear-gradient(180deg, #40e0d0 0%, #20b2aa 100%);
    color: white;
    border-radius: 20px;
    padding: 35px;
    margin-bottom: 30px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    border: 3px solid rgba(0,0,0,0.2);
  }
  
  .welcome-card h2 {
    font-weight: 900;
    margin-bottom: 8px;
    font-size: 32px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    letter-spacing: 1px;
  }
  
  .welcome-card p {
    opacity: 0.95;
    margin-bottom: 0;
    font-size: 16px;
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }
  
  .welcome-card .icon-wave {
    display: inline-block;
    animation: wave 1s ease-in-out infinite;
  }
  
  @keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(20deg); }
    75% { transform: rotate(-20deg); }
  }
  
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 30px;
  }
  
  .stat-card {
    background: linear-gradient(180deg, #48d1cc 0%, #20b2aa 100%);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.3);
    transition: all 0.3s;
    border: 3px solid rgba(0,0,0,0.2);
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
  
  /* Quick Actions */
  .quick-actions {
    margin-top: 30px;
  }
  
  .section-title {
    font-size: 24px;
    font-weight: 900;
    color: white;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    letter-spacing: 0.5px;
  }
  
  .section-title i {
    color: #ffe600;
  }
  
  .action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
  }
  
  .action-card {
    background: linear-gradient(180deg, #48d1cc 0%, #20b2aa 100%);
    border-radius: 20px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 6px 25px rgba(0,0,0,0.3);
    transition: all 0.3s;
    text-decoration: none;
    color: white;
    border: 3px solid rgba(0,0,0,0.2);
  }
  
  .action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 35px rgba(0,0,0,0.4);
    color: #ffe600;
  }
  
  .action-icon {
    width: 70px;
    height: 70px;
    background: rgba(255,255,255,0.25);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    transition: all 0.3s;
    border: 3px solid rgba(255,255,255,0.3);
  }
  
  .action-card:hover .action-icon {
    background: rgba(255,230,0,0.3);
    transform: scale(1.1);
  }
  
  .action-icon i {
    font-size: 32px;
    color: white;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
  }
  
  .action-card:hover .action-icon i {
    color: #ffe600;
  }
  
  .action-title {
    font-weight: 900;
    font-size: 16px;
    margin-bottom: 8px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    letter-spacing: 0.5px;
  }
  
  .action-desc {
    color: rgba(255,255,255,0.85);
    font-size: 13px;
    font-weight: 600;
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
    
    .welcome-card h2 {
      font-size: 24px;
    }
    
    .stats-grid,
    .action-grid {
      grid-template-columns: 1fr;
    }
    
    .section-title {
      font-size: 20px;
    }
  }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-custom">
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
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    
    <a href="dashboard.php" class="sidebar-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
      <i class="fas fa-home"></i>
      <span>Beranda</span>
    </a>
    
    <a href="peminjaman.php" class="sidebar-item <?php echo ($current_page == 'peminjaman.php') ? 'active' : ''; ?>">
      <i class="fas fa-list"></i>
      <span>Data Peminjaman</span>
    </a>
    
    <a href="peralatan.php" class="sidebar-item <?php echo ($current_page == 'peralatan.php') ? 'active' : ''; ?>">
      <i class="fas fa-tools"></i>
      <span>Kelola Peralatan</span>
    </a>
    
    <a href="notification_logs.php" class="sidebar-item <?php echo ($current_page == 'notification_logs.php') ? 'active' : ''; ?>">
      <i class="fas fa-envelope"></i>
      <span>Log Notifikasi Email</span>
    </a>
    
    <a href="info_dashboard.php" class="sidebar-item <?php echo ($current_page == 'info_dashboard.php') ? 'active' : ''; ?>">
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
  <div class="welcome-card">
    <h2>
      <span class="icon-wave">👋</span> 
      Selamat Datang, <?php echo htmlspecialchars($a['username']); ?>!
    </h2>
    <p>Kelola sistem peminjaman labor dengan mudah dan efisien</p>
  </div>

  <!-- Statistics Cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-list-check"></i>
      </div>
      <div class="stat-title">Total Peminjaman</div>
      <div class="stat-value">0</div>
      <div class="stat-desc">Peminjaman aktif</div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-tools"></i>
      </div>
      <div class="stat-title">Total Peralatan</div>
      <div class="stat-value">0</div>
      <div class="stat-desc">Peralatan tersedia</div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-clock"></i>
      </div>
      <div class="stat-title">Menunggu Persetujuan</div>
      <div class="stat-value">0</div>
      <div class="stat-desc">Perlu ditinjau</div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-envelope"></i>
      </div>
      <div class="stat-title">Email Terkirim</div>
      <div class="stat-value">0</div>
      <div class="stat-desc">Notifikasi hari ini</div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="quick-actions">
    <h3 class="section-title">
      <i class="fas fa-bolt"></i>
      Aksi Cepat
    </h3>
    
    <div class="action-grid">
      <a href="peminjaman.php" class="action-card">
        <div class="action-icon">
          <i class="fas fa-list"></i>
        </div>
        <div class="action-title">Data Peminjaman</div>
        <div class="action-desc">Lihat semua peminjaman</div>
      </a>

      <a href="peralatan.php" class="action-card">
        <div class="action-icon">
          <i class="fas fa-tools"></i>
        </div>
        <div class="action-title">Kelola Peralatan</div>
        <div class="action-desc">Tambah & edit peralatan</div>
      </a>

      <a href="notification_logs.php" class="action-card">
        <div class="action-icon">
          <i class="fas fa-envelope"></i>
        </div>
        <div class="action-title">Log Notifikasi</div>
        <div class="action-desc">Monitor email terkirim</div>
      </a>

      <a href="info_dashboard.php" class="action-card">
        <div class="action-icon">
          <i class="fas fa-edit"></i>
        </div>
        <div class="action-title">Info Dashboard</div>
        <div class="action-desc">Update informasi</div>
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle sidebar function
function toggleSidebar() {
  document.body.classList.toggle('sidebar-collapsed');
  
  // Save state to localStorage
  if (document.body.classList.contains('sidebar-collapsed')) {
    localStorage.setItem('sidebarCollapsed', 'true');
  } else {
    localStorage.setItem('sidebarCollapsed', 'false');
  }
}

// Load saved state on page load
document.addEventListener('DOMContentLoaded', function() {
  const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  if (isCollapsed) {
    document.body.classList.add('sidebar-collapsed');
  }
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
  const sidebar = document.getElementById('sidebar');
  const toggle = document.querySelector('.sidebar-toggle');
  
  if (window.innerWidth <= 768) {
    if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
      if (!document.body.classList.contains('sidebar-collapsed')) {
        toggleSidebar();
      }
    }
  }
});
</script>
</body>
</html>