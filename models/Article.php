<?php
class Article {
    private $conn;
    private $table_name = "articles";

    public $id;
    public $title;
    public $slug;
    public $content;
    public $excerpt;
    public $image_url;
    public $author;
    public $category;
    public $tags;
    public $meta_title;
    public $meta_description;
    public $created_at;
    public $updated_at;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method untuk membuat slug
    public function createSlug($title) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Cek jika slug sudah ada, tambahkan angka
        $original_slug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    // Cek apakah slug sudah ada
    private function slugExists($slug) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE slug = :slug";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Method untuk mendapatkan semua artikel dengan SEO improvements
    public function getAllArticles($limit = 10, $offset = 0) {
        $query = "SELECT *, 
                  CASE 
                    WHEN meta_title IS NOT NULL AND meta_title != '' THEN meta_title
                    ELSE title
                  END as seo_title,
                  CASE 
                    WHEN meta_description IS NOT NULL AND meta_description != '' THEN meta_description
                    ELSE excerpt
                  END as seo_description
                  FROM " . $this->table_name . " 
                  WHERE status = 'published' 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Method untuk mendapatkan artikel berdasarkan slug (SEO-friendly)
    public function getArticleBySlug($slug) {
        $query = "SELECT *, 
                  CASE 
                    WHEN meta_title IS NOT NULL AND meta_title != '' THEN meta_title
                    ELSE title
                  END as seo_title,
                  CASE 
                    WHEN meta_description IS NOT NULL AND meta_description != '' THEN meta_description
                    ELSE excerpt
                  END as seo_description
                  FROM " . $this->table_name . " 
                  WHERE slug = :slug AND status = 'published'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        return $stmt;
    }

    // Method untuk pencarian artikel
    public function searchArticles($keyword, $limit = 10, $offset = 0) {
        $query = "SELECT *, 
                  CASE 
                    WHEN meta_title IS NOT NULL AND meta_title != '' THEN meta_title
                    ELSE title
                  END as seo_title,
                  CASE 
                    WHEN meta_description IS NOT NULL AND meta_description != '' THEN meta_description
                    ELSE excerpt
                  END as seo_description
                  FROM " . $this->table_name . " 
                  WHERE status = 'published' 
                  AND (title LIKE :keyword 
                       OR content LIKE :keyword 
                       OR excerpt LIKE :keyword 
                       OR tags LIKE :keyword)
                  ORDER BY 
                    CASE 
                      WHEN title LIKE :keyword THEN 1
                      WHEN excerpt LIKE :keyword THEN 2
                      WHEN content LIKE :keyword THEN 3
                      ELSE 4
                    END,
                    created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $search_keyword = "%$keyword%";
        $stmt->bindParam(':keyword', $search_keyword);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Method untuk menghitung hasil pencarian
    public function countSearchResults($keyword) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE status = 'published' 
                  AND (title LIKE :keyword 
                       OR content LIKE :keyword 
                       OR excerpt LIKE :keyword 
                       OR tags LIKE :keyword)";
        
        $stmt = $this->conn->prepare($query);
        $search_keyword = "%$keyword%";
        $stmt->bindParam(':keyword', $search_keyword);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    // Method untuk mendapatkan artikel berdasarkan tag
    public function getArticlesByTag($tag, $limit = 10, $offset = 0) {
        $query = "SELECT *, 
                  CASE 
                    WHEN meta_title IS NOT NULL AND meta_title != '' THEN meta_title
                    ELSE title
                  END as seo_title,
                  CASE 
                    WHEN meta_description IS NOT NULL AND meta_description != '' THEN meta_description
                    ELSE excerpt
                  END as seo_description
                  FROM " . $this->table_name . " 
                  WHERE status = 'published' 
                  AND tags LIKE :tag
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $search_tag = "%$tag%";
        $stmt->bindParam(':tag', $search_tag);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Method untuk menghitung artikel berdasarkan tag
    public function countArticlesByTag($tag) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE status = 'published' 
                  AND tags LIKE :tag";
        
        $stmt = $this->conn->prepare($query);
        $search_tag = "%$tag%";
        $stmt->bindParam(':tag', $search_tag);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    // Method untuk mendapatkan semua tag yang digunakan
    public function getAllTags() {
        $query = "SELECT tags FROM " . $this->table_name . " 
                  WHERE status = 'published' AND tags IS NOT NULL AND tags != ''";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $all_tags = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['tags'])) {
                $article_tags = explode(',', $row['tags']);
                foreach ($article_tags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag) && !in_array($tag, $all_tags)) {
                        $all_tags[] = $tag;
                    }
                }
            }
        }
        
