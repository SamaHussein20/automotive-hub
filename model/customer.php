<?php

class Customer {
    private $conn;
    private $table = "Customer";

    private $customerId;
    private $userId;

    public function __construct($db, $userId = null) {
        $this->conn   = $db;
        $this->userId = $userId;
    }

    // Create customer record
    public function createCustomer($userId, $name, $deliveryAddress, $accountStatus = true) {
        $query = "INSERT INTO " . $this->table . "
                  (customer_id, name, delivery_address, account_status)
                  VALUES
                  (:customer_id, :name, :delivery_address, :account_status)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":customer_id"       => $userId,
            ":name"              => $name,
            ":delivery_address"  => $deliveryAddress,
            ":account_status"    => $accountStatus ? 1 : 0
        ]);
    }

    // Get customer by id
    public function getById($userId) {
        $query = "SELECT c.*, u.email, u.first_name, u.last_name, u.phone
                  FROM " . $this->table . " c
                  INNER JOIN Users u ON u.user_id = c.customer_id
                  WHERE c.customer_id = :customer_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update delivery address
    public function updateAddress($userId, $address) {
        $query = "UPDATE " . $this->table . "
                  SET delivery_address = :delivery_address
                  WHERE customer_id = :customer_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":delivery_address" => $address,
            ":customer_id"      => $userId
        ]);
    }

    // Toggle account status (admin)
    public function toggleStatus($userId) {
        $query = "UPDATE " . $this->table . "
                  SET account_status = NOT account_status
                  WHERE customer_id = :customer_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}

?>
