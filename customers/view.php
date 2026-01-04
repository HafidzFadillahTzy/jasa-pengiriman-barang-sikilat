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
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$customer) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pelanggan - Jasa Pengiriman Barang</title>
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
            max-width: 1200px;
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

        .detail-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 35px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .detail-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .detail-section:last-child {
            border-bottom: none;
        }

        .detail-section h2 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 0.75rem;
            gap: 1rem;
        }

        .detail-row strong {
            min-width: 180px;
            color: #555;
            font-weight: 600;
        }

        .detail-row span {
            color: #333;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95em;
        }

        .btn i {
            margin-right: 6px;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffa726, #fb8c00);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 167, 38, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #78909c, #546e7a);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(120, 144, 156, 0.4);
        }

        .customer-name {
            font-weight: 600;
            color: #667eea;
            font-size: 1.1em;
        }

        .contact-info {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #333;
        }

        .contact-info i {
            color: #667eea;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-header h1 {
                font-size: 1.6em;
            }

            .detail-row {
                flex-direction: column;
            }

            .detail-row strong {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-user"></i>
                Detail Pelanggan
            </h1>
            <div style="display: flex; gap: 10px;">
                <a href="edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        
        <div class="detail-card">
            <div class="detail-section">
                <h2>
                    <i class="fas fa-info-circle"></i>
                    Informasi Umum
                </h2>
                <div class="detail-row">
                    <strong><i class="fas fa-hashtag"></i> ID:</strong>
                    <span><?php echo $customer['id']; ?></span>
                </div>
                <div class="detail-row">
                    <strong><i class="fas fa-user"></i> Nama:</strong>
                    <span class="customer-name">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($customer['name']); ?>
                    </span>
                </div>
            </div>
            
            <div class="detail-section">
                <h2>
                    <i class="fas fa-address-book"></i>
                    Informasi Kontak
                </h2>
                <div class="detail-row">
                    <strong><i class="fas fa-envelope"></i> Email:</strong>
                    <span>
                        <?php if ($customer['email']): ?>
                            <div class="contact-info">
                                <i class="fas fa-at"></i>
                                <?php echo htmlspecialchars($customer['email']); ?>
                            </div>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-row">
                    <strong><i class="fas fa-phone"></i> Telepon:</strong>
                    <span>
                        <div class="contact-info">
                            <i class="fas fa-mobile-alt"></i>
                            <?php echo htmlspecialchars($customer['phone']); ?>
                        </div>
                    </span>
                </div>
            </div>
            
            <div class="detail-section">
                <h2>
                    <i class="fas fa-map-marker-alt"></i>
                    Informasi Alamat
                </h2>
                <div class="detail-row">
                    <strong><i class="fas fa-map-marked-alt"></i> Alamat:</strong>
                    <span><?php echo nl2br(htmlspecialchars($customer['address'])); ?></span>
                </div>
                <div class="detail-row">
                    <strong><i class="fas fa-city"></i> Kota:</strong>
                    <span><?php echo htmlspecialchars($customer['city']); ?></span>
                </div>
                <div class="detail-row">
                    <strong><i class="fas fa-mail-bulk"></i> Kode Pos:</strong>
                    <span><?php echo htmlspecialchars($customer['postal_code'] ?? '-'); ?></span>
                </div>
            </div>
            
            <div class="detail-section">
                <h2>
                    <i class="fas fa-calendar"></i>
                    Informasi Waktu
                </h2>
                <div class="detail-row">
                    <strong><i class="fas fa-calendar-plus"></i> Dibuat pada:</strong>
                    <span><?php echo date('d/m/Y H:i:s', strtotime($customer['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <strong><i class="fas fa-calendar-check"></i> Diperbarui pada:</strong>
                    <span><?php echo date('d/m/Y H:i:s', strtotime($customer['updated_at'])); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

