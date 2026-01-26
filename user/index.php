<?php
// user/index.php
session_start();

// Simple session check untuk user
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'user') {
    header('Location: ../login.php');
    exit;
}

// Include database connection
include '../config/database.php';
include '../models/Article.php';

// Initialize database and article
$database = new Database();
$db = $database->getConnection();
$article = new Article($db);

// Get published articles for user
$stmt = $article->getPublishedArticles();
$total_articles = $stmt->rowCount();

// Get recent articles
$recent_stmt = $article->getRecentArticles(10);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - SALMA Solutions</title>
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

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary), #2c5282);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-lg);
        }

        .welcome-section h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .welcome-section p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 24px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--secondary);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(74, 140, 94, 0.1);
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-numbers h3 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .stat-numbers p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Articles Grid */
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 30px;
        }

        .article-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .article-image {
            height: 160px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .article-content {
            padding: 20px;
        }

        .article-content h3 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.1rem;
            line-height: 1.4;
        }

        .article-meta {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .article-date {
            color: var(--gray);
            font-size: 0.85rem;
        }

        .read-more {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .read-more:hover {
            text-decoration: underline;
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
            
            .welcome-section {
                padding: 30px 20px;
            }
            
            .welcome-section h2 {
                font-size: 1.5rem;
            }
            
            .content {
                padding: 20px;
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
            <a href="index.php" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="articles.php" class="menu-item">
                <i class="fas fa-newspaper"></i>
                <span>Artikel</span>
            </a>
            <a href="profile.php" class="menu-item">
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
                <h1>Dashboard User</h1>
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
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2>Selamat Datang, <?php echo $_SESSION['full_name']; ?>! 👋</h2>
                <p>Terima kasih telah bergabung dengan SALMA Solutions. Mari eksplor konten menarik kami.</p>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="stat-numbers">
                            <h3><?php echo $total_articles; ?></h3>
                            <p>Total Artikel</p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-numbers">
                            <h3><?php echo $_SESSION['company'] ?? '-'; ?></h3>
                            <p>Perusahaan</p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="stat-numbers">
                            <h3><?php echo $_SESSION['position'] ?? '-'; ?></h3>
                            <p>Posisi</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <div class="stat-numbers">
                            <h3><?php echo ucfirst($_SESSION['user_type_role'] ?? 'regular'); ?></h3>
                            <p>Tipe User</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Articles -->
            <h2 style="color: var(--primary); margin-bottom: 20px;">Artikel Terbaru</h2>
            
            <div class="articles-grid">
                <?php if ($recent_stmt->rowCount() > 0): ?>
                    <?php while ($row = $recent_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="article-card">
                            <div class="article-image">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="article-content">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <p style="color: var(--gray); font-size: 0.9rem; line-height: 1.5;">
                                    <?php echo substr(strip_tags($row['content']), 0, 100) . '...'; ?>
                                </p>
                                <div class="article-meta">
                                    <span class="article-date">
                                        <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                    </span>
                                    <a href="../article.php?id=<?php echo $row['id']; ?>" class="read-more">
                                        Baca Selengkapnya
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--gray);">
                        <i class="fas fa-newspaper" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <h3>Belum ada artikel</h3>
                        <p>Silakan kembali lagi nanti untuk melihat artikel terbaru.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Hanya script untuk UI, TANPA auto-logout
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        
        mobileMenuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    </script>
</body>
</html>