<?php
if (!isLoggedIn()) {
    $base_path = get_base_path();
    header('Location: ' . $base_path . 'login.php');
    exit();
}

// Deteksi path base untuk navigasi berdasarkan script yang memanggil header
// Header bisa dipanggil dari root (index.php) atau subfolder (customers/, packages/, dll)
$script_path = $_SERVER['SCRIPT_NAME'];
$base_path = '';

// Jika script berada di subfolder (bukan di root)
if (preg_match('#/(customers|packages|shipments|users)/#', $script_path)) {
    $base_path = '../';
}
// Jika script di root, base_path tetap kosong
?>
<header class="header">
    <div class="header-content">
        <h1 class="logo">Jasa Pengiriman Barang</h1>
        <nav class="nav">
            <a href="<?php echo $base_path; ?>index.php" class="nav-link">Dashboard</a>
            <?php if (isStaff()): ?>
                <a href="<?php echo $base_path; ?>packages/index.php" class="nav-link">Paket</a>
                <a href="<?php echo $base_path; ?>customers/index.php" class="nav-link">Pelanggan</a>
                <a href="<?php echo $base_path; ?>shipments/index.php" class="nav-link">Pengiriman</a>
            <?php elseif (hasRole('Customer')): ?>
                <a href="<?php echo $base_path; ?>shipments/my-shipments.php" class="nav-link">Pengiriman Saya</a>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <a href="<?php echo $base_path; ?>users/index.php" class="nav-link">Users</a>
            <?php endif; ?>
            <div class="user-menu">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                <a href="<?php echo $base_path; ?>logout.php" class="btn btn-sm btn-danger">Logout</a>
            </div>
        </nav>
    </div>
</header>

