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
            max-width: 1500px;
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

        .alert-error {
            border-left: 4px solid #ef5350;
            color: #c62828;
        }

        .alert-error i {
            color: #ef5350;
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

        .table-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: linear-gradient(135deg, #f8f9ff, #e8ebff);
            border-radius: 12px;
        }

        .table-stats-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-stats-item i {
            color: #667eea;
            font-size: 1.3em;
        }

        .table-stats-item span {
            font-weight: 600;
            color: #667eea;
            font-size: 1.1em;
        }

        .table-stats-label {
            color: #666;
            font-size: 0.9em;
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

        .username-text {
            font-weight: 700;
            color: #667eea;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .username-text i {
            color: #667eea;
        }

        .contact-info {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #666;
        }

        .contact-info i {
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

        .badge-danger {
            background: linear-gradient(135deg, #ef5350, #e53935);
            color: white;
        }

        .badge-warning {
            background: linear-gradient(135deg, #ffa726, #fb8c00);
            color: white;
        }

        .badge-info {
            background: linear-gradient(135deg, #42a5f5, #1e88e5);
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

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .text-muted {
            color: #999;
            font-size: 0.9em;
            font-style: italic;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .text-muted i {
            color: #667eea;
        }

        .current-user-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: linear-gradient(135deg, #e8ebff, #d1d5ff);
            color: #667eea;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
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

        @media (max-width: 1200px) {
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
                <i class="fas fa-users-cog"></i>
                Daftar Users
            </h1>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i>
                Tambah User
            </a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>
                    User berhasil 
                    <?php 
                    echo $_GET['success'] === 'created' ? 'ditambahkan' : 
                         ($_GET['success'] === 'updated' ? 'diperbarui' : 'dihapus'); 
                    ?>!
                </span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>
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
                </span>
            </div>
        <?php endif; ?>
        
        <div class="content-card">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <p>Tidak ada user terdaftar.</p>
                    <a href="create.php">
                        <i class="fas fa-user-plus"></i> Tambah user pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="table-stats">
                    <div class="table-stats-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <div class="table-stats-label">Total Users</div>
                            <span><?php echo count($users); ?></span>
                        </div>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-user"></i> Username</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-id-card"></i> Nama Lengkap</th>
                            <th><i class="fas fa-phone"></i> Telepon</th>
                            <th><i class="fas fa-shield-alt"></i> Role</th>
                            <th><i class="fas fa-calendar"></i> Tanggal Dibuat</th>
                            <th><i class="fas fa-cog"></i> Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo $user['id']; ?></strong></td>
                            <td>
                                <div class="username-text">
                                    <i class="fas fa-user-circle"></i>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <i class="fas fa-at"></i>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td>
                                <?php if ($user['phone']): ?>
                                    <div class="contact-info">
                                        <i class="fas fa-mobile-alt"></i>
                                        <?php echo htmlspecialchars($user['phone']); ?>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $user['role_name'] === 'Admin' ? 'danger' : 
                                        ($user['role_name'] === 'Staff' ? 'warning' : 'info'); 
                                ?>">
                                    <i class="fas fa-<?php 
                                        echo $user['role_name'] === 'Admin' ? 'crown' : 
                                            ($user['role_name'] === 'Staff' ? 'user-tie' : 'user'); 
                                    ?>"></i>
                                    <?php echo htmlspecialchars($user['role_name']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="date-text">
                                    <i class="fas fa-calendar-day"></i>
                                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" title="Edit User">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus user ini?')"
                                           title="Hapus User">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <span class="current-user-badge">
                                            <i class="fas fa-user-check"></i>
                                            Akun Anda
                                        </span>
                                    <?php endif; ?>
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