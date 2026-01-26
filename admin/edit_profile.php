<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Include database connection
include '../config/database.php';
include '../models/Admin.php';

// Initialize database and admin
$database = new Database();
$db = $database->getConnection();
$admin = new AdminUser($db);

$error = '';
$success = '';
$profile_error = '';
$profile_success = '';
$password_error = '';
$password_success = '';

// Get current admin data
$admin_id = $_SESSION['admin_id'];
$current_admin = $admin->getAdminById($admin_id);

if (!$current_admin) {
    header('Location: index.php');
    exit;
}

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    
    // Validation
    if (empty($full_name) || empty($email) || empty($username)) {
        $profile_error = "Semua field profil harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = "Format email tidak valid!";
    } else {
        try {
            // Check if username already exists (excluding current admin)
            if ($admin->usernameExists($username) && $username !== $current_admin['username']) {
                $profile_error = "Username sudah digunakan!";
            } else {
                // Update profile using the existing method structure
                $query = "UPDATE admins 
                         SET full_name = :full_name, 
                             email = :email, 
                             username = :username
                         WHERE id = :id";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':id', $admin_id);
                
                if ($stmt->execute()) {
                    $profile_success = "Profil berhasil diperbarui!";
                    // Update session
                    $_SESSION['admin_full_name'] = $full_name;
                    $_SESSION['admin_email'] = $email;
                    $_SESSION['admin_username'] = $username;
                    // Refresh current admin data
                    $current_admin = $admin->getAdminById($admin_id);
                } else {
                    $profile_error = "Gagal memperbarui profil!";
                }
            }
        } catch (PDOException $e) {
            $profile_error = "Error: " . $e->getMessage();
        }
    }
}

