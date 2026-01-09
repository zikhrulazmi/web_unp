<?php
session_start();
if(!isset($_SESSION['user_id'])){
  header("Location: auth/login.php");
}
include 'config/database.php';
include 'assets/header.php';

// Pastikan tabel notifications ada
$create_notif = "CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(191) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_notif);

$info = mysqli_fetch_assoc(
  mysqli_query($conn,"SELECT * FROM info_dashboard ORDER BY id DESC LIMIT 1")
);

// Tangani mark as read
if(isset($_POST['mark_read']) && isset($_POST['notif_id'])){
  $nid = mysqli_real_escape_string($conn, $_POST['notif_id']);
  mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE id='$nid' AND user_id='".intval($_SESSION['user_id'])."'");
}

// Ambil notifikasi untuk user yang sedang login
$notifs_q = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id='".intval($_SESSION['user_id'])."' ORDER BY created_at DESC LIMIT 10");
$unread_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM notifications WHERE user_id='".intval($_SESSION['user_id'])."' AND is_read=0"));

// Ambil data user
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT nama FROM users WHERE id='".intval($_SESSION['user_id'])."'"));

?>

<style>
  body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  
  .dashboard-header {
    background: white;
    padding: 30px 0;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 12px;
  }
  
  .dashboard-header h1 {
    color: #667eea;
    font-weight: 700;
    margin-bottom: 0;
  }
  
  .dashboard-header p {
    color: #666;
    margin-bottom: 0;
  }
  
  .menu-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    display: block;
  }
  
  .menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  }
  
  .menu-card i {
    font-size: 40px;
    margin-bottom: 15px;
    display: block;
  }
  
  .menu-card.profil i {
    color: #667eea;
  }
  
  .menu-card.profil:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  
  .menu-card.ajukan i {
    color: #28a745;
  }
  
  .menu-card.ajukan:hover {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
  }
  
  .menu-card.riwayat i {
    color: #fd7e14;
  }
  
  .menu-card.riwayat:hover {
    background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    color: white;
  }
  
  .menu-card.notifikasi i {
    color: #dc3545;
  }
  
  .menu-card.notifikasi:hover {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
    color: white;
  }
  
  .menu-card.labor i {
    color: #17a2b8;
  }
  
  .menu-card.labor:hover {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
  }
  
  .menu-card h5 {
    font-weight: 700;
    margin-bottom: 8px;
  }
  
  .menu-card p {
    margin-bottom: 0;
    font-size: 14px;
    color: #666;
  }
  
  .menu-card:hover p {
    color: inherit;
  }
  
  .notif-widget {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-bottom: 20px;
  }
  
  .notif-widget h5 {
    color: #667eea;
    font-weight: 700;
    margin-bottom: 20px;
  }
  
  .notif-item {
    border-left: 4px solid #667eea;
    padding: 15px;
    margin-bottom: 12px;
    background: #f8f9ff;
    border-radius: 6px;
    transition: all 0.2s ease;
  }
  
  .notif-item:hover {
    background: #f0f2ff;
  }
  
  .notif-item.unread {
    background: #fff3cd;
    border-left-color: #ffc107;
  }
  
  .notif-item-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 6px;
  }
  
  .notif-item-text {
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
  }
  
  .notif-item-time {
    font-size: 11px;
    color: #999;
  }
  
  .info-banner {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    border-left: 5px solid #667eea;
  }
  
  .info-banner h5 {
    color: #667eea;
    font-weight: 700;
    margin-bottom: 12px;
  }
  
  .info-banner p {
    color: #666;
    margin-bottom: 0;
    line-height: 1.6;
  }
  
  .container.mt-4 {
    background: transparent;
  }
</style>

<div class="dashboard-header mb-4">
  <div class="container">
    <h1><i class="fas fa-home"></i> Dashboard</h1>
    <p>Selamat datang kembali, <strong><?php echo htmlspecialchars($user['nama']); ?></strong>!</p>
  </div>
</div>

<div class="row">
  <!-- Menu Cards -->
  <div class="col-md-3 mb-3">
    <a href="user/profil.php" class="menu-card profil">
      <i class="fas fa-user-circle"></i>
      <h5>Profil Saya</h5>
      <p>Lihat dan edit data profil</p>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="peminjaman/tambah.php" class="menu-card ajukan">
      <i class="fas fa-plus-circle"></i>
      <h5>Ajukan Peminjaman</h5>
      <p>Minta peralatan labor</p>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="peminjaman/riwayat.php" class="menu-card riwayat">
      <i class="fas fa-history"></i>
      <h5>Riwayat</h5>
      <p>Lihat pengajuan anda</p>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="user/notifications.php" class="menu-card notifikasi">
      <i class="fas fa-bell"></i>
      <h5>Notifikasi</h5>
      <p><?php echo $unread_count > 0 ? $unread_count . ' belum dibaca' : 'Tidak ada belum dibaca'; ?></p>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="labor.php" class="menu-card labor">
      <i class="fas fa-building"></i>
      <h5>Daftar Labor</h5>
      <p>Lihat peralatan labor</p>
    </a>
  </div>
</div>

<div class="row mt-4">
  <!-- Notifikasi Widget -->
  <div class="col-lg-8">
    <div class="notif-widget">
      <h5><i class="fas fa-bell"></i> Notifikasi Terbaru</h5>

      <?php if(mysqli_num_rows($notifs_q) > 0): ?>
        <?php while($n = mysqli_fetch_assoc($notifs_q)): ?>
          <div class="notif-item <?php echo !$n['is_read'] ? 'unread' : ''; ?>">
            <div class="d-flex justify-content-between align-items-start">
              <div style="flex: 1;">
                <div class="notif-item-title"><?php echo htmlspecialchars($n['title']); ?></div>
                <div class="notif-item-text"><?php echo nl2br(htmlspecialchars($n['message'])); ?></div>
                <div class="notif-item-time"><i class="fas fa-clock"></i> <?php echo date('d-m-Y H:i', strtotime($n['created_at'])); ?></div>
              </div>
              <?php if(!$n['is_read']): ?>
                <form method="post" style="margin-left: 10px;">
                  <input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
                  <button type="submit" name="mark_read" class="btn btn-sm btn-primary" style="padding: 4px 8px; font-size: 11px;">Tandai dibaca</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
        <div class="text-center mt-3">
          <a href="user/notifications.php" class="btn btn-sm btn-outline-primary">Lihat Semua Notifikasi</a>
        </div>
      <?php else: ?>
        <div class="text-muted text-center py-4">
          <i class="fas fa-inbox" style="font-size: 40px; color: #ddd;"></i>
          <p class="mt-2">Tidak ada notifikasi baru</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Info Banner -->
  <div class="col-lg-4">
    <div class="info-banner">
      <h5><i class="fas fa-info-circle"></i> Informasi</h5>
      <p><?php echo $info ? htmlspecialchars($info['deskripsi']) : 'Tidak ada informasi saat ini.'; ?></p>
    </div>
  </div>
</div>

<?php include 'assets/footer.php'; ?>
