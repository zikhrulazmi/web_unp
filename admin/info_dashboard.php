<?php
session_start();
include '../config/database.php';

// Cek apakah admin sudah login
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit;
}

$message = '';
$message_type = '';

if(isset($_POST['simpan'])){
  $judul = mysqli_real_escape_string($conn, $_POST['judul']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  
  if(empty($judul) || empty($deskripsi)){
    $message = "Semua field harus diisi!";
    $message_type = "warning";
  } else {
    $update = mysqli_query($conn, "UPDATE info_dashboard SET judul='$judul', deskripsi='$deskripsi', terakhir_update=NOW() WHERE id=1");
    
    if($update){
      $message = "Informasi dashboard berhasil diperbarui!";
      $message_type = "success";
    } else {
      $message = "Gagal memperbarui informasi: " . mysqli_error($conn);
      $message_type = "danger";
    }
  }
}

$info_query = mysqli_query($conn, "SELECT * FROM info_dashboard WHERE id=1");
$info = mysqli_fetch_assoc($info_query);

if(!$info){
  $info = ['judul' => '', 'deskripsi' => '', 'terakhir_update' => ''];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Info Dashboard</title>
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
  .page-header h2 {
    color: #333;
    font-weight: 700;
    margin-bottom: 10px;
  }
  .page-header p {
    color: #999;
    margin-bottom: 0;
  }
  .form-container {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
  }
  .form-group {
    margin-bottom: 25px;
  }
  .form-label {
    color: #333;
    font-weight: 600;
    margin-bottom: 12px;
    display: block;
    font-size: 15px;
  }
  .form-control {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    transition: border-color 0.3s, box-shadow 0.3s;
    font-size: 15px;
  }
  .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    color: #333;
  }
  .form-control::placeholder {
    color: #bbb;
  }
  textarea.form-control {
    resize: vertical;
    min-height: 150px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .btn-save {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 8px;
    color: white;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    color: white;
  }
  .btn-back {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
    display: inline-block;
    margin-bottom: 20px;
  }
  .btn-back:hover {
    color: #764ba2;
  }
  .info-box {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border-left: 4px solid #667eea;
    border-radius: 8px;
    padding: 15px;
    margin-top: 20px;
    color: #555;
    font-size: 13px;
  }
  .info-box i {
    color: #667eea;
    margin-right: 8px;
  }
  .last-update {
    color: #999;
    font-size: 13px;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
  }
  .char-count {
    color: #bbb;
    font-size: 13px;
    margin-top: 5px;
    text-align: right;
  }
  .preview-box {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
    display: none;
  }
  .preview-box.show {
    display: block;
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.02) 0%, rgba(118, 75, 162, 0.02) 100%);
  }
  .preview-title {
    color: #667eea;
    font-weight: 700;
    font-size: 18px;
    margin-bottom: 10px;
  }
  .preview-desc {
    color: #666;
    line-height: 1.6;
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

<div class="container py-4">
  <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>

  <!-- Message Alert -->
  <?php if($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
      <i class="fas fa-info-circle"></i> <?php echo $message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="page-header">
    <h2><i class="fas fa-cog"></i> Kelola Informasi Dashboard User</h2>
    <p>Perbarui judul dan deskripsi yang ditampilkan di halaman utama aplikasi</p>
  </div>

  <!-- Form Container -->
  <div class="form-container">
    <form method="post">
      <!-- Judul -->
      <div class="form-group">
        <label for="judul" class="form-label">
          <i class="fas fa-heading"></i> Judul Informasi
        </label>
        <input 
          type="text" 
          class="form-control" 
          id="judul"
          name="judul" 
          placeholder="Masukkan judul informasi..."
          value="<?php echo htmlspecialchars($info['judul'] ?? ''); ?>"
          required
          maxlength="100"
        >
        <div class="char-count"><span id="judul-count">0</span>/100 karakter</div>
      </div>

      <!-- Deskripsi -->
      <div class="form-group">
        <label for="deskripsi" class="form-label">
          <i class="fas fa-file-alt"></i> Deskripsi / Pengumuman
        </label>
        <textarea 
          class="form-control" 
          id="deskripsi"
          name="deskripsi" 
          placeholder="Masukkan deskripsi atau pengumuman penting..."
          required
          maxlength="1000"
        ><?php echo htmlspecialchars($info['deskripsi'] ?? ''); ?></textarea>
        <div class="char-count"><span id="deskripsi-count">0</span>/1000 karakter</div>
      </div>

      <!-- Preview -->
      <div class="preview-box" id="previewBox">
        <div class="preview-title" id="previewJudul"></div>
        <div class="preview-desc" id="previewDeskripsi"></div>
      </div>

      <!-- Info Box -->
      <div class="info-box">
        <i class="fas fa-lightbulb"></i>
        <strong>Tip:</strong> Informasi ini akan ditampilkan di halaman dashboard user. Pastikan konten informatif dan jelas.
      </div>

      <!-- Last Update -->
      <?php if(!empty($info['terakhir_update'])): ?>
        <div class="last-update">
          <i class="fas fa-history"></i> 
          <strong>Terakhir diperbarui:</strong> <?php echo date('d-m-Y H:i', strtotime($info['terakhir_update'])); ?>
        </div>
      <?php endif; ?>

      <!-- Buttons -->
      <div style="margin-top: 30px;">
        <button type="submit" class="btn btn-save" name="simpan">
          <i class="fas fa-save"></i> Simpan Perubahan
        </button>
        <a href="dashboard.php" class="btn btn-outline-secondary ms-2">
          <i class="fas fa-times"></i> Batal
        </a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Character counter untuk judul
  document.getElementById('judul').addEventListener('input', function() {
    document.getElementById('judul-count').textContent = this.value.length;
    updatePreview();
  });

  // Character counter untuk deskripsi
  document.getElementById('deskripsi').addEventListener('input', function() {
    document.getElementById('deskripsi-count').textContent = this.value.length;
    updatePreview();
  });

  // Update preview
  function updatePreview() {
    const judul = document.getElementById('judul').value;
    const deskripsi = document.getElementById('deskripsi').value;
    
    if(judul || deskripsi) {
      document.getElementById('previewBox').classList.add('show');
      document.getElementById('previewJudul').textContent = judul || 'Judul Informasi';
      document.getElementById('previewDeskripsi').textContent = deskripsi || 'Deskripsi akan muncul di sini...';
    } else {
      document.getElementById('previewBox').classList.remove('show');
    }
  }

  // Initialize preview on page load
  document.addEventListener('DOMContentLoaded', updatePreview);
</script>
</body>
</html>