// Handle password change
if ($_POST && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = "Semua field password harus diisi!";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "Password baru dan konfirmasi password tidak cocok!";
    } elseif (strlen($new_password) < 6) {
        $password_error = "Password baru minimal 6 karakter!";
    } else {
        try {
            // Verify current password by attempting login
            $admin_data = $admin->login($current_admin['username'], $current_password);
            if (!$admin_data) {
                $password_error = "Password saat ini salah!";
            } else {
                // Change password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE admins SET password_hash = :password_hash WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':password_hash', $hashed_password);
                $stmt->bindParam(':id', $admin_id);
                
                if ($stmt->execute()) {
                    $password_success = "Password berhasil diubah!";
                    // Clear form
                    $_POST['current_password'] = $_POST['new_password'] = $_POST['confirm_password'] = '';
                } else {
                    $password_error = "Gagal mengubah password!";
                }
            }
        } catch (PDOException $e) {
            $password_error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - SALMA Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a3a5f;
            --secondary: #4a8c5e;
            --accent: #f8b739;
            --light: #f8fafc;
            --dark: #2c3e50;
            --gray: #64748b;
            --white: #ffffff;
            --sidebar: #1e293b;
            --sidebar-hover: #334155;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--light);
            color: var(--dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: var(--sidebar);
            color: white;
            position: fixed;
            height: 100vh;
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid #334155;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .sidebar-header span {
            color: var(--accent);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .menu-item:hover, .menu-item.active {
            background: var(--sidebar-hover);
            color: white;
            border-left-color: var(--accent);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            transition: all 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        .top-header {
            background: var(--white);
            padding: 18px 30px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-title h1 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .logout-btn {
            background: var(--danger);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Content Area */
        .content {
            padding: 30px;
            flex: 1;
        }

        /* Profile Container */
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        @media (max-width: 968px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }

        /* Card Styles */
        .card {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            height: fit-content;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .card-header i {
            font-size: 1.5rem;
            color: var(--secondary);
        }

        .card-header h2 {
            color: var(--primary);
            font-size: 1.4rem;
            font-weight: 600;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
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
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(74, 140, 94, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
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

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: var(--secondary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn:hover {
            background: #3a7a4e;
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }

        .btn-outline:hover {
            background: var(--secondary);
            color: white;
        }

        .btn-accent {
            background: var(--accent);
            color: var(--dark);
        }

        .btn-accent:hover {
            background: #e6a82e;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }

        /* Password Strength */
        .password-strength {
            margin-top: 8px;
            height: 6px;
            border-radius: 3px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 3px;
        }

        .strength-weak {
            background: var(--danger);
            width: 33%;
        }

        .strength-medium {
            background: var(--warning);
            width: 66%;
        }

        .strength-strong {
            background: var(--success);
            width: 100%;
        }

        .password-requirements {
            margin-top: 8px;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .requirement.met {
            color: var(--success);
        }

        .requirement.unmet {
            color: var(--gray);
        }

        .requirement i {
            font-size: 0.7rem;
        }

        /* Profile Info */
        .profile-info {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .profile-info p {
            margin: 8px 0;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .profile-info strong {
            color: var(--dark);
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-header h2, .menu-item span {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .menu-item {
                justify-content: center;
                padding: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 260px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .content {
                padding: 20px;
            }
            
            .card {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .header-actions {
                gap: 10px;
            }
            
            .user-info span {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .top-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .content {
                padding: 15px;
            }
            
            .card {
                padding: 15px;
            }
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

            <a href="drafts.php" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span>Draf</span>
            </a>
            <a href="add_article.php" class="menu-item">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah Artikel</span>
            </a>

            <a href="edit_profile.php" class="menu-item active">
                <i class="fas fa-user-cog"></i>
                <span>Edit Profil</span>
            </a>
            <a href="../index.php" class="menu-item">
                <i class="fas fa-globe"></i>
                <span>Lihat Website</span>
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
                <h1>Edit Profil Admin</h1>
            </div>
            
            <div class="header-actions">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                    </div>
                    <span>Hi, <?php echo $_SESSION['admin_full_name']; ?></span>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="profile-container">
                <!-- Profile Update Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-edit"></i>
                        <h2>Edit Profil</h2>
                    </div>

                    <?php if ($profile_success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $profile_success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($profile_error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $profile_error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="profile-info">
                        <p><strong>Role:</strong> <?php echo ucfirst($current_admin['role']); ?></p>
                        <p><strong>Status:</strong> <?php echo $current_admin['is_active'] ? 'Aktif' : 'Nonaktif'; ?></p>
                        <p><strong>Login Terakhir:</strong> <?php echo $current_admin['last_login'] ? date('d M Y H:i', strtotime($current_admin['last_login'])) : 'Belum pernah'; ?></p>
                        <p><strong>Terdaftar:</strong> <?php echo date('d M Y', strtotime($current_admin['created_at'])); ?></p>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label for="full_name">Nama Lengkap *</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($current_admin['full_name']); ?>" 
                                   required placeholder="Masukkan nama lengkap">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($current_admin['email']); ?>" 
                                       required placeholder="email@example.com">
                            </div>

                            <div class="form-group">
                                <label for="username">Username *</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($current_admin['username']); ?>" 
                                       required placeholder="Masukkan username">
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="index.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" name="update_profile" class="btn">
                                <i class="fas fa-save"></i> Update Profil
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Change Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-lock"></i>
                        <h2>Ganti Password</h2>
                    </div>

                    <?php if ($password_success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $password_success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($password_error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $password_error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="passwordForm">
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini *</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" 
                                   required placeholder="Masukkan password saat ini">
                        </div>

                        <div class="form-group">
                            <label for="new_password">Password Baru *</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" 
                                   required placeholder="Masukkan password baru" minlength="6">
                            <div class="password-strength">
                                <div class="strength-bar" id="strengthBar"></div>
                            </div>
                            <div class="password-requirements" id="passwordRequirements">
                                <div class="requirement unmet" id="reqLength">
                                    <i class="fas fa-circle"></i> Minimal 6 karakter
                                </div>
                                <div class="requirement unmet" id="reqStrength">
                                    <i class="fas fa-circle"></i> Kekuatan password
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                   required placeholder="Konfirmasi password baru">
                            <div class="password-match" id="passwordMatch"></div>
                        </div>

                        <div class="form-actions">
                            <button type="reset" class="btn btn-outline" onclick="resetPasswordForm()">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                            <button type="submit" name="change_password" class="btn">
                                <i class="fas fa-key"></i> Ganti Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        
        mobileMenuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Password strength checker
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('strengthBar');
        const passwordMatch = document.getElementById('passwordMatch');
        const reqLength = document.getElementById('reqLength');
        const reqStrength = document.getElementById('reqStrength');

        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            checkPasswordStrength(password);
            checkPasswordMatch();
        });

        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        function checkPasswordStrength(password) {
            let strength = 0;
            
            // Length requirement
            if (password.length >= 6) {
                strength += 1;
                reqLength.classList.remove('unmet');
                reqLength.classList.add('met');
                reqLength.innerHTML = '<i class="fas fa-check-circle"></i> Minimal 6 karakter';
            } else {
                reqLength.classList.remove('met');
                reqLength.classList.add('unmet');
                reqLength.innerHTML = '<i class="fas fa-circle"></i> Minimal 6 karakter';
            }
            
            // Additional strength factors
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength bar and text
            strengthBar.className = 'strength-bar';
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                reqStrength.classList.remove('met', 'unmet');
                reqStrength.innerHTML = '<i class="fas fa-circle"></i> Kekuatan password';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                reqStrength.classList.remove('met');
                reqStrength.classList.add('unmet');
                reqStrength.innerHTML = '<i class="fas fa-circle"></i> Password lemah';
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
                reqStrength.classList.remove('met');
                reqStrength.classList.add('unmet');
                reqStrength.innerHTML = '<i class="fas fa-circle"></i> Password sedang';
            } else {
                strengthBar.classList.add('strength-strong');
                reqStrength.classList.remove('unmet');
                reqStrength.classList.add('met');
                reqStrength.innerHTML = '<i class="fas fa-check-circle"></i> Password kuat';
            }
        }

        function checkPasswordMatch() {
            const password = newPasswordInput.value;
            const confirm = confirmPasswordInput.value;
            
            if (confirm.length === 0) {
                passwordMatch.innerHTML = '';
                passwordMatch.className = 'password-match';
            } else if (password === confirm) {
                passwordMatch.innerHTML = '<i class="fas fa-check-circle"></i> Password cocok';
                passwordMatch.className = 'password-match requirement met';
            } else {
                passwordMatch.innerHTML = '<i class="fas fa-times-circle"></i> Password tidak cocok';
                passwordMatch.className = 'password-match requirement unmet';
            }
        }

        function resetPasswordForm() {
            newPasswordInput.value = '';
            confirmPasswordInput.value = '';
            strengthBar.className = 'strength-bar';
            strengthBar.style.width = '0%';
            passwordMatch.innerHTML = '';
            reqLength.className = 'requirement unmet';
            reqLength.innerHTML = '<i class="fas fa-circle"></i> Minimal 6 karakter';
            reqStrength.className = 'requirement unmet';
            reqStrength.innerHTML = '<i class="fas fa-circle"></i> Kekuatan password';
        }

        // Form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                e.preventDefault();
                alert('Semua field password harus diisi!');
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak cocok!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password baru minimal 6 karakter!');
                return false;
            }
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