<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Filters
$dateFrom = get('from');
$dateTo = get('to');
$type = get('type', 'all'); // 'all', 'bookings', 'orders'

$whereBookings = "status IN ('completed', 'done') AND proof_image IS NOT NULL AND proof_image != '' AND LENGTH(TRIM(proof_image)) > 0";
$whereOrders = "order_status = 'delivered' AND status != 'cancelled' AND proof_image IS NOT NULL AND proof_image != ''";

$paramsBookings = [];
$paramsOrders = [];
$typesBookings = '';
$typesOrders = '';

if ($dateFrom) {
	$whereBookings .= " AND DATE(created_at) >= ?";
	$whereOrders .= " AND DATE(order_date) >= ?";
	$paramsBookings[] = $dateFrom;
	$paramsOrders[] = $dateFrom;
	$typesBookings .= 's';
	$typesOrders .= 's';
}
if ($dateTo) {
	$whereBookings .= " AND DATE(created_at) <= ?";
	$whereOrders .= " AND DATE(order_date) <= ?";
	$paramsBookings[] = $dateTo;
	$paramsOrders[] = $dateTo;
	$typesBookings .= 's';
	$typesOrders .= 's';
}

// Fetch completed bookings
$bookings = [];
if ($type === 'all' || $type === 'bookings') {
	$sqlBookings = "SELECT 
		id,
		'booking' as transaction_type,
		name as customer_name,
		contact,
		phone_model,
		issue,
		date,
		time,
		status,
		created_at as transaction_date,
		NULL as total,
		NULL as quantity,
		NULL as product_name,
		proof_image
	FROM bookings 
	WHERE $whereBookings
	ORDER BY created_at DESC";
	
	if ($paramsBookings) {
		$stmt = $conn->prepare($sqlBookings);
		$stmt->bind_param($typesBookings, ...$paramsBookings);
		$stmt->execute();
		$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
		$stmt->close();
	} else {
		$res = $conn->query($sqlBookings);
		$bookings = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
		if ($res) $res->close();
	}
}

// Fetch delivered orders
$orders = [];
if ($type === 'all' || $type === 'orders') {
	$sqlOrders = "SELECT 
		o.id,
		'order' as transaction_type,
		u.name as customer_name,
		u.email as contact,
		NULL as phone_model,
		NULL as issue,
		NULL as date,
		NULL as time,
		o.order_status as status,
		o.order_date as transaction_date,
		o.total,
		o.quantity,
		p.name as product_name,
		o.proof_image
	FROM orders o
	JOIN users u ON u.id = o.user_id
	JOIN products p ON p.id = o.product_id
	WHERE $whereOrders
	ORDER BY o.order_date DESC";
	
	if ($paramsOrders) {
		$stmt = $conn->prepare($sqlOrders);
		$stmt->bind_param($typesOrders, ...$paramsOrders);
		$stmt->execute();
		$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
		$stmt->close();
	} else {
		$res = $conn->query($sqlOrders);
		$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
		if ($res) $res->close();
	}
}

// Merge and sort by date
$transactions = array_merge($bookings, $orders);
usort($transactions, function($a, $b) {
	return strtotime($b['transaction_date']) - strtotime($a['transaction_date']);
});

