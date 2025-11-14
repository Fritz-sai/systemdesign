<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Quick stats (week/month/back-end logic unchanged)
$now = new DateTime();
$startOfWeek = (clone $now)->modify('monday this week')->format('Y-m-d 00:00:00');
$startOfMonth = $now->format('Y-m-01 00:00:00');

$qWeek = $conn->prepare("SELECT COALESCE(SUM(quantity),0) qty FROM orders WHERE order_date >= ? AND (order_status IN ('delivered','received') OR status='completed')");
$qWeek->bind_param('s', $startOfWeek);
$qWeek->execute();
$qtyWeek = (int)($qWeek->get_result()->fetch_assoc()['qty'] ?? 0);
$qWeek->close();
$qMonth = $conn->prepare("SELECT COALESCE(SUM(quantity),0) qty FROM orders WHERE order_date >= ? AND (order_status IN ('delivered','received') OR status='completed')");
$qMonth->bind_param('s', $startOfMonth);
$qMonth->execute();
$qtyMonth = (int)($qMonth->get_result()->fetch_assoc()['qty'] ?? 0);
$qMonth->close();

$bWeek = $conn->prepare("SELECT SUM(status='pending') pending, SUM(status IN ('completed','done')) done, COUNT(*) total FROM bookings WHERE created_at >= ? AND status != 'cancelled'");
$bWeek->bind_param('s', $startOfWeek);
$bWeek->execute();
$bW = $bWeek->get_result()->fetch_assoc() ?: ['pending'=>0,'done'=>0,'total'=>0];
$bWeek->close();
$bMonth = $conn->prepare("SELECT SUM(status='pending') pending, SUM(status IN ('completed','done')) done, COUNT(*) total FROM bookings WHERE created_at >= ? AND status != 'cancelled'");
$bMonth->bind_param('s', $startOfMonth);
$bMonth->execute();
$bM = $bMonth->get_result()->fetch_assoc() ?: ['pending'=>0,'done'=>0,'total'=>0];
$bMonth->close();

$ordersTotal = $conn->query("SELECT COUNT(*) c FROM orders WHERE status != 'cancelled' AND order_status != 'cancelled'")->fetch_assoc()['c'] ?? 0;
$usersTotal = $conn->query("SELECT COUNT(*) c FROM users WHERE COALESCE(active,1)=1")->fetch_assoc()['c'] ?? 0;
?>

