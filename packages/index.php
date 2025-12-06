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
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Daftar Paket</h1>
            <a href="create.php" class="btn btn-primary">Tambah Paket</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Paket berhasil <?php echo $_GET['success'] === 'created' ? 'ditambahkan' : ($_GET['success'] === 'updated' ? 'diperbarui' : 'dihapus'); ?>!</div>
        <?php endif; ?>
        
        <?php if (empty($packages)): ?>
            <p>Tidak ada paket. <a href="create.php">Tambah paket pertama</a></p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Berat (kg)</th>
                        <th>Dimensi (cm)</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $package): ?>
                    <tr>
                        <td><?php echo $package['id']; ?></td>
                        <td><?php echo htmlspecialchars($package['name']); ?></td>
                        <td><?php echo htmlspecialchars($package['description'] ?? '-'); ?></td>
                        <td><?php echo number_format($package['weight'], 2); ?></td>
                        <td><?php echo $package['length'] && $package['width'] && $package['height'] ? $package['length'] . ' x ' . $package['width'] . ' x ' . $package['height'] : '-'; ?></td>
                        <td>Rp <?php echo number_format($package['price'], 0, ',', '.'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $package['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo $package['status'] === 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $package['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete.php?id=<?php echo $package['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
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

