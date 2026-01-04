<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireRole('Admin');

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');
    exit();
}

// Prevent deleting own account
if ($id == $_SESSION['user_id']) {
    header('Location: index.php?error=cannot_delete_self');
    exit();
}

$conn = getDBConnection();

// Check if user is used in shipments (created_by)
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM shipments WHERE created_by = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];
$stmt->close();

if ($count > 0) {
    $conn->close();
    header('Location: index.php?error=user_in_use');
    exit();
}

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: index.php?success=deleted');
exit();
?>



