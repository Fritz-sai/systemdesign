<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Status update (simple - no proof requirement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'update_status') {
	$id = (int)post('id');
	$status = post('status');
	$allowed = ['pending','out_for_delivery','delivered','received'];
	if ($id > 0 && in_array($status, $allowed, true)) {
		$stmt = $conn->prepare("UPDATE orders SET order_status=? WHERE id=?");
		$stmt->bind_param('si', $status, $id);
		$stmt->execute();
		$stmt->close();
		set_admin_flash('success', 'Order status updated successfully.');
	}
	 if ($allowed === '') {
	header("Location: bookings.php");
	exit;
	 }
}

// Separate proof upload action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'upload_proof') {
	$id = (int)post('id');
	$error = '';
	
	if ($id > 0) {
		if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
			$error = 'Please select a proof photo to upload.';
		} else {
			$file = $_FILES['proof_image'];
			
			// Validate file type (JPG, PNG only)
			$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
			$fileType = mime_content_type($file['tmp_name']);
			
			if (!in_array($fileType, $allowedTypes)) {
				$error = 'Invalid file type. Only JPG and PNG images are allowed.';
			} elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
				$error = 'File size exceeds 5MB limit.';
			} else {
				$uploadDir = __DIR__ . '/../uploads/proofs/';
				if (!is_dir($uploadDir)) {
					mkdir($uploadDir, 0755, true);
				}
				$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
				$fileName = 'proof_' . $id . '_' . time() . '.' . $fileExtension;
				$filePath = $uploadDir . $fileName;
				$relativePath = 'uploads/proofs/' . $fileName;
				if (move_uploaded_file($file['tmp_name'], $filePath)) {
					$stmt = $conn->prepare('SELECT proof_image FROM orders WHERE id = ?');
					$stmt->bind_param('i', $id);
					$stmt->execute();
					$result = $stmt->get_result();
					$order = $result->fetch_assoc();
					$stmt->close();
					if ($order && !empty($order['proof_image']) && file_exists(__DIR__ . '/../' . $order['proof_image'])) {
						@unlink(__DIR__ . '/../' . $order['proof_image']);
					}
					$stmt = $conn->prepare("UPDATE orders SET proof_image=? WHERE id=?");
					$stmt->bind_param('si', $relativePath, $id);
					$stmt->execute();
					$stmt->close();
					set_admin_flash('success', 'Proof photo uploaded successfully.');
				} else {
					$error = 'Failed to upload proof image.';
				}
			}
		}
	} else {
		$error = 'Invalid order ID.';
	}
	
	if ($error) {
		set_admin_flash('error', $error);
	}
	
	header("Location: /systemFinals/admin/orders.php?" . http_build_query($_GET));
	exit;
}

// Filters
$dateFrom = get('from');
$dateTo = get('to');
$where = "1=1";
$params = [];
$types = '';
if ($dateFrom) { $where .= " AND DATE(order_date) >= ?"; $params[] = $dateFrom; $types .= 's'; }
if ($dateTo)   { $where .= " AND DATE(order_date) <= ?"; $params[] = $dateTo;   $types .= 's'; }

$sql = "SELECT o.*, u.name as customer_name, p.name as product_name 
FROM orders o 
JOIN users u ON u.id=o.user_id 
JOIN products p ON p.id=o.product_id
WHERE $where 
  AND o.status != 'cancelled' 
  AND o.order_status != 'cancelled'
  AND NOT (o.order_status = 'delivered' AND o.proof_image IS NOT NULL AND o.proof_image != '' AND LENGTH(TRIM(o.proof_image)) > 0)
ORDER BY o.order_date DESC";

$flash = pull_admin_flash();

