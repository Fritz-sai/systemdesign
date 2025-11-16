// Sidebar toggle is handled by admin/includes/sidebar.php script
// This section removed to prevent conflicts
document.addEventListener('DOMContentLoaded', () => {
	// Sidebar initialization handled by sidebar.php
	// No duplicate toggle handler needed
});

;(function(){
	// Sidebar toggle is handled by admin/includes/sidebar.php
	// Check if there's a different sidebar (not adminSidebar) that needs handling
	const sidebar = document.querySelector('.sidebar:not(#adminSidebar)');
	const toggleBtn = sidebar ? document.getElementById('sidebarToggle') : null;
	
	// Only attach if it's NOT the admin sidebar to avoid conflicts
	if (toggleBtn && sidebar && !document.getElementById('adminSidebar')) {
		toggleBtn.addEventListener('click', function(e){
			// Check if adminSidebar exists, if so, don't handle
			if (document.getElementById('adminSidebar')) return;
			
			const collapsed = sidebar.classList.toggle('sidebar--collapsed');
			this.textContent = collapsed ? '⟩' : '⟨';
		});
	}
	
	// Mobile open with "m" key (only for non-admin sidebars)
	document.addEventListener('keydown', function(e){
		if (e.key.toLowerCase() === 'm' && !document.getElementById('adminSidebar')){
			document.querySelector('.sidebar:not(#adminSidebar)')?.classList.toggle('is-open');
		}
	});
	// Modal helpers
	window.openModal = function(id){
		const m = document.getElementById(id);
		if (m){ m.classList.add('is-open'); }
	}
	window.closeModal = function(id){
		const m = document.getElementById(id);
		if (m){ m.classList.remove('is-open'); }
	}
	// Export report (demo)
	window.exportReport = function(){
		alert('Exporting report... (hook up to server-side export)');
	}
	
})();


