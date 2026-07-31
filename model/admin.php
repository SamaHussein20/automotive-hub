<?php

class Admin {
    private $conn;
    private $table = "Admin";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Check if user is admin
    public function isAdmin($userId) {
        $query = "SELECT admin_id FROM " . $this->table . "
                  WHERE admin_id = :admin_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":admin_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}

?>
