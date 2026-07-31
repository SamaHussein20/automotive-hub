<?php

class Wishlist {
    private $conn;
    private $table = "Wishlist";
    private $userId;

    public function __construct($db, $userId) {
        $this->conn   = $db;
        $this->userId = $userId;
    }

    // Add product to wishlist
    public function addItem($productId) {
        // Check if already in wishlist
        if ($this->isInWishlist($productId)) return true;

        $query = "INSERT INTO " . $this->table . " (user_id, product_id)
                  VALUES (:user_id, :product_id)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":user_id"    => $this->userId,
            ":product_id" => $productId
        ]);
    }

    // Remove product from wishlist
    public function removeItem($productId) {
        $query = "DELETE FROM " . $this->table . "
                  WHERE user_id = :user_id AND product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":user_id"    => $this->userId,
            ":product_id" => $productId
        ]);
    }

    // Check if product is in wishlist
    public function isInWishlist($productId) {
        $query = "SELECT favorite_id FROM " . $this->table . "
                  WHERE user_id = :user_id AND product_id = :product_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([":user_id" => $this->userId, ":product_id" => $productId]);

        return $stmt->rowCount() > 0;
    }

    // Get all wishlist items
    public function getItems() {
        $query = "SELECT w.favorite_id, w.product_id,
                         p.name, p.brand, p.category, p.price, p.image, p.average_rating, p.stock_count
                  FROM " . $this->table . " w
                  INNER JOIN Product p ON w.product_id = p.product_id
                  WHERE w.user_id = :user_id
                  ORDER BY w.favorite_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
