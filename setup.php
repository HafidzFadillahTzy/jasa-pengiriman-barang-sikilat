<?php
/**
 * Setup Script - Jalankan sekali untuk setup awal
 * Akses: http://localhost/nama-folder/setup.php
 */

require_once 'config/database.php';

$conn = getDBConnection();

// Update admin password
$password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $password);

if ($stmt->execute()) {
    echo "<h1>Setup Berhasil!</h1>";
    echo "<p>Password admin telah diupdate.</p>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><a href='login.php'>Klik di sini untuk login</a></p>";
} else {
    echo "<h1>Error!</h1>";
    echo "<p>Gagal update password: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();
?>

