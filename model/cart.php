<?php

class Cart {
    private $conn;
    private $cartId;
    private $userId;
    private $sessionId;

    public function __construct($db, $userId = null, $sessionId = null) {
        $this->conn      = $db;
        $this->userId    = $userId;
        $this->sessionId = $sessionId;
        $this->cartId    = null;

        // Only load existing cart — do NOT create one yet.
        // A new cart row is created lazily in getOrCreateCart() when actually needed.
        if ($userId !== null || $sessionId !== null) {
            $this->cartId = $this->findExistingCart();
        }
    }

    private function ensureCustomerRecord() {
        if ($this->userId === null || $this->userId === '') return true;

        $checkStmt = $this->conn->prepare(
            "SELECT customer_id FROM Customer WHERE customer_id = :uid LIMIT 1"
        );
        $checkStmt->bindParam(":uid", $this->userId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->fetch()) return true;

        $userStmt = $this->conn->prepare(
            "SELECT first_name, last_name FROM Users WHERE user_id = :uid LIMIT 1"
        );
        $userStmt->bindParam(":uid", $this->userId, PDO::PARAM_INT);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;

        $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        if ($name === '') $name = 'Customer';

        $insertStmt = $this->conn->prepare(
            "INSERT INTO Customer (customer_id, name, delivery_address, account_status)
             VALUES (:cid, :name, '', TRUE)"
        );
        $insertStmt->bindParam(":cid",  $this->userId, PDO::PARAM_INT);
        $insertStmt->bindParam(":name", $name);

        return $insertStmt->execute();
    }

