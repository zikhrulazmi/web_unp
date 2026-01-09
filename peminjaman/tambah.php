<?php
session_start();
include '../config/database.php';
include '../assets/header.php';

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

// Pastikan kolom labor_id ada di tabel peminjaman
$check_labor_col = mysqli_query($conn, "SHOW COLUMNS FROM peminjaman LIKE 'labor_id'");
if($check_labor_col && mysqli_num_rows($check_labor_col) == 0){
  mysqli_query($conn, "ALTER TABLE peminjaman ADD COLUMN labor_id INT NULL");
}

// Pastikan kolom barang_labor_id ada di tabel peminjaman
$check_barang_col = mysqli_query($conn, "SHOW COLUMNS FROM peminjaman LIKE 'barang_labor_id'");
if($check_barang_col && mysqli_num_rows($check_barang_col) == 0){
  mysqli_query($conn, "ALTER TABLE peminjaman ADD COLUMN barang_labor_id INT NULL");
}

$message = '';
$message_type = '';

// Ambil labor_id dari parameter GET jika ada
$selected_labor_id = isset($_GET['labor_id']) ? intval($_GET['labor_id']) : null;

if(isset($_POST['kirim'])){
  $labor_id = isset($_POST['labor_id']) && !empty($_POST['labor_id']) ? intval($_POST['labor_id']) : null;
  $barang_id = isset($_POST['barang_labor_id']) && !empty($_POST['barang_labor_id']) ? intval($_POST['barang_labor_id']) : null;
  $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
  $keperluan = mysqli_real_escape_string($conn, $_POST['tanggung']);
  $user_id = intval($_SESSION['user_id']);
  
  if(empty($tanggal) || empty($keperluan) || !$labor_id || !$barang_id){
    $message = "Semua field harus diisi!";
    $message_type = "warning";
  } else {
    $ins = mysqli_query($conn, "INSERT INTO peminjaman(user_id, tanggal, tanggung_jawab, labor_id, barang_labor_id, status) 
      VALUES('$user_id', '$tanggal', '$keperluan', $labor_id, $barang_id, 'Menunggu')");
    
    if($ins){
      $message = "Peminjaman berhasil diajukan! Tunggu persetujuan dari admin.";
      $message_type = "success";
    } else {
      $message = "Gagal mengajukan peminjaman!";
      $message_type = "danger";
    }
  }
}

// Ambil daftar labor
$labor_q = mysqli_query($conn, "SELECT * FROM labor ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Peminjaman Labor</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  body {
    background-color: #f5f7fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .form-container {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin: 30px auto;
    max-width: 600px;
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
  .btn-submit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    font-weight: 600;
    padding: 10px 30px;
  }
  .btn-submit:hover {
    color: white;
  }
  h4 {
    color: #333;
    font-weight: 700;
    margin-bottom: 25px;
  }
</style>
</head>
<body>

<div style="padding: 20px;">
  <a href="../dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="form-container">
  <h4><i class="fas fa-clipboard-list"></i> Form Peminjaman Labor</h4>

  <?php if($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
      <i class="fas fa-info-circle"></i> <?php echo $message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label"><i class="fas fa-building"></i> Pilih Labor</label>
      <select name="labor_id" class="form-control" id="labor_select" required>
        <option value="">-- Pilih Labor --</option>
        <?php 
        mysqli_data_seek($labor_q, 0);
        while($l = mysqli_fetch_assoc($labor_q)): 
        ?>
          <option value="<?php echo $l['id']; ?>" <?php echo $selected_labor_id == $l['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($l['nama']); ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label"><i class="fas fa-box"></i> Pilih Peralatan</label>
      <select name="barang_labor_id" class="form-control" id="barang_select" required>
        <option value="">-- Pilih Peralatan --</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label"><i class="fas fa-calendar"></i> Tanggal Peminjaman</label>
      <input type="datetime-local" name="tanggal" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label"><i class="fas fa-comment"></i> Keperluan Meminjam Barang</label>
      <textarea name="tanggung" class="form-control" rows="4" placeholder="Jelaskan keperluan meminjam barang..." required></textarea>
    </div>

    <button type="submit" class="btn btn-submit" name="kirim">
      <i class="fas fa-paper-plane"></i> Ajukan Peminjaman
    </button>
  </form>
</div>

<?php include '../assets/footer.php'; ?>

<script>
// Fungsi untuk load barang berdasarkan labor
function loadBarang(laborId) {
  const barangSelect = document.getElementById('barang_select');
  
  if(!laborId) {
    barangSelect.innerHTML = '<option value="">-- Pilih Labor Dulu --</option>';
    return;
  }
  
  // Fetch barang dari labor yang dipilih
  fetch('../config/get_barang.php?labor_id=' + laborId)
    .then(response => response.json())
    .then(data => {
      barangSelect.innerHTML = '<option value="">-- Pilih Peralatan --</option>';
      if(data.length > 0) {
        data.forEach(barang => {
          if(barang.stok > 0) {
            const option = document.createElement('option');
            option.value = barang.id;
            option.textContent = barang.nama + ' (' + barang.stok + ' tersedia)';
            barangSelect.appendChild(option);
          }
        });
      } else {
        barangSelect.innerHTML = '<option value="">Tidak ada peralatan tersedia</option>';
      }
    })
    .catch(error => console.error('Error:', error));
}

// Event listener untuk labor select
document.getElementById('labor_select').addEventListener('change', function() {
  loadBarang(this.value);
});

// Auto-load barang saat page load jika ada parameter labor_id
document.addEventListener('DOMContentLoaded', function() {
  const laborSelect = document.getElementById('labor_select');
  if(laborSelect.value) {
    loadBarang(laborSelect.value);
  }
});
</script>
</body>
</html>
