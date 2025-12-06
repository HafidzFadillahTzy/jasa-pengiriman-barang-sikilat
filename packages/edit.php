<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireStaff();

$error = '';
$package = null;

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$package = $result->fetch_assoc();
$stmt->close();

if (!$package) {
    header('Location: index.php');
    exit();
}

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
        $stmt = $conn->prepare("UPDATE packages SET name = ?, description = ?, weight = ?, length = ?, width = ?, height = ?, price = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssdddddsi", $name, $description, $weight, $length, $width, $height, $price, $status, $id);
        
        if ($stmt->execute()) {
            header('Location: index.php?success=updated');
            exit();
        } else {
            $error = 'Gagal memperbarui paket: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = 'Silakan isi semua field yang wajib!';
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Paket - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Edit Paket</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <div class="form-group">
                <label for="name">Nama Paket *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($package['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($package['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="weight">Berat (kg) *</label>
                    <input type="number" id="weight" name="weight" step="0.01" min="0.01" value="<?php echo $package['weight']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="price">Harga (Rp) *</label>
                    <input type="number" id="price" name="price" min="0" value="<?php echo $package['price']; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="length">Panjang (cm)</label>
                    <input type="number" id="length" name="length" step="0.01" min="0" value="<?php echo $package['length'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="width">Lebar (cm)</label>
                    <input type="number" id="width" name="width" step="0.01" min="0" value="<?php echo $package['width'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="height">Tinggi (cm)</label>
                    <input type="number" id="height" name="height" step="0.01" min="0" value="<?php echo $package['height'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?php echo $package['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="inactive" <?php echo $package['status'] === 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
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

