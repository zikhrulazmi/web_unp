<?php
session_start();
include '../config/database.php';
include '../assets/header.php';

// Cek apakah admin sudah login
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit;
}

$message = '';
$message_type = '';

// Pastikan tabel labor ada
$create_labor = "CREATE TABLE IF NOT EXISTS labor (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(191) NOT NULL,
  deskripsi TEXT,
  icon VARCHAR(50),
  color VARCHAR(20),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_labor);

// Pastikan tabel barang_labor ada
$create_barang = "CREATE TABLE IF NOT EXISTS barang_labor (
  id INT AUTO_INCREMENT PRIMARY KEY,
  labor_id INT NOT NULL,
  nama VARCHAR(191) NOT NULL,
  deskripsi TEXT,
  stok INT DEFAULT 1,
  status VARCHAR(20) DEFAULT 'Tersedia',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (labor_id) REFERENCES labor(id)
)";
mysqli_query($conn, $create_barang);

// Tambah barang labor
if(isset($_POST['tambah'])){
  $labor_id = intval($_POST['labor_id']);
  $nama = mysqli_real_escape_string($conn, $_POST['nama']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $stok = intval($_POST['stok']);
  
  if(empty($nama) || !$labor_id){
    $message = "Labor dan nama barang harus diisi!";
    $message_type = "warning";
  } else {
    $ins = mysqli_query($conn, "INSERT INTO barang_labor (labor_id, nama, deskripsi, stok) VALUES ($labor_id, '$nama', '$deskripsi', $stok)");
    if($ins){
      $message = "Barang berhasil ditambahkan!";
      $message_type = "success";
    } else {
      $message = "Gagal menambahkan barang: " . mysqli_error($conn);
      $message_type = "danger";
    }
  }
}

// Edit barang labor
if(isset($_POST['edit'])){
  $id = intval($_POST['barang_id']);
  $labor_id = intval($_POST['labor_id']);
  $nama = mysqli_real_escape_string($conn, $_POST['nama']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $stok = intval($_POST['stok']);
  $status = mysqli_real_escape_string($conn, $_POST['status']);
  
  if(empty($nama) || !$labor_id){
    $message = "Labor dan nama barang harus diisi!";
    $message_type = "warning";
  } else {
    $upd = mysqli_query($conn, "UPDATE barang_labor SET labor_id=$labor_id, nama='$nama', deskripsi='$deskripsi', stok=$stok, status='$status' WHERE id=$id");
    if($upd){
      $message = "Barang berhasil diperbarui!";
      $message_type = "success";
    } else {
      $message = "Gagal memperbarui barang!";
      $message_type = "danger";
    }
  }
}

// Hapus barang labor
if(isset($_POST['hapus'])){
  $id = intval($_POST['barang_id']);
  $del = mysqli_query($conn, "DELETE FROM barang_labor WHERE id=$id");
  if($del){
    $message = "Barang berhasil dihapus!";
    $message_type = "success";
  } else {
    $message = "Gagal menghapus barang!";
    $message_type = "danger";
  }
}

// Ambil data labor
$labor_list = mysqli_query($conn, "SELECT * FROM labor ORDER BY nama ASC");

// Ambil data barang_labor
$barang_q = mysqli_query($conn, "SELECT bl.*, l.nama as labor_nama FROM barang_labor bl LEFT JOIN labor l ON bl.labor_id=l.id ORDER BY l.nama, bl.nama ASC");

$edit_data = null;
if(isset($_GET['edit'])){
  $edit_id = intval($_GET['edit']);
  $edit_q = mysqli_query($conn, "SELECT * FROM barang_labor WHERE id=$edit_id");
  $edit_data = mysqli_fetch_assoc($edit_q);
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Peralatan</title>
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
  .form-container, .table-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-bottom: 30px;
  }
  .form-label {
    color: #333;
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
  .table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e0e0e0;
    font-weight: 600;
    padding: 15px;
  }
  .table td {
    padding: 15px;
    border-color: #e0e0e0;
  }
  .btn-save {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
  }
  .btn-save:hover {
    color: white;
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
  <div class="back-link mb-3">
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
    <h2><i class="fas fa-tools"></i> Kelola Peralatan</h2>
    <p class="text-muted">Tambah, edit, dan kelola daftar peralatan yang dapat dipinjam</p>
  </div>

  <!-- Form -->
  <div class="form-container">
    <h5><?php echo $edit_data ? 'Edit Peralatan' : 'Tambah Peralatan Baru'; ?></h5>
    <form method="post">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label"><i class="fas fa-building"></i> Labor</label>
          <select class="form-control" name="labor_id" required>
            <option value="">-- Pilih Labor --</option>
            <?php 
            mysqli_data_seek($labor_list, 0);
            while($l = mysqli_fetch_assoc($labor_list)): 
            ?>
              <option value="<?php echo $l['id']; ?>" <?php echo $edit_data && $edit_data['labor_id'] == $l['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($l['nama']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label"><i class="fas fa-box"></i> Nama Peralatan</label>
          <input type="text" class="form-control" name="nama" placeholder="Nama peralatan..." required value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama']) : ''; ?>">
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label"><i class="fas fa-cubes"></i> Stok</label>
          <input type="number" class="form-control" name="stok" min="1" value="<?php echo $edit_data ? intval($edit_data['stok']) : 1; ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label"><i class="fas fa-info-circle"></i> Status</label>
          <select class="form-control" name="status" <?php echo $edit_data ? '' : 'disabled'; ?>>
            <option value="Tersedia" <?php echo ($edit_data && $edit_data['status'] === 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
            <option value="Tidak Tersedia" <?php echo ($edit_data && $edit_data['status'] === 'Tidak Tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
          </select>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label"><i class="fas fa-comment"></i> Deskripsi</label>
        <textarea class="form-control" name="deskripsi" rows="3" placeholder="Deskripsi peralatan..."><?php echo $edit_data ? htmlspecialchars($edit_data['deskripsi']) : ''; ?></textarea>
      </div>
      <div>
        <button type="submit" class="btn btn-save" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>">
          <i class="fas fa-<?php echo $edit_data ? 'save' : 'plus'; ?>"></i> <?php echo $edit_data ? 'Perbarui' : 'Tambah'; ?>
        </button>
        <?php if($edit_data): ?>
          <a href="peralatan.php" class="btn btn-secondary">Batal</a>
        <?php endif; ?>
      </div>
      <?php if($edit_data): ?>
        <input type="hidden" name="barang_id" value="<?php echo $edit_data['id']; ?>">
      <?php endif; ?>
    </form>
  </div>

  <!-- Data Table -->
  <div class="table-container">
    <h5>Daftar Peralatan</h5>
    <?php if(mysqli_num_rows($barang_q) > 0): ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th><i class="fas fa-building"></i> Labor</th>
              <th><i class="fas fa-box"></i> Nama Peralatan</th>
              <th><i class="fas fa-align-left"></i> Deskripsi</th>
              <th><i class="fas fa-cubes"></i> Stok</th>
              <th><i class="fas fa-info-circle"></i> Status</th>
              <th style="text-align: center;"><i class="fas fa-cog"></i> Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while($a = mysqli_fetch_assoc($barang_q)): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($a['labor_nama']); ?></strong></td>
                <td><?php echo htmlspecialchars($a['nama']); ?></td>
                <td><?php echo htmlspecialchars(substr($a['deskripsi'] ?? '', 0, 40)); ?></td>
                <td><?php echo intval($a['stok']); ?></td>
                <td>
                  <?php if($a['status'] === 'Tersedia'): ?>
                    <span class="badge bg-success">Tersedia</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Tidak Tersedia</span>
                  <?php endif; ?>
                </td>
                <td style="text-align: center;">
                  <a href="peralatan.php?edit=<?php echo $a['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="barang_id" value="<?php echo $a['id']; ?>">
                    <button type="submit" name="hapus" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus peralatan ini?')">
                      <i class="fas fa-trash"></i> Hapus
                    </button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-muted text-center py-5">
        <i class="fas fa-inbox" style="font-size: 48px; color: #ddd;"></i>
        <p>Tidak ada peralatan</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
