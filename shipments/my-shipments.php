<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireLogin();

// Hanya customer yang bisa akses halaman ini
if (!hasRole('Customer')) {
    $base_path = get_base_path();
    header('Location: ' . $base_path . 'index.php');
    exit();
}

$conn = getDBConnection();

// Ambil email user yang login
$user_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

if (!$user || !$user['email']) {
    $conn->close();
    $base_path = get_base_path();
    header('Location: ' . $base_path . 'index.php');
    exit();
}

// Ambil pengiriman customer berdasarkan email
$shipments = [];
$stmt = $conn->prepare("SELECT s.*, c.name as customer_name, p.name as package_name 
                       FROM shipments s 
                       JOIN customers c ON s.customer_id = c.id 
                       JOIN packages p ON s.package_id = p.id 
                       WHERE c.email = ?
                       ORDER BY s.created_at DESC");
$stmt->bind_param("s", $user['email']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $shipments[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengiriman Saya - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Pengiriman Saya</h1>
        </div>
        
        <?php if (empty($shipments)): ?>
            <div class="alert alert-info">
                <p>Anda belum memiliki pengiriman. Silakan hubungi staff untuk membuat pengiriman baru.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Tracking</th>
                        <th>Paket</th>
                        <th>Pengirim</th>
                        <th>Penerima</th>
                        <th>Status</th>
                        <th>Tanggal Kirim</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shipments as $shipment): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($shipment['tracking_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($shipment['package_name']); ?></td>
                        <td><?php echo htmlspecialchars($shipment['sender_name']); ?></td>
                        <td><?php echo htmlspecialchars($shipment['receiver_name']); ?></td>
                        <td>
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
                        </td>
                        <td><?php echo $shipment['shipping_date'] ? date('d/m/Y', strtotime($shipment['shipping_date'])) : '-'; ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $shipment['id']; ?>" class="btn btn-sm btn-info">Lihat Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

