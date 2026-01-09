<?php
session_start();
if(!isset($_SESSION['user_id'])){
  header("Location: auth/login.php");
}
include 'config/database.php';
include 'assets/header.php';

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

// Insert default labor jika kosong
$labor_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM labor"));
if($labor_count == 0){
  mysqli_query($conn, "INSERT INTO labor (nama, deskripsi, icon, color) VALUES 
    ('Labor Animasi', 'Fasilitas lengkap untuk pembuatan animasi 2D dan 3D dengan perangkat profesional', 'fa-film', 'primary'),
    ('Labor Game', 'Studio pengembangan game dengan perangkat gaming dan development tools terlengkap', 'fa-gamepad', 'success'),
    ('Labor Audio', 'Studio rekaman dan produksi audio profesional dengan peralatan berkualitas tinggi', 'fa-volume-up', 'danger')
  ");
  
  // Insert barang untuk setiap labor
  $labor_q = mysqli_query($conn, "SELECT id, nama FROM labor");
  while($l = mysqli_fetch_assoc($labor_q)){
    if($l['nama'] == 'Labor Animasi'){
      mysqli_query($conn, "INSERT INTO barang_labor (labor_id, nama, deskripsi, stok) VALUES 
        (".$l['id'].", 'Tablet Wacom Pro', 'Tablet grafis profesional 22 inch', 2),
        (".$l['id'].", 'Monitor 4K', 'Monitor resolusi 4K untuk color grading', 3),
        (".$l['id'].", 'Laptop Rendering', 'Laptop dengan GPU RTX 3080 Ti', 2),
        (".$l['id'].", 'Stylus Pen Premium', 'Stylus dengan pressure sensitivity tinggi', 5),
        (".$l['id'].", 'External SSD 2TB', 'Storage eksternal untuk project besar', 4)
      ");
    } elseif($l['nama'] == 'Labor Game'){
      mysqli_query($conn, "INSERT INTO barang_labor (labor_id, nama, deskripsi, stok) VALUES 
        (".$l['id'].", 'VR Headset HTC Vive', 'Virtual reality headset untuk development', 2),
        (".$l['id'].", 'Gaming PC High-End', 'PC dengan RTX 4070 untuk game development', 3),
        (".$l['id'].", 'Motion Capture Suit', 'Suit untuk motion capture 3D character', 1),
        (".$l['id'].", 'Racing Simulator Setup', 'Cockpit racing game profesional', 2),
        (".$l['id'].", 'Joy-Con Pro Controller', 'Wireless game controller premium', 6)
      ");
    } elseif($l['nama'] == 'Labor Audio'){
      mysqli_query($conn, "INSERT INTO barang_labor (labor_id, nama, deskripsi, stok) VALUES 
        (".$l['id'].", 'Microphone Neumann U87', 'Microphone studio kondenser profesional', 2),
        (".$l['id'].", 'Audio Interface MOTU', 'Audio interface 16 channel profesional', 2),
        (".$l['id'].", 'Monitor Speaker Yamaha', 'Speaker monitor studio aktif', 4),
        (".$l['id'].", 'Headphone Reference Audio', 'Headphone monitoring akurat untuk mixing', 5),
        (".$l['id'].", 'XLR Cable Professional', 'Kabel audio profesional 10 meter', 10)
      ");
    }
  }
}

// Ambil semua labor
$labor_all = mysqli_query($conn, "SELECT * FROM labor ORDER BY id ASC");

$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT nama FROM users WHERE id='".intval($_SESSION['user_id'])."'"));

?>

<style>
  body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .labor-header {
    background: white;
    padding: 30px 0;
    margin-bottom: 40px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 12px;
  }

  .labor-header h1 {
    color: #667eea;
    font-weight: 700;
    margin-bottom: 0;
  }

  .labor-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
  }

  .labor-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
  }

  .labor-card-header {
    padding: 25px;
    color: white;
    text-align: center;
  }

  .labor-card-header.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .labor-card-header.success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  }

  .labor-card-header.danger {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
  }

  .labor-card-header i {
    font-size: 50px;
    display: block;
    margin-bottom: 15px;
  }

  .labor-card-header h3 {
    font-weight: 700;
    margin-bottom: 10px;
  }

  .labor-card-header p {
    font-size: 13px;
    margin-bottom: 0;
    opacity: 0.9;
    line-height: 1.5;
  }

  .labor-card-body {
    padding: 25px;
  }

  .labor-card-body h5 {
    color: #333;
    font-weight: 700;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
  }

  .barang-item {
    display: flex;
    align-items: center;
    padding: 12px;
    margin-bottom: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.2s ease;
  }

  .barang-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
  }

  .barang-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
    font-weight: 700;
  }

  .barang-icon.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .barang-icon.success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  }

  .barang-icon.danger {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
  }

  .barang-info {
    flex: 1;
  }

  .barang-nama {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
  }

  .barang-desc {
    font-size: 12px;
    color: #666;
  }

  .barang-stok {
    text-align: right;
  }

  .barang-stok-badge {
    background: #e9ecef;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #495057;
  }

  .barang-stok-badge.tersedia {
    background: #d4edda;
    color: #155724;
  }

  .barang-stok-badge.habis {
    background: #f8d7da;
    color: #721c24;
  }

  .btn-back-labor {
    display: inline-block;
    margin-bottom: 20px;
  }

  .empty-message {
    text-align: center;
    padding: 40px 20px;
    color: #999;
  }

  .empty-message i {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 15px;
    display: block;
  }

  .btn-ajukan-labor {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 8px;
    margin-top: 15px;
    display: block;
    width: 100%;
    text-align: center;
    transition: all 0.3s ease;
  }

  .btn-ajukan-labor:hover {
    color: white;
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  }
</style>

