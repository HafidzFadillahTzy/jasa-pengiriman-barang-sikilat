<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireStaff();

$conn = getDBConnection();
$shipments = [];

$result = $conn->query("SELECT s.*, c.name as customer_name, p.name as package_name 
                       FROM shipments s 
                       JOIN customers c ON s.customer_id = c.id 
                       JOIN packages p ON s.package_id = p.id 
                       ORDER BY s.created_at DESC");
while ($row = $result->fetch_assoc()) {
    $shipments[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengiriman - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Daftar Pengiriman</h1>
            <a href="create.php" class="btn btn-primary">Tambah Pengiriman</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Pengiriman berhasil <?php echo $_GET['success'] === 'created' ? 'ditambahkan' : ($_GET['success'] === 'updated' ? 'diperbarui' : 'dihapus'); ?>!</div>
        <?php endif; ?>
        
        <?php if (empty($shipments)): ?>
            <p>Tidak ada pengiriman. <a href="create.php">Tambah pengiriman pertama</a></p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Tracking</th>
                        <th>Pelanggan</th>
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
                        <td><?php echo htmlspecialchars($shipment['customer_name']); ?></td>
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
                            <a href="view.php?id=<?php echo $shipment['id']; ?>" class="btn btn-sm btn-info">Lihat</a>
                            <a href="edit.php?id=<?php echo $shipment['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete.php?id=<?php echo $shipment['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
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