    private function findExistingCart() {
        if ($this->userId !== null && $this->userId !== '') {
            $stmt = $this->conn->prepare(
                "SELECT cart_id FROM Cart WHERE user_id = :user_id LIMIT 1"
            );
            $stmt->bindParam(":user_id", $this->userId, PDO::PARAM_INT);
        } elseif ($this->sessionId !== null && $this->sessionId !== '') {
            $stmt = $this->conn->prepare(
                "SELECT cart_id FROM Cart WHERE session_id = :session_id LIMIT 1"
            );
            $stmt->bindParam(":session_id", $this->sessionId);
        } else {
            return null;
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['cart_id'] : null;
    }

    private function getOrCreateCart() {
        // Return existing cart_id if we already have one
        if ($this->cartId !== null) {
            return $this->cartId;
        }

        if ($this->userId !== null && $this->userId !== '') {
            $this->ensureCustomerRecord();
        }

        // Try to find again (race-condition safety)
        $existing = $this->findExistingCart();
        if ($existing !== null) {
            $this->cartId = $existing;
            return $this->cartId;
        }

        // Create a new cart row
        $insertStmt = $this->conn->prepare(
            "INSERT INTO Cart (user_id, session_id) VALUES (:user_id, :session_id)"
        );
        $insertStmt->bindParam(":user_id",    $this->userId);
        $insertStmt->bindParam(":session_id", $this->sessionId);
        $insertStmt->execute();

        $this->cartId = $this->conn->lastInsertId();
        return $this->cartId;
    }

    public function getCartId() {
        return $this->cartId;
    }

    public function addItem($productId, $quantity = 1) {
        $productId = (int)$productId;
        $quantity  = (int)$quantity;

        if ($quantity <= 0) return false;

        // Ensure cart exists (creates one if needed)
        $this->getOrCreateCart();
        if ($this->cartId === null) return false;

        $productStmt = $this->conn->prepare(
            "SELECT product_id, price, stock_count FROM Product
             WHERE product_id = :product_id LIMIT 1"
        );
        $productStmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $productStmt->execute();

        if ($productStmt->rowCount() == 0) return false;

        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if ($product['stock_count'] < $quantity) return false;

        $checkStmt = $this->conn->prepare(
            "SELECT cart_item_id, quantity FROM Cart_Item
             WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1"
        );
        $checkStmt->bindParam(":cart_id",    $this->cartId, PDO::PARAM_INT);
        $checkStmt->bindParam(":product_id", $productId,   PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $cartItem    = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = $cartItem['quantity'] + $quantity;

            if ($product['stock_count'] < $newQuantity) return false;

            return $this->updateQuantity($cartItem['cart_item_id'], $newQuantity);
        }

        $insertStmt = $this->conn->prepare(
            "INSERT INTO Cart_Item (cart_id, product_id, quantity, price)
             VALUES (:cart_id, :product_id, :quantity, :price)"
        );
        $insertStmt->bindParam(":cart_id",    $this->cartId,        PDO::PARAM_INT);
        $insertStmt->bindParam(":product_id", $productId,           PDO::PARAM_INT);
        $insertStmt->bindParam(":quantity",   $quantity,            PDO::PARAM_INT);
        $insertStmt->bindParam(":price",      $product['price']);

        return $insertStmt->execute();
    }

    public function removeItem($cartItemId) {
        $stmt = $this->conn->prepare(
            "DELETE FROM Cart_Item WHERE cart_item_id = :cart_item_id AND cart_id = :cart_id"
        );
        $stmt->bindParam(":cart_item_id", $cartItemId, PDO::PARAM_INT);
        $stmt->bindParam(":cart_id",      $this->cartId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateQuantity($cartItemId, $quantity) {
        $cartItemId = (int)$cartItemId;
        $quantity   = (int)$quantity;

        if ($quantity <= 0) return $this->removeItem($cartItemId);

        $checkStmt = $this->conn->prepare(
            "SELECT ci.product_id, p.stock_count
             FROM Cart_Item ci
             INNER JOIN Product p ON ci.product_id = p.product_id
             WHERE ci.cart_item_id = :cart_item_id AND ci.cart_id = :cart_id LIMIT 1"
        );
        $checkStmt->bindParam(":cart_item_id", $cartItemId,    PDO::PARAM_INT);
        $checkStmt->bindParam(":cart_id",      $this->cartId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() == 0) return false;

        $item = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($item['stock_count'] < $quantity) return false;

        $stmt = $this->conn->prepare(
            "UPDATE Cart_Item SET quantity = :quantity
             WHERE cart_item_id = :cart_item_id AND cart_id = :cart_id"
        );
        $stmt->bindParam(":quantity",     $quantity,    PDO::PARAM_INT);
        $stmt->bindParam(":cart_item_id", $cartItemId, PDO::PARAM_INT);
        $stmt->bindParam(":cart_id",      $this->cartId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getItems() {
        if ($this->cartId === null) return [];

        $stmt = $this->conn->prepare(
            "SELECT ci.cart_item_id, ci.cart_id, ci.product_id,
                    p.name, p.brand, p.category, p.image, p.stock_count,
                    ci.quantity, ci.price, ci.quantity * ci.price AS total
             FROM Cart_Item ci
             INNER JOIN Product p ON ci.product_id = p.product_id
             WHERE ci.cart_id = :cart_id"
        );
        $stmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotal() {
        if ($this->cartId === null) return 0;

        $stmt = $this->conn->prepare(
            "SELECT SUM(quantity * price) AS total FROM Cart_Item WHERE cart_id = :cart_id"
        );
        $stmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result && $result['total'] !== null ? (float)$result['total'] : 0;
    }

    public function applyCoupon($couponCode) {
        $stmt = $this->conn->prepare(
            "SELECT coupon_id, code, discount, expiry_date
             FROM Coupon
             WHERE code = :code AND expiry_date >= CURDATE() LIMIT 1"
        );
        $stmt->bindParam(":code", $couponCode);
        $stmt->execute();

        if ($stmt->rowCount() == 0) return false;

        $coupon         = $stmt->fetch(PDO::FETCH_ASSOC);
        $subtotal       = $this->getTotal();
        $discountAmount = ($subtotal * $coupon['discount']) / 100;

        return [
            "coupon_id"        => $coupon['coupon_id'],
            "code"             => $coupon['code'],
            "subtotal"         => $subtotal,
            "discount_percent" => $coupon['discount'],
            "discount_amount"  => $discountAmount,
            "final_total"      => $subtotal - $discountAmount
        ];
    }

    public function clear() {
        $stmt = $this->conn->prepare(
            "DELETE FROM Cart_Item WHERE cart_id = :cart_id"
        );
        $stmt->bindParam(":cart_id", $this->cartId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Merge all items from a guest (session) cart into this user cart.
     * Called right after login so nothing is lost.
     */
    public function mergeGuestCart($guestSessionId) {
        // Find guest cart
        $stmt = $this->conn->prepare(
            "SELECT cart_id FROM Cart WHERE session_id = :sid AND user_id IS NULL LIMIT 1"
        );
        $stmt->bindParam(":sid", $guestSessionId);
        $stmt->execute();
        $guestCart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$guestCart) return; // guest had no cart — nothing to merge

        $guestCartId = $guestCart['cart_id'];

        // Get all guest items
        $items = $this->conn->prepare(
            "SELECT product_id, quantity, price FROM Cart_Item WHERE cart_id = :cid"
        );
        $items->bindParam(":cid", $guestCartId, PDO::PARAM_INT);
        $items->execute();

        foreach ($items->fetchAll(PDO::FETCH_ASSOC) as $item) {
            // If product already in user cart → add quantities
            $check = $this->conn->prepare(
                "SELECT cart_item_id, quantity FROM Cart_Item
                 WHERE cart_id = :cid AND product_id = :pid LIMIT 1"
            );
            $check->bindParam(":cid", $this->cartId, PDO::PARAM_INT);
            $check->bindParam(":pid", $item['product_id'], PDO::PARAM_INT);
            $check->execute();

            if ($existing = $check->fetch(PDO::FETCH_ASSOC)) {
                $newQty = $existing['quantity'] + $item['quantity'];
                $upd = $this->conn->prepare(
                    "UPDATE Cart_Item SET quantity = :qty
                     WHERE cart_item_id = :id"
                );
                $upd->bindParam(":qty", $newQty, PDO::PARAM_INT);
                $upd->bindParam(":id",  $existing['cart_item_id'], PDO::PARAM_INT);
                $upd->execute();
            } else {
                $ins = $this->conn->prepare(
                    "INSERT INTO Cart_Item (cart_id, product_id, quantity, price)
                     VALUES (:cid, :pid, :qty, :price)"
                );
                $ins->bindParam(":cid",   $this->cartId,       PDO::PARAM_INT);
                $ins->bindParam(":pid",   $item['product_id'], PDO::PARAM_INT);
                $ins->bindParam(":qty",   $item['quantity'],   PDO::PARAM_INT);
                $ins->bindParam(":price", $item['price']);
                $ins->execute();
            }
        }

        // Delete the guest cart (Cart_Item rows cascade automatically)
        $del = $this->conn->prepare("DELETE FROM Cart WHERE cart_id = :cid");
        $del->bindParam(":cid", $guestCartId, PDO::PARAM_INT);
        $del->execute();
    }
}

?>
