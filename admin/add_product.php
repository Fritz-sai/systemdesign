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
	@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
	
	:root{
		--bg: #0b1220;
		--card: linear-gradient(180deg,#0f1724 0%, #0b1220 100%);
		--muted: #97a0b3;
		--accent: #10ff89;
		--accent-2: #08c77a;
		--surface: #0f1724;
		--glass: rgba(255,255,255,0.02);
		--radius: 14px;
		--border: rgba(255,255,255,0.08);
		font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
	}
	
	body {
		background: #10161d !important;
		color: #e6eef8;
		font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
		min-height: 100vh;
		margin: 0;
	}
	
	.admin-main {
		padding: 2rem;
		min-height: calc(100vh - 72px);
		background: linear-gradient(180deg, rgba(12,16,22,0.4) 0%, transparent 60%), var(--bg);
		color: #e6eef8;
		max-width: 1200px;
		margin: 0 auto;
	}
	
	.section-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		margin-bottom: 2rem;
		padding-bottom: 1rem;
		border-bottom: 1px solid var(--border);
	}
	
	.section-header h2 {
		margin: 0;
		font-size: 1.75rem;
		font-weight: 800;
		letter-spacing: -0.3px;
		color: #fff;
		background: linear-gradient(135deg, #fff 0%, var(--accent) 100%);
		-webkit-background-clip: text;
		-webkit-text-fill-color: transparent;
		background-clip: text;
	}
	
	.section-header .actions .btn {
		background: transparent;
		border: 1.5px solid var(--border);
		color: var(--muted);
		padding: 0.65rem 1.2rem;
		border-radius: 10px;
		font-weight: 600;
		font-size: 0.95rem;
		display: inline-flex;
		gap: 8px;
		align-items: center;
		transition: all 0.2s ease;
		text-decoration: none;
	}
	
	.section-header .actions .btn:hover {
		border-color: var(--accent);
		color: var(--accent);
		background: rgba(16,255,137,0.05);
		transform: translateY(-1px);
	}
	
	.card.form-card {
		background: var(--card);
		border: 1.5px solid #08c77a;
		border-radius: var(--radius);
		padding: 2rem;
		box-shadow: 0 10px 40px rgba(2,6,23,0.8), 0 0 0 1px rgba(255,255,255,0.02);
		max-width: 1000px;
		margin: 0 auto;
	}
	
	.form-grid {
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 1.5rem;
	}
	
	.form-grid .full { 
		grid-column: 1 / -1; 
	}
	
	.label {
		color: #b8c5d6;
		font-size: 0.9rem;
		margin-bottom: 0.5rem;
		font-weight: 600;
		display: block;
		letter-spacing: 0.01em;
	}
	
	.label::after {
		content: ':';
		margin-left: 2px;
	}
	
	.input {
		width: 90%;
		background: rgba(15, 23, 36, 0.6);
		border: 1.5px solid #08c77a;
		color: #e6eef8;
		padding: 0.85rem 1rem;
		border-radius: 10px;
		outline: none;
		transition: all 0.2s ease;
		font-size: 0.95rem;
		font-family: inherit;
	}
	
	.input:hover {
		border-color: rgba(255,255,255,0.12);
	}
	
	.input:focus {
		box-shadow: 0 0 0 3px rgba(16,255,137,0.1), 0 6px 24px rgba(16,255,137,0.12);
		border-color: var(--accent);
		background: rgba(15, 23, 36, 0.8);
		transform: translateY(-1px);
	}
	
	textarea.input {
		min-height: 130px;
		resize: vertical;
		line-height: 1.6;
	}
	
	.file-upload-area {
		position: relative;
		width: 100%;
	}
	
	.file-uploader {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		gap: 1rem;
		border-radius: 12px;
		padding: 2rem;
		background: linear-gradient(135deg, rgba(16,255,137,0.03) 0%, rgba(16,255,137,0.01) 100%);
		border: 2px dashed var(--border);
		transition: all 0.3s ease;
		cursor: pointer;
		min-height: 160px;
	}
	
	.file-uploader:hover {
		border-color: var(--accent);
		background: linear-gradient(135deg, rgba(16,255,137,0.06) 0%, rgba(16,255,137,0.02) 100%);
		transform: translateY(-2px);
	}
	
	.file-uploader.dragover {
		border-color: var(--accent);
		background: linear-gradient(135deg, rgba(16,255,137,0.1) 0%, rgba(16,255,137,0.05) 100%);
		box-shadow: 0 0 0 3px rgba(16,255,137,0.15);
	}
	
	.file-uploader input[type="file"] {
		position: absolute;
		opacity: 0;
		width: 100%;
		height: 100%;
		cursor: pointer;
		top: 0;
		left: 0;
	}
	
	.file-upload-icon {
		font-size: 2.5rem;
		color: var(--accent);
		opacity: 0.8;
	}
	
	.file-upload-text {
		text-align: center;
	}
	
	.file-upload-text strong {
		color: var(--accent);
		font-weight: 600;
		display: block;
		margin-bottom: 0.25rem;
	}
	
	.file-uploader .hint {
		color: var(--muted);
		font-size: 0.85rem;
		margin-top: 0.5rem;
	}
	
	.preview {
		display: flex;
		flex-direction: column;
		gap: 1rem;
		align-items: flex-start;
	}
	
	.preview-img-container {
		position: relative;
		width: 100%;
		max-width: 200px;
	}
	
	.preview img {
		width: 100%;
		height: 200px;
		object-fit: cover;
		border-radius: 12px;
		border: 2px solid var(--border);
		box-shadow: 0 8px 30px rgba(0,0,0,0.4);
		background: rgba(15, 23, 36, 0.6);
	}
	
	.preview .noimg {
		width: 100%;
		height: 200px;
		border-radius: 12px;
		background: linear-gradient(135deg,#0d1319 0%,#0b0f14 100%);
		border: 2px dashed var(--border);
		color: var(--muted);
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 0.9rem;
		flex-direction: column;
		gap: 0.5rem;
	}
	
	.preview .noimg::before {
		content: '📷';
		font-size: 2rem;
		opacity: 0.5;
	}
	
	.preview-info {
		color: var(--muted);
		font-size: 0.92rem;
		line-height: 1.6;
	}
	
	.preview-info strong {
		color: #fff;
		font-weight: 600;
		display: block;
		margin-bottom: 0.5rem;
		font-size: 1rem;
	}
	
	.error-message {
		background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(239, 68, 68, 0.08) 100%);
		border: 1.5px solid rgba(239, 68, 68, 0.3);
		color: #fecaca;
		padding: 1rem 1.25rem;
		margin-bottom: 1.5rem;
		border-radius: 10px;
		font-size: 0.95rem;
		font-weight: 500;
		display: flex;
		align-items: center;
		gap: 0.75rem;
	}
	
	.error-message::before {
		content: '⚠️';
		font-size: 1.2rem;
	}
	
	.form-actions {
		margin-top: 2rem;
		padding-top: 1.5rem;
		border-top: 1px solid var(--border);
		display: flex;
		gap: 1rem;
		align-items: center;
		justify-content: flex-end;
	}
	
	.btn {
		padding: 0.75rem 1.5rem;
		border-radius: 10px;
		border: 1.5px solid var(--border);
		color: var(--muted);
		background: transparent;
		cursor: pointer;
		font-weight: 600;
		font-size: 0.95rem;
		transition: all 0.2s ease;
		display: inline-flex;
		align-items: center;
		gap: 0.5rem;
		text-decoration: none;
	}
	
	.btn:hover {
		border-color: rgba(255,255,255,0.15);
		color: #fff;
		background: rgba(255,255,255,0.03);
		transform: translateY(-1px);
	}
	
	.btn-primary {
		background: linear-gradient(135deg, var(--accent) 0%, var(--accent-2) 100%);
		color: #041417;
		border: none;
		box-shadow: 0 8px 30px rgba(16,255,137,0.2), 0 0 0 1px rgba(16,255,137,0.1);
		font-weight: 700;
	}
	
	.btn-primary:hover {
		background: linear-gradient(135deg, var(--accent-2) 0%, var(--accent) 100%);
		box-shadow: 0 10px 40px rgba(16,255,137,0.3), 0 0 0 1px rgba(16,255,137,0.15);
		transform: translateY(-2px);
		color: #000;
	}
	
	@media (max-width: 880px) {
		.form-grid { 
			grid-template-columns: 1fr; 
		}
		.preview img, .preview .noimg { 
			height: 160px; 
		}
		.admin-main { 
			padding: 1.5rem; 
		}
		.section-header {
			flex-direction: column;
			align-items: flex-start;
		}
		.form-actions {
			flex-direction: column-reverse;
		}
		.form-actions .btn {
			width: 100%;
			justify-content: center;
		}
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
		<div class="error-message">
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
					<textarea class="input" name="description" required style="width: 95%;"><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
				</div>

				<div class="full">
					<div class="label">Product Image</div>
					<div class="file-upload-area">
						<label class="file-uploader" id="fileUploader">
							<input type="file" name="image" accept="image/jpeg,image/jpg,image/png" id="imageInput" aria-label="Product image">
							<div class="file-upload-icon">
								<i class="fa-solid fa-cloud-arrow-up"></i>
							</div>
							<div class="file-upload-text">
								<strong>Click to upload or drag and drop</strong>
								<span class="hint">JPG or PNG (Max 2MB)</span>
							</div>
						</label>
					</div>
				</div>

				<div class="full">
					<div class="label">Preview</div>
					<div class="preview">
						<div class="preview-img-container">
							<?php if ($editing && !empty($editing['image'])): ?>
								<img id="previewImage" src="<?php echo htmlspecialchars($editing['image']); ?>" alt="<?php echo htmlspecialchars($editing['name'] ?? 'Product'); ?>">
							<?php else: ?>
								<div class="noimg" id="noImagePlaceholder">No image selected</div>
							<?php endif; ?>
						</div>
						<div class="preview-info">
							<strong><?php echo htmlspecialchars($editing['name'] ?? 'Product Name'); ?></strong>
							<div><?php echo htmlspecialchars(mb_strimwidth($editing['description'] ?? 'Product description will appear here...', 0, 120, '...')); ?></div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-actions">
				<a class="btn" href="products.php"><i class="fa-solid fa-times"></i> Cancel</a>
				<button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> <?php echo $editing ? 'Update Product' : 'Save Product'; ?></button>
			</div>
		</form>
	</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const fileInput = document.getElementById('imageInput');
	const fileUploader = document.getElementById('fileUploader');
	const previewImage = document.getElementById('previewImage');
	const noImagePlaceholder = document.getElementById('noImagePlaceholder');
	
	if (fileInput && fileUploader) {
		// Handle file selection
		fileInput.addEventListener('change', function(e) {
			const file = e.target.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = function(e) {
					if (noImagePlaceholder) {
						noImagePlaceholder.style.display = 'none';
					}
					if (previewImage) {
						previewImage.src = e.target.result;
						previewImage.style.display = 'block';
					} else {
						// Create image element if it doesn't exist
						const img = document.createElement('img');
						img.id = 'previewImage';
						img.src = e.target.result;
						img.alt = 'Product preview';
						img.style.display = 'block';
						noImagePlaceholder.parentElement.replaceChild(img, noImagePlaceholder);
					}
				};
				reader.readAsDataURL(file);
			}
		});
		
		// Handle drag and drop
		fileUploader.addEventListener('dragover', function(e) {
			e.preventDefault();
			fileUploader.classList.add('dragover');
		});
		
		fileUploader.addEventListener('dragleave', function(e) {
			e.preventDefault();
			fileUploader.classList.remove('dragover');
		});
		
		fileUploader.addEventListener('drop', function(e) {
			e.preventDefault();
			fileUploader.classList.remove('dragover');
			
			const file = e.dataTransfer.files[0];
			if (file && file.type.startsWith('image/')) {
				fileInput.files = e.dataTransfer.files;
				fileInput.dispatchEvent(new Event('change'));
			}
		});
	}
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
