<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireStaff();

$conn = getDBConnection();
$packages = [];

$result = $conn->query("SELECT * FROM packages ORDER BY created_at DESC");
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
    <title>Paket - Jasa Pengiriman Barang</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-header h1 {
            color: #667eea;
            font-size: 2.2em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .alert {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px 25px;
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

        .alert-success {
            border-left: 4px solid #66bb6a;
            color: #2e7d32;
        }

        .alert-success i {
            color: #66bb6a;
            font-size: 1.5em;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 35px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 5em;
            margin-bottom: 25px;
            color: #ddd;
        }

        .empty-state p {
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .empty-state a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .empty-state a:hover {
            color: #764ba2;
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

        .badge-success {
            background: linear-gradient(135deg, #66bb6a, #43a047);
            color: white;
        }

        .badge-secondary {
            background: linear-gradient(135deg, #bdbdbd, #9e9e9e);
            color: white;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9em;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffa726, #fb8c00);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 167, 38, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef5350, #e53935);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 83, 80, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, #29b6f6, #0288d1);
            color: white;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(41, 182, 246, 0.4);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .price-tag {
            color: #667eea;
            font-weight: 700;
            font-size: 1.05em;
        }

        .dimension-text {
            color: #666;
            font-size: 0.95em;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-header h1 {
                font-size: 1.6em;
            }

            .data-table {
                font-size: 0.85em;
            }

            .data-table thead th,
            .data-table tbody td {
                padding: 10px 8px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-sm {
                width: 100%;
                text-align: center;
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
                <i class="fas fa-boxes"></i>
                Daftar Paket
            </h1>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i>
                Tambah Paket
            </a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>
                    Paket berhasil 
                    <?php 
                    echo $_GET['success'] === 'created' ? 'ditambahkan' : 
                         ($_GET['success'] === 'updated' ? 'diperbarui' : 'dihapus'); 
                    ?>!
                </span>
            </div>
        <?php endif; ?>
        
        <div class="content-card">
            <?php if (empty($packages)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Tidak ada paket tersedia.</p>
                    <a href="create.php">
                        <i class="fas fa-plus-circle"></i> Tambah paket pertama
                    </a>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-box"></i> Nama</th>
                            <th><i class="fas fa-align-left"></i> Deskripsi</th>
                            <th><i class="fas fa-weight"></i> Berat (kg)</th>
                            <th><i class="fas fa-ruler-combined"></i> Dimensi (cm)</th>
                            <th><i class="fas fa-tag"></i> Harga</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th><i class="fas fa-cog"></i> Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $package): ?>
                        <tr>
                            <td><strong><?php echo $package['id']; ?></strong></td>
                            <td><strong><?php echo htmlspecialchars($package['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($package['description'] ?? '-'); ?></td>
                            <td><?php echo number_format($package['weight'], 2); ?></td>
                            <td class="dimension-text">
                                <?php 
                                echo $package['length'] && $package['width'] && $package['height'] 
                                    ? $package['length'] . ' × ' . $package['width'] . ' × ' . $package['height'] 
                                    : '-'; 
                                ?>
                            </td>
                            <td class="price-tag">
                                Rp <?php echo number_format($package['price'], 0, ',', '.'); ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $package['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <i class="fas fa-<?php echo $package['status'] === 'active' ? 'check' : 'times'; ?>"></i>
                                    <?php echo $package['status'] === 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view.php?id=<?php echo $package['id']; ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i> Lihat
                                    </a>
                                    <a href="edit.php?id=<?php echo $package['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete.php?id=<?php echo $package['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Yakin ingin menghapus paket ini?')"
                                       title="Hapus">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>