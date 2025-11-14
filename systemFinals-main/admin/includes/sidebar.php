<?php
require_once __DIR__ . '/functions.php';
?>
<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar" aria-hidden="true" aria-label="Admin sidebar">
  <nav>
    <ul>
      <li><a href="dashboard.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a></li>
      <li><a href="products.php"><i class="fa-solid fa-box"></i> <span>Products</span></a></li>
      <li><a href="add_product.php"><i class="fa-solid fa-plus"></i> <span>Add Product</span></a></li>
      <li><a href="approvals.php"><i class="fa-solid fa-check-double"></i> <span>Approvals</span></a></li>
      <li><a href="orders.php"><i class="fa-solid fa-receipt"></i> <span>Orders</span></a></li>
      <li><a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a></li>
      <li><a href="transaction_history.php"><i class="fa-solid fa-history"></i> <span>Transaction History</span></a></li>
      <li><a href="users.php"><i class="fa-solid fa-users"></i> <span>Users</span></a></li>
       <li class="Home Page">
        <a href="../index.php?home=1">
          <i class="fa-solid fa-right-from-bracket"></i> <span>Home Page</span>
        </a>
      </li>
      <li class="logout">
        <a href="../login.php?logout=1">
          <i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span>
        </a>
      </li>
     
    </ul>
  </nav>
</aside>

<!-- Sidebar overlay (click to close) -->
<div class="admin-sidebar-overlay" id="adminSidebarOverlay" tabindex="-1" aria-hidden="true"></div>

<!-- Sidebar styles (scoped here so you don't have to modify your main stylesheet) -->
<style>
	/* Sidebar base (hidden by default) */
	.admin-sidebar {
	  position: fixed;
	  top: 0; left: 0;
	  height: 100vh;
	  width: 220px;
	  background: linear-gradient(140deg, #161824 85%, #09e06c1e 100%);
	  box-shadow: 4px 0 30px #05ba5d18;
	  border-right: 2px solid #09e06c28;
	  color: #eafbe6;
	  z-index: 140;
	  transition: transform 0.23s cubic-bezier(.77,.2,.05,1.0), box-shadow .18s;
	  transform: translateX(-100%); /* hidden by default */
	}
	.admin-sidebar.show { transform: translateX(0); }

	.admin-sidebar nav ul { list-style: none; margin: 0; padding: 1.25rem 0; }
	.admin-sidebar nav ul li { margin: 0.25rem 0; }
	.admin-sidebar nav ul li a {
	  display: flex; align-items:center; gap:0.86rem;
	  padding:0.8rem 1.6rem; color:#caffea; text-decoration:none;
	  font-weight:600; border-radius:1.2rem 0 0 1.2rem; font-size:1.06rem;
	  transition: color .15s, background .15s;
	}
	.admin-sidebar nav ul li a:hover,
	.admin-sidebar nav ul li.active a {
	  background: linear-gradient(95deg, #13ff8b33 65%, #08151322 100%);
	  color: #10ff89; box-shadow: 0 2px 16px #00ff6a15;
	}
	.admin-sidebar nav ul li a .fa-solid {
	  font-size:1.07rem; color:#09e06c; min-width:1.29rem; text-align:center;
	}
	.admin-sidebar nav ul li.logout a {
	  color:#ff5555; background:#231010; border-top:1px solid #2e1313; border-radius:1.2rem; margin-top:1.6rem;
	}
	.admin-sidebar nav ul li.logout a:hover { background:#ff3333; color:#fff; }

	/* Overlay */
	.admin-sidebar-overlay {
	  display:none; position:fixed; inset:0; background:#010f0822; backdrop-filter: blur(2px);
	  z-index:135; transition: opacity 0.18s; opacity:0;
	}
	.admin-sidebar-overlay.show { display:block; opacity:1; }

</style>

<script>
(function () {
  'use strict';

  var btn = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('adminSidebar');
  var overlay = document.getElementById('adminSidebarOverlay');

  if (!btn || !sidebar || !overlay) return;

  function showSidebar() {
    sidebar.classList.add('show');
    sidebar.setAttribute('aria-hidden', 'false');
    overlay.classList.add('show');
    overlay.setAttribute('aria-hidden', 'false');
    btn.setAttribute('aria-expanded', 'true');
    document.documentElement.style.overflow = 'hidden';
  }

  function hideSidebar() {
    sidebar.classList.remove('show');
    sidebar.setAttribute('aria-hidden', 'true');
    overlay.classList.remove('show');
    overlay.setAttribute('aria-hidden', 'true');
    btn.setAttribute('aria-expanded', 'false');
    document.documentElement.style.overflow = '';
  }

  function toggleSidebar() {
    if (sidebar.classList.contains('show')) hideSidebar();
    else showSidebar();
  }

  btn.addEventListener('click', function (e) {
    e.preventDefault();
    toggleSidebar();
  });

  overlay.addEventListener('click', function () {
    hideSidebar();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && sidebar.classList.contains('show')) hideSidebar();
  });

  sidebar.addEventListener('focusout', function (e) {
    if (!sidebar.contains(e.relatedTarget) && sidebar.classList.contains('show') && e.relatedTarget !== btn) {
      hideSidebar();
    }
  });
})();
</script>