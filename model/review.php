<?php

class Review {
    private $conn;
    private $table = "Review";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add review
    public function create($productId, $userId, $rating, $comment) {
        if ($rating < 1 || $rating > 5) return false;

        // Check if user already reviewed this product
        if ($this->hasReviewed($productId, $userId)) return false;

        $query = "INSERT INTO " . $this->table . "
                  (product_id, user_id, rating, comment)
                  VALUES
                  (:product_id, :user_id, :rating, :comment)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":product_id" => $productId,
            ":user_id"    => $userId,
            ":rating"     => $rating,
            ":comment"    => trim($comment)
        ]);
    }

    // Check if user already reviewed product
    public function hasReviewed($productId, $userId) {
        $query = "SELECT review_id FROM " . $this->table . "
                  WHERE product_id = :product_id AND user_id = :user_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([":product_id" => $productId, ":user_id" => $userId]);

        return $stmt->rowCount() > 0;
    }

    // Get reviews for a product
    public function getByProduct($productId) {
        $query = "SELECT r.review_id, r.rating, r.comment,
                         u.first_name, u.last_name
                  FROM " . $this->table . " r
                  INNER JOIN Users u ON r.user_id = u.user_id
                  WHERE r.product_id = :product_id
                  ORDER BY r.review_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete review
    public function delete($reviewId) {
        $query = "DELETE FROM " . $this->table . " WHERE review_id = :review_id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(":review_id", $reviewId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}

?>
