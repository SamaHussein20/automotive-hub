<?php

class Product {
    private $conn;
    private $table = "Product";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create product
    public function create($name, $description, $brand, $category, $price, $stockCount, $image = null, $adminId = null) {
        if ($price < 0 || $stockCount < 0) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . "
                  (name, description, brand, category, price, stock_count, image, admin_id)
                  VALUES
                  (:name, :description, :brand, :category, :price, :stock_count, :image, :admin_id)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":name"        => $name,
            ":description" => $description,
            ":brand"       => $brand,
            ":category"    => $category,
            ":price"       => $price,
            ":stock_count" => $stockCount,
            ":image"       => $image,
            ":admin_id"    => $adminId
        ]);
    }

    // Get product by id
    public function getById($productId) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE product_id = :product_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all products
    public function getAll() {
        $query = "SELECT *
                  FROM " . $this->table . "
                  ORDER BY product_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get products by category
    public function getByCategory($category) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE category = :category
                  ORDER BY product_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category", $category);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get products by brand
    public function getByBrand($brand) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE brand = :brand
                  ORDER BY product_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":brand", $brand);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get products by main category (e.g. 'Cars') — returns all sub_types
    public function getByMainCategory($mainCategory) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE category = :category
                  ORDER BY product_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category", $mainCategory);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search products
    public function search($keyword) {
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE name LIKE :keyword_name
                     OR description LIKE :keyword_desc
                     OR brand LIKE :keyword_brand
                     OR category LIKE :keyword_cat
                  ORDER BY product_id DESC";

        $stmt = $this->conn->prepare($query);
        $kw = "%" . $keyword . "%";
        $stmt->bindValue(":keyword_name",  $kw);
        $stmt->bindValue(":keyword_desc",  $kw);
        $stmt->bindValue(":keyword_brand", $kw);
        $stmt->bindValue(":keyword_cat",   $kw);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Filter products
    public function filter($brand = "", $category = "", $maxPrice = "", $minRating = "", $sort = "") {
        $where  = [];
        $params = [];

        if (!empty($brand)) {
            $where[] = "brand LIKE :brand";
            $params[":brand"] = "%" . $brand . "%";
        }

        if (!empty($category)) {
            $where[] = "category LIKE :category";
            $params[":category"] = "%" . $category . "%";
        }

        if (!empty($maxPrice)) {
            $where[] = "price <= :max_price";
            $params[":max_price"] = $maxPrice;
        }

        if (!empty($minRating)) {
            $where[] = "average_rating >= :min_rating";
            $params[":min_rating"] = $minRating;
        }

        $sql = "SELECT * FROM " . $this->table;

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        if ($sort == "price") {
            $sql .= " ORDER BY price ASC";
        } elseif ($sort == "name") {
            $sql .= " ORDER BY name ASC";
        } elseif ($sort == "rating") {
            $sql .= " ORDER BY average_rating DESC";
        } else {
            $sql .= " ORDER BY product_id DESC";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update product
    public function update($productId, $data) {
        $allowed = ["name", "description", "brand", "category", "price", "stock_count", "image"];
        $fields  = [];
        $params  = [":product_id" => $productId];

        if (isset($data['price']) && (float)$data['price'] < 0) return false;
        if (isset($data['stock_count']) && (int)$data['stock_count'] < 0) return false;

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) return false;

        $query = "UPDATE " . $this->table . "
                  SET " . implode(", ", $fields) . "
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }

    // Delete product
    public function delete($productId) {
        $query = "DELETE FROM " . $this->table . "
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Check stock
    public function isInStock($productId, $count = 1) {
        $query = "SELECT stock_count FROM " . $this->table . "
                  WHERE product_id = :product_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product && $product["stock_count"] >= $count;
    }

    // Decrease stock after order
    public function decreaseStock($productId, $quantity) {
        if ($quantity <= 0) return false;

        $query = "UPDATE " . $this->table . "
                  SET stock_count = stock_count - :quantity_remove
                  WHERE product_id = :product_id
                  AND stock_count >= :quantity_check";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":quantity_remove", (int)$quantity, PDO::PARAM_INT);
        $stmt->bindValue(":quantity_check",  (int)$quantity, PDO::PARAM_INT);
        $stmt->bindValue(":product_id",      (int)$productId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Increase stock if order cancelled
    public function increaseStock($productId, $quantity) {
        if ($quantity <= 0) return false;

        $query = "UPDATE " . $this->table . "
                  SET stock_count = stock_count + :quantity
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity",   $quantity,   PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Get distinct brands
    public function getBrands() {
        $query = "SELECT DISTINCT brand FROM " . $this->table . " ORDER BY brand ASC";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Get distinct categories
    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table . " ORDER BY category ASC";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Update rating
    public function updateRating($productId) {
        $query = "SELECT COALESCE(AVG(rating), 0) AS avg_rating, COUNT(*) AS rev_count
                  FROM Review WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $updateQuery = "UPDATE " . $this->table . "
                        SET average_rating = :average_rating,
                            review_count   = :review_count
                        WHERE product_id = :product_id";

        $updateStmt = $this->conn->prepare($updateQuery);

        return $updateStmt->execute([
            ":average_rating" => round($result["avg_rating"], 2),
            ":review_count"   => $result["rev_count"],
            ":product_id"     => $productId
        ]);
    }

    // Get product reviews
    public function getReviews($productId) {
        $query = "SELECT r.review_id, r.product_id, r.user_id, r.rating, r.comment,
                         u.first_name, u.last_name
                  FROM Review r
                  INNER JOIN Users u ON r.user_id = u.user_id
                  WHERE r.product_id = :product_id
                  ORDER BY r.review_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
