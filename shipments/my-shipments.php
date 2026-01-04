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

        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px 40px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .page-header h1 {
            color: #667eea;
            font-size: 2.2em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header p {
            color: #666;
            margin-top: 10px;
            font-size: 1.05em;
        }

        .alert {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-info {
            border-left: 4px solid #42a5f5;
            color: #1565c0;
        }

        .alert-info i {
            color: #42a5f5;
            font-size: 2em;
        }

        .alert-info p {
            margin: 0;
            font-size: 1.05em;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 35px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: linear-gradient(135deg, #f8f9ff, #e8ebff);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-5px);
        }

        .stat-box i {
            font-size: 2em;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-box .stat-number {
            font-size: 2em;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-box .stat-label {
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            transform: scale(1.005);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .data-table tbody td {
            padding: 18px 15px;
            color: #333;
            border-bottom: 1px solid #eee;
        }

        .tracking-number {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #667eea;
            font-size: 1.05em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tracking-number i {
            color: #667eea;
        }

        .person-info {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .person-info i {
            color: #667eea;
            font-size: 0.9em;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge i {
            font-size: 0.9em;
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

        .date-text {
            color: #666;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .date-text i {
            color: #667eea;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95em;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9em;
        }

        .btn-info {
            background: linear-gradient(135deg, #29b6f6, #0288d1);
            color: white;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(41, 182, 246, 0.4);
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.6em;
            }

            .stats-summary {
                grid-template-columns: 1fr;
            }

            .data-table {
                font-size: 0.85em;
            }

            .data-table thead th,
            .data-table tbody td {
                padding: 10px 8px;
            }
        }

        @media (max-width: 1024px) {
            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-box-open"></i>
                Pengiriman Saya
            </h1>
            <p><i class="fas fa-info-circle"></i> Lihat dan lacak semua pengiriman Anda di sini</p>
        </div>
        
        <?php if (empty($shipments)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <div>
                    <p><strong>Belum Ada Pengiriman</strong></p>
                    <p>Anda belum memiliki pengiriman. Silakan hubungi staff untuk membuat pengiriman baru.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="content-card">
                <div class="stats-summary">
                    <?php
                    $total = count($shipments);
                    $pending = count(array_filter($shipments, fn($s) => $s['status'] === 'pending'));
                    $in_transit = count(array_filter($shipments, fn($s) => $s['status'] === 'in_transit'));
                    $delivered = count(array_filter($shipments, fn($s) => $s['status'] === 'delivered'));
                    ?>
                    <div class="stat-box">
                        <i class="fas fa-boxes"></i>
                        <div class="stat-number"><?php echo $total; ?></div>
                        <div class="stat-label">Total Pengiriman</div>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-clock"></i>
                        <div class="stat-number"><?php echo $pending; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-truck"></i>
                        <div class="stat-number"><?php echo $in_transit; ?></div>
                        <div class="stat-label">Dalam Perjalanan</div>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-check-circle"></i>
                        <div class="stat-number"><?php echo $delivered; ?></div>
                        <div class="stat-label">Terkirim</div>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-barcode"></i> No. Tracking</th>
                            <th><i class="fas fa-box"></i> Paket</th>
                            <th><i class="fas fa-paper-plane"></i> Pengirim</th>
                            <th><i class="fas fa-user-tag"></i> Penerima</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th><i class="fas fa-calendar"></i> Tanggal Kirim</th>
                            <th><i class="fas fa-eye"></i> Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $shipment): ?>
                        <tr>
                            <td>
                                <div class="tracking-number">
                                    <i class="fas fa-hashtag"></i>
                                    <?php echo htmlspecialchars($shipment['tracking_number']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="person-info">
                                    <i class="fas fa-cube"></i>
                                    <?php echo htmlspecialchars($shipment['package_name']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="person-info">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($shipment['sender_name']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="person-info">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($shipment['receiver_name']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $shipment['status']; ?>">
                                    <?php 
                                    $status_icons = [
                                        'pending' => 'clock',
                                        'in_transit' => 'truck',
                                        'delivered' => 'check-circle',
                                        'cancelled' => 'times-circle'
                                    ];
                                    $status_labels = [
                                        'pending' => 'Pending',
                                        'in_transit' => 'Dalam Perjalanan',
                                        'delivered' => 'Terkirim',
                                        'cancelled' => 'Dibatalkan'
                                    ];
                                    ?>
                                    <i class="fas fa-<?php echo $status_icons[$shipment['status']] ?? 'circle'; ?>"></i>
                                    <?php echo $status_labels[$shipment['status']] ?? $shipment['status']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="date-text">
                                    <i class="fas fa-calendar-day"></i>
                                    <?php echo $shipment['shipping_date'] ? date('d/m/Y', strtotime($shipment['shipping_date'])) : '-'; ?>
                                </div>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $shipment['id']; ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>