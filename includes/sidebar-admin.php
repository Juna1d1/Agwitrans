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
    <a href="dashboard.php"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
    <a href="users.php"><i class="fa-solid fa-user-check"></i> <span>Approval User</span></a>
    <a href="kendaraan.php"><i class="fa-solid fa-truck"></i> <span>Tambah Unit</span></a>
    <a href="muat.php"><i class="fa-solid fa-box"></i> <span>Data Muat</span></a>
    <a href="bongkar.php"><i class="fa-solid fa-warehouse"></i> <span>Data Bongkar</span></a>
    <a href="maintenance.php"><i class="fa-solid fa-screwdriver-wrench"></i> <span>Maintenance</span></a>
    <a class="logout" href="../auth/Logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
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
