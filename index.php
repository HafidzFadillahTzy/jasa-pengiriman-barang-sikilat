<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireLogin();

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total shipments
$result = $conn->query("SELECT COUNT(*) as total FROM shipments");
$stats['total_shipments'] = $result->fetch_assoc()['total'];

// Pending shipments
$result = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE status = 'pending'");
$stats['pending_shipments'] = $result->fetch_assoc()['total'];

// In transit shipments
$result = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE status = 'in_transit'");
$stats['in_transit_shipments'] = $result->fetch_assoc()['total'];

// Delivered shipments
$result = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE status = 'delivered'");
$stats['delivered_shipments'] = $result->fetch_assoc()['total'];

// Total customers
$result = $conn->query("SELECT COUNT(*) as total FROM customers");
$stats['total_customers'] = $result->fetch_assoc()['total'];

// Recent shipments
$recent_shipments = [];
if (isStaff()) {
    $result = $conn->query("SELECT s.*, c.name as customer_name, p.name as package_name 
                           FROM shipments s 
                           JOIN customers c ON s.customer_id = c.id 
                           JOIN packages p ON s.package_id = p.id 
                           ORDER BY s.created_at DESC LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        $recent_shipments[] = $row;
    }
} else {
    // Customer hanya melihat pengiriman mereka sendiri
    $user_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_stmt->close();
    
    if ($user && $user['email']) {
        $stmt = $conn->prepare("SELECT s.*, c.name as customer_name, p.name as package_name 
                               FROM shipments s 
                               JOIN customers c ON s.customer_id = c.id 
                               JOIN packages p ON s.package_id = p.id 
                               WHERE c.email = ?
                               ORDER BY s.created_at DESC LIMIT 5");
        $stmt->bind_param("s", $user['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $recent_shipments[] = $row;
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Dashboard</h1>
        <p class="welcome">Selamat datang, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
        
        <?php if (isStaff()): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Pengiriman</h3>
                <p class="stat-number"><?php echo $stats['total_shipments']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending</h3>
                <p class="stat-number"><?php echo $stats['pending_shipments']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Dalam Perjalanan</h3>
                <p class="stat-number"><?php echo $stats['in_transit_shipments']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Terkirim</h3>
                <p class="stat-number"><?php echo $stats['delivered_shipments']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Pelanggan</h3>
                <p class="stat-number"><?php echo $stats['total_customers']; ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Pengiriman Terbaru</h2>
            <?php if (empty($recent_shipments)): ?>
                <p>Tidak ada pengiriman.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Tracking</th>
                            <th>Pelanggan</th>
                            <th>Paket</th>
                            <th>Penerima</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_shipments as $shipment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($shipment['tracking_number']); ?></td>
                            <td><?php echo htmlspecialchars($shipment['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($shipment['package_name']); ?></td>
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
                            <td>
                                <a href="shipments/view.php?id=<?php echo $shipment['id']; ?>" class="btn btn-sm btn-info">Lihat</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