<div class="labor-header mb-4">
  <div class="container">
    <h1><i class="fas fa-building"></i> Daftar Labor</h1>
    <p>Pilih labor dan lihat peralatan yang tersedia untuk dipinjam</p>
  </div>
</div>

<div class="container">
  <a href="dashboard.php" class="btn btn-secondary btn-back-labor"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>

  <div class="row">
    <?php while($labor = mysqli_fetch_assoc($labor_all)): ?>
      <div class="col-lg-4 col-md-6">
        <div class="labor-card">
          <div class="labor-card-header <?php echo $labor['color']; ?>">
            <i class="fas <?php echo $labor['icon']; ?>"></i>
            <h3><?php echo htmlspecialchars($labor['nama']); ?></h3>
            <p><?php echo htmlspecialchars($labor['deskripsi']); ?></p>
          </div>

          <div class="labor-card-body">
            <h5><i class="fas fa-box"></i> Peralatan Tersedia</h5>

            <?php 
            $barang_q = mysqli_query($conn, "SELECT * FROM barang_labor WHERE labor_id=".$labor['id']." ORDER BY nama ASC");
            if(mysqli_num_rows($barang_q) > 0):
            ?>
              <?php while($barang = mysqli_fetch_assoc($barang_q)): ?>
                <div class="barang-item">
                  <div class="barang-icon <?php echo $labor['color']; ?>">
                    <i class="fas fa-cube"></i>
                  </div>
                  <div class="barang-info">
                    <div class="barang-nama"><?php echo htmlspecialchars($barang['nama']); ?></div>
                    <div class="barang-desc"><?php echo htmlspecialchars($barang['deskripsi']); ?></div>
                  </div>
                  <div class="barang-stok">
                    <span class="barang-stok-badge <?php echo $barang['stok'] > 0 ? 'tersedia' : 'habis'; ?>">
                      <?php echo $barang['stok'] > 0 ? $barang['stok'] . ' tersedia' : 'Habis'; ?>
                    </span>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="empty-message">
                <i class="fas fa-box-open"></i>
                <p>Tidak ada peralatan di labor ini</p>
              </div>
            <?php endif; ?>

            <a href="peminjaman/tambah.php?labor_id=<?php echo $labor['id']; ?>" class="btn-ajukan-labor">
              <i class="fas fa-paper-plane"></i> Ajukan Peminjaman
            </a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<?php include 'assets/footer.php'; ?>
