<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireRole('Admin');

$error = '';
$user = null;

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $conn->close();
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role_id = $_POST['role_id'] ?? 0;
    
    if ($username && $email && $full_name && $role_id) {
        // Check if username exists (except current user)
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check_stmt->bind_param("si", $username, $id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan!';
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            // Check if email exists (except current user)
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_stmt->bind_param("si", $email, $id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = 'Email sudah digunakan!';
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // Update user
                if ($password) {
                    // Update with new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, full_name = ?, phone = ?, address = ?, role_id = ? WHERE id = ?");
                    $stmt->bind_param("sssssssi", $username, $email, $hashed_password, $full_name, $phone, $address, $role_id, $id);
                } else {
                    // Update without changing password
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, address = ?, role_id = ? WHERE id = ?");
                    $stmt->bind_param("ssssssi", $username, $email, $full_name, $phone, $address, $role_id, $id);
                }
                
                if ($stmt->execute()) {
                    // If editing own account, update session
                    if ($id == $_SESSION['user_id']) {
                        $_SESSION['username'] = $username;
                        $_SESSION['full_name'] = $full_name;
                        $_SESSION['role'] = $user['role_name'];
                        $_SESSION['role_id'] = $role_id;
                    }
                    
                    header('Location: index.php?success=updated');
                    exit();
                } else {
                    $error = 'Gagal memperbarui user: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    } else {
        $error = 'Silakan isi semua field yang wajib!';
    }
}

// Get roles for dropdown
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
    <title>Edit User - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Edit User</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <input type="password" id="password" name="password" minlength="6">
                    <small>Kosongkan jika tidak ingin mengubah password (minimal 6 karakter jika diisi)</small>
                </div>
                
                <div class="form-group">
                    <label for="role_id">Role *</label>
                    <select id="role_id" name="role_id" required>
                        <option value="">Pilih Role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo $role['id'] == $user['role_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="full_name">Nama Lengkap *</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Telepon</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
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



