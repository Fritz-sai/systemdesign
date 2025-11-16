<?php
require_once __DIR__ . '/functions.php';
ensure_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Panel</title>

	<!-- Main stylesheet (keep using your existing styles) -->
	<link rel="stylesheet" href="style.css">

	<!-- Fonts & icons -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

	<!-- Header-scoped styles (keeps your main stylesheet untouched) -->
	<style>
		:root{
			--hdr-bg: #0b1220;
			--hdr-border: rgba(255,255,255,0.03);
			--muted: #98a0b3;
			--text: #e6eef8;
			--radius: 8px;
			font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
		}
		.admin-header{
			display:flex;
			align-items:center;
			justify-content:space-between;
			gap:1rem;
			padding:0.75rem 1rem;
			background:var(--hdr-bg);
			border-bottom:1px solid var(--hdr-border);
			position:relative;
			z-index:120;
		}
		.branding{display:flex;align-items:center;gap:0.75rem}
		.branding h1{margin:0;font-size:1rem;font-weight:600;color:var(--text)}
		.sidebar-toggle{
			background:transparent;border:0;color:var(--text);font-size:1.1rem;padding:0.4rem;border-radius:6px;cursor:pointer;
			display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;
		}
		.sidebar-toggle:focus{outline:2px solid rgba(79,70,229,0.18)}

		.admin-nav{display:flex;align-items:center;gap:1rem;flex:1;justify-content:center}
		.admin-nav-list{display:flex;gap:0.75rem;align-items:center;margin:0;padding:0;list-style:none}
		.admin-nav-list a{
			color:var(--muted);
			text-decoration:none;
			padding:0.45rem 0.7rem;
			border-radius:6px;
			display:inline-flex;
			align-items:center;
			gap:0.5rem;
			font-weight:500;
			font-size:0.95rem;
		}
		.admin-nav-list a:hover,
		.admin-nav-list a.active{ color:var(--text); background:rgba(255,255,255,0.02) }

		.header-search{display:flex;align-items:center;gap:0.5rem}
		.header-search input[type="search"]{
			background:transparent;border:1px solid rgba(255,255,255,0.04);color:var(--text);padding:0.45rem 0.6rem;border-radius:6px;
			min-width:160px;
		}
		.header-actions{display:flex;align-items:center;gap:0.6rem}
		.welcome{color:var(--muted);font-weight:500;font-size:0.95rem}

		details.profile{position:relative}
		details.profile summary{
			list-style:none;cursor:pointer;display:inline-flex;align-items:center;gap:0.6rem;border-radius:6px;padding:0.35rem 0.5rem;
			border:1px solid rgba(255,255,255,0.03);background:transparent;color:var(--text);
		}
		details.profile summary::-webkit-details-marker{display:none}
		details.profile .menu{
			position:absolute;right:0;top:calc(100% + 8px);background:var(--hdr-bg);box-shadow:0 8px 30px rgba(2,6,23,0.6);
			border:1px solid var(--hdr-border);padding:0.5rem;border-radius:8px;min-width:160px;z-index:130;
		}
		.menu a{display:block;padding:0.5rem 0.6rem;color:var(--muted);text-decoration:none;border-radius:6px;}
		.menu a:hover{color:var(--text);background:rgba(255,255,255,0.02)}

		@media (max-width:900px){
			.admin-nav{display:none}
			.header-search input[type="search"]{min-width:120px}
		}

		.sr-only{position:absolute!important;height:1px;width:1px;overflow:hidden;clip:rect(1px,1px,1px,1px);white-space:nowrap}

		/* Added styles for the search button to ensure it's visible and functional */
		.header-search .btn {
			background: transparent;
			border: 0;
			color: var(--muted);
			cursor: pointer;
			padding: 0.45rem;
			border-radius: 6px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			transition: color 0.15s, background 0.15s;
		}
		.header-search .btn:hover {
			color: var(--text);
			background: rgba(255,255,255,0.02);
		}
	</style>
</head>
<body>
	<header class="admin-header">
		<div class="branding">
			<!-- This button toggles the sidebar. Keep the id 'sidebarToggle' so the sidebar script can find it. -->
			<button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu" title="Toggle sidebar" aria-expanded="false">
				<i class="fa-solid fa-bars" aria-hidden="true"></i>
			</button>
			<h1>Admin</h1>
		</div>

		<!-- Primary nav -->
		<nav class="admin-nav" aria-label="Primary admin navigation">
			<ul class="admin-nav-list">
				<li><a href="dashboard.php" class="active"><i class="fa-solid fa-gauge" aria-hidden="true"></i>Dashboard</a></li>
				<li><a href="users.php"><i class="fa-solid fa-users" aria-hidden="true"></i>Users</a></li>
			</ul>
		</nav>

		<!-- Search + actions -->
		<div style="display:flex;align-items:center;gap:0.75rem;">

			<div class="header-actions" role="region" aria-label="User actions">
				<span class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></span>

				<details class="profile" aria-label="Profile menu">
					<summary aria-haspopup="true" aria-expanded="false">
						<i class="fa-solid fa-user" aria-hidden="true"></i>
						<span class="sr-only"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></span>
					</summary>
					<div class="menu" role="menu">
						<a href="profile.php" role="menuitem"><i class="fa-solid fa-id-badge" style="width:18px;text-align:center"></i> Profile Settings</a>
					</div>
				</details>
			</div>
		</div>
	</header>

