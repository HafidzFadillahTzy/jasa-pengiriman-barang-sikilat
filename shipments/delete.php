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
$stmt = $conn->prepare("DELETE FROM shipments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: index.php?success=deleted');
exit();
?>

