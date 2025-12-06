<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireStaff();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $weight = $_POST['weight'] ?? 0;
    $length = $_POST['length'] ?? null;
    $width = $_POST['width'] ?? null;
    $height = $_POST['height'] ?? null;
    $price = $_POST['price'] ?? 0;
    $status = $_POST['status'] ?? 'active';
    
    if ($name && $weight > 0 && $price > 0) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO packages (name, description, weight, length, width, height, price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddddds", $name, $description, $weight, $length, $width, $height, $price, $status);
        
        if ($stmt->execute()) {
            header('Location: index.php?success=created');
            exit();
        } else {
            $error = 'Gagal menambahkan paket: ' . $stmt->error;
        }
        $stmt->close();
        $conn->close();
    } else {
        $error = 'Silakan isi semua field yang wajib!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Paket - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Tambah Paket</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <div class="form-group">
                <label for="name">Nama Paket *</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="weight">Berat (kg) *</label>
                    <input type="number" id="weight" name="weight" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="price">Harga (Rp) *</label>
                    <input type="number" id="price" name="price" min="0" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="length">Panjang (cm)</label>
                    <input type="number" id="length" name="length" step="0.01" min="0">
                </div>
                
                <div class="form-group">
                    <label for="width">Lebar (cm)</label>
                    <input type="number" id="width" name="width" step="0.01" min="0">
                </div>
                
                <div class="form-group">
                    <label for="height">Tinggi (cm)</label>
                    <input type="number" id="height" name="height" step="0.01" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

