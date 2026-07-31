<?php

class User {
    protected $conn;
    private $table = "Users";

    protected $userId;
    private $email;
    private $password;
    private $firstName;
    private $lastName;
    private $phone;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register new user
    public function registration($email, $password, $firstName, $lastName, $phone) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (strlen($password) < 6) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . "
                  (email, password, first_name, last_name, phone, registration_date)
                  VALUES
                  (:email, :password, :first_name, :last_name, :phone, CURDATE())";

        $stmt = $this->conn->prepare($query);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            return $stmt->execute([
                ":email" => $email,
                ":password" => $hashedPassword,
                ":first_name" => trim($firstName),
                ":last_name" => trim($lastName),
                ":phone" => trim($phone)
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Login user
    public function login($email, $password) {
        $query = "SELECT 
                    u.*,
                    c.account_status
                  FROM " . $this->table . " u
                  LEFT JOIN Customer c ON c.customer_id = u.user_id
                  WHERE u.email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Blocked customers cannot login
            if ($user['account_status'] !== null && (int)$user['account_status'] === 0) {
                return false;
            }

            if (password_verify($password, $user['password'])) {
                $this->userId = $user['user_id'];
                $this->email  = $user['email'];
                $this->firstName = $user['first_name'];
                $this->lastName  = $user['last_name'];

                return $user;
            }
        }

        return false;
    }

    // Get user by id
    public function getUserById($userId) {
        $query = "SELECT user_id, email, first_name, last_name, phone, registration_date
                  FROM " . $this->table . "
                  WHERE user_id = :user_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT user_id FROM " . $this->table . "
                  WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Update profile
    public function updateProfile($userId, $firstName, $lastName, $phone) {
        $query = "UPDATE " . $this->table . "
                  SET first_name = :first_name,
                      last_name  = :last_name,
                      phone      = :phone
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":first_name" => trim($firstName),
            ":last_name"  => trim($lastName),
            ":phone"      => trim($phone),
            ":user_id"    => $userId
        ]);
    }

    // Change password
    public function changePassword($userId, $newPassword) {
        $query = "UPDATE " . $this->table . "
                  SET password = :password
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":password" => password_hash($newPassword, PASSWORD_DEFAULT),
            ":user_id"  => $userId
        ]);
    }

    // Get all users (admin)
    public function getAll() {
        $query = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.phone, u.registration_date,
                         c.account_status
                  FROM " . $this->table . " u
                  LEFT JOIN Customer c ON c.customer_id = u.user_id
                  ORDER BY u.user_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
