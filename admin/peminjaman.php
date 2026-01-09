<?php
session_start();
include '../config/database.php';

// Cek apakah admin sudah login
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit;
}

// Pastikan kolom yang diperlukan ada di tabel peminjaman
// Jika kolom tidak ada, tambahkan secara otomatis dengan default yang aman
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
  .stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    border-left: 5px solid;
  }
  .stat-card.pending {
    border-left-color: #ffc107;
  }
  .stat-card.approved {
    border-left-color: #28a745;
  }
  .stat-card.rejected {
    border-left-color: #dc3545;
  }
  .stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #667eea;
  }
  .stat-label {
    color: #999;
    font-size: 14px;
    margin-top: 5px;
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
  .badge-pending {
    background-color: #ffc107;
    color: #000;
  }
  .badge-approved {
    background-color: #28a745;
  }
  .badge-rejected {
    background-color: #dc3545;
  }
  .btn-approve, .btn-reject {
    padding: 8px 12px;
    font-size: 13px;
    border-radius: 6px;
  }
  .btn-approve {
    background-color: #28a745;
    border: none;
    color: white;
  }
  .btn-approve:hover {
    background-color: #218838;
    color: white;
  }
  .btn-reject {
    background-color: #dc3545;
    border: none;
    color: white;
  }
  .btn-reject:hover {
    background-color: #c82333;
    color: white;
  }
  .btn-back {
    background-color: #667eea;
    border: none;
    color: white;
  }
  .btn-back:hover {
    background-color: #764ba2;
    color: white;
  }
  .modal-header {
    background-color: #667eea;
    color: white;
  }
  .modal-body {
    padding: 25px;
  }
  .form-label {
    color: #555;
    font-weight: 600;
    margin-bottom: 8px;
  }
  .form-control {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px;
  }
  .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
  .back-link a:hover {
    color: #764ba2;
  }
  .empty-message {
    text-align: center;
    padding: 40px;
    color: #999;
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
    <p class="text-muted">Kelola dan persetujuan peminjaman dari mahasiswa</p>
  </div>

  <!-- Statistics -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="stat-card pending">
        <div class="stat-number"><?php echo $pending; ?></div>
        <div class="stat-label"><i class="fas fa-clock"></i> Menunggu Persetujuan</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card approved">
        <div class="stat-number"><?php echo $approved; ?></div>
        <div class="stat-label"><i class="fas fa-check-circle"></i> Disetujui</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card rejected">
        <div class="stat-number"><?php echo $rejected; ?></div>
        <div class="stat-label"><i class="fas fa-times-circle"></i> Ditolak</div>
      </div>
    </div>
  </div>

  <!-- Data Table -->
  <div class="table-container">
    <?php if(mysqli_num_rows($q) > 0): ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th><i class="fas fa-user"></i> Nama</th>
              <th><i class="fas fa-id-card"></i> NIM</th>
              <th><i class="fas fa-calendar"></i> Tanggal</th>
              <th><i class="fas fa-building"></i> Labor</th>
              <th><i class="fas fa-box"></i> Peralatan</th>
              <th><i class="fas fa-file-alt"></i> Keperluan</th>
              <th><i class="fas fa-info-circle"></i> Status</th>
              <th style="text-align: center;"><i class="fas fa-cog"></i> Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while($d = mysqli_fetch_assoc($q)): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($d['nama']); ?></strong></td>
                <td><?php echo htmlspecialchars($d['nim']); ?></td>
                <td><?php echo date('d-m-Y H:i', strtotime($d['tanggal'])); ?></td>
                <td><strong><?php echo htmlspecialchars($d['labor_nama'] ?? '-'); ?></strong></td>
                <td><?php echo htmlspecialchars($d['barang_nama'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($d['tanggung_jawab']); ?></td>
                <td>
                  <?php 
                    $status = $d['status'] ?? '';
                    if($status === 'Menunggu'){
                      echo '<span class="badge badge-pending">Menunggu</span>';
                    } elseif($status === 'Disetujui'){
                      echo '<span class="badge badge-approved">Disetujui</span>';
                    } elseif($status === 'Ditolak'){
                      echo '<span class="badge badge-rejected">Ditolak</span>';
                    } else {
                      echo '<span class="text-muted">-</span>';
                    }
                  ?>
                </td>
                <td style="text-align: center;">
                  <?php if($status === 'Menunggu'): ?>
                    <button class="btn btn-approve btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $d['id']; ?>">
                      <i class="fas fa-check"></i> Setujui
                    </button>
                    <button class="btn btn-reject btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $d['id']; ?>">
                      <i class="fas fa-times"></i> Tolak
                    </button>

                    <!-- Modal Setujui -->
                    <div class="modal fade" id="approveModal<?php echo $d['id']; ?>" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-check-circle"></i> Setujui Peminjaman</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <p>Apakah Anda yakin ingin menyetujui peminjaman dari <strong><?php echo htmlspecialchars($d['nama']); ?></strong>?</p>
                            <div class="alert alert-info">
                              <strong>NIM:</strong> <?php echo htmlspecialchars($d['nim']); ?><br>
                              <strong>Labor:</strong> <?php echo htmlspecialchars($d['labor_nama'] ?? '-'); ?><br>
                              <strong>Peralatan:</strong> <?php echo htmlspecialchars($d['barang_nama'] ?? '-'); ?><br>
                              <strong>Tanggal:</strong> <?php echo date('d-m-Y H:i', strtotime($d['tanggal'])); ?><br>
                              <strong>Keperluan:</strong> <?php echo htmlspecialchars($d['tanggung_jawab']); ?>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <form method="post" style="display:inline;">
                              <input type="hidden" name="peminjaman_id" value="<?php echo $d['id']; ?>">
                              <button type="submit" name="approve" class="btn btn-success">
                                <i class="fas fa-check"></i> Setujui
                              </button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Modal Tolak -->
                    <div class="modal fade" id="rejectModal<?php echo $d['id']; ?>" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-times-circle"></i> Tolak Peminjaman</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                          </div>
                          <form method="post">
                            <div class="modal-body">
                              <p>Apakah Anda yakin ingin menolak peminjaman dari <strong><?php echo htmlspecialchars($d['nama']); ?></strong>?</p>
                              <div class="alert alert-warning mb-3">
                                <strong>Labor:</strong> <?php echo htmlspecialchars($d['labor_nama'] ?? '-'); ?><br>
                                <strong>Peralatan:</strong> <?php echo htmlspecialchars($d['barang_nama'] ?? '-'); ?><br>
                                <strong>Tanggal:</strong> <?php echo date('d-m-Y H:i', strtotime($d['tanggal'])); ?>
                              </div>
                              <div class="mb-3">
                                <label class="form-label"><i class="fas fa-comment"></i> Alasan Penolakan</label>
                                <textarea class="form-control" name="alasan_penolakan" rows="4" placeholder="Jelaskan alasan penolakan..." required></textarea>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <input type="hidden" name="peminjaman_id" value="<?php echo $d['id']; ?>">
                              <button type="submit" name="reject" class="btn btn-danger">
                                <i class="fas fa-times"></i> Tolak
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-message">
        <i class="fas fa-inbox" style="font-size: 48px; color: #ddd;"></i>
        <p>Tidak ada data peminjaman</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
