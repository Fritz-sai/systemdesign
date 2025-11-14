<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_product') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        // Check if product exists before deleting
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($exists) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: /systemFinals/admin/products.php");
    exit;
}

$result = $conn->query("SELECT id, name, description, price, image, category, stock FROM products ORDER BY created_at DESC");
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
if ($result) $result->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Admin | Products</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<style>
		body, .dashboard-bg {
			background: #10161d !important;
			color: #d9fff8;
			font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
			min-height: 100vh;
			margin: 0;
		}
		.admin-main.dashboard-bg {
			max-width: 1200px;
			margin: 2rem auto;
			padding: 24px 12px;
			border-radius: 20px;
		}
		.section-header.neon-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 28px;
		}
		.neon-header h2 {
			color: #21fc89;
			font-size: 2.35rem;
			font-weight: 700;
			margin-bottom: 0;
			text-shadow: 0 0 12px #21fc89;
			letter-spacing: 1px;
		}
		.actions .btn {
			font-weight: 500;
			padding: 11px 18px;
			border-radius: 8px;
			font-size: 1rem;
			cursor: pointer;
			box-shadow: 0 1px 4px #000;
			transition: box-shadow 0.2s, background 0.18s, color 0.18s;
			border: none;
		}
		.btn.neon-btn {
			background: linear-gradient(90deg,#21fc89 20%,#13c37a 100%);
			color: #011;
			text-shadow: 0 0 4px #21fc8955;
			border: none;
		}
		.btn.neon-btn:hover {
			background: linear-gradient(90deg,#19e088 0%,#00a769 100%);
			color: #fff;
			box-shadow: 0 0 16px #21fc89cc;
		}
		.btn.neon-outline {
			border: 2px solid #30e688;
			background: transparent;
			color: #30e688;
			text-shadow: 0 0 4px #21fc8955;
		}
		.btn.neon-outline:hover {
			background: #164f2766;
			box-shadow: 0 0 8px #21fc89bb;
			color: #d9ffed;
		}
		.btn.neon-danger {
			background: linear-gradient(90deg, #fc2121 0%, #fc2189 100%);
			color: #fff;
			text-shadow: 0 0 4px #fc218966;
			border: none;
		}
		.btn.neon-danger:hover {
			background: linear-gradient(90deg, #e02f59 0%, #ff2189 100%);
			box-shadow: 0 0 16px #fc218999;
		}
		.neon-card {
			background: rgba(20,36,28,0.9);
			border-radius: 17px;
			box-shadow: 0 0 22px #22ff8f22, 0 4px 20px #000;
			padding: 38px 22px;
			border: 2px solid #21fc89;
			margin-bottom: 24px;
		}
		.search-row {
			max-width: 360px;
			margin-bottom: 19px;
		}
		.input.neon-input {
			width: 100%;
			padding: 12px 18px;
			border: 2px solid #21fc89;
			border-radius: 10px;
			background: #10181f;
			color: #21fc89;
			font-size: 1rem;
			transition: border-color 0.2s;
			outline: none;
			box-shadow: 0 0 6px #21fc8955;
		}
		.input.neon-input:focus {
			border-color: #30fcbc;
			box-shadow: 0 0 8px #21fc89BB;
		}
		.table-responsive {
			width: 100%;
			overflow-x: auto;
			margin-top: 12px;
		}
		.table.neon-table {
			width: 100%;
			border-collapse: separate;
			border-spacing: 0;
			background: rgba(15,28,20,0.85);
			border-radius: 13px;
			overflow: hidden;
			color: #ccffd9;
			box-shadow: 0 2px 12px #21fc8922;
		}
		.table.neon-table th, .table.neon-table td {
			padding: 16px 14px;
			text-align: left;
			font-size: 1.05rem;
			border-bottom: 1px solid #214f39;
		}
		.table.neon-table thead th {
			background: rgba(20,36,28,0.9);
			color: #30e688;
			font-weight: 600;
			border-bottom: 2px solid #21fc89;
			text-shadow: 0 0 6px #21fc89;
		}
		.table.neon-table tbody tr {
			transition: background 0.15s;
		}
		.table.neon-table tbody tr:hover {
			background: #123821cc;
			box-shadow: 0 0 8px #21fc89cc;
		}
		.product-img {
			width: 54px;
			height: 54px;
			border-radius: 9px;
			overflow: hidden;
			box-shadow: 0 0 12px #21fc8966;
			background: #111;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.product-img img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			border-radius: 7px;
		}
		.actions {
			display: flex;
			gap: 9px;
			align-items: center;
		}
		::-webkit-scrollbar {
			height: 7px;
			background: #222;
		}
		::-webkit-scrollbar-thumb {
			background: #21fc89aa;
			border-radius: 9px;
		}
		@media (max-width: 768px) {
			.admin-main.dashboard-bg, .neon-card { padding: 3vw 6px!important; }
			.product-img { width:32px; height:32px;}
			.section-header h2 { font-size: 1.35rem;}
			.table.neon-table th, .table.neon-table td { padding: 8px 4px; font-size: 0.95rem;}
		}
	</style>
</head>
<body>
<main class="admin-main dashboard-bg">
    <div class="section-header neon-header">
        <h2>Products</h2>
        <div class="actions">
            <a class="btn neon-btn" href="add_product.php">
                <i class="fa-solid fa-plus"></i> Add Product
            </a>
        </div>
    </div>
    <div class="neon-card">
        <div class="search-row">
            <input class="input neon-input" type="text" id="searchInput" placeholder="Search products...">
        </div>
        <div class="table-responsive">
            <table class="table neon-table" id="productsTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <div class="product-img">
                                    <img src="/systemFinals/<?php echo htmlspecialchars($p['image']); ?>" alt="">
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo format_currency($p['price']); ?></td>
                            <td><?php echo htmlspecialchars($p['category'] ?? '—'); ?></td>
                            <td><?php echo (int)($p['stock'] ?? 0); ?></td>
                            <td class="actions">
                                <a class="btn neon-outline" href="add_product.php?id=<?php echo (int)$p['id']; ?>">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <form method="post" onsubmit="return confirm('Delete this product?');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                    <button class="btn neon-danger" type="submit">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6">No products found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<script>
const searchInput = document.getElementById('searchInput');
const table = document.getElementById('productsTable');
if (searchInput && table) {
    searchInput.addEventListener('input', () => {
        const term = searchInput.value.toLowerCase();
        table.querySelectorAll('tbody tr').forEach(tr => {
            tr.style.display = tr.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>