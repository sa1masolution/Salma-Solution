<?php
// admin/index.php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
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

// Get all articles for dashboard
$stmt = $article->getAllArticles(1000, 0);
$total_articles = $article->getTotalArticles();

// Get draft articles count
$draft_stmt = $article->getDraftArticles();
$total_drafts = $draft_stmt->rowCount();

// Get published articles count
$published_stmt = $article->getPublishedArticles();
$total_published = $published_stmt->rowCount();

// Get recent articles
$recent_stmt = $article->getRecentArticles(5);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_query = "DELETE FROM articles WHERE id = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $_GET['id']);
    
    if ($delete_stmt->execute()) {
        $success = "Artikel berhasil dihapus!";
        header('Location: index.php?success=' . urlencode($success));
        exit;
    } else {
        $error = "Gagal menghapus artikel!";
    }
}

// Handle draft action - Move to draft
if (isset($_GET['action']) && $_GET['action'] == 'draft' && isset($_GET['id'])) {
    $draft_query = "UPDATE articles SET status = 'draft' WHERE id = :id";
    $draft_stmt = $db->prepare($draft_query);
    $draft_stmt->bindParam(':id', $_GET['id']);
    
    if ($draft_stmt->execute()) {
        $success = "Artikel berhasil dipindahkan ke draft!";
        header('Location: index.php?success=' . urlencode($success));
        exit;
    } else {
        $error = "Gagal memindahkan artikel ke draft!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SALMA Solutions</title>
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
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.warning {
            border-left-color: var(--warning);
        }

        .stat-card.danger {
            border-left-color: var(--danger);
        }

        .stat-card.success {
            border-left-color: var(--success);
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

        .stat-card.warning .stat-icon {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-card.danger .stat-icon {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .stat-card.success .stat-icon {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-numbers {
            text-align: right;
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

        /* Content Header */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .content-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
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

        .btn-accent {
            background: var(--accent);
            color: var(--dark);
        }

        .btn-accent:hover {
            background: #e6a82e;
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

        /* Table Styles */
        .table-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        thead {
            background: var(--primary);
        }

        th {
            padding: 16px 20px;
            text-align: left;
            color: white;
            font-weight: 500;
            font-size: 0.9rem;
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .article-title {
            font-weight: 500;
            color: var(--primary);
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 0.8rem;
            border-radius: 6px;
        }

        .btn-draft {
            background: var(--warning);
            color: var(--dark);
        }

        .btn-delete {
            background: var(--danger);
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-published {
            background: #d1fae5;
            color: #065f46;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--primary);
            margin-bottom: 12px;
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

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
            background: var(--white);
            border-radius: 10px;
            text-decoration: none;
            color: var(--dark);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            flex: 1;
            min-width: 200px;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .quick-action-btn i {
            font-size: 1.5rem;
            color: var(--secondary);
        }

        .quick-action-text h4 {
            color: var(--primary);
            margin-bottom: 4px;
        }

        .quick-action-text p {
            color: var(--gray);
            font-size: 0.85rem;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .top-header {
                padding: 15px 20px;
            }
            
            .content {
                padding: 20px;
            }
            
            .header-actions {
                gap: 10px;
            }
            
            .user-info span {
                display: none;
            }
            
            .quick-actions {
                flex-direction: column;
            }
            
            .quick-action-btn {
                min-width: auto;
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
            
            .stat-card {
                padding: 18px;
            }
            
            .stat-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .stat-numbers {
                text-align: left;
                width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-sm {
                width: 100%;
                justify-content: center;
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

            <a href="drafts.php" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span>Draf</span>
            </a>
            <a href="add_article.php" class="menu-item">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah Artikel</span>
            </a>
    <a href="edit_profile.php" class="menu-item">
                <i class="fas fa-user-cog"></i>
                <span>Edit Profil</span>
            </a>
            <a href="../blog.php" class="menu-item">
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
                <h1>Dashboard Admin</h1>
            </div>
            
            <div class="header-actions">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                    </div>
                    <span>Hi, <?php echo $_SESSION['admin_username']; ?></span>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <span><?php echo htmlspecialchars($_GET['success']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> 
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="drafts.php" class="quick-action-btn">
                    <i class="fas fa-file-alt"></i>
                    <div class="quick-action-text">
                        <h4>Kelola Draf</h4>
                        <p>Lihat dan edit artikel yang belum dipublikasikan</p>
                    </div>
                </a>
                <a href="add_article.php" class="quick-action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <div class="quick-action-text">
                        <h4>Tulis Artikel Baru</h4>
                        <p>Buat konten fresh untuk blog Anda</p>
                    </div>
                </a>
                <a href="../blog.php" class="quick-action-btn" target="_blank">
                    <i class="fas fa-eye"></i>
                    <div class="quick-action-text">
                        <h4>Preview Blog</h4>
                        <p>Lihat bagaimana blog tampil untuk pengunjung</p>
                    </div>
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <a href="index.php" class="stat-card" style="text-decoration: none; color: inherit;">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="stat-numbers">
                            <h3><?php echo $total_articles; ?></h3>
                            <p>Total Artikel</p>
                        </div>
                    </div>
                </a>
                
                <a href="drafts.php" class="stat-card warning" style="text-decoration: none; color: inherit;">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-numbers">
                            <h3><?php echo $total_drafts; ?></h3>
                            <p>Draf Artikel</p>
                        </div>
                    </div>
                </a>
                
                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-numbers">
                            <h3><?php echo $total_published; ?></h3>
                            <p>Artikel Publik</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-numbers">
                            <h3><?php echo $recent_stmt->rowCount(); ?></h3>
                            <p>Artikel Baru</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Articles Section -->
            <div class="content-header">
                <h2>Semua Artikel</h2>
                <div class="header-actions">
                    <a href="drafts.php" class="btn btn-outline">
                        <i class="fas fa-file-alt"></i> Lihat Draf
                    </a>
                    <a href="add_article.php" class="btn">
                        <i class="fas fa-plus"></i> Tambah Artikel Baru
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <?php if ($stmt->rowCount() > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Judul Artikel</th>
                                <th>Penulis</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td>
                                        <div class="article-title" title="<?php echo htmlspecialchars($row['title']); ?>">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $row['status']; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($row['status'] == 'published'): ?>
                                                <a href="index.php?action=draft&id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-draft" 
                                                   onclick="return confirm('Yakin ingin memindahkan artikel ini ke draft?')"
                                                   title="Pindah ke Draft">
                                                    <i class="fas fa-file-alt"></i> 
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="index.php?action=delete&id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-delete" 
                                               onclick="return confirm('Yakin ingin menghapus artikel ini?')"
                                               title="Hapus">
                                                <i class="fas fa-trash"></i> 
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-newspaper"></i>
                        <h3>Belum ada artikel</h3>
                        <p>Mulai dengan menambahkan artikel pertama Anda</p>
                        <a href="add_article.php" class="btn" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Tambah Artikel Pertama
                        </a>
                    </div>
                <?php endif; ?>
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

        // Auto hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Add hover effects to table rows
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(4px)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });
        });

        // Make stat cards clickable
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('click', function() {
                    if (this.querySelector('a')) {
                        this.querySelector('a').click();
                    }
                });
            });
        });
    </script>
</body>
</html>