<?php

session_start();

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/admin.php";
require_once __DIR__ . "/../model/product.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

$adminModel = new Admin($db);
if (!isset($_SESSION['user_id']) || !$adminModel->isAdmin($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$productModel = new Product($db);
$products     = $productModel->getAll();

$msg = $_SESSION['admin_msg'] ?? '';
unset($_SESSION['admin_msg']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-container">

    <aside class="admin-sidebar">
        <div class="logo">⚙ Admin Panel</div>
        <nav class="admin-nav">
            <a href="admin.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="admin-products.php" class="active"><i class="fa-solid fa-car"></i> Products</a>
            <a href="admin-orders.php"><i class="fa-solid fa-box"></i> Orders</a>
            <a href="admin-users.php"><i class="fa-solid fa-users"></i> Users</a>
            <a href="index.php"><i class="fa-solid fa-house"></i> Back to Site</a>
            <form method="POST" action="../controller/AuthController.php" style="padding:12px 20px;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" style="background:transparent;border:none;color:#94a3b8;cursor:pointer;font-size:14px;text-align:left;width:100%;">
                    <i class="fa-solid fa-right-from-bracket" style="width:20px;margin-right:8px;"></i> Logout
                </button>
            </form>
        </nav>
    </aside>

    <main class="admin-content">
        <h1 class="admin-title">Manage <span>Products</span></h1>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="table-wrapper">
            <div class="table-header">
                <h3>All Products (<?= count($products) ?>)</h3>
                <button class="action-btn add" onclick="openAddModal()">+ Add Product</button>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="">
                            <?php else: ?>
                                <div style="width:55px;height:45px;background:#1e293b;border-radius:6px;display:flex;align-items:center;justify-content:center;">🚗</div>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:200px;"><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['brand']) ?></td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td><?= $p['stock_count'] ?></td>
                        <td>
                            <button class="action-btn edit"
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)">
                                Edit
                            </button>
                            <form method="POST" action="../controller/AdminController.php" style="display:inline;"
                                  onsubmit="return confirm('Delete this product?')">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                <button type="submit" class="action-btn delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                    <tr><td colspan="7" style="text-align:center; color:#64748b; padding:30px;">No products yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add Product Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        <div class="modal-title">Add New Product</div>
        <form method="POST" action="../controller/AdminController.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_product">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"></textarea>
            </div>
            <div class="form-group">
                <label>Brand</label>
                <input type="text" name="brand">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="Cars">Cars</option>
                    <option value="Motorcycles">Motorcycles</option>
                    <option value="Electric">Electric</option>
                    <option value="Spare Parts">Spare Parts</option>
                </select>
            </div>
            <div class="form-group">
                <label>Price *</label>
                <input type="number" name="price" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Stock Count *</label>
                <input type="number" name="stock_count" min="0" required>
            </div>
            <div class="form-group">
                <label>Image URL (or upload below)</label>
                <input type="text" name="image_url" placeholder="https://...">
            </div>
            <div class="form-group">
                <label>Upload Image</label>
                <input type="file" name="image" accept="image/*" style="background:transparent; border:none; padding:0; color:#94a3b8;">
            </div>
            <button type="submit" class="modal-btn">Add Product</button>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        <div class="modal-title">Edit Product</div>
        <form method="POST" action="../controller/AdminController.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_product">
            <input type="hidden" name="product_id" id="edit_product_id">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description"></textarea>
            </div>
            <div class="form-group">
                <label>Brand</label>
                <input type="text" name="brand" id="edit_brand">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" id="edit_category">
                    <option value="Cars">Cars</option>
                    <option value="Motorcycles">Motorcycles</option>
                    <option value="Electric">Electric</option>
                    <option value="Spare Parts">Spare Parts</option>
                </select>
            </div>
            <div class="form-group">
                <label>Price *</label>
                <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Stock Count *</label>
                <input type="number" name="stock_count" id="edit_stock" min="0" required>
            </div>
            <div class="form-group">
                <label>Image URL (leave blank to keep current)</label>
                <input type="text" name="image_url" id="edit_image_url" placeholder="https://...">
            </div>
            <div class="form-group">
                <label>Upload New Image</label>
                <input type="file" name="image" accept="image/*" style="background:transparent; border:none; padding:0; color:#94a3b8;">
            </div>
            <button type="submit" class="modal-btn">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.add('active');
}
function openEditModal(p) {
    document.getElementById('edit_product_id').value = p.product_id;
    document.getElementById('edit_name').value        = p.name;
    document.getElementById('edit_description').value = p.description || '';
    document.getElementById('edit_brand').value       = p.brand || '';
    document.getElementById('edit_category').value    = p.category || 'Cars';
    document.getElementById('edit_price').value       = p.price;
    document.getElementById('edit_stock').value       = p.stock_count;
    document.getElementById('edit_image_url').value   = p.image || '';
    document.getElementById('editModal').classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});
</script>
<script src="script.js"></script>
</body>
</html>
