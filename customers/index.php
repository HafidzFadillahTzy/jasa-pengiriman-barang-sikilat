<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireStaff();

$conn = getDBConnection();
$customers = [];

$result = $conn->query("SELECT * FROM customers ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelanggan - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Daftar Pelanggan</h1>
            <a href="create.php" class="btn btn-primary">Tambah Pelanggan</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Pelanggan berhasil <?php echo $_GET['success'] === 'created' ? 'ditambahkan' : ($_GET['success'] === 'updated' ? 'diperbarui' : 'dihapus'); ?>!</div>
        <?php endif; ?>
        
        <?php if (empty($customers)): ?>
            <p>Tidak ada pelanggan. <a href="create.php">Tambah pelanggan pertama</a></p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Kota</th>
                        <th>Kode Pos</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['id']; ?></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo htmlspecialchars($customer['address']); ?></td>
                        <td><?php echo htmlspecialchars($customer['city']); ?></td>
                        <td><?php echo htmlspecialchars($customer['postal_code'] ?? '-'); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
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

