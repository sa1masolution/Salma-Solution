<?php
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

// Get draft articles only
$stmt = $article->getDraftArticles();
$total_drafts = $stmt->rowCount();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_query = "DELETE FROM articles WHERE id = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $_GET['id']);
    
    if ($delete_stmt->execute()) {
        $success = "Artikel berhasil dihapus!";
        header('Location: drafts.php?success=' . urlencode($success));
        exit;
    } else {
        $error = "Gagal menghapus artikel!";
    }
}

// Handle publish action
if (isset($_GET['action']) && $_GET['action'] == 'publish' && isset($_GET['id'])) {
    $publish_query = "UPDATE articles SET status = 'published', updated_at = NOW() WHERE id = :id";
    $publish_stmt = $db->prepare($publish_query);
    $publish_stmt->bindParam(':id', $_GET['id']);
    
    if ($publish_stmt->execute()) {
        $success = "Artikel berhasil dipublikasikan!";
        header('Location: drafts.php?success=' . urlencode($success));
        exit;
    } else {
        $error = "Gagal mempublikasikan artikel!";
    }
}

// Handle multiple actions
if (isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_articles = $_POST['selected_articles'] ?? [];
    
    if (!empty($selected_articles)) {
        $placeholders = implode(',', array_fill(0, count($selected_articles), '?'));
        
        if ($bulk_action == 'publish') {
            $query = "UPDATE articles SET status = 'published', updated_at = NOW() WHERE id IN ($placeholders)";
            $message = "Artikel berhasil dipublikasikan!";
        } elseif ($bulk_action == 'delete') {
            $query = "DELETE FROM articles WHERE id IN ($placeholders)";
            $message = "Artikel berhasil dihapus!";
        }
        
        $stmt = $db->prepare($query);
        if ($stmt->execute($selected_articles)) {
            $success = $message;
            header('Location: drafts.php?success=' . urlencode($success));
            exit;
        } else {
            $error = "Gagal melakukan aksi bulk!";
        }
    } else {
        $error = "Tidak ada artikel yang dipilih!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draf Artikel - SALMA Solutions</title>
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

        .btn-success {
            background: var(--success);
        }

        .btn-success:hover {
            background: #0d9c6d;
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

        /* Bulk Actions */
        .bulk-actions {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            display: none;
        }

        .bulk-actions.active {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .bulk-actions select {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            background: white;
        }

        .bulk-actions .btn-sm {
            padding: 8px 16px;
            font-size: 0.85rem;
        }

        .selected-count {
            color: var(--primary);
            font-weight: 600;
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

        .article-title a {
            color: inherit;
            text-decoration: none;
        }

        .article-title a:hover {
            color: var(--secondary);
            text-decoration: underline;
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: var(--primary);
            color: white;
        }

        .btn-view:hover {
            background: #0f2a45;
        }

        .btn-edit {
            background: var(--accent);
            color: var(--dark);
        }

        .btn-edit:hover {
            background: #e6a82e;
        }

        .btn-publish {
            background: var(--success);
            color: white;
        }

        .btn-publish:hover {
            background: #0d9c6d;
        }

        .btn-delete {
            background: var(--danger);
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        /* Checkbox */
        .checkbox-cell {
            width: 40px;
            text-align: center;
        }

        .bulk-checkbox {
            width: 18px;
            height: 18px;
            accent-color: var(--secondary);
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
            
            .bulk-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-sm {
                width: 100%;
                justify-content: center;
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

            <a href="drafts.php" class="menu-item active">
                <i class="fas fa-file-alt"></i>
                <span>Draf</span>
            </a>
            <a href="add_article.php" class="menu-item">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah Artikel</span>
            </a> <a href="edit_profile.php" class="menu-item">
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
                <h1>Draf Artikel</h1>
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
                <a href="add_article.php" class="quick-action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <div class="quick-action-text">
                        <h4>Tulis Artikel Baru</h4>
                        <p>Buat konten fresh untuk blog Anda</p>
                    </div>
                </a>
                <a href="index.php" class="quick-action-btn">
                    <i class="fas fa-tachometer-alt"></i>
                    <div class="quick-action-text">
                        <h4>Kembali ke Dashboard</h4>
                        <p>Lihat semua artikel dan statistik</p>
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

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-numbers">
                            <h3><?php echo $total_drafts; ?></h3>
                            <p>Total Draf</p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-numbers">
                            <h3><?php echo $total_drafts; ?></h3>
                            <p>Menunggu Publikasi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <form method="POST" id="bulkForm" class="bulk-actions">
                <div class="selected-count" id="selectedCount">0 artikel dipilih</div>
                <select name="bulk_action" class="form-control" style="max-width: 200px;">
                    <option value="">Pilih Aksi</option>
                    <option value="publish">Publikasikan</option>
                    <option value="delete">Hapus</option>
                </select>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-play"></i> Terapkan
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="clearSelection()">
                    <i class="fas fa-times"></i> Batal
                </button>
            </form>

            <!-- Articles Section -->
            <div class="content-header">
                <h2>Draf Artikel</h2>
                <div class="header-actions">
                    <a href="add_article.php" class="btn">
                        <i class="fas fa-plus"></i> Tambah Artikel Baru
                    </a>
                    <a href="index.php" class="btn btn-accent">
                        <i class="fas fa-list"></i> Lihat Semua Artikel
                    </a>
                </div>
            </div>

            <div class="table-container">
                <?php if ($stmt->rowCount() > 0): ?>
                    <form method="POST" id="mainForm">
                        <table>
                            <thead>
                                <tr>
                                    <th class="checkbox-cell">
                                        <input type="checkbox" id="selectAll" class="bulk-checkbox">
                                    </th>
                                    <th>Judul Artikel</th>
                                    <th>Penulis</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td class="checkbox-cell">
                                            <input type="checkbox" name="selected_articles[]" value="<?php echo $row['id']; ?>" class="bulk-checkbox article-checkbox">
                                        </td>
                                        <td>
                                            <div class="article-title">
                                                <a href="../article.php?id=<?php echo $row['id']; ?>" target="_blank" title="Preview">
                                                    <?php echo htmlspecialchars($row['title']); ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <td>
                                            <span class="status-badge status-draft">
                                                Draft
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                
                                   
                                                <a href="drafts.php?action=publish&id=<?php echo $row['id']; ?>" 
                                                   class="btn-sm btn-publish" 
                                                   onclick="return confirm('Yakin ingin mempublikasikan artikel ini?')"
                                                   title="Publikasikan">
                                                    <i class="fas fa-paper-plane"></i>
                                                </a>
                                                <a href="drafts.php?action=delete&id=<?php echo $row['id']; ?>" 
                                                   class="btn-sm btn-delete" 
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
                    </form>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h3>Tidak ada draf artikel</h3>
                        <p>Semua artikel Anda sudah dipublikasikan atau belum ada artikel yang dibuat</p>
                        <a href="add_article.php" class="btn" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Tambah Artikel Baru
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

        // Bulk selection functionality
        const selectAll = document.getElementById('selectAll');
        const articleCheckboxes = document.querySelectorAll('.article-checkbox');
        const bulkActions = document.querySelector('.bulk-actions');
        const selectedCount = document.getElementById('selectedCount');
        const bulkForm = document.getElementById('bulkForm');

        // Select all functionality
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const isChecked = this.checked;
                articleCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                updateBulkActions();
            });
        }

        // Individual checkbox functionality
        articleCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        // Update bulk actions visibility and count
        function updateBulkActions() {
            const selectedCountValue = document.querySelectorAll('.article-checkbox:checked').length;
            
            if (selectedCountValue > 0) {
                bulkActions.classList.add('active');
                selectedCount.textContent = `${selectedCountValue} artikel dipilih`;
                
                // Update select all checkbox state
                if (selectAll) {
                    selectAll.checked = selectedCountValue === articleCheckboxes.length;
                    selectAll.indeterminate = selectedCountValue > 0 && selectedCountValue < articleCheckboxes.length;
                }
            } else {
                bulkActions.classList.remove('active');
            }
        }

        // Clear selection
        function clearSelection() {
            articleCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            if (selectAll) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
            updateBulkActions();
        }

        // Bulk form submission
        if (bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                const selectedAction = this.querySelector('select[name="bulk_action"]').value;
                const selectedArticles = document.querySelectorAll('.article-checkbox:checked');
                
                if (selectedArticles.length === 0) {
                    e.preventDefault();
                    alert('Tidak ada artikel yang dipilih!');
                    return;
                }
                
                if (!selectedAction) {
                    e.preventDefault();
                    alert('Silakan pilih aksi yang ingin dilakukan!');
                    return;
                }
                
                if (selectedAction === 'delete') {
                    if (!confirm(`Yakin ingin menghapus ${selectedArticles.length} artikel?`)) {
                        e.preventDefault();
                        return;
                    }
                } else if (selectedAction === 'publish') {
                    if (!confirm(`Yakin ingin mempublikasikan ${selectedArticles.length} artikel?`)) {
                        e.preventDefault();
                        return;
                    }
                }
            });
        }

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
    </script>
</body>
</html>