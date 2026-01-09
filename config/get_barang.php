<?php
session_start();
include 'database.php';

if(isset($_GET['labor_id'])){
  $labor_id = intval($_GET['labor_id']);
  
  $q = mysqli_query($conn, "SELECT id, nama, stok, deskripsi FROM barang_labor WHERE labor_id=$labor_id AND status='Tersedia' ORDER BY nama ASC");
  
  $barang = [];
  while($row = mysqli_fetch_assoc($q)){
    $barang[] = $row;
  }
  
  header('Content-Type: application/json');
  echo json_encode($barang);
} else {
  header('Content-Type: application/json');
  echo json_encode([]);
}
?>
