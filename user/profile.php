<?php
// user/profile.php
session_start();

// Simple session check untuk user
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'user') {
    header('Location: ../login.php');
    exit;
}

// Include database connection
include '../config/database.php';
include '../models/User.php';

// Initialize database and user
$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);

// Get user data
$user_data = $user_model->getUserById($_SESSION['user_id']);

// Handle profile update
$update_success = '';
$update_error = '';

if ($_POST) {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $company = $_POST['company'] ?? '';
    $position = $_POST['position'] ?? '';
    
    // Update user data in session
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email'] = $email;
    $_SESSION['phone'] = $phone;
    $_SESSION['company'] = $company;
    $_SESSION['position'] = $position;
    
    $update_success = "Profil berhasil diperbarui!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - SALMA Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS sama seperti index.php */
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-header {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--secondary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 600;
            margin: 0 auto 20px;
        }
        
        .profile-form {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(74, 140, 94, 0.1);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--secondary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #3a7a4e;
            transform: translateY(-1px);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>SALMA <span>Solutions</span></h2>
        </div>
        
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="articles.php" class="menu-item">
                <i class="fas fa-newspaper"></i>
                <span>Artikel</span>
            </a>
            <a href="profile.php" class="menu-item active">
                <i class="fas fa-user"></i>
                <span>Profil Saya</span>
            </a>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <div class="header-title">
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Profil Saya</h1>
            </div>
            
            <div class="header-actions">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <span>Hi, <?php echo $_SESSION['full_name']; ?></span>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="profile-container">
                <?php if ($update_success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $update_success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($update_error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $update_error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <h2><?php echo $_SESSION['full_name']; ?></h2>
                    <p><?php echo $_SESSION['position']; ?> at <?php echo $_SESSION['company']; ?></p>
                </div>
                
                <div class="profile-form">
                    <form method="POST">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" value="<?php echo $_SESSION['username']; ?>" readonly>
                            <small style="color: var(--gray);">Username tidak dapat diubah</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo $_SESSION['full_name']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $_SESSION['email']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo $_SESSION['phone'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Perusahaan</label>
                            <input type="text" name="company" class="form-control" value="<?php echo $_SESSION['company'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Posisi</label>
                            <input type="text" name="position" class="form-control" value="<?php echo $_SESSION['position'] ?? ''; ?>">
                        </div>
                        
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hanya script UI, TANPA auto-logout
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        
        mobileMenuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Auto hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>