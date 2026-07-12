<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireAdmin();

if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $stmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: users.php");
    exit;
}

if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND status='pending'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: users.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("UPDATE users SET status='deleted' WHERE id=? AND status='approved'");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: users.php");
        exit;
    } else {
        echo "<script>alert('Gagal menghapus user: " . addslashes($conn->error) . "'); window.location='users.php';</script>";
        exit;
    }
}

if (isset($_POST['reset_password'])) {
    $id = $_POST['user_id'];
    $newPassword = trim($_POST['new_password']);
    if ($newPassword == '') {
        echo "<script>alert('Password wajib diisi!'); window.location='users.php';</script>";
        exit;
    }
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hash, $id);
    $stmt->execute();
    echo "<script>alert('Password berhasil direset!'); window.location='users.php';</script>";
    exit;
}

$pending = $conn->query("SELECT * FROM users WHERE status='pending'");
$approved = $conn->query("SELECT * FROM users WHERE status='approved'");
$totalPending = $pending->num_rows;
$totalApproved = $approved->num_rows;

$pageTitle = "Approval User";
$cssFile = "admin";
?>
<?php require "../includes/header.php"; ?>
<div class="admin-wrap">

<?php require "../includes/sidebar-admin.php"; ?>

<div class="content">

    <div class="page-header">
        <h1><i class="fa-solid fa-user-check"></i> Approval User</h1>
        <a href="dashboard.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-info">
                <h3><?= $totalPending ?></h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fa-solid fa-check-circle"></i></div>
            <div class="stat-info">
                <h3><?= $totalApproved ?></h3>
                <p>Approved</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-hourglass-half" style="color:#facc15;"></i>
            User Pending Approval
            <span class="count-badge"><?= $totalPending ?></span>
        </div>

        <?php if ($totalPending > 0): ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>No. hp</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $pending->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= $row['username'] ?></strong></td>
                        <td><?= $row['no_hp'] ?></td>
                        <td><span class="badge badge-warning"><i class="fa-solid fa-clock"></i> Pending</span></td>
                        <td class="text-center">
                            <div class="btn-group" style="justify-content:center;">
                                <a href="?approve=<?= $row['id'] ?>" class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i> Approve</a>
                                <a href="?reject=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Reject user ini?')"><i class="fa-solid fa-times"></i> Reject</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <p>Tidak ada user pending</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-check-circle" style="color:#4ade80;"></i>
            User Approved
            <span class="count-badge"><?= $totalApproved ?></span>
        </div>

        <?php if ($totalApproved > 0): ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>No. Hp</th>
                        <th>Password</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $approved->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= $row['username'] ?></strong></td>
                        <td><?= $row['no_hp'] ?></td>
                        <td><span style="color:#475569;">••••••••</span></td>
                        <td class="text-center"><span class="badge badge-success"><i class="fa-solid fa-check"></i> Approved</span></td>
                        <td class="text-center">
                            <div class="btn-group" style="justify-content:center;">
                                <button class="btn btn-sm btn-warning" onclick="openModal(<?= $row['id'] ?>)"><i class="fa-solid fa-key"></i> Reset</button>
                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus user ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-users"></i>
            <p>Belum ada user yang approved</p>
        </div>
        <?php endif; ?>
    </div>

</div>
</div>

<div id="resetModal" class="modal-overlay">
    <div class="modal-box">
        <button class="close-btn" onclick="closeModal()">&times;</button>
        <h2><i class="fa-solid fa-key" style="color:#f59e0b;"></i> Reset Password</h2>
        <form method="POST">
            <input type="hidden" name="user_id" id="resetUserId">
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="new_password" class="form-control" placeholder="Masukkan password baru" required>
            </div>
            <br>
            <button type="submit" name="reset_password" class="btn btn-warning" style="width:100%;justify-content:center;"><i class="fa-solid fa-save"></i> Simpan Password</button>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById("resetModal").classList.add("active");
    document.getElementById("resetUserId").value = id;
}
function closeModal() {
    document.getElementById("resetModal").classList.remove("active");
}
document.getElementById("resetModal").addEventListener("click", function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>
