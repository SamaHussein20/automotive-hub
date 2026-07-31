<?php

class Order {
    private $conn;
    private $table = "Orders";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create order
    public function create($userId, $totalAmount, $discount = 0, $shippingCost = 0, $couponId = null) {
        $query = "INSERT INTO " . $this->table . "
                  (order_date, user_id, total_amount, status, discount, shipping_cost, coupon_id)
                  VALUES
                  (CURDATE(), :user_id, :total_amount, 'Processing', :discount, :shipping_cost, :coupon_id)";

        $stmt = $this->conn->prepare($query);

        $success = $stmt->execute([
            ":user_id"       => $userId,
            ":total_amount"  => $totalAmount,
            ":discount"      => $discount,
            ":shipping_cost" => $shippingCost,
            ":coupon_id"     => $couponId
        ]);

        return $success ? $this->conn->lastInsertId() : false;
    }

    // Get order by id
    public function getById($orderId) {
        $query = "SELECT o.*, u.first_name, u.last_name, u.email
                  FROM " . $this->table . " o
                  INNER JOIN Users u ON o.user_id = u.user_id
                  WHERE o.order_id = :order_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get user orders
    public function getByUser($userId) {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE user_id = :user_id
                  ORDER BY order_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all orders (admin)
    public function getAll() {
        $query = "SELECT o.*, u.first_name, u.last_name, u.email
                  FROM " . $this->table . " o
                  INNER JOIN Users u ON o.user_id = u.user_id
                  ORDER BY o.order_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update order status
    public function updateStatus($orderId, $status) {
        $allowed = ['Processing', 'Shipped', 'Delivered', 'Cancelled'];

        if (!in_array($status, $allowed)) return false;

        $query = "UPDATE " . $this->table . "
                  SET status = :status WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([":status" => $status, ":order_id" => $orderId]);
    }

    // Get order items
    public function getItems($orderId) {
        $query = "SELECT oi.*, p.name, p.image, p.brand
                  FROM Order_Item oi
                  INNER JOIN Product p ON oi.product_id = p.product_id
                  WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add item to order
    public function addItem($orderId, $productId, $quantity, $unitPrice) {
        $query = "INSERT INTO Order_Item (order_id, product_id, quantity, unit_price, total)
                  VALUES (:order_id, :product_id, :quantity, :unit_price, :total)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":order_id"   => $orderId,
            ":product_id" => $productId,
            ":quantity"   => $quantity,
            ":unit_price" => $unitPrice,
            ":total"      => $quantity * $unitPrice
        ]);
    }
}

?>
