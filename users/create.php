<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireRole('Admin');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role_id = $_POST['role_id'] ?? 0;
    
    if ($username && $email && $password && $full_name && $role_id) {
        $conn = getDBConnection();
        
        // Check if username exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan!';
            $check_stmt->close();
            $conn->close();
        } else {
            $check_stmt->close();
            
            // Check if email exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = 'Email sudah digunakan!';
                $check_stmt->close();
                $conn->close();
            } else {
                $check_stmt->close();
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssi", $username, $email, $hashed_password, $full_name, $phone, $address, $role_id);
                
                if ($stmt->execute()) {
                    header('Location: index.php?success=created');
                    exit();
                } else {
                    $error = 'Gagal menambahkan user: ' . $stmt->error;
                }
                $stmt->close();
                $conn->close();
            }
        }
    } else {
        $error = 'Silakan isi semua field yang wajib!';
    }
}

// Get roles for dropdown
$conn = getDBConnection();
$roles = [];
$result = $conn->query("SELECT id, name FROM roles ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Tambah User</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small>Minimal 6 karakter</small>
                </div>
                
                <div class="form-group">
                    <label for="role_id">Role *</label>
                    <select id="role_id" name="role_id" required>
                        <option value="">Pilih Role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="full_name">Nama Lengkap *</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Telepon</label>
                    <input type="text" id="phone" name="phone">
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" rows="3"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>



