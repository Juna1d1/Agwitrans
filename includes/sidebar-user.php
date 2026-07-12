<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fa-solid fa-truck"></i>
            <span>AGWI TRANS</span>
        </div>
    </div>
    <button class="toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <a href="Dashboard.php" class="<?= $activePage === 'Dashboard' ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i> <span>Dashboard</span>
    </a>
    <a href="muat.php" class="<?= $activePage === 'muat' ? 'active' : '' ?>">
        <i class="fa-solid fa-box"></i> <span>Muat Barang</span>
    </a>
    <a href="bongkar.php" class="<?= $activePage === 'bongkar' ? 'active' : '' ?>">
        <i class="fa-solid fa-truck-ramp-box"></i> <span>Bongkar Barang</span>
    </a>
    <a href="maintenance.php" class="<?= $activePage === 'maintenance' ? 'active' : '' ?>">
        <i class="fa-solid fa-screwdriver-wrench"></i> <span>Maintenance</span>
    </a>
    <a href="../auth/Logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
</div>
<script>
function toggleSidebar(){
    var s = document.getElementById('sidebar');
    if(!s) return;
    s.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', s.classList.contains('collapsed'));
}
(function(){
    var s = document.getElementById('sidebar');
    if(s && localStorage.getItem('sidebarCollapsed') === 'true') s.classList.add('collapsed');
})();
</script>
