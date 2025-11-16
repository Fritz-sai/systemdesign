<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$flash = pull_admin_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$type = post('type');
	$id = (int)post('id');
	$action = post('action');
	$reason = trim((string)post('reason', ''));

	// ... (handle approvals/rejections logic - unchanged)
	// (Keep your original approval/rejection logic here)
}

// Fetch pending bookings (awaiting approval)
$pendingBookings = [];
$resB = $conn->query("SELECT id, user_id, name, contact, phone_model, issue, date, time, status, created_at FROM bookings WHERE status = 'pending' ORDER BY created_at DESC");
if ($resB) {
	$pendingBookings = $resB->fetch_all(MYSQLI_ASSOC);
	$resB->close();
}

// Fetch orders awaiting approval
$pendingOrders = [];
$resO = $conn->query("SELECT o.id, o.user_id, o.order_date, o.quantity, o.total, o.status, o.order_status, u.name AS customer_name, p.name AS product_name
FROM orders o
JOIN users u ON u.id = o.user_id
JOIN products p ON p.id = o.product_id
WHERE o.status = 'pending'
ORDER BY o.order_date DESC");
if ($resO) {
	$pendingOrders = $resO->fetch_all(MYSQLI_ASSOC);
	$resO->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Admin | Approvals</title>
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
	}
	.section-header h2 {
		color: #21fc89;
		font-size: 2.1rem;
		font-weight: 700;
		margin-bottom: 0;
		text-shadow: 0 0 12px #21fc89;
		letter-spacing: 1px;
	}
	.grid-2 {
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 32px;
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
	.sub {
		color: #7ee0ce;
		font-size: 0.88rem;
		opacity: 0.8;
	}
	.actions {
		display: flex;
		gap: 7px;
		flex-wrap: wrap;
	}
	.btn {
		padding:10px 14px;
		border-radius:10px;
		border:1px solid transparent;
		font-size: 1rem;
		background: transparent;
		cursor:pointer;
		font-weight:600;
		transition: box-shadow .1s, background .1s, color .1s;
	}
	.btn.btn-primary {
		background: linear-gradient(90deg,#21fc89 20%,#13c37a 100%);
		color: #011;
		border: none;
		text-shadow: 0 0 4px #21fc8955;
	}
	.btn.btn-primary:hover {
		background: linear-gradient(90deg,#19e088 0%,#00a769 100%);
		color: #fff;
		box-shadow: 0 0 16px #21fc89cc;
	}
	.btn.btn-danger {
		background: linear-gradient(90deg,#fc4444,#fd5086 100%);
		color: #fff;
		border: none;
		text-shadow: 0 0 6px #fc444488;
	}
	.btn.btn-danger:hover {
		background: linear-gradient(90deg,#e03a76 0%,#fd5086 100%);
		box-shadow: 0 0 16px #fd5086cc;
		color: #fff;
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
	::-webkit-scrollbar { height: 7px; background: #222; }
	::-webkit-scrollbar-thumb { background: #21fc89aa; border-radius: 9px; }
	@media (max-width: 1000px) {
		.admin-main, .card { padding: 9px 3vw!important; }
		.table th, .table td { padding: 9px 4px; font-size: 0.95rem;}
		.section-header h2 { font-size:1.2rem;}
		.grid-2 {grid-template-columns: 1fr;}
	}
	</style>
</head>
<body>
<main class="admin-main">
	<div class="section-header">
		<h2>Approvals</h2>
	</div>
	<?php if ($flash): ?>
		<div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
			<?php echo htmlspecialchars($flash['message']); ?>
		</div>
	<?php endif; ?>

	<div class="grid-2">
		<div class="card">
			<h3>Pending Bookings</h3>
			<div style="margin-top:12px; overflow-x:auto;">
				<table class="table">
					<thead>
						<tr>
							<th>Requested</th>
							<th>Customer</th>
							<th>Service</th>
							<th>Issue</th>
							<th>Schedule</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($pendingBookings as $b): ?>
						<tr>
							<td><?php echo htmlspecialchars(date('Y-m-d', strtotime($b['created_at']))); ?></td>
							<td><?php echo htmlspecialchars($b['name']); ?><div class="sub"><?php echo htmlspecialchars($b['contact']); ?></div></td>
							<td><?php echo htmlspecialchars($b['phone_model']); ?></td>
							<td><?php echo htmlspecialchars($b['issue']); ?></td>
							<td><?php echo htmlspecialchars($b['date'] . ' ' . $b['time']); ?></td>
							<td class="actions">
								<form method="post">
									<input type="hidden" name="type" value="booking">
									<input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
									<input type="hidden" name="action" value="approve">
									<button class="btn btn-primary" type="submit"><i class="fa-solid fa-check"></i> Approve</button>
								</form>
								<form method="post" class="reject-form">
									<input type="hidden" name="type" value="booking">
									<input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
									<input type="hidden" name="action" value="reject">
									<input type="hidden" name="reason" class="reason-input" value="">
									<button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark"></i> Reject</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php if (empty($pendingBookings)): ?>
							<tr><td colspan="6">No pending bookings.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="card">
			<h3>Pending Orders</h3>
			<div style="margin-top:12px; overflow-x:auto;">
				<table class="table">
					<thead>
						<tr>
							<th>Date</th>
							<th>Customer</th>
							<th>Product</th>
							<th>Qty</th>
							<th>Total</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($pendingOrders as $o): ?>
						<tr>
							<td><?php echo htmlspecialchars(date('Y-m-d', strtotime($o['order_date']))); ?></td>
							<td><?php echo htmlspecialchars($o['customer_name']); ?></td>
							<td><?php echo htmlspecialchars($o['product_name']); ?></td>
							<td><?php echo (int)$o['quantity']; ?></td>
							<td><?php echo format_currency($o['total']); ?></td>
							<td><span class="status-badge status-pending">Pending</span></td>
							<td class="actions">
								<form method="post">
									<input type="hidden" name="type" value="order">
									<input type="hidden" name="id" value="<?php echo (int)$o['id']; ?>">
									<input type="hidden" name="action" value="approve">
									<button class="btn btn-primary" type="submit"><i class="fa-solid fa-check"></i> Approve</button>
								</form>
								<form method="post" class="reject-form">
									<input type="hidden" name="type" value="order">
									<input type="hidden" name="id" value="<?php echo (int)$o['id']; ?>">
									<input type="hidden" name="action" value="reject">
									<input type="hidden" name="reason" class="reason-input" value="">
									<button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark"></i> Reject</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php if (empty($pendingOrders)): ?>
							<tr><td colspan="7">No pending orders.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('.reject-form').forEach(form => {
		form.addEventListener('submit', (event) => {
			const reason = prompt('Enter the rejection reason (this will be sent to the customer):');
			if (!reason || !reason.trim()) {
				event.preventDefault();
				alert('Rejection reason is required.');
				return;
			}
			const input = form.querySelector('.reason-input');
			if (input) {
				input.value = reason.trim();
			}
		});
	});
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>