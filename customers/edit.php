<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireStaff();

$error = '';
$customer = null;

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();

if (!$customer) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    
    if ($name && $phone && $address && $city) {
        $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, address = ?, city = ?, postal_code = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $name, $email, $phone, $address, $city, $postal_code, $id);
        
        if ($stmt->execute()) {
            header('Location: index.php?success=updated');
            exit();
        } else {
            $error = 'Gagal memperbarui pelanggan: ' . $stmt->error;
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
    <title>Edit Pelanggan - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Edit Pelanggan</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <div class="form-group">
                <label for="name">Nama *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Telepon *</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Alamat *</label>
                <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($customer['address']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="city">Kota *</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($customer['city']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="postal_code">Kode Pos</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($customer['postal_code'] ?? ''); ?>">
                </div>
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