        sort($all_tags);
        return $all_tags;
    }

    // Method untuk mendapatkan tag populer (dengan jumlah artikel)
    public function getPopularTags($limit = 10) {
        $all_tags = $this->getAllTags();
        $tag_counts = [];
        
        foreach ($all_tags as $tag) {
            $count = $this->countArticlesByTag($tag);
            $tag_counts[$tag] = $count;
        }
        
        arsort($tag_counts);
        return array_slice($tag_counts, 0, $limit, true);
    }

    // Method untuk mendapatkan artikel terbaru
    public function getRecentArticles($limit = 5) {
        $query = "SELECT id, title, slug, image_url, created_at FROM " . $this->table_name . " 
                  WHERE status = 'published' 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Method untuk menghitung total artikel
    public function getTotalArticles() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'published'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    // Update method untuk create article dengan SEO fields
    public function createArticle($title, $content, $excerpt, $image_url, $author, $category, $tags, $status, $meta_title = null, $meta_description = null) {
        $slug = $this->createSlug($title);
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, slug, content, excerpt, image_url, author, category, tags, meta_title, meta_description, status, created_at, updated_at) 
                  VALUES (:title, :slug, :content, :excerpt, :image_url, :author, :category, :tags, :meta_title, :meta_description, :status, NOW(), NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':excerpt', $excerpt);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':tags', $tags);
        $stmt->bindParam(':meta_title', $meta_title);
        $stmt->bindParam(':meta_description', $meta_description);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }

    // Method untuk mendapatkan artikel berdasarkan ID (untuk kompatibilitas)
    public function getArticleById($id) {
        $query = "SELECT *, 
                  CASE 
                    WHEN meta_title IS NOT NULL AND meta_title != '' THEN meta_title
                    ELSE title
                  END as seo_title,
                  CASE 
                    WHEN meta_description IS NOT NULL AND meta_description != '' THEN meta_description
                    ELSE excerpt
                  END as seo_description
                  FROM " . $this->table_name . " 
                  WHERE id = :id AND status = 'published'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt;
    }

    // Method untuk mendapatkan artikel berdasarkan ID (tanpa filter status - untuk admin)
    public function getArticleByIdAdmin($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt;
    }

    // Get draft articles only
    public function getDraftArticles() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'draft' ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get published articles only
    public function getPublishedArticles() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'published' ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Method untuk update artikel
    public function updateArticle($id, $title, $content, $excerpt, $image_url, $author, $category, $tags, $status, $meta_title = null, $meta_description = null) {
        // Dapatkan artikel saat ini untuk memeriksa apakah judul berubah
        $current_article = $this->getArticleByIdAdmin($id);
        if ($current_article_row = $current_article->fetch(PDO::FETCH_ASSOC)) {
            if ($current_article_row['title'] != $title) {
                $slug = $this->createSlug($title);
            } else {
                $slug = $current_article_row['slug'];
            }
        } else {
            $slug = $this->createSlug($title);
        }
        
        $query = "UPDATE " . $this->table_name . " 
                  SET title = :title, 
                      slug = :slug,
                      content = :content, 
                      excerpt = :excerpt, 
                      image_url = :image_url, 
                      author = :author, 
                      category = :category, 
                      tags = :tags, 
                      meta_title = :meta_title,
                      meta_description = :meta_description,
                      status = :status,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':excerpt', $excerpt);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':tags', $tags);
        $stmt->bindParam(':meta_title', $meta_title);
        $stmt->bindParam(':meta_description', $meta_description);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Method untuk menghapus artikel
    public function deleteArticle($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Method untuk publish artikel
    public function publishArticle($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'published', 
                      updated_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Method untuk mendapatkan semua artikel (untuk admin - tanpa filter status)
    public function getAllArticlesAdmin($limit = 1000, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
// models/Article.php

public function getArticlesByCategory($category, $limit = 10, $offset = 0) {
    $query = "SELECT * FROM articles 
              WHERE category = :category AND status = 'published' 
              ORDER BY created_at DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt;
}

public function getTotalArticlesByCategory($category) {
    $query = "SELECT COUNT(*) as total FROM articles 
              WHERE category = :category AND status = 'published'";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':category', $category);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}
    // Method untuk menghitung total semua artikel (untuk admin)
    public function getTotalArticlesAdmin() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
}
?>