<?php
// admin/layout.php
if (!isset($_SESSION)) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? 'Admin Dashboard';
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>SALMA <span>Solutions</span></h2>
    </div>
    
    <div class="sidebar-menu">
        <a href="index.php" class="menu-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="drafts.php" class="menu-item <?php echo $current_page == 'drafts.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i>
            <span>Draf Artikel</span>
        </a>
        <a href="add_article.php" class="menu-item <?php echo $current_page == 'add_article.php' ? 'active' : ''; ?>">
            <i class="fas fa-plus-circle"></i>
            <span>Tambah Artikel</span>
        </a>
        <a href="../blog.php" class="menu-item" target="_blank">
            <i class="fas fa-eye"></i>
            <span>Lihat Blog</span>
        </a>
        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

