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

// Create uploads directory if not exists
$upload_dir = '../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Get existing categories for suggestions
$category_query = "SELECT DISTINCT category FROM articles ORDER BY category";
$category_stmt = $db->prepare($category_query);
$category_stmt->execute();
$existing_categories = $category_stmt->fetchAll(PDO::FETCH_COLUMN);

$error = '';
$success = '';

if ($_POST) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $author = $_POST['author'] ?? '';
    $category = $_POST['category'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    
    // Initialize image_url
    $image_url = '';

    // Validation
    if (empty($title) || empty($content) || empty($author) || empty($category)) {
        $error = "Semua field wajib diisi!";
    } else {
        try {
            // Handle file upload
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['featured_image'];
                
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = mime_content_type($file['tmp_name']);
                
                if (!in_array($file_type, $allowed_types)) {
                    $error = "Hanya file gambar (JPG, PNG, GIF, WebP) yang diizinkan!";
                } else {
                    // Validate file size (max 5MB)
                    $max_size = 5 * 1024 * 1024; // 5MB
                    if ($file['size'] > $max_size) {
                        $error = "Ukuran file maksimal 5MB!";
                    } else {
                        // Generate unique filename
                        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = 'article_' . time() . '_' . uniqid() . '.' . $file_extension;
                        $file_path = $upload_dir . $filename;
                        
                        // Move uploaded file
                        if (move_uploaded_file($file['tmp_name'], $file_path)) {
                            $image_url = 'uploads/' . $filename;
                        } else {
                            $error = "Gagal mengupload gambar!";
                        }
                    }
                }
            }
            
            // If no upload error, proceed with database insert
            if (empty($error)) {
                $query = "INSERT INTO articles (title, content, excerpt, image_url, author, category, tags, status) 
                          VALUES (:title, :content, :excerpt, :image_url, :author, :category, :tags, :status)";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':excerpt', $excerpt);
                $stmt->bindParam(':image_url', $image_url);
                $stmt->bindParam(':author', $author);
                $stmt->bindParam(':category', $category);
                $stmt->bindParam(':tags', $tags);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $success = "Artikel berhasil ditambahkan!";
                    // Clear form
                    $_POST = array();
                } else {
                    $error = "Gagal menambahkan artikel!";
                }
            }
        } catch (PDOException $exception) {
            $error = "Database error: " . $exception->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel - SALMA Solutions</title>
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

        /* Form Container */
        .form-container {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
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

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
            position: relative;
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

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        #content {
            min-height: 300px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Category Suggestions */
        .category-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 150px;
            overflow-y: auto;
            z-index: 100;
            display: none;
            box-shadow: var(--shadow);
        }

        .suggestion-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        .suggestion-item:hover {
            background: #f8fafc;
            color: var(--secondary);
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestions-label {
            font-size: 0.8rem;
            color: var(--gray);
            padding: 8px 15px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
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

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }

        .character-count {
            text-align: right;
            color: var(--gray);
            font-size: 0.8rem;
            margin-top: 5px;
        }

        /* File Upload Styles */
        .file-upload {
            border: 2px dashed #e2e8f0;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .file-upload:hover {
            border-color: var(--secondary);
            background: #f1f5f9;
        }

        .file-upload.dragover {
            border-color: var(--secondary);
            background: #f0f9f4;
        }

        .file-upload-icon {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 15px;
        }

        .file-upload-text {
            margin-bottom: 15px;
        }

        .file-upload-text h4 {
            color: var(--dark);
            margin-bottom: 5px;
        }

        .file-upload-text p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .file-input {
            display: none;
        }

        .file-upload-btn {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            font-weight: 500;
        }

        .file-upload-btn:hover {
            background: #0f2a45;
        }

        .file-preview {
            margin-top: 20px;
            display: none;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .file-info {
            margin-top: 10px;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .remove-image {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .remove-image:hover {
            background: #dc2626;
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
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .content {
                padding: 20px;
            }
            
            .form-container {
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
            
            .form-container {
                padding: 15px;
            }
            
            .form-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
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
            <a href="add_article.php" class="menu-item active">
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
                <h1>Tambah Artikel Baru</h1>
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
            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fas fa-plus-circle"></i> Tambah Artikel Baru</h2>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Judul Artikel *</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                               required maxlength="255">
                        <div class="character-count">
                            <span id="title-count">0</span>/255 karakter
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Ringkasan Artikel *</label>
                        <textarea id="excerpt" name="excerpt" class="form-control" 
                                  required maxlength="500"><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                        <div class="character-count">
                            <span id="excerpt-count">0</span>/500 karakter
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="content">Konten Artikel *</label>
                        <textarea id="content" name="content" class="form-control" 
                                  required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Featured Image *</label>
                        <div class="file-upload" id="fileUploadArea">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">
                                <h4>Upload Gambar Artikel</h4>
                                <p>Drag & drop file atau klik untuk memilih</p>
                                <p class="file-requirements">Format: JPG, PNG, GIF, WebP (Maks. 5MB)</p>
                            </div>
                            <div class="file-upload-btn" onclick="document.getElementById('featured_image').click()">
                                <i class="fas fa-folder-open"></i> Pilih File
                            </div>
                            <input type="file" id="featured_image" name="featured_image" class="file-input" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        </div>
                        <div class="file-preview" id="filePreview">
                            <img id="previewImage" src="" alt="Preview">
                            <div class="file-info">
                                <span id="fileName"></span>
                                <span id="fileSize"></span>
                            </div>
                            <button type="button" class="remove-image" onclick="removeImage()">
                                <i class="fas fa-times"></i> Hapus Gambar
                            </button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="author">Penulis *</label>
                            <input type="text" id="author" name="author" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['author'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="category">Kategori *</label>
                            <input type="text" id="category" name="category" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>" 
                                   placeholder="Masukkan kategori artikel" required
                                   autocomplete="off">
                            <div class="category-suggestions" id="categorySuggestions">
                                <div class="suggestions-label">Kategori yang pernah digunakan:</div>
                                <?php foreach($existing_categories as $cat): ?>
                                    <div class="suggestion-item" data-category="<?php echo htmlspecialchars($cat); ?>">
                                        <?php echo htmlspecialchars($cat); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tags">Tags (pisahkan dengan koma)</label>
                            <input type="text" id="tags" name="tags" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>"
                                   placeholder="rekrutmen, sdm, bisnis, manajemen">
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="draft" <?php echo ($_POST['status'] ?? 'draft') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($_POST['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn btn-outline" onclick="resetForm()">
                            <i class="fas fa-redo"></i> Reset Form
                        </button>
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Simpan Artikel
                        </button>
                    </div>
                </form>
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

        // Character count
        const titleInput = document.getElementById('title');
        const excerptInput = document.getElementById('excerpt');
        const titleCount = document.getElementById('title-count');
        const excerptCount = document.getElementById('excerpt-count');

        function updateCharacterCount(input, countElement) {
            countElement.textContent = input.value.length;
        }

        titleInput.addEventListener('input', () => updateCharacterCount(titleInput, titleCount));
        excerptInput.addEventListener('input', () => updateCharacterCount(excerptInput, excerptCount));

        // Initialize counts
        updateCharacterCount(titleInput, titleCount);
        updateCharacterCount(excerptInput, excerptCount);

        // Category suggestions functionality
        const categoryInput = document.getElementById('category');
        const categorySuggestions = document.getElementById('categorySuggestions');
        const suggestionItems = document.querySelectorAll('.suggestion-item');

        categoryInput.addEventListener('focus', function() {
            if (suggestionItems.length > 0) {
                categorySuggestions.style.display = 'block';
            }
        });

        categoryInput.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            let hasVisibleSuggestions = false;
            
            suggestionItems.forEach(item => {
                const category = item.getAttribute('data-category').toLowerCase();
                if (category.includes(value)) {
                    item.style.display = 'block';
                    hasVisibleSuggestions = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide suggestions based on matches
            categorySuggestions.style.display = hasVisibleSuggestions ? 'block' : 'none';
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!categoryInput.contains(e.target) && !categorySuggestions.contains(e.target)) {
                categorySuggestions.style.display = 'none';
            }
        });

        // Handle suggestion selection
        suggestionItems.forEach(item => {
            item.addEventListener('click', function() {
                categoryInput.value = this.getAttribute('data-category');
                categorySuggestions.style.display = 'none';
            });
        });

        // File upload functionality
        const fileInput = document.getElementById('featured_image');
        const fileUploadArea = document.getElementById('fileUploadArea');
        const filePreview = document.getElementById('filePreview');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        // File selection handler
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                previewFile(file);
            }
        });

        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            fileUploadArea.classList.add('dragover');
        }

        function unhighlight() {
            fileUploadArea.classList.remove('dragover');
        }

        fileUploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const file = dt.files[0];
            fileInput.files = dt.files;
            previewFile(file);
        }

        function previewFile(file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Hanya file gambar (JPG, PNG, GIF, WebP) yang diizinkan!');
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file maksimal 5MB!');
                return;
            }

            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onloadend = function() {
                previewImage.src = reader.result;
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                filePreview.style.display = 'block';
                fileUploadArea.style.display = 'none';
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function removeImage() {
            fileInput.value = '';
            filePreview.style.display = 'none';
            fileUploadArea.style.display = 'block';
        }

        function resetForm() {
            removeImage();
            // Reset character counts
            updateCharacterCount(titleInput, titleCount);
            updateCharacterCount(excerptInput, excerptCount);
            // Hide category suggestions
            categorySuggestions.style.display = 'none';
        }

        // Auto hide alerts
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