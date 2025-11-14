<?php
ob_start(); // Prevent output before headers
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$editingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = null;
if ($editingId > 0) {
	$stmt = $conn->prepare("SELECT id, name, description, price, image, category, stock FROM products WHERE id = ?");
	$stmt->bind_param('i', $editingId);
	$stmt->execute();
	$editing = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim((string)post('name'));
	$price = (float)post('price');
	$description = trim((string)post('description'));
	$category = trim((string)post('category'));
	$stock = (int)post('stock', 0);
	$imagePath = $editing['image'] ?? 'images/placeholder.png';

	// Validate
	if ($name === '' || $price <= 0 || $description === '') {
		$error = 'Please fill in all required fields.';
	} else {
		// Handle image upload if provided
		if (!empty($_FILES['image']['name'])) {
			$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/jpg' => 'jpg'];
			if (!isset($allowed[$_FILES['image']['type']])) {
				$error = 'Invalid image type. Allowed: JPG, PNG.';
			} elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
				$error = 'Image too large. Max 2MB.';
			} else {
				$ext = $allowed[$_FILES['image']['type']];
				$fname = 'admin/assets/uploads/' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
				$abs = __DIR__ . '/../' . $fname; // admin/ + ../ => project root
				$dir = dirname($abs);
				if (!is_dir($dir)) {
					mkdir($dir, 0777, true);
				}
				if (move_uploaded_file($_FILES['image']['tmp_name'], $abs)) {
					$imagePath = $fname;
				} else {
					$error = 'Failed to upload image.';
				}
			}
		}
	}

	if ($error === '') {
		if ($editing) {
			$stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=?, category=?, stock=? WHERE id=?");
			$stmt->bind_param('ssdsiii', $name, $description, $price, $imagePath, $category, $stock, $editingId);
			if ($stmt->execute()) {
				$stmt->close();
			} else {
				$error = 'Failed to update product.';
			}
		} else {
			$stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
			$stmt->bind_param('ssdsii', $name, $description, $price, $imagePath, $category, $stock);
			if ($stmt->execute()) {
				$stmt->close();
			} else {
				$error = 'Failed to add product.';
			}
		}
		if ($error === '') {
			header("Location: products.php");
			exit;
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Admin | <?php echo $editing ? 'Edit Product' : 'Add Product'; ?></title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<style>
	:root{
		--bg: #0b1220;
		--card: linear-gradient(180deg,#0f1724 0%, #0b1220 100%);
		--muted: #97a0b3;
		--accent: #10ff89;
		--accent-2: #08c77a;
		--surface: #0f1724;
		--glass: rgba(255,255,255,0.02);
		--radius: 12px;
		--border: rgba(255,255,255,0.06);
		font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
	}
	body {
		background: #10161d !important;
		color: #e6eef8;
		font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
		min-height: 100vh;
		margin: 0;
	}
	.admin-main {
		padding: 28px;
		min-height: calc(100vh - 72px);
		background: linear-gradient(180deg, rgba(12,16,22,0.4) 0%, transparent 60%), var(--bg);
		color: #e6eef8;
	}
	.section-header {
		display:flex;
		align-items:center;
		justify-content:space-between;
		gap:16px;
		margin-bottom:18px;
	}
	.section-header h2{
		margin:0;
		font-size:1.4rem;
		letter-spacing:-0.2px;
		color:#fff;
	}
	.section-header .actions .btn{
		background:transparent;
		border:1px solid var(--border);
		color:var(--muted);
		padding:10px 14px;
		border-radius:10px;
		font-weight:600;
		display:inline-flex;
		gap:8px;
		align-items:center;
	}
	.card.form-card{
		background: var(--card);
		border: 1px solid var(--border);
		border-radius: var(--radius);
		padding:20px;
		box-shadow: 0 8px 30px rgba(2,6,23,0.6);
		max-width: 980px;
	}
	.form-grid{
		display:grid;
		grid-template-columns: repeat(2, 1fr);
		gap:14px;
	}
	.form-grid .full { grid-column: 1 / -1; }
	.label{
		color:var(--muted);
		font-size:0.85rem;
		margin-bottom:8px;
		font-weight:600;
	}
	.input{
		width:100%;
		background:transparent;
		border:1px solid var(--border);
		color:var(--text, #e6eef8);
		padding:10px 12px;
		border-radius:10px;
		outline:none;
		transition: box-shadow .14s, border-color .12s, transform .12s;
		font-size:0.96rem;
	}
	.input:focus{
		box-shadow: 0 6px 24px rgba(16,255,137,0.06);
		border-color: rgba(16,255,137,0.18);
		transform: translateY(-1px);
	}
	textarea.input{
		min-height:120px;
		resize:vertical;
	}
	.file-uploader{
		display:flex;
		align-items:center;
		gap:12px;
		border-radius:10px;
		padding:8px;
		background: linear-gradient(180deg, rgba(255,255,255,0.01), transparent);
		border:1px dashed rgba(255,255,255,0.03);
	}
	.file-uploader input[type="file"]{
		display:block;
	}
	.file-uploader .hint{
		color:var(--muted);
		font-size:0.9rem;
	}
	.preview{
		display:flex;
		align-items:center;
		gap:12px;
	}
	.preview img{
		width:120px;
		height:120px;
		object-fit:cover;
		border-radius:12px;
		border:1px solid var(--border);
		box-shadow: 0 6px 22px rgba(0,0,0,0.5);
	}
	.preview .noimg{
		display:inline-block;
		width:120px;
		height:120px;
		border-radius:12px;
		background:linear-gradient(180deg,#0d1319,#0b0f14);
		border:1px solid var(--border);
		color:var(--muted);
		display:flex;
		align-items:center;
		justify-content:center;
		font-size:0.9rem;
	}
	.btn{
		padding:10px 14px;
		border-radius:10px;
		border:1px solid var(--border);
		color:var(--muted);
		background:transparent;
		cursor:pointer;
		font-weight:600;
	}
	.btn-primary{
		background:linear-gradient(90deg,var(--accent),var(--accent-2));
		color:#041417;
		border: none;
		box-shadow: 0 8px 30px rgba(16,255,137,0.08);
	}
	@media (max-width:880px){
		.form-grid{ grid-template-columns: 1fr; }
		.preview img, .preview .noimg{ width:96px;height:96px; }
		.admin-main{ padding:16px; }
	}
	</style>
</head>
<body>
<main class="admin-main">
	<div class="section-header">
		<h2><?php echo $editing ? 'Edit Product' : 'Add Product'; ?></h2>
		<div class="actions">
			<a class="btn" href="products.php"><i class="fa-solid fa-arrow-left" style="opacity:0.85"></i> Back</a>
		</div>
	</div>

	<?php if ($error): ?>
		<div class="card" style="border-color:#ef4444;color:#fecaca;padding:12px;margin-bottom:12px;">
			<?php echo htmlspecialchars($error); ?>
		</div>
	<?php endif; ?>

	<div class="card form-card">
		<form method="post" enctype="multipart/form-data" novalidate>
			<div class="form-grid">
				<div>
					<div class="label">Name</div>
					<input class="input" type="text" name="name" required value="<?php echo htmlspecialchars($editing['name'] ?? ''); ?>">
				</div>
				<div>
					<div class="label">Price (USD)</div>
					<input class="input" type="number" name="price" step="0.01" min="0" required value="<?php echo htmlspecialchars($editing['price'] ?? ''); ?>">
				</div>

				<div>
					<div class="label">Category</div>
					<input class="input" type="text" name="category" value="<?php echo htmlspecialchars($editing['category'] ?? ''); ?>">
				</div>
				<div>
					<div class="label">Stock</div>
					<input class="input" type="number" name="stock" min="0" value="<?php echo htmlspecialchars((string)($editing['stock'] ?? 0)); ?>">
				</div>

				<div class="full">
					<div class="label">Description</div>
					<textarea class="input" name="description" required><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
				</div>

				<div>
					<div class="label">Image</div>
					<label class="file-uploader">
						<input class="input-file" type="file" name="image" accept="image/*" aria-label="Product image">
						<span class="hint">Choose an image (JPG/PNG). Max 2MB.</span>
					</label>
				</div>

				<div>
					<div class="label">Preview</div>
					<div class="preview">
						<?php if ($editing && !empty($editing['image'])): ?>
							<img src="<?php echo htmlspecialchars($editing['image']); ?>" alt="<?php echo htmlspecialchars($editing['name'] ?? 'Product'); ?>">
						<?php else: ?>
							<div class="noimg">No image selected</div>
						<?php endif; ?>
						<div style="color:var(--muted);font-size:0.92rem">
							<strong style="color:#fff"><?php echo htmlspecialchars($editing['name'] ?? '—'); ?></strong>
							<div style="margin-top:6px"><?php echo htmlspecialchars(mb_strimwidth($editing['description'] ?? 'No description', 0, 110, '...')); ?></div>
						</div>
					</div>
				</div>
			</div>

			<div style="margin-top:18px;display:flex;gap:12px;align-items:center;justify-content:flex-end">
				<a class="btn" href="products.php">Cancel</a>
				<button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk" style="margin-right:8px"></i> Save</button>
			</div>
		</form>
	</div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
