<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireStaff();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();

// Check if customer is used in shipments
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM shipments WHERE customer_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];
$stmt->close();

if ($count > 0) {
    header('Location: index.php?error=customer_in_use');
    exit();
}

$stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: index.php?success=deleted');
exit();
?>

