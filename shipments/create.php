<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireStaff();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? 0;
    $package_id = $_POST['package_id'] ?? 0;
    $sender_name = $_POST['sender_name'] ?? '';
    $sender_address = $_POST['sender_address'] ?? '';
    $sender_phone = $_POST['sender_phone'] ?? '';
    $receiver_name = $_POST['receiver_name'] ?? '';
    $receiver_address = $_POST['receiver_address'] ?? '';
    $receiver_phone = $_POST['receiver_phone'] ?? '';
    $receiver_city = $_POST['receiver_city'] ?? '';
    $receiver_postal_code = $_POST['receiver_postal_code'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    $shipping_date = $_POST['shipping_date'] ?? null;
    $notes = $_POST['notes'] ?? '';
    
    if ($customer_id && $package_id && $sender_name && $sender_address && $sender_phone && 
        $receiver_name && $receiver_address && $receiver_phone && $receiver_city) {
        
        $conn = getDBConnection();
        $tracking_number = generateTrackingNumber();
        
        // Check if tracking number exists
        $check_stmt = $conn->prepare("SELECT id FROM shipments WHERE tracking_number = ?");
        $check_stmt->bind_param("s", $tracking_number);
        $check_stmt->execute();
        while ($check_stmt->get_result()->num_rows > 0) {
            $tracking_number = generateTrackingNumber();
            $check_stmt->execute();
        }
        $check_stmt->close();
        
        $stmt = $conn->prepare("INSERT INTO shipments (tracking_number, customer_id, package_id, sender_name, sender_address, sender_phone, receiver_name, receiver_address, receiver_phone, receiver_city, receiver_postal_code, status, shipping_date, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siisssssssssssi", $tracking_number, $customer_id, $package_id, $sender_name, $sender_address, $sender_phone, $receiver_name, $receiver_address, $receiver_phone, $receiver_city, $receiver_postal_code, $status, $shipping_date, $notes, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            header('Location: index.php?success=created');
            exit();
        } else {
            $error = 'Gagal menambahkan pengiriman: ' . $stmt->error;
        }
        $stmt->close();
        $conn->close();
    } else {
        $error = 'Silakan isi semua field yang wajib!';
    }
}

$conn = getDBConnection();
$customers = [];
$packages = [];

$result = $conn->query("SELECT id, name FROM customers ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

$result = $conn->query("SELECT id, name FROM packages WHERE status = 'active' ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $packages[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengiriman - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Tambah Pengiriman</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_id">Pelanggan *</label>
                    <select id="customer_id" name="customer_id" required>
                        <option value="">Pilih Pelanggan</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="package_id">Paket *</label>
                    <select id="package_id" name="package_id" required>
                        <option value="">Pilih Paket</option>
                        <?php foreach ($packages as $package): ?>
                            <option value="<?php echo $package['id']; ?>"><?php echo htmlspecialchars($package['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <h3>Data Pengirim</h3>
            <div class="form-group">
                <label for="sender_name">Nama Pengirim *</label>
                <input type="text" id="sender_name" name="sender_name" required>
            </div>
            
            <div class="form-group">
                <label for="sender_address">Alamat Pengirim *</label>
                <textarea id="sender_address" name="sender_address" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="sender_phone">Telepon Pengirim *</label>
                <input type="text" id="sender_phone" name="sender_phone" required>
            </div>
            
            <h3>Data Penerima</h3>
            <div class="form-group">
                <label for="receiver_name">Nama Penerima *</label>
                <input type="text" id="receiver_name" name="receiver_name" required>
            </div>
            
            <div class="form-group">
                <label for="receiver_address">Alamat Penerima *</label>
                <textarea id="receiver_address" name="receiver_address" rows="3" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="receiver_phone">Telepon Penerima *</label>
                    <input type="text" id="receiver_phone" name="receiver_phone" required>
                </div>
                
                <div class="form-group">
                    <label for="receiver_city">Kota Penerima *</label>
                    <input type="text" id="receiver_city" name="receiver_city" required>
                </div>
                
                <div class="form-group">
                    <label for="receiver_postal_code">Kode Pos Penerima</label>
                    <input type="text" id="receiver_postal_code" name="receiver_postal_code">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="pending">Pending</option>
                        <option value="in_transit">Dalam Perjalanan</option>
                        <option value="delivered">Terkirim</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="shipping_date">Tanggal Pengiriman</label>
                    <input type="date" id="shipping_date" name="shipping_date">
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
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

