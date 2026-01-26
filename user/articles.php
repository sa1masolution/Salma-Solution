<?php
// user/articles.php
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

// Get all published articles
$stmt = $article->getPublishedArticles();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel - SALMA Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS sama seperti index.php */
        
        .articles-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            padding: 12px 15px;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            width: 300px;
        }
        
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
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
            <a href="articles.php" class="menu-item active">
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
                <h1>Semua Artikel</h1>
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
            <div class="articles-header">
                <h2 style="color: var(--primary);">Artikel Terbaru</h2>
                <div class="search-box">
                    <input type="text" class="search-input" placeholder="Cari artikel...">
                    <button class="btn">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </div>
            
            <div class="articles-grid">
                <?php if ($stmt->rowCount() > 0): ?>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="article-card">
                            <div class="article-image">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="article-content">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <p style="color: var(--gray); font-size: 0.9rem; line-height: 1.5;">
                                    <?php echo substr(strip_tags($row['content']), 0, 150) . '...'; ?>
                                </p>
                                <div class="article-meta">
                                    <span class="article-date">
                                        <i class="fas fa-calendar"></i> 
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
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: var(--gray);">
                        <i class="fas fa-newspaper" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.5;"></i>
                        <h3>Belum ada artikel</h3>
                        <p>Silakan kembali lagi nanti untuk melihat artikel terbaru.</p>
                    </div>
                <?php endif; ?>
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
    </script>
</body>
</html>