<!-- MODERN ADMIN DASHBOARD CSS START -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
body {
  background: #101113;
  font-family: 'Inter', Arial, sans-serif;
  color: #eaeef2;
  margin: 0;
}
.dashboard-topbar {
  background: linear-gradient(90deg,#0dffb3 0%,#007d4b 100%);
  color: #021917;
  border-radius: 0 0 2.2rem 2.2rem;
  box-shadow: 0 12px 48px #05ba5d33;
  padding: 2rem 0 1.7rem 0;
  text-align: center;
  margin-bottom: 2rem;
}
.dashboard-topbar .title {
  font-size: 2.6rem;
  font-weight: 800;
  letter-spacing: -0.04em;
  margin: 0 0 0.6rem;
  text-shadow: 0 0 6px #02d96e22;
}
.dashboard-topbar .subtitle {
  font-size: 1.13rem;
  font-weight: 500;
  opacity: 0.8;
  margin-bottom: 0.3rem;
}
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 2.6rem 1.8rem;
  padding: 1rem 2.7vw;
}
.stat-card {
  background: linear-gradient(145deg,#191a1c 85%,#0dffb30b 100%);
  border-radius: 1.6rem;
  box-shadow: 0 6px 32px #0dffb426;
  padding: 2.1rem 2.2rem 1.2rem 2.2rem;
  position: relative;
  border: 1.5px solid #12ff8f22;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  transition: box-shadow 0.18s, border-color .14s, transform .15s;
}
.stat-card:hover {
  box-shadow: 0 18px 44px #11e76a41;
  border-color: #0dffb372;
  transform: translateY(-4px) scale(1.03);
}
.stat-card .stat-title {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  font-size: 1.22rem;
  font-weight: 700;
  color: #04e570;
  margin-bottom: 1.0rem;
  text-shadow: 0 0 6px #08e16a22;
}
.stat-card .icon {
  font-size: 2.1rem;
  color: #09b95b;
}
.stat-card .stat-value {
  font-size: 2.32rem;
  font-weight: 800;
  color: #eafbe6;
  margin-bottom: 0.45rem;
  letter-spacing: 0.01em;
  text-shadow: 0 0 10px #0dffb311;
}
.stat-card .stat-sub {
  font-size: 0.99rem;
  font-weight: 600;
  color: #c2fad7;
  margin-bottom: 0.08rem;
  margin-left: 0.24rem;
}
.stat-card .highlight {
  color: #10ff89;
  background: #16161c;
  border-radius: 0.45rem;
  padding: 0.12rem 0.5rem;
  font-size: 1.09rem;
  font-weight: 700;
  margin-left: .6rem;
  text-shadow: 0 0 8px #00ff6a35;
}
@media (max-width: 1020px) {
  .dashboard-grid { padding: 0 0.5vw; gap: 1.1rem 0.7rem; }
  .stat-card { border-radius: 1rem; padding: 1.15rem 1rem 0.82rem; }
  .dashboard-topbar { border-radius: 0 0 1.15rem 1.15rem; }
}
@media (max-width: 670px) {
  .dashboard-grid { grid-template-columns: 1fr; }
  .dashboard-topbar { padding: 1.2rem 0 0.7rem 0; }
  .dashboard-topbar .title { font-size: 1.79rem;}
}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<!-- MODERN ADMIN DASHBOARD CSS END -->

<main>
  <div class="dashboard-topbar">
    <div class="title">Admin Dashboard</div>
    <div class="subtitle">Your business activity & stats at a glance</div>
  </div>
  <section class="dashboard-grid">
    <div class="stat-card">
      <div class="stat-title"><i class="fa-solid fa-box icon"></i> Products Sold</div>
      <div class="stat-value"><?php echo $qtyWeek; ?> <span class="stat-sub">this week</span></div>
      <div class="stat-sub">This month <span class="highlight"><?php echo $qtyMonth; ?></span></div>
    </div>
    <div class="stat-card">
      <div class="stat-title"><i class="fa-solid fa-calendar-check icon"></i> Bookings</div>
      <div class="stat-value"><?php echo (int)$bW['total']; ?> <span class="stat-sub">this week</span></div>
      <div class="stat-sub">This month <span class="highlight"><?php echo (int)$bM['total']; ?></span></div>
    </div>
    <div class="stat-card">
      <div class="stat-title"><i class="fa-solid fa-clock icon"></i> Pending Bookings</div>
      <div class="stat-value"><?php echo (int)$bW['pending']; ?> <span class="stat-sub">this week</span></div>
      <div class="stat-sub">This month <span class="highlight"><?php echo (int)$bM['pending']; ?></span></div>
    </div>
    <div class="stat-card">
      <div class="stat-title"><i class="fa-solid fa-check-circle icon"></i> Done Bookings</div>
      <div class="stat-value"><?php echo (int)$bW['done']; ?> <span class="stat-sub">this week</span></div>
      <div class="stat-sub">This month <span class="highlight"><?php echo (int)$bM['done']; ?></span></div>
    </div>
    <div class="stat-card">
      <div class="stat-title"><i class="fa-solid fa-receipt icon"></i> Total Orders</div>
      <div class="stat-value"><?php echo (int)$ordersTotal; ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-title"><i class="fa-solid fa-users icon"></i> Total Users</div>
      <div class="stat-value"><?php echo (int)$usersTotal; ?></div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>