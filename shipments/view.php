<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();

// Jika customer, hanya bisa lihat pengiriman mereka sendiri
if (hasRole('Customer') && !isStaff()) {
    // Ambil email user yang login
    $user_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_stmt->close();
    
    if ($user && $user['email']) {
        $stmt = $conn->prepare("SELECT s.*, c.name as customer_name, c.email as customer_email, p.name as package_name, p.price as package_price, u.full_name as created_by_name 
                               FROM shipments s 
                               JOIN customers c ON s.customer_id = c.id 
                               JOIN packages p ON s.package_id = p.id 
                               JOIN users u ON s.created_by = u.id 
                               WHERE s.id = ? AND c.email = ?");
        $stmt->bind_param("is", $id, $user['email']);
    } else {
        $stmt = null;
    }
} else {
    // Staff dan Admin bisa lihat semua
    $stmt = $conn->prepare("SELECT s.*, c.name as customer_name, c.email as customer_email, p.name as package_name, p.price as package_price, u.full_name as created_by_name 
                           FROM shipments s 
                           JOIN customers c ON s.customer_id = c.id 
                           JOIN packages p ON s.package_id = p.id 
                           JOIN users u ON s.created_by = u.id 
                           WHERE s.id = ?");
    $stmt->bind_param("i", $id);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $shipment = $result->fetch_assoc();
    $stmt->close();
} else {
    $shipment = null;
}

$conn->close();

if (!$shipment) {
    $base_path = get_base_path();
    if (hasRole('Customer') && !isStaff()) {
        header('Location: ' . $base_path . 'shipments/my-shipments.php');
    } else {
        header('Location: ' . $base_path . 'shipments/index.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengiriman - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Detail Pengiriman</h1>
            <?php if (isStaff()): ?>
                <a href="edit.php?id=<?php echo $shipment['id']; ?>" class="btn btn-warning">Edit</a>
            <?php endif; ?>
            <a href="<?php 
                if (isStaff()) {
                    echo 'index.php';
                } elseif (hasRole('Customer')) {
                    echo 'my-shipments.php';
                } else {
                    echo '../index.php';
                }
            ?>" class="btn btn-secondary">Kembali</a>
        </div>
        
        <div class="detail-card">
            <div class="detail-section">
                <h2>Informasi Tracking</h2>
                <div class="detail-row">
                    <strong>No. Tracking:</strong>
                    <span class="tracking-number"><?php echo htmlspecialchars($shipment['tracking_number']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong>
                    <span class="badge badge-<?php echo $shipment['status']; ?>">
                        <?php 
                        $status_labels = [
                            'pending' => 'Pending',
                            'in_transit' => 'Dalam Perjalanan',
                            'delivered' => 'Terkirim',
                            'cancelled' => 'Dibatalkan'
                        ];
                        echo $status_labels[$shipment['status']] ?? $shipment['status'];
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="detail-section">
                <h2>Informasi Pelanggan</h2>
                <div class="detail-row">
                    <strong>Nama:</strong>
                    <span><?php echo htmlspecialchars($shipment['customer_name']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($shipment['customer_email'] ?? '-'); ?></span>
                </div>
            </div>
            
            <div class="detail-section">
                <h2>Informasi Paket</h2>
                <div class="detail-row">
                    <strong>Nama Paket:</strong>
                    <span><?php echo htmlspecialchars($shipment['package_name']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Harga:</strong>
                    <span>Rp <?php echo number_format($shipment['package_price'], 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <div class="detail-section">
                <h2>Data Pengirim</h2>
                <div class="detail-row">
                    <strong>Nama:</strong>
                    <span><?php echo htmlspecialchars($shipment['sender_name']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Alamat:</strong>
                    <span><?php echo nl2br(htmlspecialchars($shipment['sender_address'])); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Telepon:</strong>
                    <span><?php echo htmlspecialchars($shipment['sender_phone']); ?></span>
                </div>
            </div>
            
            <div class="detail-section">
                <h2>Data Penerima</h2>
                <div class="detail-row">
                    <strong>Nama:</strong>
                    <span><?php echo htmlspecialchars($shipment['receiver_name']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Alamat:</strong>
                    <span><?php echo nl2br(htmlspecialchars($shipment['receiver_address'])); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Telepon:</strong>
                    <span><?php echo htmlspecialchars($shipment['receiver_phone']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Kota:</strong>
                    <span><?php echo htmlspecialchars($shipment['receiver_city']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Kode Pos:</strong>
                    <span><?php echo htmlspecialchars($shipment['receiver_postal_code'] ?? '-'); ?></span>
                </div>
            </div>
            
            <div class="detail-section">
                <h2>Informasi Pengiriman</h2>
                <div class="detail-row">
                    <strong>Tanggal Pengiriman:</strong>
                    <span><?php echo $shipment['shipping_date'] ? date('d/m/Y', strtotime($shipment['shipping_date'])) : '-'; ?></span>
                </div>
                <div class="detail-row">
                    <strong>Tanggal Terkirim:</strong>
                    <span><?php echo $shipment['delivery_date'] ? date('d/m/Y', strtotime($shipment['delivery_date'])) : '-'; ?></span>
                </div>
                <div class="detail-row">
                    <strong>Dibuat oleh:</strong>
                    <span><?php echo htmlspecialchars($shipment['created_by_name']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Catatan:</strong>
                    <span><?php echo $shipment['notes'] ? nl2br(htmlspecialchars($shipment['notes'])) : '-'; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

