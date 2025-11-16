<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Actions: delete, deactivate/activate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$act = post('action');
	$id = (int)post('id');
	if ($id > 0) {
		if ($act === 'delete') {
			$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$stmt->close();
		} elseif ($act === 'deactivate') {
			$stmt = $conn->prepare("UPDATE users SET active=0 WHERE id=?");
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$stmt->close();
		} elseif ($act === 'activate') {
			$stmt = $conn->prepare("UPDATE users SET active=1 WHERE id=?");
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$stmt->close();
		}
	}
	header("Location: /systemFinals/admin/users.php");
	exit;
}

$res = $conn->query("SELECT id, name, email, role, created_at, COALESCE(active,1) active FROM users ORDER BY created_at DESC");
$users = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
if ($res) $res->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Admin | Users</title>
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
	.status-approved {background:linear-gradient(90deg,#21fc89,#13c37a);}
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
	.btn {
		padding:10px 14px;
		border-radius:10px;
		border:1px solid transparent;
		font-size: 1rem;
		background: transparent;
		cursor:pointer;
		font-weight:600;
		transition: box-shadow .1s, background .1s, color .1s;
		width: 100%;
		text-align: center;
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
	::-webkit-scrollbar { height: 7px; background: #222; }
	::-webkit-scrollbar-thumb { background: #21fc89aa; border-radius: 9px; }
	@media (max-width: 900px) {
		.admin-main, .card { padding: 9px 3vw!important; }
		.table th, .table td { padding: 9px 4px; font-size: 0.95rem;}
		.section-header h2 { font-size:1.2rem;}
	}
	</style>
</head>
<body>
<main class="admin-main">
	<div class="section-header">
		<h2>Users</h2>
	</div>
	<div class="card" style="overflow-x:auto;">
		<table class="table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Role</th>
					<th>Registered</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($users as $u): ?>
					<tr>
						<td><?php echo htmlspecialchars($u['name']); ?></td>
						<td><?php echo htmlspecialchars($u['email']); ?></td>
						<td><?php echo htmlspecialchars($u['role']); ?></td>
						<td><?php echo htmlspecialchars(date('Y-m-d', strtotime($u['created_at']))); ?></td>
						<td>
							<span class="status-badge <?php echo $u['active'] ? 'status-approved' : 'status-cancelled'; ?>">
								<?php echo $u['active'] ? 'Active' : 'Deactivated'; ?>
							</span>
						</td>
						<td class="actions">
							<?php if ((int)$u['id'] !== (int)($_SESSION['user_id'] ?? -1)): ?>
								<form method="post" onsubmit="return confirm('Delete this user?');">
									<input type="hidden" name="action" value="delete">
									<input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
									<button class="btn btn-danger" type="submit"><i class="fa-solid fa-trash"></i> Delete</button>
								</form>
								<?php if ($u['active']): ?>
									<form method="post">
										<input type="hidden" name="action" value="deactivate">
										<input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
										<button class="btn" type="submit"><i class="fa-solid fa-user-slash"></i> Deactivate</button>
									</form>
								<?php else: ?>
									<form method="post">
										<input type="hidden" name="action" value="activate">
										<input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
										<button class="btn btn-primary" type="submit"><i class="fa-solid fa-user-check"></i> Activate</button>
									</form>
								<?php endif; ?>
							<?php else: ?>
								<span class="sub">You</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				<?php if (empty($users)): ?>
					<tr><td colspan="6">No users found.</td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
