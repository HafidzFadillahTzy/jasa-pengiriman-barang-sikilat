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
    $result = $conn->query("SELECT s.*, c.name as customer_name, p.name as package_name 
                           FROM shipments s 
                           JOIN customers c ON s.customer_id = c.id 
                           JOIN packages p ON s.package_id = p.id 
                           WHERE c.email = (SELECT email FROM users WHERE id = " . $_SESSION['user_id'] . ")
                           ORDER BY s.created_at DESC LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        $recent_shipments[] = $row;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .header-section h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .welcome {
            color: #666;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .welcome i {
            color: #667eea;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.95em;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3em;
            color: rgba(102, 126, 234, 0.1);
        }

        .section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .section h2 {
            color: #667eea;
            font-size: 1.8em;
            margin-bottom: 25px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 12px;
        }

        .data-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .data-table thead th {
            color: white;
            font-weight: 600;
            text-align: left;
            padding: 18px 15px;
            font-size: 0.95em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table tbody tr {
            background: white;
            transition: all 0.3s ease;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8f9ff;
        }

        .data-table tbody tr:hover {
            background: #e8ebff;
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .data-table tbody td {
            padding: 18px 15px;
            color: #333;
            border-bottom: 1px solid #eee;
        }

        .badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pending {
            background: linear-gradient(135deg, #ffa726, #fb8c00);
            color: white;
        }

        .badge-in_transit {
            background: linear-gradient(135deg, #42a5f5, #1e88e5);
            color: white;
        }

        .badge-delivered {
            background: linear-gradient(135deg, #66bb6a, #43a047);
            color: white;
        }

        .badge-cancelled {
            background: linear-gradient(135deg, #ef5350, #e53935);
            color: white;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9em;
        }

        .btn-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            color: #ddd;
        }

        .empty-state p {
            font-size: 1.1em;
        }

        @media (max-width: 768px) {
            .header-section h1 {
                font-size: 1.8em;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .data-table {
                font-size: 0.9em;
            }

            .data-table thead th,
            .data-table tbody td {
                padding: 12px 8px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="header-section">
            <h1><i class="fas fa-dashboard"></i> Dashboard</h1>
            <p class="welcome">
                <i class="fas fa-user-circle"></i>
                Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>!
            </p>
        </div>
        
        <?php if (isStaff()): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Pengiriman</h3>
                <p class="stat-number"><?php echo $stats['total_shipments']; ?></p>
                <i class="fas fa-box stat-icon"></i>
            </div>
            <div class="stat-card">
                <h3>Pending</h3>
                <p class="stat-number"><?php echo $stats['pending_shipments']; ?></p>
                <i class="fas fa-clock stat-icon"></i>
            </div>
            <div class="stat-card">
                <h3>Dalam Perjalanan</h3>
                <p class="stat-number"><?php echo $stats['in_transit_shipments']; ?></p>
                <i class="fas fa-truck stat-icon"></i>
            </div>
            <div class="stat-card">
                <h3>Terkirim</h3>
                <p class="stat-number"><?php echo $stats['delivered_shipments']; ?></p>
                <i class="fas fa-check-circle stat-icon"></i>
            </div>
            <div class="stat-card">
                <h3>Total Pelanggan</h3>
                <p class="stat-number"><?php echo $stats['total_customers']; ?></p>
                <i class="fas fa-users stat-icon"></i>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h2><i class="fas fa-shipping-fast"></i> Pengiriman Terbaru</h2>
            <?php if (empty($recent_shipments)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Tidak ada pengiriman.</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-barcode"></i> No. Tracking</th>
                            <th><i class="fas fa-user"></i> Pelanggan</th>
                            <th><i class="fas fa-box"></i> Paket</th>
                            <th><i class="fas fa-user-tag"></i> Penerima</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th><i class="fas fa-cog"></i> Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_shipments as $shipment): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($shipment['tracking_number']); ?></strong></td>
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
                                <a href="shipments/view.php?id=<?php echo $shipment['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
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