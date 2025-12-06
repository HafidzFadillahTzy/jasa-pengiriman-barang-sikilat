<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireStaff();

$error = '';
$shipment = null;

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM shipments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$shipment = $result->fetch_assoc();
$stmt->close();

if (!$shipment) {
    header('Location: index.php');
    exit();
}

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
    $delivery_date = $_POST['delivery_date'] ?? null;
    $notes = $_POST['notes'] ?? '';
    
    if ($customer_id && $package_id && $sender_name && $sender_address && $sender_phone && 
        $receiver_name && $receiver_address && $receiver_phone && $receiver_city) {
        
        $stmt = $conn->prepare("UPDATE shipments SET customer_id = ?, package_id = ?, sender_name = ?, sender_address = ?, sender_phone = ?, receiver_name = ?, receiver_address = ?, receiver_phone = ?, receiver_city = ?, receiver_postal_code = ?, status = ?, shipping_date = ?, delivery_date = ?, notes = ? WHERE id = ?");
        $stmt->bind_param("iisssssssssssssi", $customer_id, $package_id, $sender_name, $sender_address, $sender_phone, $receiver_name, $receiver_address, $receiver_phone, $receiver_city, $receiver_postal_code, $status, $shipping_date, $delivery_date, $notes, $id);
        
        if ($stmt->execute()) {
            header('Location: index.php?success=updated');
            exit();
        } else {
            $error = 'Gagal memperbarui pengiriman: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = 'Silakan isi semua field yang wajib!';
    }
}

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
    <title>Edit Pengiriman - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Edit Pengiriman</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <div class="form-group">
                <label>No. Tracking</label>
                <input type="text" value="<?php echo htmlspecialchars($shipment['tracking_number']); ?>" disabled>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_id">Pelanggan *</label>
                    <select id="customer_id" name="customer_id" required>
                        <option value="">Pilih Pelanggan</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>" <?php echo $shipment['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($customer['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="package_id">Paket *</label>
                    <select id="package_id" name="package_id" required>
                        <option value="">Pilih Paket</option>
                        <?php foreach ($packages as $package): ?>
                            <option value="<?php echo $package['id']; ?>" <?php echo $shipment['package_id'] == $package['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($package['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <h3>Data Pengirim</h3>
            <div class="form-group">
                <label for="sender_name">Nama Pengirim *</label>
                <input type="text" id="sender_name" name="sender_name" value="<?php echo htmlspecialchars($shipment['sender_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="sender_address">Alamat Pengirim *</label>
                <textarea id="sender_address" name="sender_address" rows="3" required><?php echo htmlspecialchars($shipment['sender_address']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="sender_phone">Telepon Pengirim *</label>
                <input type="text" id="sender_phone" name="sender_phone" value="<?php echo htmlspecialchars($shipment['sender_phone']); ?>" required>
            </div>
            
            <h3>Data Penerima</h3>
            <div class="form-group">
                <label for="receiver_name">Nama Penerima *</label>
                <input type="text" id="receiver_name" name="receiver_name" value="<?php echo htmlspecialchars($shipment['receiver_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="receiver_address">Alamat Penerima *</label>
                <textarea id="receiver_address" name="receiver_address" rows="3" required><?php echo htmlspecialchars($shipment['receiver_address']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="receiver_phone">Telepon Penerima *</label>
                    <input type="text" id="receiver_phone" name="receiver_phone" value="<?php echo htmlspecialchars($shipment['receiver_phone']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="receiver_city">Kota Penerima *</label>
                    <input type="text" id="receiver_city" name="receiver_city" value="<?php echo htmlspecialchars($shipment['receiver_city']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="receiver_postal_code">Kode Pos Penerima</label>
                    <input type="text" id="receiver_postal_code" name="receiver_postal_code" value="<?php echo htmlspecialchars($shipment['receiver_postal_code'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="pending" <?php echo $shipment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_transit" <?php echo $shipment['status'] === 'in_transit' ? 'selected' : ''; ?>>Dalam Perjalanan</option>
                        <option value="delivered" <?php echo $shipment['status'] === 'delivered' ? 'selected' : ''; ?>>Terkirim</option>
                        <option value="cancelled" <?php echo $shipment['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="shipping_date">Tanggal Pengiriman</label>
                    <input type="date" id="shipping_date" name="shipping_date" value="<?php echo $shipment['shipping_date'] ? date('Y-m-d', strtotime($shipment['shipping_date'])) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="delivery_date">Tanggal Terkirim</label>
                    <input type="date" id="delivery_date" name="delivery_date" value="<?php echo $shipment['delivery_date'] ? date('Y-m-d', strtotime($shipment['delivery_date'])) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($shipment['notes'] ?? ''); ?></textarea>
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

