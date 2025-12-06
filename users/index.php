<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireRole('Admin');

$conn = getDBConnection();
$users = [];

$result = $conn->query("SELECT u.*, r.name as role_name 
                       FROM users u 
                       JOIN roles r ON u.role_id = r.id 
                       ORDER BY u.created_at DESC");
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Daftar Users</h1>
            <a href="create.php" class="btn btn-primary">Tambah User</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">User berhasil <?php echo $_GET['success'] === 'created' ? 'ditambahkan' : ($_GET['success'] === 'updated' ? 'diperbarui' : 'dihapus'); ?>!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php 
                if ($_GET['error'] === 'user_in_use') {
                    echo 'User tidak dapat dihapus karena sudah digunakan dalam sistem!';
                } elseif ($_GET['error'] === 'cannot_delete_self') {
                    echo 'Anda tidak dapat menghapus akun sendiri!';
                } elseif ($_GET['error'] === 'username_exists') {
                    echo 'Username sudah digunakan!';
                } elseif ($_GET['error'] === 'email_exists') {
                    echo 'Email sudah digunakan!';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($users)): ?>
            <p>Tidak ada user. <a href="create.php">Tambah user pertama</a></p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Nama Lengkap</th>
                        <th>Telepon</th>
                        <th>Role</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $user['role_name'] === 'Admin' ? 'danger' : 
                                    ($user['role_name'] === 'Staff' ? 'warning' : 'info'); 
                            ?>">
                                <?php echo htmlspecialchars($user['role_name']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
                            <?php else: ?>
                                <span class="text-muted">(Akun Anda)</span>
                            <?php endif; ?>
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