if ($params) {
	$stmt = $conn->prepare($sql);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	$stmt->close();
} else {
	$res = $conn->query($sql);
	$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
	if ($res) $res->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Admin | Orders</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<style>
		body, .admin-main {
			background: #10161d !important;
			color: #d9fff8;
			font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
			min-height: 100vh;
			margin: 0;
		}
		.admin-main {
			max-width: 1200px;
			margin: 2rem auto;
			padding: 24px 12px;
			border-radius: 20px;
		}
		.section-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 28px;
			gap:16px;
		}
		.section-header h2 {
			color: #21fc89;
			font-size: 2.1rem;
			font-weight: 700;
			margin-bottom: 0;
			text-shadow: 0 0 12px #21fc89;
			letter-spacing: 1px;
		}
		.search-row {
			display: flex;
			gap: 8px;
			align-items: center;
		}
		.input {
			width: 100%;
			min-width: 108px;
			padding: 10px 18px;
			border: 2px solid #21fc89;
			border-radius: 10px;
			background: #10181f;
			color: #21fc89;
			font-size: 1rem;
			transition: border-color 0.2s;
			outline: none;
			box-shadow: 0 0 6px #21fc8955;
		}
		.input:focus {
			border-color: #30fcbc;
			box-shadow: 0 0 8px #21fc89BB;
		}
		.btn {
			font-weight: 500;
			padding: 10px 16px;
			border-radius: 8px;
			font-size: 1rem;
			cursor: pointer;
			box-shadow: 0 1px 4px #000;
			transition: box-shadow 0.2s, background 0.18s, color 0.18s;
			border: none;
			background: transparent;
		}
		.btn.btn-primary {
			background: linear-gradient(90deg,#21fc89 20%,#13c37a 100%);
			color: #011;
			text-shadow: 0 0 4px #21fc8955;
			border: none;
		}
		.btn.btn-primary:hover {
			background: linear-gradient(90deg,#19e088 0%,#00a769 100%);
			color: #fff;
			box-shadow: 0 0 16px #21fc89cc;
		}
		.btn.btn-outline {
			border: 2px solid #30e688;
			background: transparent;
			color: #30e688;
			text-shadow: 0 0 4px #21fc8955;
			font-size: 0.98rem;
		}
		.btn.btn-outline:hover {
			background: #164f2766;
			box-shadow: 0 0 8px #21fc89bb;
			color: #d9ffed;
		}
		.card {
			background: rgba(20,36,28,0.93);
			border-radius: 17px;
			box-shadow: 0 0 22px #22ff8f22, 0 4px 20px #000;
			padding: 38px 22px;
			border: 2px solid #21fc89;
			margin-bottom: 24px;
		}
		.table {
			width: 100%;
			border-collapse: separate;
			border-spacing: 0;
			background: rgba(15,28,20,0.85);
			border-radius: 13px;
			overflow: hidden;
			color: #ccffd9;
			box-shadow: 0 2px 12px #21fc8922;
		}
		.table th, .table td {
			padding: 16px 10px;
			text-align: left;
			font-size: 1.05rem;
			border-bottom: 1px solid #214f39;
		}
		.table thead th {
			background: rgba(20,36,28,0.92);
			color: #30e688;
			font-weight: 600;
			border-bottom: 2px solid #21fc89;
			text-shadow: 0 0 6px #21fc89;
		}
		.table tbody tr:hover {
			background: #123821cc;
			box-shadow: 0 0 8px #21fc89cc;
		}
		.status-badge {
			display:inline-block;
			padding:6px 13px;
			border-radius:10px;
			color:#041417;
			font-size:0.99rem;
			font-weight:700;
			background:linear-gradient(90deg, #19e088 0%, #21fc89 100%);
			box-shadow:0 2px 18px #19e08833;
			margin-bottom:3px;
			letter-spacing:.5px;
		}
		.status-pending {background:linear-gradient(90deg,#06d6a0,#21fc89);}
		.status-out-for-delivery {background:linear-gradient(90deg,#fae91a,#21fc89);}
		.status-delivered {background:linear-gradient(90deg,#21ffba,#09ff94);}
		.status-received {background:linear-gradient(90deg,#21fc89,#13c37a);}
		.sub {
			color: #7ee0ce;
			font-size: 0.88rem;
			opacity: 0.8;
		}
		.actions {
			display: flex;
			flex-direction: column;
			gap: 8px;
		}
		.alert {
			border-radius: 10px;
			padding: 12px 18px;
			font-size: 1rem;
			font-weight: 600;
			margin-bottom: 18px;
			background: linear-gradient(90deg,#ffe3e3,#21fc89 50%);
			color: #041417;
			box-shadow: 0 0 12px #21fc8922;
		}
		.alert-success { background: linear-gradient(90deg,#e0ffe3,#21fc89 60%);}
		.alert-error { background: linear-gradient(90deg,#ffe3e3,#ff99aa 80%);}
		::-webkit-scrollbar {
			height: 7px;
			background: #222;
		}
		::-webkit-scrollbar-thumb {
			background: #21fc89aa;
			border-radius: 9px;
		}
		@media (max-width: 900px) {
			.admin-main, .card { padding: 9px 3vw!important; }
			.table th, .table td { padding: 9px 4px; font-size: 0.95rem;}
			.section-header h2 { font-size:1.2rem;}
		}
	</style>
</head>
<body>
<main class="admin-main">
	<?php if ($flash): ?>
		<div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" style="margin-bottom: 1rem;">
			<?php echo htmlspecialchars($flash['message']); ?>
		</div>
	<?php endif; ?>
	<div class="section-header">
		<h2>Orders</h2>
		<form class="search-row" method="get">
			<label>From <input class="input" type="date" name="from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>"></label>
			<label>To <input class="input" type="date" name="to" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>"></label>
			<button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
			<a class="btn" href="/systemFinals/admin/orders.php">Reset</a>
		</form>
	</div>

	<div class="card" style="overflow-x:auto;">
		<table class="table">
			<thead>
				<tr>
					<th>Date</th>
					<th>Customer</th>
					<th>Product</th>
					<th>Qty</th>
					<th>Total</th>
					<th>Status</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($orders as $o): ?>
				<tr>
					<td><?php echo htmlspecialchars(date('Y-m-d', strtotime($o['order_date']))); ?></td>
					<td><?php echo htmlspecialchars($o['customer_name']); ?></td>
					<td><?php echo htmlspecialchars($o['product_name']); ?></td>
					<td><?php echo (int)$o['quantity']; ?></td>
					<td><?php echo format_currency($o['total']); ?></td>
					<td>
						<span class="status-badge status-<?php echo htmlspecialchars(str_replace('_','-',$o['order_status'])); ?>">
							<?php echo htmlspecialchars(ucwords(str_replace('_',' ',$o['order_status']))); ?>
						</span>
					</td>
					<td>
						<div class="actions">
							<!-- Status Update Form -->
							<form method="post" style="display: flex; gap: 8px; align-items: center;">
								<input type="hidden" name="action" value="update_status">
								<input type="hidden" name="id" value="<?php echo (int)$o['id']; ?>">
								<select class="input" name="status" style="flex: 1;">
									<?php foreach (['pending','out_for_delivery','delivered','received'] as $st): ?>
										<option value="<?php echo $st; ?>" <?php echo $o['order_status']===$st?'selected':''; ?>>
											<?php echo ucwords(str_replace('_',' ',$st)); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<button class="btn btn-primary" type="submit">Update Status</button>
							</form>
							
							<!-- Proof Upload Form -->
							<form method="post" enctype="multipart/form-data" style="display: flex; gap: 8px; align-items: center;">
								<input type="hidden" name="action" value="upload_proof">
								<input type="hidden" name="id" value="<?php echo (int)$o['id']; ?>">
								<input type="file" name="proof_image" accept="image/jpeg,image/jpg,image/png" class="input" style="flex: 1;">
								<button class="btn btn-outline" type="submit">
									<i class="fa-solid fa-upload"></i> Upload Proof
								</button>
							</form>
							
							<?php if (!empty($o['proof_image'])): ?>
								<a href="/systemFinals/<?php echo htmlspecialchars($o['proof_image']); ?>" target="_blank" class="btn btn-outline" style="font-size: 0.875rem; text-align: center;">
									<i class="fa-solid fa-image"></i> View Proof
								</a>
							<?php endif; ?>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php if (empty($orders)): ?>
					<tr><td colspan="7">No orders found.</td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>