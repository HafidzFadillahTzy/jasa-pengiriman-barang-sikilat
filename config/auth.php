<?php
session_start();

// Fungsi untuk mendapatkan base URL aplikasi
function base_url($path = '') {
    // Deteksi protocol
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    
    // Dapatkan host
    $host = $_SERVER['HTTP_HOST'];
    
    // Dapatkan script name dan directory
    $script_name = $_SERVER['SCRIPT_NAME'];
    $script_dir = dirname($script_name);
    
    // Jika script_dir adalah root, gunakan '/'
    // Jika tidak, gunakan script_dir
    $base_path = ($script_dir === '/' || $script_dir === '\\') ? '' : $script_dir;
    
    // Hapus trailing slash
    $base_path = rtrim($base_path, '/');
    
    // Jika path dimulai dengan /, hapus
    $path = ltrim($path, '/');
    
    // Gabungkan
    $url = $protocol . '://' . $host . $base_path;
    if ($path) {
        $url .= '/' . $path;
    }
    
    return $url;
}

// Fungsi untuk mendapatkan path relatif dari root
function get_base_path() {
    $script_name = $_SERVER['SCRIPT_NAME'];
    $script_dir = dirname($script_name);
    
    // Hitung depth dari root
    $depth = substr_count($script_dir, '/') - 1;
    if ($depth < 0) $depth = 0;
    
    // Return '../' sebanyak depth
    return str_repeat('../', $depth);
}

// Fungsi untuk cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk cek role user
function hasRole($roleName) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return isset($_SESSION['role']) && $_SESSION['role'] === $roleName;
}

// Fungsi untuk cek apakah user adalah admin
function isAdmin() {
    return hasRole('Admin');
}

// Fungsi untuk cek apakah user adalah staff
function isStaff() {
    return hasRole('Staff') || isAdmin();
}

// Fungsi untuk cek apakah user adalah customer
function isCustomer() {
    return hasRole('Customer');
}

// Fungsi untuk require login
function requireLogin() {
    if (!isLoggedIn()) {
        $base_path = get_base_path();
        header('Location: ' . $base_path . 'login.php');
        exit();
    }
}

// Fungsi untuk require role tertentu
function requireRole($roleName) {
    requireLogin();
    if (!hasRole($roleName) && !isAdmin()) {
        $base_path = get_base_path();
        header('Location: ' . $base_path . 'index.php');
        exit();
    }
}

// Fungsi untuk require admin atau staff
function requireStaff() {
    requireLogin();
    if (!isStaff()) {
        $base_path = get_base_path();
        header('Location: ' . $base_path . 'index.php');
        exit();
    }
}

// Fungsi untuk logout
function logout() {
    session_unset();
    session_destroy();
    $base_path = get_base_path();
    header('Location: ' . $base_path . 'login.php');
    exit();
}
?>

