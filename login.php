<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: ' . get_base_path() . 'index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role_name'];
                $_SESSION['role_id'] = $user['role_id'];
                
                header('Location: ' . get_base_path() . 'index.php');
                exit();
            } else {
                $error = 'Username atau password salah!';
            }
        } else {
            $error = 'Username atau password salah!';
        }
        $stmt->close();
        $conn->close();
    } else {
        $error = 'Silakan isi semua field!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jasa Pengiriman Barang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body.login-page {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background Elements */
        body.login-page::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            left: -250px;
            animation: float 6s ease-in-out infinite;
        }

        body.login-page::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -200px;
            right: -200px;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(20px);
            }
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 50px 45px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-box h1 {
            text-align: center;
            color: #667eea;
            font-size: 2em;
            margin-bottom: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .login-box h1 i {
            font-size: 1.2em;
        }

        .login-box h2 {
            text-align: center;
            color: #666;
            font-size: 1.5em;
            margin-bottom: 35px;
            font-weight: 600;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .alert-error {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            border-left: 4px solid #ef5350;
            color: #c62828;
        }

        .alert-error i {
            color: #ef5350;
            font-size: 1.3em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #555;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: #667eea;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 50px;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 5px 10px;
            font-size: 1.1em;
            transition: all 0.3s ease;
        }

        .toggle-password:hover {
            color: #667eea;
        }

        .toggle-password:focus {
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #999;
            font-size: 0.9em;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
        }

        .feature-item {
            text-align: center;
            color: #666;
        }

        .feature-item i {
            color: #667eea;
            font-size: 1.8em;
            margin-bottom: 8px;
            display: block;
        }

        .feature-item span {
            font-size: 0.85em;
            display: block;
        }

        @media (max-width: 768px) {
            .login-box {
                padding: 35px 30px;
            }

            .login-box h1 {
                font-size: 1.6em;
            }

            .login-box h2 {
                font-size: 1.2em;
            }

            .features {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .feature-item {
                display: flex;
                align-items: center;
                gap: 10px;
                text-align: left;
            }

            .feature-item i {
                margin-bottom: 0;
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>
                <i class="fas fa-shipping-fast"></i>
                Jasa Pengiriman Barang
            </h1>
            <h2>Selamat Datang</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <div class="input-wrapper">
                        <input type="text" 
                               id="username" 
                               name="username" 
                               placeholder="Masukkan username Anda"
                               required 
                               autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Masukkan password Anda"
                               required>
                        <button type="button" 
                                class="toggle-password" 
                                onclick="togglePassword()"
                                title="Tampilkan/Sembunyikan Password">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>

            <div class="features">
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Aman</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-bolt"></i>
                    <span>Cepat</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Terpercaya</span>
                </div>
            </div>

            <div class="login-footer">
                <i class="fas fa-lock"></i> Koneksi Anda aman dan terenkripsi
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Tambahkan keyboard shortcut untuk toggle password (Alt+S)
        document.addEventListener('keydown', function(e) {
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                togglePassword();
            }
        });
    </script>
</body>
</html>