// Calculate totals
$totalBookings = count($bookings);
$totalOrders = count($orders);
$totalRevenue = array_sum(array_column($orders, 'total'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Admin | Transaction History</title>
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
		.grid-3 {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 22px;
		}
		.grid-3 .card {
			background: rgba(20,36,28,0.96);
			border-radius: 17px;
			box-shadow: 0 0 22px #22ff8f22, 0 4px 20px #000;
			padding: 24px;
			border: 2px solid #21fc89;
			display: flex;
			flex-direction: column;
			align-items: center;
		}
		.grid-3 .card h3 {
			color: #22ffae;
			margin: 0 0 8px 0;
			font-size: 1.17rem;
			font-weight: 600;
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
		.status-booking {background:linear-gradient(90deg,#21fc89,#30ffcf);}
		.status-order {background:linear-gradient(90deg,#21ffba,#21fc89);}
		.status-pending {background:linear-gradient(90deg,#06d6a0,#21fc89);}
		.status-approved {background:linear-gradient(90deg,#21fc89,#13c37a);}
		.status-in-progress {background:linear-gradient(90deg,#fbff00,#21fc89);}
		.status-done, .status-completed, .status-delivered {background:linear-gradient(90deg,#21ffba,#09ff94);}
		.status-cancelled {background:linear-gradient(90deg,#e83e3e,#fa7b7b);}
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
		.modal {
			display: none;
			position: fixed;
			z-index: 1000;
			left: 0; top: 0;
			width: 100%; height: 100%;
			background-color: rgba(0,0,0,0.5);
		}
		.modal-content {
			background-color: #fff;
			margin: 5% auto;
			padding: 0;
			border: none;
			border-radius: 8px;
			width: 90%;
			max-width: 800px;
			max-height: 90vh;
			overflow: auto;
		}
		@media (max-width: 1000px) {
			.admin-main, .card { padding: 9px 3vw!important; }
			.table th, .table td { padding: 9px 4px; font-size: 0.95rem;}
			.section-header h2 { font-size:1.2rem;}
			.grid-3 {grid-template-columns: 1fr;}
		}
	</style>
</head>
<body>
<main class="admin-main">
	<div class="section-header">
		<h2>Transaction History</h2>
		<form class="search-row" method="get">
			<select class="input" name="type">
				<option value="all" <?php echo $type==='all'?'selected':''; ?>>All Transactions</option>
				<option value="bookings" <?php echo $type==='bookings'?'selected':''; ?>>Bookings Only</option>
				<option value="orders" <?php echo $type==='orders'?'selected':''; ?>>Orders Only</option>
			</select>
			<label>From <input class="input" type="date" name="from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>"></label>
			<label>To <input class="input" type="date" name="to" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>"></label>
			<button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
			<a class="btn" href="transaction_history.php">Reset</a>
		</form>
	</div>

	<!-- Summary Cards -->
	<div class="grid-3" style="margin-bottom: 2rem;">
		<div class="card">
			<h3>Completed Bookings</h3>
			<div class="value" style="font-size: 2rem; margin-top: 0.5rem;"><?php echo $totalBookings; ?></div>
		</div>
		<div class="card">
			<h3>Delivered Orders</h3>
			<div class="value" style="font-size: 2rem; margin-top: 0.5rem;"><?php echo $totalOrders; ?></div>
		</div>
		<div class="card">
			<h3>Total Revenue</h3>
			<div class="value" style="font-size: 2rem; margin-top: 0.5rem;"><?php echo format_currency($totalRevenue); ?></div>
		</div>
	</div>

	<div class="card" style="overflow-x:auto;">
		<table class="table">
			<thead>
				<tr>
					<th>Date</th>
					<th>Type</th>
					<th>Customer</th>
					<th>Details</th>
					<th>Status</th>
					<th>Amount</th>
					<th>Proof</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($transactions as $t): ?>
				<tr>
					<td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($t['transaction_date']))); ?></td>
					<td>
						<span class="status-badge <?php echo $t['transaction_type'] === 'booking' ? 'status-approved' : 'status-delivered'; ?>">
							<?php echo $t['transaction_type'] === 'booking' ? '📅 Booking' : '📦 Order'; ?>
						</span>
					</td>
					<td>
						<?php echo htmlspecialchars($t['customer_name']); ?>
						<?php if ($t['contact']): ?>
							<div class="sub"><?php echo htmlspecialchars($t['contact']); ?></div>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($t['transaction_type'] === 'booking'): ?>
							<strong><?php echo htmlspecialchars($t['phone_model']); ?></strong>
							<div class="sub"><?php echo htmlspecialchars($t['issue']); ?></div>
							<?php if ($t['date'] && $t['time']): ?>
								<div class="sub">Scheduled: <?php echo htmlspecialchars($t['date'] . ' ' . $t['time']); ?></div>
							<?php endif; ?>
						<?php else: ?>
							<strong><?php echo htmlspecialchars($t['product_name']); ?></strong>
							<div class="sub">Qty: <?php echo (int)$t['quantity']; ?></div>
						<?php endif; ?>
					</td>
					<td>
						<span class="status-badge status-<?php echo htmlspecialchars(str_replace('_','-',$t['status'])); ?>">
							<?php echo htmlspecialchars(ucwords(str_replace('_',' ',$t['status']))); ?>
						</span>
					</td>
					<td>
						<?php if ($t['transaction_type'] === 'order' && $t['total']): ?>
							<strong><?php echo format_currency($t['total']); ?></strong>
						<?php else: ?>
							<span class="sub">—</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if (!empty($t['proof_image'])): ?>
							<button type="button" class="btn btn-outline view-proof-btn" data-proof-path="/systemFinals/<?php echo htmlspecialchars($t['proof_image']); ?>" style="font-size: 0.875rem;">
								<i class="fa-solid fa-image"></i> View Proof
							</button>
						<?php else: ?>
							<span class="sub">—</span>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php if (empty($transactions)): ?>
					<tr><td colspan="7">No completed transactions found.</td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</main>

<!-- View Proof Modal -->
<div id="viewProofModal" class="modal">
	<div class="modal-content">
		<div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #e5e7eb;">
			<h3 style="margin: 0;">Proof of Delivery</h3>
			<button type="button" class="modal-close" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666;">&times;</button>
		</div>
		<div style="padding: 1rem; text-align: center;">
			<img id="proof_viewer_img" src="" alt="Proof of delivery" style="max-width: 100%; height: auto; border-radius: 4px;">
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const viewProofButtons = document.querySelectorAll('.view-proof-btn');
	const viewProofModal = document.getElementById('viewProofModal');
	const proofViewerImg = document.getElementById('proof_viewer_img');
	const modalClose = document.querySelector('#viewProofModal .modal-close');
	
	viewProofButtons.forEach(btn => {
		btn.addEventListener('click', () => {
			const proofPath = btn.getAttribute('data-proof-path');
			if (proofPath) {
				proofViewerImg.src = proofPath;
				viewProofModal.style.display = 'block';
			}
		});
	});
	if (modalClose) {
		modalClose.addEventListener('click', () => {
			viewProofModal.style.display = 'none';
		});
	}
	if (viewProofModal) {
		viewProofModal.addEventListener('click', (e) => {
			if (e.target === viewProofModal) {
				viewProofModal.style.display = 'none';
			}
		});
	}
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && viewProofModal.style.display === 'block') {
			viewProofModal.style.display = 'none';
		}
	});
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>