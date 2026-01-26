<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password_hash;
    public $email;
    public $full_name;
    public $phone;
    public $company;
    public $position;
    public $user_type;
    public $is_active;
    public $last_login;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Login user
    public function login($username, $password) {
        $query = "SELECT id, username, password_hash, email, full_name, phone, company, position, user_type, is_active 
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
    private function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET last_login = CURRENT_TIMESTAMP 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();
    }

    // Get user by ID
    public function getUserById($id) {
        $query = "SELECT id, username, email, full_name, phone, company, position, user_type, is_active, last_login, created_at 
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

    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, password_hash=:password_hash, email=:email, 
                      full_name=:full_name, phone=:phone, company=:company, position=:position, user_type=:user_type";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->company = htmlspecialchars(strip_tags($this->company));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->user_type = htmlspecialchars(strip_tags($this->user_type));

        // Bind parameters
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":company", $this->company);
        $stmt->bindParam(":position", $this->position);
        $stmt->bindParam(":user_type", $this->user_type);

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