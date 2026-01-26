<?php
class AdminUser {
    private $conn;
    private $table_name = "admins";

    public $id;
    public $username;
    public $password_hash;
    public $email;
    public $full_name;
    public $role;
    public $is_active;
    public $last_login;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Login admin
    public function login($username, $password) {
        $query = "SELECT id, username, password_hash, email, full_name, role, is_active 
                  FROM " . $this->table_name . " 
                  WHERE username = :username AND is_active = 1 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($password, $row['password_hash'])) {
                // Update last login
                $this->updateLastLogin($row['id']);
                
                return $row;
            }
        }
        return false;
    }

    // Update last login time
    private function updateLastLogin($admin_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET last_login = CURRENT_TIMESTAMP 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $admin_id);
        $stmt->execute();
    }

    // Get admin by ID
    public function getAdminById($id) {
        $query = "SELECT id, username, email, full_name, role, is_active, last_login, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if username exists
    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Update admin profile
    public function updateProfile($id, $full_name, $email, $username) {
        $query = "UPDATE " . $this->table_name . " 
                  SET full_name = :full_name, 
                      email = :email, 
                      username = :username
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":full_name", $full_name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Change password
    public function changePassword($id, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $query = "UPDATE " . $this->table_name . " 
                  SET password_hash = :password_hash 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password_hash", $hashed_password);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Create new admin
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, password_hash=:password_hash, email=:email, 
                      full_name=:full_name, role=:role, is_active=:is_active";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Bind parameters
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":is_active", $this->is_active);

        // Hash password
        $password_hash = password_hash($this->password_hash, PASSWORD_DEFAULT);
        $stmt->bindParam(":password_hash", $password_hash);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>